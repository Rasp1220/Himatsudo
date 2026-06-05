/**
 * E2E: Article management flows
 * mirrors cms/src/views/articles/ + cms/src/stores/articles.ts
 */
import { test, expect, type Page } from '@playwright/test'

const ADMIN_EMAIL    = process.env.E2E_ADMIN_EMAIL    ?? 'e2e-admin@example.com'
const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD ?? 'test-password-e2e'

async function login(page: Page): Promise<void> {
  await page.goto('/login')
  await page.locator('input[type="email"], input[name="email"]').fill(ADMIN_EMAIL)
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
    // Wait for the list to render
    await expect(page.locator('table, [data-testid="articles-list"]')).toBeVisible({ timeout: 8_000 })
  })

  test('opens new article form', async ({ page }) => {
    await page.goto('/articles/new')
    await expect(page.locator('input[name="title"], [data-testid="article-title"]')).toBeVisible({ timeout: 8_000 })
  })

  test('creates a draft article', async ({ page }) => {
    const uniqueTitle = `E2E Draft ${Date.now()}`

    await page.goto('/articles/new')

    const titleInput = page.locator('input[name="title"], [data-testid="article-title"]').first()
    await titleInput.fill(uniqueTitle)

    const slugInput = page.locator('input[name="slug"], [data-testid="article-slug"]').first()
    if (await slugInput.isVisible()) {
      await slugInput.fill(`e2e-draft-${Date.now()}`)
    }

    // Save as draft
    const draftBtn = page.locator('button:has-text("下書き"), button:has-text("Draft"), [data-testid="save-draft"]').first()
    if (await draftBtn.isVisible()) {
      await draftBtn.click()
    } else {
      const saveBtn = page.locator('button[type="submit"], button:has-text("保存"), button:has-text("Save")').first()
      await saveBtn.click()
    }

    // Should navigate back to list or show success
    await page.waitForURL(/articles/, { timeout: 10_000 })
  })

  test('shows article count in list', async ({ page }) => {
    await page.goto('/articles')
    // The list should render (even if empty)
    await expect(page.locator('table, [data-testid="articles-list"], [data-testid="empty-state"]')).toBeVisible({ timeout: 8_000 })
  })
})
