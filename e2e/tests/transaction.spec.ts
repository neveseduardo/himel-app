import { expect, test } from '@playwright/test';

import { TransactionPage } from '../pages/TransactionPage';

// ---------------------------------------------------------------------------
// 1. Transaction Listing
// ---------------------------------------------------------------------------

test.describe('Transaction Listing', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('page renders with title "Transações"', async () => {
		const title = await transactionPage.getPageTitle();
		expect(title).toBe('Transações');
	});

	test('DataTable displays seeded transaction records', async () => {
		await transactionPage.search('Salário Mensal');
		const salarioRow = await transactionPage.getRowByDescription('Salário Mensal');
		await expect(salarioRow).toBeVisible();

		await transactionPage.clearSearch();
		await transactionPage.search('Supermercado');
		const superRow = await transactionPage.getRowByDescription('Supermercado');
		await expect(superRow).toBeVisible();

		await transactionPage.clearSearch();
		await transactionPage.search('Conta de Luz');
		const luzRow = await transactionPage.getRowByDescription('Conta de Luz');
		await expect(luzRow).toBeVisible();
	});

	test('each row displays description, amount, direction badge, status badge, and date', async () => {
		// Salário Mensal: INFLOW, PAID, R$ 8.500,00
		await transactionPage.search('Salário Mensal');
		const salarioRow = await transactionPage.getRowByDescription('Salário Mensal');
		await expect(salarioRow).toContainText('Salário Mensal');
		await expect(salarioRow).toContainText('8.500,00');
		await expect(salarioRow).toContainText('Entrada');
		await expect(salarioRow).toContainText('Pago');

		// Supermercado: OUTFLOW, PAID, R$ 450,00
		await transactionPage.clearSearch();
		await transactionPage.search('Supermercado');
		const superRow = await transactionPage.getRowByDescription('Supermercado');
		await expect(superRow).toContainText('Supermercado');
		await expect(superRow).toContainText('450,00');
		await expect(superRow).toContainText('Saída');
		await expect(superRow).toContainText('Pago');

		// Conta de Luz: OUTFLOW, PENDING, R$ 180,00
		await transactionPage.clearSearch();
		await transactionPage.search('Conta de Luz');
		const luzRow = await transactionPage.getRowByDescription('Conta de Luz');
		await expect(luzRow).toContainText('Conta de Luz');
		await expect(luzRow).toContainText('180,00');
		await expect(luzRow).toContainText('Saída');
		await expect(luzRow).toContainText('Pendente');
	});
});

// ---------------------------------------------------------------------------
// 2. Transaction Search and Filtering
// ---------------------------------------------------------------------------

test.describe('Transaction Search and Filtering', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('typing search term filters DataTable to matching transactions only', async () => {
		await transactionPage.search('Salário Mensal');

		const salarioRow = await transactionPage.getRowByDescription('Salário Mensal');
		await expect(salarioRow).toBeVisible();

		const rows = await transactionPage.getTableRows();
		expect(rows.length).toBe(1);
	});

	test('clearing search returns all transactions', async () => {
		await transactionPage.search('Salário Mensal');

		let rows = await transactionPage.getTableRows();
		expect(rows.length).toBe(1);

		await transactionPage.clearSearch();

		rows = await transactionPage.getTableRows();
		expect(rows.length).toBeGreaterThan(1);
	});

	test('non-matching search shows empty result', async () => {
		await transactionPage.search('TransacaoInexistente');

		const emptyState = await transactionPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 3. Transaction Pagination
// ---------------------------------------------------------------------------

test.describe('Transaction Pagination', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('pagination controls visible when transactions exceed per-page limit', async () => {
		await expect(transactionPage.getNextButton()).toBeVisible();
		await expect(transactionPage.getPreviousButton()).toBeVisible();
	});

	test('clicking "Próxima" navigates to next page', async () => {
		const firstPageRows = await transactionPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);

		await transactionPage.goToNextPage();

		const secondPageRows = await transactionPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);
	});

	test('clicking "Anterior" navigates to previous page', async () => {
		await transactionPage.goToNextPage();

		const secondPageRows = await transactionPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);

		await transactionPage.goToPreviousPage();

		const firstPageRows = await transactionPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);
	});

	test('"Anterior" disabled on first page', async () => {
		await expect(transactionPage.getPreviousButton()).toBeDisabled();
	});

	test('"Próxima" disabled on last page', async () => {
		const nextBtn = transactionPage.getNextButton();
		while (await nextBtn.isEnabled()) {
			const responsePromise = transactionPage.page.waitForResponse(
				(resp) => resp.url().includes('transactions') && resp.status() === 200
			);
			await nextBtn.click();
			await responsePromise;
		}
		await expect(nextBtn).toBeDisabled();
	});
});

// ---------------------------------------------------------------------------
// 4. Transaction Dialog Reopen
// ---------------------------------------------------------------------------

test.describe('Transaction Dialog Reopen', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('dialog reopens after closing via ESC', async () => {
		await transactionPage.clickCreateButton();
		expect(await transactionPage.isModalOpen()).toBe(true);

		await transactionPage.closeDialogByEsc();
		expect(await transactionPage.isModalOpen()).toBe(false);

		await transactionPage.clickCreateButton();
		expect(await transactionPage.isModalOpen()).toBe(true);

		const modalTitle = await transactionPage.getModalTitle();
		expect(modalTitle).toBe('Nova Saída');
	});

	test('dialog reopens after closing via overlay click', async () => {
		await transactionPage.clickCreateButton();
		expect(await transactionPage.isModalOpen()).toBe(true);

		await transactionPage.closeDialogByOverlay();
		expect(await transactionPage.isModalOpen()).toBe(false);

		await transactionPage.clickCreateButton();
		expect(await transactionPage.isModalOpen()).toBe(true);

		const modalTitle = await transactionPage.getModalTitle();
		expect(modalTitle).toBe('Nova Saída');
	});
});

// ---------------------------------------------------------------------------
// 5. Transaction Creation
// ---------------------------------------------------------------------------

test.describe('Transaction Creation', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('clicking "Nova Transação" → "Saída" opens modal with title "Nova Saída"', async () => {
		await transactionPage.clickCreateButton();

		const modalTitle = await transactionPage.getModalTitle();
		expect(modalTitle).toBe('Nova Saída');
	});

	test('filling all fields and submitting shows success toast', async () => {
		await transactionPage.clickCreateButton();

		await transactionPage.fillForm({
			account_uid: 'Conta Corrente BB',
			category_uid: 'Alimentação',
			amount: 99.90,
			status: 'PENDING',
			description: 'Transação Teste E2E',
			occurred_at: '2024-06-15',
		});

		await transactionPage.submitForm();
		await transactionPage.waitForToast('Transação criado(a) com sucesso!');
	});

	test('newly created transaction appears in DataTable', async () => {
		await transactionPage.search('Transação Teste E2E');
		const newRow = await transactionPage.getRowByDescription('Transação Teste E2E');
		await expect(newRow).toBeVisible();
	});

	test('submitting with invalid data shows validation errors', async () => {
		await transactionPage.clickCreateButton();

		await transactionPage.fillForm({
			account_uid: '',
			category_uid: '',
			amount: 0,
			status: 'PENDING',
			description: '',
			occurred_at: '2024-06-15',
		});

		await transactionPage.submitForm();

		const amountError = await transactionPage.getValidationError('amount');
		expect(amountError).toBeTruthy();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await transactionPage.clickCreateButton();

		const isOpen = await transactionPage.isModalOpen();
		expect(isOpen).toBe(true);

		await transactionPage.cancelForm();

		await transactionPage.page.getByRole('dialog').waitFor({ state: 'hidden' });
		const isOpenAfter = await transactionPage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 6. Transaction Editing
// ---------------------------------------------------------------------------

test.describe('Transaction Editing', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('clicking edit icon opens modal with title "Editar Saída"', async () => {
		await transactionPage.search('Supermercado');
		await transactionPage.clickEditButton('Supermercado');

		const modalTitle = await transactionPage.getModalTitle();
		expect(modalTitle).toBe('Editar Saída');
	});

	test('form fields pre-populated with existing data', async () => {
		await transactionPage.search('Supermercado');
		await transactionPage.clickEditButton('Supermercado');

		await transactionPage.page.getByRole('dialog').locator('[name="description"]').waitFor({ state: 'visible' });

		const description = await transactionPage.getFormFieldValue('description');
		expect(description).toBe('Supermercado');

		const status = await transactionPage.getFormFieldValue('status');
		expect(status).toBe('Pago');
	});

	test('modifying and submitting shows success toast', async () => {
		await transactionPage.search('Supermercado');
		await transactionPage.clickEditButton('Supermercado');

		const dialog = transactionPage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('Supermercado Editado');

		await transactionPage.submitForm();
		await transactionPage.waitForToast('Transação atualizado(a) com sucesso!');
	});

	test('DataTable reflects updated data', async () => {
		await transactionPage.search('Supermercado Editado');
		const updatedRow = await transactionPage.getRowByDescription('Supermercado Editado');
		await expect(updatedRow).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 7. Transaction Viewing
// ---------------------------------------------------------------------------

test.describe('Transaction Viewing', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('clicking view icon opens modal with direction-specific title', async () => {
		await transactionPage.search('Salário Mensal');
		await transactionPage.clickViewButton('Salário Mensal');

		const modalTitle = await transactionPage.getModalTitle();
		expect(modalTitle).toBe('Detalhes da Entrada');
	});

	test('all form fields are disabled (read-only) for OUTFLOW transaction', async () => {
		await transactionPage.search('Supermercado');
		await transactionPage.clickViewButton('Supermercado');

		expect(await transactionPage.isFieldDisabled('account_uid')).toBe(true);
		expect(await transactionPage.isFieldDisabled('category_uid')).toBe(true);
		expect(await transactionPage.isFieldDisabled('amount')).toBe(true);
		expect(await transactionPage.isFieldDisabled('status')).toBe(true);
		expect(await transactionPage.isFieldDisabled('description')).toBe(true);
		expect(await transactionPage.isFieldDisabled('occurred_at')).toBe(true);
	});

	test('no submit button visible in view mode', async () => {
		await transactionPage.search('Supermercado');
		await transactionPage.clickViewButton('Supermercado');

		const isVisible = await transactionPage.isSubmitButtonVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 8. Transaction Deletion
// ---------------------------------------------------------------------------

test.describe('Transaction Deletion', () => {
	let transactionPage: TransactionPage;

	test.beforeEach(async ({ page }) => {
		transactionPage = new TransactionPage(page);
		await transactionPage.goto();
	});

	test('clicking delete icon shows confirmation popover', async () => {
		await transactionPage.search('Conta de Luz');
		await transactionPage.clickDeleteButton('Conta de Luz');

		const popoverText = transactionPage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await transactionPage.search('Transação Teste E2E');
		await transactionPage.clickDeleteButton('Transação Teste E2E');
		await transactionPage.confirmDelete();

		await transactionPage.waitForToast('Transação excluído(a) com sucesso!');
	});

	test('deleted transaction removed from DataTable', async () => {
		await transactionPage.search('Conta de Luz');

		const rowBefore = await transactionPage.getRowByDescription('Conta de Luz');
		await expect(rowBefore).toBeVisible();

		await transactionPage.clickDeleteButton('Conta de Luz');
		await transactionPage.confirmDelete();
		await transactionPage.waitForToast('Transação excluído(a) com sucesso!');

		await transactionPage.page.locator('table').waitFor({ state: 'visible' });
		await transactionPage.search('Conta de Luz');

		const emptyState = await transactionPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});
