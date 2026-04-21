import { expect, test } from '@playwright/test';

import { CreditCardChargePage } from '../pages/CreditCardChargePage';

// ---------------------------------------------------------------------------
// 1. CreditCardCharge Listing
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Listing', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('page renders with title "Compras no Cartão"', async () => {
		const title = await chargePage.getPageTitle();
		expect(title).toBe('Compras no Cartão');
	});

	test('DataTable displays seeded credit card charge records', async () => {
		await chargePage.search('Notebook Dell');
		const notebookRow = await chargePage.getRowByDescription('Notebook Dell');
		await expect(notebookRow).toBeVisible();

		await chargePage.clearSearch();
		await chargePage.search('Fone Bluetooth');
		const foneRow = await chargePage.getRowByDescription('Fone Bluetooth');
		await expect(foneRow).toBeVisible();

		await chargePage.clearSearch();
		await chargePage.search('Curso Online');
		const cursoRow = await chargePage.getRowByDescription('Curso Online');
		await expect(cursoRow).toBeVisible();
	});

	test('each row displays description, formatted amount, installments, and card name', async () => {
		await chargePage.search('Notebook Dell');
		const notebookRow = await chargePage.getRowByDescription('Notebook Dell');
		await expect(notebookRow).toContainText('Notebook Dell');
		await expect(notebookRow).toContainText('15/03/2024');
		await expect(notebookRow).toContainText('4.500,00');
		await expect(notebookRow).toContainText('12x');
		await expect(notebookRow).toContainText('Nubank');

		await chargePage.clearSearch();
		await chargePage.search('Fone Bluetooth');
		const foneRow = await chargePage.getRowByDescription('Fone Bluetooth');
		await expect(foneRow).toContainText('Fone Bluetooth');
		await expect(foneRow).toContainText('20/02/2024');
		await expect(foneRow).toContainText('250,00');
		await expect(foneRow).toContainText('3x');
		await expect(foneRow).toContainText('Inter');

		await chargePage.clearSearch();
		await chargePage.search('Curso Online');
		const cursoRow = await chargePage.getRowByDescription('Curso Online');
		await expect(cursoRow).toContainText('Curso Online');
		await expect(cursoRow).toContainText('10/01/2024');
		await expect(cursoRow).toContainText('1.200,00');
		await expect(cursoRow).toContainText('6x');
		await expect(cursoRow).toContainText('C6 Bank');
	});
});

// ---------------------------------------------------------------------------
// 2. CreditCardCharge Search and Filtering
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Search and Filtering', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('typing search term filters DataTable to matching charges only', async () => {
		await chargePage.search('Notebook Dell');

		const notebookRow = await chargePage.getRowByDescription('Notebook Dell');
		await expect(notebookRow).toBeVisible();

		const rows = await chargePage.getTableRows();
		expect(rows.length).toBe(1);
	});

	test('clearing search returns all credit card charges', async () => {
		await chargePage.search('Notebook Dell');

		let rows = await chargePage.getTableRows();
		expect(rows.length).toBe(1);

		await chargePage.clearSearch();

		rows = await chargePage.getTableRows();
		expect(rows.length).toBeGreaterThan(0);
	});

	test('non-matching search shows empty result', async () => {
		await chargePage.search('CompraInexistente');

		const emptyState = await chargePage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 3. CreditCardCharge Pagination
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Pagination', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('pagination controls visible when charges exceed per-page limit', async () => {
		await expect(chargePage.getNextButton()).toBeVisible();
		await expect(chargePage.getPreviousButton()).toBeVisible();
	});

	test('clicking "Próxima" navigates to next page', async () => {
		const firstPageRows = await chargePage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);

		await chargePage.goToNextPage();

		const secondPageRows = await chargePage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);
	});

	test('clicking "Anterior" navigates to previous page', async () => {
		await chargePage.goToNextPage();

		const secondPageRows = await chargePage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);

		await chargePage.goToPreviousPage();

		const firstPageRows = await chargePage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);
	});

	test('"Anterior" disabled on first page', async () => {
		await expect(chargePage.getPreviousButton()).toBeDisabled();
	});

	test('"Próxima" disabled on last page', async () => {
		const nextBtn = chargePage.getNextButton();
		while (await nextBtn.isEnabled()) {
			const responsePromise = chargePage.page.waitForResponse(
				(resp) => resp.url().includes('credit-card-charges') && resp.status() === 200
			);
			await nextBtn.click();
			await responsePromise;
		}
		await expect(nextBtn).toBeDisabled();
	});
});

// ---------------------------------------------------------------------------
// 3.5. CreditCardCharge Dialog Reopen
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Dialog Reopen', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('dialog reopens after closing via ESC', async () => {
		await chargePage.clickCreateButton();
		expect(await chargePage.isModalOpen()).toBe(true);

		await chargePage.closeDialogByEsc();
		expect(await chargePage.isModalOpen()).toBe(false);

		await chargePage.clickCreateButton();
		expect(await chargePage.isModalOpen()).toBe(true);

		const modalTitle = await chargePage.getModalTitle();
		expect(modalTitle).toBe('Nova Compra');
	});

	test('dialog reopens after closing via overlay click', async () => {
		await chargePage.clickCreateButton();
		expect(await chargePage.isModalOpen()).toBe(true);

		await chargePage.closeDialogByOverlay();
		expect(await chargePage.isModalOpen()).toBe(false);

		await chargePage.clickCreateButton();
		expect(await chargePage.isModalOpen()).toBe(true);

		const modalTitle = await chargePage.getModalTitle();
		expect(modalTitle).toBe('Nova Compra');
	});
});

// ---------------------------------------------------------------------------
// 4. CreditCardCharge Creation
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Creation', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('clicking "Criar" opens modal with title "Nova Compra"', async () => {
		await chargePage.clickCreateButton();

		const modalTitle = await chargePage.getModalTitle();
		expect(modalTitle).toBe('Nova Compra');
	});

	test('filling all fields and submitting shows success toast', async () => {
		await chargePage.clickCreateButton();

		await chargePage.fillForm({
			credit_card_uid: 'Nubank',
			description: 'Compra Teste E2E',
			amount: 199.90,
			total_installments: 3,
			purchase_date: '2024-06-15',
		});

		await chargePage.submitForm();
		await chargePage.waitForToast('Compra no cartão criado(a) com sucesso!');
	});

	test('newly created charge appears in DataTable', async () => {
		await chargePage.clickCreateButton();

		await chargePage.fillForm({
			credit_card_uid: 'Nubank',
			description: 'Compra Nova Listagem',
			amount: 350.00,
			total_installments: 2,
			purchase_date: '2024-07-20',
		});

		await chargePage.submitForm();

		// Wait for Inertia redirect to complete (POST → 302 → GET 200)
		await chargePage.page.waitForURL(/credit-card-charges/, { timeout: 10_000 });
		await chargePage.page.locator('table').waitFor({ state: 'visible' });

		await chargePage.search('Compra Nova Listagem');
		const newRow = await chargePage.getRowByDescription('Compra Nova Listagem');
		await expect(newRow).toBeVisible();
	});

	test('submitting with invalid data shows validation errors', async () => {
		await chargePage.clickCreateButton();

		// Clear the description field to trigger validation
		const dialog = chargePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('');
		await dialog.locator('[name="amount"]').fill('0');

		await chargePage.submitForm();

		// Wait for validation error to appear
		const errorSpan = dialog.locator('.text-destructive').first();
		await errorSpan.waitFor({ state: 'visible', timeout: 5_000 });
		const errorText = await errorSpan.innerText();
		expect(errorText).toBeTruthy();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await chargePage.clickCreateButton();

		const isOpen = await chargePage.isModalOpen();
		expect(isOpen).toBe(true);

		await chargePage.cancelForm();

		await chargePage.page.getByRole('dialog').waitFor({ state: 'hidden' });
		const isOpenAfter = await chargePage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 5. CreditCardCharge Viewing
// ---------------------------------------------------------------------------

test.describe('CreditCardCharge Viewing', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('clicking view icon opens modal with title "Detalhes da Compra"', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickViewButton('Notebook Dell');

		const modalTitle = await chargePage.getModalTitle();
		expect(modalTitle).toBe('Detalhes da Compra');
	});

	test('all form fields are disabled (read-only)', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickViewButton('Notebook Dell');

		expect(await chargePage.isFieldDisabled('credit_card_uid')).toBe(true);
		expect(await chargePage.isFieldDisabled('description')).toBe(true);
		expect(await chargePage.isFieldDisabled('purchase_date')).toBe(true);
		expect(await chargePage.isFieldDisabled('amount')).toBe(true);
		expect(await chargePage.isFieldDisabled('total_installments')).toBe(true);
	});

	test('no submit button visible', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickViewButton('Notebook Dell');

		const isVisible = await chargePage.isSubmitButtonVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 6. CreditCardCharge Editing (SKIP — UI not implemented yet)
// ---------------------------------------------------------------------------

test.describe.skip('CreditCardCharge Editing', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('clicking edit icon opens modal with title "Editar Compra"', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickEditButton('Notebook Dell');

		const modalTitle = await chargePage.getModalTitle();
		expect(modalTitle).toBe('Editar Compra');
	});

	test('form fields pre-populated with existing data', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickEditButton('Notebook Dell');

		await chargePage.page.getByRole('dialog').locator('[name="description"]').waitFor({ state: 'visible' });

		const description = await chargePage.getFormFieldValue('description');
		expect(description).toBe('Notebook Dell');

		const amount = await chargePage.getFormFieldValue('amount');
		expect(amount).toBe('4500');
	});

	test('modifying and submitting shows success toast', async () => {
		await chargePage.search('Notebook Dell');
		await chargePage.clickEditButton('Notebook Dell');

		const dialog = chargePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('Notebook Dell Editado');

		await chargePage.submitForm();
		await chargePage.waitForToast('Compra no cartão atualizado(a) com sucesso!');
	});

	test('DataTable reflects updated data', async () => {
		await chargePage.search('Fone Bluetooth');
		await chargePage.clickEditButton('Fone Bluetooth');

		const dialog = chargePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('Fone Bluetooth Editado');

		await chargePage.submitForm();
		await chargePage.waitForToast('Compra no cartão atualizado(a) com sucesso!');

		await chargePage.search('Fone Bluetooth Editado');
		const updatedRow = await chargePage.getRowByDescription('Fone Bluetooth Editado');
		await expect(updatedRow).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 7. CreditCardCharge Deletion (SKIP — UI not implemented yet)
// ---------------------------------------------------------------------------

test.describe.skip('CreditCardCharge Deletion', () => {
	let chargePage: CreditCardChargePage;

	test.beforeEach(async ({ page }) => {
		chargePage = new CreditCardChargePage(page);
		await chargePage.goto();
	});

	test('clicking delete icon shows confirmation popover', async () => {
		await chargePage.search('Curso Online');
		await chargePage.clickDeleteButton('Curso Online');

		const popoverText = chargePage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await chargePage.search('Compra Teste E2E');
		await chargePage.clickDeleteButton('Compra Teste E2E');
		await chargePage.confirmDelete();

		await chargePage.waitForToast('Compra no cartão excluído(a) com sucesso!');
	});

	test('deleted charge removed from DataTable', async () => {
		await chargePage.search('Curso Online');

		const rowBefore = await chargePage.getRowByDescription('Curso Online');
		await expect(rowBefore).toBeVisible();

		await chargePage.clickDeleteButton('Curso Online');
		await chargePage.confirmDelete();
		await chargePage.waitForToast('Compra no cartão excluído(a) com sucesso!');

		await chargePage.page.locator('table').waitFor({ state: 'visible' });
		await chargePage.search('Curso Online');

		const emptyState = await chargePage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});
