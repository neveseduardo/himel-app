import { expect, test } from '@playwright/test';

import { DashboardPage } from '../pages/DashboardPage';

// ---------------------------------------------------------------------------
// 1. Page Load
// ---------------------------------------------------------------------------

test.describe('Dashboard Page Load', () => {
	let dashboardPage: DashboardPage;

	test.beforeEach(async ({ page }) => {
		dashboardPage = new DashboardPage(page);
		await dashboardPage.goto();
	});

	test('page renders with title "Dashboard"', async () => {
		const title = await dashboardPage.getPageTitle();
		expect(title).toBe('Dashboard');
	});

	test('summary cards are visible', async () => {
		const entradas = await dashboardPage.getSummaryCardValue('Entradas');
		expect(entradas).toBeTruthy();

		const saidas = await dashboardPage.getSummaryCardValue('Saídas');
		expect(saidas).toBeTruthy();

		const saldo = await dashboardPage.getSummaryCardValue('Saldo');
		expect(saldo).toBeTruthy();

		const cartoes = await dashboardPage.getSummaryCardValue('Total Cartões');
		expect(cartoes).toBeTruthy();
	});
});

// ---------------------------------------------------------------------------
// 2. Summary Cards
// ---------------------------------------------------------------------------

test.describe('Dashboard Summary Cards', () => {
	let dashboardPage: DashboardPage;

	test.beforeEach(async ({ page }) => {
		dashboardPage = new DashboardPage(page);
		await dashboardPage.goto();
	});

	test('card values are formatted in R$', async () => {
		const entradas = await dashboardPage.getSummaryCardValue('Entradas');
		expect(entradas).toContain('R$');

		const saidas = await dashboardPage.getSummaryCardValue('Saídas');
		expect(saidas).toContain('R$');

		const saldo = await dashboardPage.getSummaryCardValue('Saldo');
		expect(saldo).toContain('R$');

		const cartoes = await dashboardPage.getSummaryCardValue('Total Cartões');
		expect(cartoes).toContain('R$');
	});
});

// ---------------------------------------------------------------------------
// 3. Period Selector
// ---------------------------------------------------------------------------

test.describe('Dashboard Period Selector', () => {
	test.describe.configure({ mode: 'serial' });

	let dashboardPage: DashboardPage;

	test.beforeEach(async ({ page }) => {
		dashboardPage = new DashboardPage(page);
		await dashboardPage.goto();
	});

	test('shows a period as default selection', async () => {
		const selected = await dashboardPage.getSelectedPeriod();
		expect(selected).toBeTruthy();
		// Should contain a year
		expect(selected).toMatch(/\d{4}/);
	});

	test('can change period via selector', async () => {
		const initialPeriod = await dashboardPage.getSelectedPeriod();

		// Select Fevereiro 2025
		await dashboardPage.selectPeriod('Fevereiro 2025');

		const newPeriod = await dashboardPage.getSelectedPeriod();
		expect(newPeriod).toContain('Fevereiro 2025');
		expect(newPeriod).not.toBe(initialPeriod);
	});
});

// ---------------------------------------------------------------------------
// 4. Charts
// ---------------------------------------------------------------------------

test.describe('Dashboard Charts', () => {
	let dashboardPage: DashboardPage;

	test.beforeEach(async ({ page }) => {
		dashboardPage = new DashboardPage(page);
		await dashboardPage.goto();
	});

	test('outflow composition chart is rendered', async () => {
		const visible = await dashboardPage.isChartVisible('chart-outflow-composition');
		expect(visible).toBe(true);
	});

	test('inflow vs outflow chart is rendered', async () => {
		const visible = await dashboardPage.isChartVisible('chart-inflow-vs-outflow');
		expect(visible).toBe(true);
	});

	test('card breakdown chart is rendered', async () => {
		const visible = await dashboardPage.isChartVisible('chart-card-breakdown');
		expect(visible).toBe(true);
	});

	test('status chart is rendered', async () => {
		const visible = await dashboardPage.isChartVisible('chart-status');
		expect(visible).toBe(true);
	});

	test('category breakdown chart is rendered', async () => {
		const visible = await dashboardPage.isChartVisible('chart-category-breakdown');
		expect(visible).toBe(true);
	});
});

// ---------------------------------------------------------------------------
// 5. Sidebar
// ---------------------------------------------------------------------------

test.describe('Dashboard Sidebar', () => {
	let dashboardPage: DashboardPage;

	test.beforeEach(async ({ page }) => {
		dashboardPage = new DashboardPage(page);
		await dashboardPage.goto();
	});

	test('Dashboard is the first item in the sidebar navigation group', async () => {
		const items = await dashboardPage.getSidebarItems();
		// Filter to only finance nav items
		const financeItems = items.filter((item) =>
			['Dashboard', 'Períodos', 'Contas', 'Categorias', 'Transferências', 'Despesas Fixas', 'Cartões', 'Compras Cartão'].includes(item)
		);
		expect(financeItems.length).toBeGreaterThan(0);
		expect(financeItems[0]).toBe('Dashboard');
	});
});
