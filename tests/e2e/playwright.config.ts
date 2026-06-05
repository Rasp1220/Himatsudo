import { defineConfig, devices } from '@playwright/test'

const CMS_URL = process.env.E2E_CMS_URL ?? 'http://localhost:5173'
const API_URL = process.env.E2E_API_URL ?? 'http://localhost:8080'

export default defineConfig({
  testDir: '.',
  testMatch: '**/*.spec.ts',
  timeout: 30_000,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],

  use: {
    baseURL: CMS_URL,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  webServer: [
    {
      command: `cd ../.. && php -S localhost:8080 -t public`,
      url: `${API_URL}/admin/api/auth/me`,
      reuseExistingServer: !process.env.CI,
      timeout: 15_000,
    },
    {
      command: `cd ../../cms && npm run dev`,
      url: CMS_URL,
      reuseExistingServer: !process.env.CI,
      timeout: 30_000,
    },
  ],
})
