import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './e2e/tests',
  outputDir: './e2e/results',
  globalSetup: './e2e/setup/global-setup.ts',
  timeout: 30000,
  retries: 1,
  reporter: 'list',
  use: {
    baseURL: process.env.APP_URL || 'http://localhost',
    storageState: 'e2e/.auth/user.json',
    actionTimeout: 15000,
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'], headless: true },
    },
  ],
});
