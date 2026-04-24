import type { Locator, Page } from '@playwright/test';

export class DashboardPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/dashboard', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.getByRole('heading', { name: 'Dashboard' }).waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', { name: 'Dashboard' });
		return heading.innerText();
	}

	// ---------------------------------------------------------------------------
	// Summary Cards
	// ---------------------------------------------------------------------------

	async getSummaryCardValue(label: string): Promise<string> {
		const card = this.page.locator('[data-slot="card"]').filter({
			has: this.page.getByText(label, { exact: true }),
		});
		const value = card.locator('p.text-2xl');
		await value.waitFor({ state: 'visible', timeout: 5000 });
		return value.innerText();
	}

	// ---------------------------------------------------------------------------
	// Period Selector
	// ---------------------------------------------------------------------------

	async getSelectedPeriod(): Promise<string> {
		const trigger = this.page.locator('[data-slot="select-trigger"]');
		await trigger.waitFor({ state: 'visible', timeout: 5000 });
		return trigger.innerText();
	}

	async selectPeriod(label: string): Promise<void> {
		const trigger = this.page.locator('[data-slot="select-trigger"]');
		await trigger.click();

		const option = this.page.getByRole('option', { name: label });
		await option.waitFor({ state: 'visible', timeout: 5000 });

		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('dashboard') && resp.status() === 200
		);
		await option.click();
		await responsePromise;
		await this.page.getByRole('heading', { name: 'Dashboard' }).waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Charts
	// ---------------------------------------------------------------------------

	async isChartVisible(testId: string): Promise<boolean> {
		const chart = this.page.locator(`[data-testid="${testId}"]`);
		return chart.isVisible();
	}

	getChartContainer(testId: string): Locator {
		return this.page.locator(`[data-testid="${testId}"]`);
	}

	// ---------------------------------------------------------------------------
	// Empty State
	// ---------------------------------------------------------------------------

	async getEmptyStateMessage(): Promise<string> {
		const message = this.page.locator('[data-slot="card-content"]').getByText('Nenhum período encontrado');
		await message.waitFor({ state: 'visible', timeout: 5000 });
		return message.innerText();
	}

	// ---------------------------------------------------------------------------
	// Sidebar
	// ---------------------------------------------------------------------------

	async getSidebarItems(): Promise<string[]> {
		const items = this.page.locator('[data-slot="sidebar-menu-button"]');
		const count = await items.count();
		const texts: string[] = [];
		for (let i = 0; i < count; i++) {
			texts.push(await items.nth(i).innerText());
		}
		return texts;
	}
}
