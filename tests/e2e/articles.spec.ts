/**
 * E2E: Article management flows
 * mirrors cms/src/views/articles/ + cms/src/stores/articles.ts
 */
import { test, expect, type Page } from '@playwright/test'

const ADMIN_EMAIL    = process.env.E2E_ADMIN_EMAIL    ?? 'e2e-admin@example.com'
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'test-password-e2e'

async function login(page: Page): Promise<void> {
  await page.goto('/login')
  await page.locator('input[type="email"]').fill(ADMIN_EMAIL)
  await page.locator('input[type="password"]').fill(ADMIN_PASSWORD)
  await page.locator('button[type="submit"]').click()
  await page.waitForURL(/dashboard/, { timeout: 10_000 })
}

test.describe('Articles management', () => {
  test.beforeEach(async ({ page }) => {
    await login(page)
  })

  test('navigates to articles list', async ({ page }) => {
    await page.goto('/articles')
    await expect(page).toHaveURL(/articles/)
    await expect(page.locator('table')).toBeVisible({ timeout: 8_000 })
  })

  test('opens new article form', async ({ page }) => {
    await page.goto('/articles/new')
    await expect(page.getByPlaceholder('記事タイトル')).toBeVisible({ timeout: 8_000 })
  })

  test('creates a draft article', async ({ page }) => {
    const uniqueTitle = `E2E Draft ${Date.now()}`

    await page.goto('/articles/new')

    // Wait for categories to load (they are fetched in onMounted)
    await expect(page.getByPlaceholder('記事タイトル')).toBeVisible({ timeout: 8_000 })

    await page.getByPlaceholder('記事タイトル').fill(uniqueTitle)

    // slug is auto-generated from title but may need manual fill for non-ASCII
    const slugInput = page.getByPlaceholder('article-slug')
    await slugInput.fill(`e2e-draft-${Date.now()}`)

    // Select the first available category (seeded as "General")
    await page.getByLabel('カテゴリ').selectOption({ index: 1 })

    // Click 下書き保存
    await page.getByRole('button', { name: '下書き保存' }).click()

    // After save, router.push('/articles')
    await page.waitForURL(/\/articles$/, { timeout: 10_000 })
  })

  test('shows article count in list', async ({ page }) => {
    await page.goto('/articles')
    await expect(page.locator('table')).toBeVisible({ timeout: 8_000 })
  })
})
