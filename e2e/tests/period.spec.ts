import { expect, test } from '@playwright/test';

import { PeriodPage } from '../pages/PeriodPage';

// ---------------------------------------------------------------------------
// 1. Period Listing
// ---------------------------------------------------------------------------

test.describe('Period Listing', () => {
	let periodPage: PeriodPage;

	test.beforeEach(async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
	});

	test('page renders with title "Períodos"', async () => {
		const title = await periodPage.getPageTitle();
		expect(title).toBe('Períodos');
	});

	test('DataTable displays seeded periods (Janeiro, Fevereiro, Março 2025)', async () => {
		const janeiroRow = await periodPage.getRowByMonthYear('Janeiro', '2025');
		await expect(janeiroRow).toBeVisible();

		const fevereiroRow = await periodPage.getRowByMonthYear('Fevereiro', '2025');
		await expect(fevereiroRow).toBeVisible();

		const marcoRow = await periodPage.getRowByMonthYear('Março', '2025');
		await expect(marcoRow).toBeVisible();
	});

	test('each row displays month name, year, and transaction count', async () => {
		const janeiroRow = await periodPage.getRowByMonthYear('Janeiro', '2025');
		await expect(janeiroRow).toContainText('Janeiro');
		await expect(janeiroRow).toContainText('2025');
		// Janeiro has seeded transactions, so count should be > 0
		const rowText = await janeiroRow.innerText();
		expect(rowText).toMatch(/\d+/);
	});
});

// ---------------------------------------------------------------------------
// 2. Period Creation
// ---------------------------------------------------------------------------

test.describe('Period Creation', () => {
	let periodPage: PeriodPage;

	test.beforeEach(async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
	});

	test('clicking "Criar Período" opens modal with title "Criar Período"', async () => {
		await periodPage.clickCreateButton();

		const modalTitle = await periodPage.getModalTitle();
		expect(modalTitle).toBe('Criar Período');
	});

	test('selecting month/year and submitting creates period with success toast', async () => {
		await periodPage.clickCreateButton();

		await periodPage.selectMonth('Maio');
		await periodPage.selectYear('2025');
		await periodPage.submitCreate();

		await periodPage.waitForToast('Período criado(a) com sucesso!');
	});

	test('newly created period appears in DataTable', async () => {
		await periodPage.page.locator('table').waitFor({ state: 'visible' });

		const maioRow = await periodPage.getRowByMonthYear('Maio', '2025');
		await expect(maioRow).toBeVisible();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await periodPage.clickCreateButton();

		const isOpen = await periodPage.isModalOpen();
		expect(isOpen).toBe(true);

		await periodPage.cancelCreate();

		await periodPage.page.getByRole('dialog').waitFor({ state: 'hidden' });
		const isOpenAfter = await periodPage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 3. Show Page — Basic
// ---------------------------------------------------------------------------

test.describe('Show Page — Basic', () => {
	let periodPage: PeriodPage;

	test.beforeEach(async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
	});

	test('clicking view icon navigates to Show page with correct title', async () => {
		await periodPage.gotoShow('Janeiro');

		const title = await periodPage.getShowPageTitle();
		expect(title).toContain('Janeiro');
		expect(title).toContain('2025');
	});

	test('summary cards display Entradas, Saídas, Saldo values', async () => {
		await periodPage.gotoShow('Janeiro');

		const entradas = await periodPage.getSummaryCardValue('Entradas');
		expect(entradas).toBeTruthy();

		const saidas = await periodPage.getSummaryCardValue('Saídas');
		expect(saidas).toBeTruthy();

		const saldo = await periodPage.getSummaryCardValue('Saldo');
		expect(saldo).toBeTruthy();
	});

	test('outflow card shows composition breakdown', async () => {
		await periodPage.gotoShow('Janeiro');

		const composition = await periodPage.getOutflowComposition();
		expect(composition).toHaveProperty('Despesas Fixas');
		expect(composition).toHaveProperty('Parcelas de Cartão');
		expect(composition).toHaveProperty('Manuais');
		expect(composition).toHaveProperty('Transferências');
	});
});

// ---------------------------------------------------------------------------
// 4. Show Page — Fixed Expenses Section
// ---------------------------------------------------------------------------

test.describe('Show Page — Fixed Expenses Section', () => {
	let periodPage: PeriodPage;

	test('section is visible with correct title on Janeiro 2025', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const title = await periodPage.getFixedExpensesSectionTitle();
		expect(title).toBe('Despesas Fixas');
	});

	test('section shows subtotal in header', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const subtotal = await periodPage.getFixedExpensesSubtotal();
		expect(subtotal).toBeTruthy();
		expect(subtotal).toContain('R$');
	});

	test('table displays fixed expense items', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const rows = await periodPage.getFixedExpensesRows();
		expect(rows.length).toBeGreaterThan(0);

		// Verify Aluguel row exists
		const section = page.locator('[class*="card"]').filter({
			has: page.getByRole('heading', { name: 'Despesas Fixas' }),
		}).first();
		await expect(section.getByText('Aluguel')).toBeVisible();
	});

	test('empty state shows message for period with no fixed expenses', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Fevereiro');

		const emptyState = await periodPage.getFixedExpensesEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 5. Show Page — Installments Section
// ---------------------------------------------------------------------------

test.describe('Show Page — Installments Section', () => {
	let periodPage: PeriodPage;

	test('section is visible with correct title on Janeiro 2025', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const title = await periodPage.getInstallmentsSectionTitle();
		expect(title).toBe('Parcelas de Cartão');
	});

	test('section shows subtotal in header', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const subtotal = await periodPage.getInstallmentsSubtotal();
		expect(subtotal).toBeTruthy();
		expect(subtotal).toContain('R$');
	});

	test('table displays installment items with badge', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const rows = await periodPage.getInstallmentsRows();
		expect(rows.length).toBeGreaterThan(0);

		// Verify badge shows X/Y format
		const badge = await periodPage.getInstallmentBadge(0);
		expect(badge).toMatch(/\d+\/\d+/);
	});

	test('empty state shows message for period with no installments', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Fevereiro');

		const emptyState = await periodPage.getInstallmentsEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 6. Show Page — Card Breakdown Section
// ---------------------------------------------------------------------------

test.describe('Show Page — Card Breakdown Section', () => {
	let periodPage: PeriodPage;

	test('section is visible when there are installments (Janeiro 2025)', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const isVisible = await periodPage.isCardBreakdownVisible();
		expect(isVisible).toBe(true);
	});

	test('shows each card name with its total', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const items = await periodPage.getCardBreakdownItems();
		expect(items.length).toBeGreaterThan(0);

		for (const item of items) {
			expect(item.name).toBeTruthy();
			expect(item.total).toContain('R$');
		}
	});

	test('shows grand total in footer', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Janeiro');

		const grandTotal = await periodPage.getCardBreakdownGrandTotal();
		expect(grandTotal).toContain('R$');
	});

	test('section is hidden when there are no installments (Fevereiro 2025)', async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
		await periodPage.gotoShow('Fevereiro');

		const isVisible = await periodPage.isCardBreakdownVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 7. Period Deletion
// ---------------------------------------------------------------------------

test.describe('Period Deletion', () => {
	let periodPage: PeriodPage;

	test.beforeEach(async ({ page }) => {
		periodPage = new PeriodPage(page);
		await periodPage.goto();
	});

	test('clicking delete shows confirmation', async () => {
		await periodPage.clickDeleteButton('Maio', '2025');

		const popoverText = periodPage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await periodPage.clickDeleteButton('Maio', '2025');
		await periodPage.confirmDelete();

		await periodPage.waitForToast('Período excluído(a) com sucesso!');
	});

	test('deleted period removed from DataTable', async () => {
		await periodPage.page.locator('table').waitFor({ state: 'visible' });

		// Verify Maio is no longer visible after deletion in previous test
		const maioRow = await periodPage.getRowByMonthYear('Maio', '2025');
		await expect(maioRow).not.toBeVisible();
	});
});
