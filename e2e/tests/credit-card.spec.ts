import { expect, test } from '@playwright/test';

import { CreditCardPage } from '../pages/CreditCardPage';

// ---------------------------------------------------------------------------
// 1. CreditCard Listing (Task 6.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Listing', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('page renders with title "Cartões de Crédito"', async () => {
		const title = await creditCardPage.getPageTitle();
		expect(title).toBe('Cartões de Crédito');
	});

	test('DataTable displays seeded credit card records', async () => {
		await creditCardPage.search('Nubank');
		const nubankRow = await creditCardPage.getRowByName('Nubank');
		await expect(nubankRow).toBeVisible();

		await creditCardPage.clearSearch();
		await creditCardPage.search('Inter');
		const interRow = await creditCardPage.getRowByName('Inter');
		await expect(interRow).toBeVisible();

		await creditCardPage.clearSearch();
		await creditCardPage.search('C6 Bank');
		const c6Row = await creditCardPage.getRowByName('C6 Bank');
		await expect(c6Row).toBeVisible();
	});

	test('each row displays card name, card type, and due day', async () => {
		await creditCardPage.search('Nubank');
		const nubankRow = await creditCardPage.getRowByName('Nubank');
		await expect(nubankRow).toContainText('Nubank');
		await expect(nubankRow).toContainText('Físico');
		await expect(nubankRow).toContainText('Dia 15');

		await creditCardPage.clearSearch();
		await creditCardPage.search('Inter');
		const interRow = await creditCardPage.getRowByName('Inter');
		await expect(interRow).toContainText('Inter');
		await expect(interRow).toContainText('Virtual');
		await expect(interRow).toContainText('Dia 20');

		await creditCardPage.clearSearch();
		await creditCardPage.search('C6 Bank');
		const c6Row = await creditCardPage.getRowByName('C6 Bank');
		await expect(c6Row).toContainText('C6 Bank');
		await expect(c6Row).toContainText('Físico');
		await expect(c6Row).toContainText('Dia 10');
	});
});

// ---------------------------------------------------------------------------
// 2. CreditCard Search and Filtering (Task 7.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Search and Filtering', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('typing search term filters DataTable to matching cards only', async () => {
		await creditCardPage.search('Nubank');

		const nubankRow = await creditCardPage.getRowByName('Nubank');
		await expect(nubankRow).toBeVisible();

		const rows = await creditCardPage.getTableRows();
		expect(rows.length).toBe(1);
	});

	test('clearing search returns all credit cards', async () => {
		await creditCardPage.search('Nubank');

		let rows = await creditCardPage.getTableRows();
		expect(rows.length).toBe(1);

		await creditCardPage.clearSearch();

		rows = await creditCardPage.getTableRows();
		expect(rows.length).toBeGreaterThan(0);
	});

	test('non-matching search shows empty result', async () => {
		await creditCardPage.search('CartaoInexistente');

		const emptyState = await creditCardPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 3. CreditCard Pagination (Task 8.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Pagination', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('pagination controls visible when cards exceed per-page limit', async () => {
		await expect(creditCardPage.getNextButton()).toBeVisible();
		await expect(creditCardPage.getPreviousButton()).toBeVisible();
	});

	test('clicking "Próxima" navigates to next page', async () => {
		const firstPageRows = await creditCardPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);

		await creditCardPage.goToNextPage();

		const secondPageRows = await creditCardPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);
	});

	test('clicking "Anterior" navigates to previous page', async () => {
		await creditCardPage.goToNextPage();

		const secondPageRows = await creditCardPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);

		await creditCardPage.goToPreviousPage();

		const firstPageRows = await creditCardPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);
	});

	test('"Anterior" disabled on first page', async () => {
		await expect(creditCardPage.getPreviousButton()).toBeDisabled();
	});

	test('"Próxima" disabled on last page', async () => {
		// Navigate to the last page by clicking Next until disabled
		while (await creditCardPage.getNextButton().isEnabled()) {
			await creditCardPage.goToNextPage();
		}
		await expect(creditCardPage.getNextButton()).toBeDisabled();
	});
});

// ---------------------------------------------------------------------------
// 4. CreditCard Creation (Task 10.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Creation', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('clicking "Criar" opens modal with title "Novo Cartão"', async () => {
		await creditCardPage.clickCreateButton();

		const modalTitle = await creditCardPage.getModalTitle();
		expect(modalTitle).toBe('Novo Cartão');
	});

	test('filling all fields and submitting shows success toast', async () => {
		await creditCardPage.clickCreateButton();

		await creditCardPage.fillForm({
			name: 'Cartão Teste E2E',
			closing_day: 5,
			due_day: 12,
			card_type: 'PHYSICAL',
			last_four_digits: '4321',
		});

		await creditCardPage.submitForm();
		await creditCardPage.waitForToast('Cartão criado(a) com sucesso!');
	});

	test('newly created card appears in DataTable', async () => {
		await creditCardPage.clickCreateButton();

		await creditCardPage.fillForm({
			name: 'Cartão Novo Listagem',
			closing_day: 8,
			due_day: 18,
			card_type: 'PHYSICAL',
			last_four_digits: '7777',
		});

		await creditCardPage.submitForm();
		await creditCardPage.waitForToast('Cartão criado(a) com sucesso!');

		await creditCardPage.search('Cartão Novo Listagem');
		const newRow = await creditCardPage.getRowByName('Cartão Novo Listagem');
		await expect(newRow).toBeVisible();
	});

	test('submitting with invalid data shows validation errors', async () => {
		await creditCardPage.clickCreateButton();

		await creditCardPage.fillForm({
			name: '',
			closing_day: 0,
			due_day: 0,
			card_type: 'PHYSICAL',
			last_four_digits: '',
		});

		await creditCardPage.submitForm();

		const nameError = await creditCardPage.getValidationError('name');
		expect(nameError).toBeTruthy();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await creditCardPage.clickCreateButton();

		const isOpen = await creditCardPage.isModalOpen();
		expect(isOpen).toBe(true);

		await creditCardPage.cancelForm();

		await creditCardPage.page.waitForTimeout(500);
		const isOpenAfter = await creditCardPage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 5. CreditCard Editing (Task 11.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Editing', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('clicking edit icon opens modal with title "Editar Cartão"', async () => {
		await creditCardPage.search('Nubank');
		await creditCardPage.clickEditButton('Nubank');

		const modalTitle = await creditCardPage.getModalTitle();
		expect(modalTitle).toBe('Editar Cartão');
	});

	test('form fields pre-populated with existing data', async () => {
		await creditCardPage.search('Nubank');
		await creditCardPage.clickEditButton('Nubank');

		await creditCardPage.page.waitForTimeout(500);

		const name = await creditCardPage.getFormFieldValue('name');
		expect(name).toBe('Nubank');

		const dueDay = await creditCardPage.getFormFieldValue('due_day');
		expect(dueDay).toBe('15');
	});

	test('modifying and submitting shows success toast', async () => {
		await creditCardPage.search('Nubank');
		await creditCardPage.clickEditButton('Nubank');

		const dialog = creditCardPage.page.getByRole('dialog');
		await dialog.locator('[name="name"]').fill('Nubank Editado');

		await creditCardPage.submitForm();
		await creditCardPage.waitForToast('Cartão atualizado(a) com sucesso!');
	});

	test('DataTable reflects updated data', async () => {
		await creditCardPage.search('Inter');
		await creditCardPage.clickEditButton('Inter');

		const dialog = creditCardPage.page.getByRole('dialog');
		await dialog.locator('[name="name"]').fill('Inter Editado');

		await creditCardPage.submitForm();
		await creditCardPage.waitForToast('Cartão atualizado(a) com sucesso!');

		await creditCardPage.search('Inter Editado');
		const updatedRow = await creditCardPage.getRowByName('Inter Editado');
		await expect(updatedRow).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 6. CreditCard Viewing (Task 12.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Viewing', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('clicking view icon opens modal with title "Detalhes do Cartão"', async () => {
		await creditCardPage.search('C6 Bank');
		await creditCardPage.clickViewButton('C6 Bank');

		const modalTitle = await creditCardPage.getModalTitle();
		expect(modalTitle).toBe('Detalhes do Cartão');
	});

	test('all form fields are disabled (read-only)', async () => {
		await creditCardPage.search('C6 Bank');
		await creditCardPage.clickViewButton('C6 Bank');

		expect(await creditCardPage.isFieldDisabled('name')).toBe(true);
		expect(await creditCardPage.isFieldDisabled('closing_day')).toBe(true);
		expect(await creditCardPage.isFieldDisabled('due_day')).toBe(true);
		expect(await creditCardPage.isFieldDisabled('card_type')).toBe(true);
		expect(await creditCardPage.isFieldDisabled('last_four_digits')).toBe(true);
	});

	test('no submit button visible', async () => {
		await creditCardPage.search('C6 Bank');
		await creditCardPage.clickViewButton('C6 Bank');

		const isVisible = await creditCardPage.isSubmitButtonVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 7. CreditCard Deletion (Task 13.1)
// ---------------------------------------------------------------------------

test.describe('CreditCard Deletion', () => {
	let creditCardPage: CreditCardPage;

	test.beforeEach(async ({ page }) => {
		creditCardPage = new CreditCardPage(page);
		await creditCardPage.goto();
	});

	test('clicking delete icon shows confirmation popover', async () => {
		await creditCardPage.search('C6 Bank');
		await creditCardPage.clickDeleteButton('C6 Bank');

		const popoverText = creditCardPage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await creditCardPage.search('Cartão Teste E2E');
		await creditCardPage.clickDeleteButton('Cartão Teste E2E');
		await creditCardPage.confirmDelete();

		await creditCardPage.waitForToast('Cartão excluído(a) com sucesso!');
	});

	test('deleted card removed from DataTable', async () => {
		await creditCardPage.search('C6 Bank');

		const rowBefore = await creditCardPage.getRowByName('C6 Bank');
		await expect(rowBefore).toBeVisible();

		await creditCardPage.clickDeleteButton('C6 Bank');
		await creditCardPage.confirmDelete();
		await creditCardPage.waitForToast('Cartão excluído(a) com sucesso!');

		await creditCardPage.page.waitForTimeout(1000);
		await creditCardPage.search('C6 Bank');

		const emptyState = await creditCardPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});
