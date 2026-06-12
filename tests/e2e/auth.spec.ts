/**
 * E2E: Authentication flows
 * mirrors cms/src/views/LoginView.vue + cms/src/stores/auth.ts
 */
import { test, expect } from '@playwright/test'

const ADMIN_EMAIL    = process.env.E2E_ADMIN_EMAIL    ?? 'e2e-admin@example.com'
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'test-password-e2e'

test.describe('Login / Logout', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login')
  })

  test('shows login form', async ({ page }) => {
    await expect(page.locator('input[type="email"], input[name="email"]')).toBeVisible()
    await expect(page.locator('input[type="password"]')).toBeVisible()
    await expect(page.locator('button[type="submit"]')).toBeVisible()
  })

  test('redirects to dashboard after successful login', async ({ page }) => {
    await page.locator('input[type="email"], input[name="email"]').fill(ADMIN_EMAIL)
    await page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
    await page.locator('button[type="submit"]').click()

    await expect(page).toHaveURL(/dashboard/, { timeout: 10_000 })
  })

  test('stores tokens in localStorage after login', async ({ page }) => {
    await page.locator('input[type="email"], input[name="email"]').fill(ADMIN_EMAIL)
    await page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
    await page.locator('button[type="submit"]').click()

    await page.waitForURL(/dashboard/, { timeout: 10_000 })

    const accessToken = await page.evaluate(() => localStorage.getItem('access_token'))
    expect(accessToken).toBeTruthy()
  })

  test('shows error for wrong credentials', async ({ page }) => {
    await page.locator('input[type="email"], input[name="email"]').fill('wrong@example.com')
    await page.locator('input[type="password"]').fill('wrongpassword')
    await page.locator('button[type="submit"]').click()

    // Should remain on login page
    await expect(page).toHaveURL(/login/, { timeout: 5_000 })
  })

  test('redirects unauthenticated user to login', async ({ page }) => {
    await page.evaluate(() => {
      localStorage.removeItem('access_token')
      localStorage.removeItem('refresh_token')
    })
    await page.goto('/dashboard')
    await expect(page).toHaveURL(/login/, { timeout: 5_000 })
  })

  test('redirects authenticated user away from login', async ({ page }) => {
    // Login first
    await page.locator('input[type="email"], input[name="email"]').fill(ADMIN_EMAIL)
    await page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
    await page.locator('button[type="submit"]').click()
    await page.waitForURL(/dashboard/, { timeout: 10_000 })

    // Navigating to /login should redirect back to dashboard
    await page.goto('/login')
    await expect(page).toHaveURL(/dashboard/, { timeout: 5_000 })
  })

  test('logout clears session and redirects to login', async ({ page }) => {
    // Login
    await page.locator('input[type="email"], input[name="email"]').fill(ADMIN_EMAIL)
    await page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
    await page.locator('button[type="submit"]').click()
    await page.waitForURL(/dashboard/, { timeout: 10_000 })

    // Logout — AppHeader renders a button with text "ログアウト"
    const logoutBtn = page.getByRole('button', { name: 'ログアウト' })
    await logoutBtn.click()

    await expect(page).toHaveURL(/login/, { timeout: 5_000 })
    const token = await page.evaluate(() => localStorage.getItem('access_token'))
    expect(token).toBeNull()
  })
})
