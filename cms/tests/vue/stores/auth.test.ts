/**
 * mirrors cms/src/stores/auth.ts
 */
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '@/stores/auth'

vi.mock('@/api/client', () => ({
  authApi: {
    login: vi.fn(),
    logout: vi.fn(),
    me: vi.fn(),
    getProfile: vi.fn(),
    updateProfile: vi.fn(),
  },
}))

import { authApi } from '@/api/client'

const mockUser = {
  id: 1,
  name: 'Admin',
  email: 'admin@example.com',
  role: 'admin' as const,
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
}

describe('useAuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    localStorage.clear()
    vi.clearAllMocks()
  })

  it('initialises with no user when localStorage is empty', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.accessToken).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('reads token from localStorage on initialisation', () => {
    localStorage.setItem('access_token', 'existing-token')
    const store = useAuthStore()
    expect(store.accessToken).toBe('existing-token')
    expect(store.isAuthenticated).toBe(true)
  })

  it('login stores tokens and user in state and localStorage', async () => {
    vi.mocked(authApi.login).mockResolvedValue({
      access_token: 'access-abc',
      refresh_token: 'refresh-xyz',
      token_type: 'Bearer',
      expires_in: 3600,
      user: mockUser,
    })

    const store = useAuthStore()
    await store.login({ email: 'admin@example.com', password: 'secret' })

    expect(store.accessToken).toBe('access-abc')
    expect(store.refreshToken).toBe('refresh-xyz')
    expect(store.user).toEqual(mockUser)
    expect(localStorage.getItem('access_token')).toBe('access-abc')
    expect(localStorage.getItem('refresh_token')).toBe('refresh-xyz')
  })

  it('logout clears state and localStorage', async () => {
    localStorage.setItem('access_token', 'token')
    localStorage.setItem('refresh_token', 'refresh')

    vi.mocked(authApi.logout).mockResolvedValue(undefined as never)

    const store = useAuthStore()
    store.user = mockUser

    await store.logout()

    expect(store.accessToken).toBeNull()
    expect(store.refreshToken).toBeNull()
    expect(store.user).toBeNull()
    expect(localStorage.getItem('access_token')).toBeNull()
    expect(localStorage.getItem('refresh_token')).toBeNull()
  })

  it('logout clears state even when API call fails', async () => {
    vi.mocked(authApi.logout).mockRejectedValue(new Error('network error'))
    localStorage.setItem('access_token', 'token')

    const store = useAuthStore()
    store.user = mockUser

    // try-finally (no catch): finally runs clearing state, but error propagates
    await store.logout().catch(() => {})

    expect(store.user).toBeNull()
    expect(store.accessToken).toBeNull()
  })

  it('rehydrate fetches user when token exists but user is null', async () => {
    localStorage.setItem('access_token', 'token')
    vi.mocked(authApi.me).mockResolvedValue(mockUser)

    const store = useAuthStore()
    await store.rehydrate()

    expect(store.user).toEqual(mockUser)
    expect(vi.mocked(authApi.me)).toHaveBeenCalledOnce()
  })

  it('rehydrate clears tokens when API returns error', async () => {
    localStorage.setItem('access_token', 'expired-token')
    vi.mocked(authApi.me).mockRejectedValue(new Error('401'))

    const store = useAuthStore()
    await store.rehydrate()

    expect(store.user).toBeNull()
    expect(store.accessToken).toBeNull()
    expect(localStorage.getItem('access_token')).toBeNull()
  })

  it('rehydrate skips API call when user is already loaded', async () => {
    localStorage.setItem('access_token', 'token')
    const store = useAuthStore()
    store.user = mockUser

    await store.rehydrate()

    expect(vi.mocked(authApi.me)).not.toHaveBeenCalled()
  })

  it('rehydrate skips API call when no access token', async () => {
    const store = useAuthStore()
    await store.rehydrate()

    expect(vi.mocked(authApi.me)).not.toHaveBeenCalled()
  })

  it('fetchProfile loads the current user into state', async () => {
    const profile = { ...mockUser, avatar: '/img/a.png', bio: 'hello' }
    vi.mocked(authApi.getProfile).mockResolvedValue(profile)

    const store = useAuthStore()
    const result = await store.fetchProfile()

    expect(store.user).toEqual(profile)
    expect(result).toEqual(profile)
  })

  it('updateProfile saves and refreshes the user in state', async () => {
    const updated = { ...mockUser, name: 'Renamed', bio: 'updated' }
    vi.mocked(authApi.updateProfile).mockResolvedValue(updated)

    const store = useAuthStore()
    store.user = mockUser
    await store.updateProfile({ name: 'Renamed', email: mockUser.email, avatar: '', bio: 'updated' })

    expect(vi.mocked(authApi.updateProfile)).toHaveBeenCalledOnce()
    expect(store.user).toEqual(updated)
  })

  it('updateProfile forwards SNS fields', async () => {
    const updated = { ...mockUser, instagram_url: 'https://instagram.com/test', twitter_url: 'https://x.com/test', tiktok_url: 'https://tiktok.com/@test' }
    vi.mocked(authApi.updateProfile).mockResolvedValue(updated)

    const store = useAuthStore()
    store.user = mockUser
    await store.updateProfile({
      name: mockUser.name,
      email: mockUser.email,
      avatar: '',
      bio: '',
      instagram_url: 'https://instagram.com/test',
      twitter_url: 'https://x.com/test',
      tiktok_url: 'https://tiktok.com/@test',
    })

    expect(store.user).toEqual(updated)
    expect(store.user?.instagram_url).toBe('https://instagram.com/test')
  })
})
