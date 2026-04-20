import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
	testDir: './e2e/tests',
	outputDir: './e2e/results',
	globalSetup: './e2e/setup/global-setup.ts',
	timeout: 15000,
	retries: 1,
	reporter: 'list',
	use: {
		baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000',
		storageState: 'e2e/.auth/user.json',
		actionTimeout: 5000,
		trace: 'on-first-retry',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],
	webServer: {
		command: 'bash e2e/start-server.sh',
		url: 'http://127.0.0.1:8000',
		reuseExistingServer: !process.env.CI,
		timeout: 120000,
	},
});
