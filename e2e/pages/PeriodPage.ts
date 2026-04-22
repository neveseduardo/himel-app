import type { Locator, Page } from '@playwright/test';

export class PeriodPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/periods', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async gotoShow(periodName: string): Promise<void> {
		const row = this.page.locator('table tbody tr').filter({
			has: this.page.getByText(periodName, { exact: false }),
		});
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('/periods/') && resp.status() === 200
		);
		await row.getByRole('button').first().click();
		await responsePromise;
		await this.page.locator('h1').waitFor({ state: 'visible', timeout: 10_000 });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', { name: 'Períodos' });
		return heading.innerText();
	}

	// ---------------------------------------------------------------------------
	// DataTable
	// ---------------------------------------------------------------------------

	async getTableRows(): Promise<Locator[]> {
		const rows = this.page.locator('table tbody tr').filter({
			hasNot: this.page.locator('td[colspan]'),
		});
		const count = await rows.count();
		const result: Locator[] = [];
		for (let i = 0; i < count; i++) {
			result.push(rows.nth(i));
		}
		return result;
	}

	async getRowByMonthYear(month: string, year: string): Promise<Locator> {
		return this.page.locator('table tbody tr').filter({
			has: this.page.getByText(month, { exact: true }),
		}).filter({
			has: this.page.getByText(year, { exact: true }),
		});
	}

	async getEmptyState(): Promise<Locator> {
		return this.page.getByText('Nenhum registro encontrado.');
	}

	// ---------------------------------------------------------------------------
	// Pagination
	// ---------------------------------------------------------------------------

	getNextButton(): Locator {
		return this.page.getByRole('button', { name: 'Próxima' });
	}

	getPreviousButton(): Locator {
		return this.page.getByRole('button', { name: 'Anterior' });
	}

	async goToNextPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('periods') && resp.status() === 200
		);
		await this.getNextButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async goToPreviousPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('periods') && resp.status() === 200
		);
		await this.getPreviousButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Create modal
	// ---------------------------------------------------------------------------

	async clickCreateButton(): Promise<void> {
		await this.page.getByRole('button', { name: 'Criar Período' }).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async getModalTitle(): Promise<string> {
		const title = this.page.getByRole('dialog').getByRole('heading');
		await title.waitFor({ state: 'visible' });
		return title.innerText();
	}

	async isModalOpen(): Promise<boolean> {
		return this.page.getByRole('dialog').isVisible();
	}

	async selectMonth(month: string): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		const monthTrigger = dialog.getByRole('combobox').first();
		await monthTrigger.click();
		await this.page.getByRole('option', { name: month, exact: true }).click();
	}

	async selectYear(year: string): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		const yearTrigger = dialog.getByRole('combobox').nth(1);
		await yearTrigger.click();
		await this.page.getByRole('option', { name: year, exact: true }).click();
	}

	async submitCreate(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		await dialog.getByRole('button', { name: 'Criar' }).click();
	}

	async cancelCreate(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		await dialog.getByRole('button', { name: 'Cancelar' }).click();
	}

	// ---------------------------------------------------------------------------
	// Delete
	// ---------------------------------------------------------------------------

	async clickDeleteButton(month: string, year: string): Promise<void> {
		const row = await this.getRowByMonthYear(month, year);
		await row.getByRole('button').nth(1).click();
	}

	async confirmDelete(): Promise<void> {
		await this.page.getByRole('button', { name: 'Excluir' }).click();
	}

	// ---------------------------------------------------------------------------
	// Show page assertions
	// ---------------------------------------------------------------------------

	async getShowPageTitle(): Promise<string> {
		const heading = this.page.locator('h1');
		await heading.waitFor({ state: 'visible' });
		return heading.innerText();
	}

	async getSummaryCardValue(label: string): Promise<string> {
		const card = this.page.locator('[data-slot="card"]').filter({
			has: this.page.getByText(label, { exact: true }),
		}).first();
		const value = card.locator('p.text-2xl');
		await value.waitFor({ state: 'visible' });
		return value.innerText();
	}

	// ---------------------------------------------------------------------------
	// Fixed Expenses section
	// ---------------------------------------------------------------------------

	private getSection(title: string): Locator {
		return this.page.locator('[data-slot="card"]').filter({
			has: this.page.getByRole('heading', { name: title }),
		}).first();
	}

	async getFixedExpensesSectionTitle(): Promise<string> {
		const title = this.page.getByRole('heading', { name: 'Despesas Fixas' });
		await title.waitFor({ state: 'visible' });
		return title.innerText();
	}

	async getFixedExpensesSubtotal(): Promise<string> {
		const section = this.getSection('Despesas Fixas');
		const subtotal = section.locator('[data-slot="card-header"] span');
		await subtotal.waitFor({ state: 'visible' });
		return subtotal.innerText();
	}

	async getFixedExpensesRows(): Promise<Locator[]> {
		const section = this.getSection('Despesas Fixas');
		const rows = section.locator('table tbody tr');
		const count = await rows.count();
		const result: Locator[] = [];
		for (let i = 0; i < count; i++) {
			result.push(rows.nth(i));
		}
		return result;
	}

	async getFixedExpensesEmptyState(): Promise<Locator> {
		const section = this.getSection('Despesas Fixas');
		return section.getByText('Nenhuma despesa fixa neste período.');
	}

	// ---------------------------------------------------------------------------
	// Installments section
	// ---------------------------------------------------------------------------

	async getInstallmentsSectionTitle(): Promise<string> {
		const title = this.page.getByRole('heading', { name: 'Parcelas de Cartão' });
		await title.waitFor({ state: 'visible' });
		return title.innerText();
	}

	async getInstallmentsSubtotal(): Promise<string> {
		const section = this.getSection('Parcelas de Cartão');
		const subtotal = section.locator('[data-slot="card-header"] span');
		await subtotal.waitFor({ state: 'visible' });
		return subtotal.innerText();
	}

	async getInstallmentsRows(): Promise<Locator[]> {
		const section = this.getSection('Parcelas de Cartão');
		const rows = section.locator('table tbody tr');
		const count = await rows.count();
		const result: Locator[] = [];
		for (let i = 0; i < count; i++) {
			result.push(rows.nth(i));
		}
		return result;
	}

	async getInstallmentsEmptyState(): Promise<Locator> {
		const section = this.getSection('Parcelas de Cartão');
		return section.getByText('Nenhuma parcela de cartão neste período.');
	}

	async getInstallmentBadge(rowIndex: number): Promise<string> {
		const section = this.getSection('Parcelas de Cartão');
		const row = section.locator('table tbody tr').nth(rowIndex);
		const badge = row.locator('[data-slot="badge"]');
		await badge.waitFor({ state: 'visible' });
		return badge.innerText();
	}

	// ---------------------------------------------------------------------------
	// Card Breakdown section
	// ---------------------------------------------------------------------------

	async isCardBreakdownVisible(): Promise<boolean> {
		const section = this.page.locator('[data-slot="card"]').filter({
			has: this.page.getByRole('heading', { name: 'Resumo por Cartão' }),
		}).first();
		return section.isVisible();
	}

	async getCardBreakdownItems(): Promise<Array<{ name: string; total: string }>> {
		const section = this.getSection('Resumo por Cartão');
		const items = section.locator('[data-slot="card-content"] > div > div');
		const count = await items.count();
		const result: Array<{ name: string; total: string }> = [];
		for (let i = 0; i < count; i++) {
			const item = items.nth(i);
			const spans = item.locator('span');
			const name = await spans.first().innerText();
			const total = await spans.last().innerText();
			result.push({ name, total });
		}
		return result;
	}

	async getCardBreakdownGrandTotal(): Promise<string> {
		const section = this.getSection('Resumo por Cartão');
		const footer = section.locator('[data-slot="card-footer"] span').last();
		await footer.waitFor({ state: 'visible' });
		return footer.innerText();
	}

	// ---------------------------------------------------------------------------
	// Outflow composition
	// ---------------------------------------------------------------------------

	async getOutflowComposition(): Promise<Record<string, string>> {
		const card = this.page.locator('[data-slot="card"]').filter({
			has: this.page.getByText('Saídas', { exact: true }),
		}).first();
		const lines = card.locator('.space-y-1 > div');
		const count = await lines.count();
		const result: Record<string, string> = {};
		for (let i = 0; i < count; i++) {
			const line = lines.nth(i);
			const spans = line.locator('span');
			const label = await spans.first().innerText();
			const value = await spans.last().innerText();
			result[label] = value;
		}
		return result;
	}

	// ---------------------------------------------------------------------------
	// Initialize
	// ---------------------------------------------------------------------------

	async clickInitializeButton(): Promise<void> {
		await this.page.getByRole('button', { name: /Inicializar Período/ }).click();
	}

	async initializePeriodAndWait(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('/periods/') && resp.status() === 200
		);
		await this.clickInitializeButton();
		await responsePromise;
		// Wait for the page to reload with new data
		await this.page.locator('h1').waitFor({ state: 'visible', timeout: 10_000 });
	}

	async getAllCurrencyValues(): Promise<string[]> {
		const elements = this.page.locator('text=/R\\$\\s/');
		const count = await elements.count();
		const values: string[] = [];
		for (let i = 0; i < count; i++) {
			values.push(await elements.nth(i).innerText());
		}
		return values;
	}

	// ---------------------------------------------------------------------------
	// Toast
	// ---------------------------------------------------------------------------

	async waitForToast(message: string): Promise<void> {
		await this.page
			.getByText(message)
			.waitFor({ state: 'visible', timeout: 5_000 });
	}
}
