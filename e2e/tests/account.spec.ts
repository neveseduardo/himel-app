import { expect, test } from '@playwright/test';

import { AccountPage } from '../pages/AccountPage';

// ---------------------------------------------------------------------------
// 1. Account Listing
// ---------------------------------------------------------------------------

test.describe('Account Listing', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('page renders with title "Contas"', async () => {
		const title = await accountPage.getPageTitle();
		expect(title).toBe('Contas');
	});

	test('DataTable displays seeded account records', async () => {
		await accountPage.search('Conta Corrente BB');
		const bbRow = await accountPage.getRowByName('Conta Corrente BB');
		await expect(bbRow).toBeVisible();

		await accountPage.clearSearch();
		await accountPage.search('Poupança Nubank');
		const nubankRow = await accountPage.getRowByName('Poupança Nubank');
		await expect(nubankRow).toBeVisible();

		await accountPage.clearSearch();
		await accountPage.search('Carteira');
		const carteiraRow = await accountPage.getRowByName('Carteira');
		await expect(carteiraRow).toBeVisible();
	});

	test('each row displays name, type, and balance', async () => {
		await accountPage.search('Conta Corrente BB');
		const bbRow = await accountPage.getRowByName('Conta Corrente BB');
		await expect(bbRow).toContainText('Conta Corrente BB');
		await expect(bbRow).toContainText('Conta Corrente');
		await expect(bbRow).toContainText('5.000,00');

		await accountPage.clearSearch();
		await accountPage.search('Poupança Nubank');
		const nubankRow = await accountPage.getRowByName('Poupança Nubank');
		await expect(nubankRow).toContainText('Poupança Nubank');
		await expect(nubankRow).toContainText('Poupança');
		await expect(nubankRow).toContainText('12.000,00');

		await accountPage.clearSearch();
		await accountPage.search('Carteira');
		const carteiraRow = await accountPage.getRowByName('Carteira');
		await expect(carteiraRow).toContainText('Carteira');
		await expect(carteiraRow).toContainText('Dinheiro');
		await expect(carteiraRow).toContainText('350,50');
	});
});

// ---------------------------------------------------------------------------
// 2. Account Search and Filtering
// ---------------------------------------------------------------------------

test.describe('Account Search and Filtering', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('typing search term filters DataTable to matching accounts only', async () => {
		await accountPage.search('Conta Corrente BB');

		const bbRow = await accountPage.getRowByName('Conta Corrente BB');
		await expect(bbRow).toBeVisible();

		const rows = await accountPage.getTableRows();
		expect(rows.length).toBe(1);
	});

	test('clearing search returns all accounts', async () => {
		await accountPage.search('Conta Corrente BB');

		let rows = await accountPage.getTableRows();
		expect(rows.length).toBe(1);

		await accountPage.clearSearch();

		rows = await accountPage.getTableRows();
		expect(rows.length).toBeGreaterThan(1);
	});

	test('non-matching search shows empty result', async () => {
		await accountPage.search('ContaInexistente');

		const emptyState = await accountPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 3. Account Pagination
// ---------------------------------------------------------------------------

test.describe('Account Pagination', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('pagination controls visible when accounts exceed per-page limit', async () => {
		await expect(accountPage.getNextButton()).toBeVisible();
		await expect(accountPage.getPreviousButton()).toBeVisible();
	});

	test('clicking "Próxima" navigates to next page', async () => {
		const firstPageRows = await accountPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);

		await accountPage.goToNextPage();

		const secondPageRows = await accountPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);
	});

	test('clicking "Anterior" navigates to previous page', async () => {
		await accountPage.goToNextPage();

		const secondPageRows = await accountPage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);

		await accountPage.goToPreviousPage();

		const firstPageRows = await accountPage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);
	});

	test('"Anterior" disabled on first page', async () => {
		await expect(accountPage.getPreviousButton()).toBeDisabled();
	});

	test('"Próxima" disabled on last page', async () => {
		const nextBtn = accountPage.getNextButton();
		while (await nextBtn.isEnabled()) {
			const responsePromise = accountPage.page.waitForResponse(
				(resp) => resp.url().includes('accounts') && resp.status() === 200
			);
			await nextBtn.click();
			await responsePromise;
		}
		await expect(nextBtn).toBeDisabled();
	});
});

// ---------------------------------------------------------------------------
// 4. Account Dialog Reopen
// ---------------------------------------------------------------------------

test.describe('Account Dialog Reopen', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('dialog reopens after closing via ESC', async () => {
		await accountPage.clickCreateButton();
		expect(await accountPage.isModalOpen()).toBe(true);

		await accountPage.closeDialogByEsc();
		expect(await accountPage.isModalOpen()).toBe(false);

		await accountPage.clickCreateButton();
		expect(await accountPage.isModalOpen()).toBe(true);

		const modalTitle = await accountPage.getModalTitle();
		expect(modalTitle).toBe('Nova Conta');
	});

	test('dialog reopens after closing via overlay click', async () => {
		await accountPage.clickCreateButton();
		expect(await accountPage.isModalOpen()).toBe(true);

		await accountPage.closeDialogByOverlay();
		expect(await accountPage.isModalOpen()).toBe(false);

		await accountPage.clickCreateButton();
		expect(await accountPage.isModalOpen()).toBe(true);

		const modalTitle = await accountPage.getModalTitle();
		expect(modalTitle).toBe('Nova Conta');
	});
});

// ---------------------------------------------------------------------------
// 5. Account Creation
// ---------------------------------------------------------------------------

test.describe('Account Creation', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('clicking "Criar" opens modal with title "Nova Conta"', async () => {
		await accountPage.clickCreateButton();

		const modalTitle = await accountPage.getModalTitle();
		expect(modalTitle).toBe('Nova Conta');
	});

	test('filling all fields and submitting shows success toast', async () => {
		await accountPage.clickCreateButton();

		await accountPage.fillForm({
			name: 'Conta Teste E2E',
			type: 'CHECKING',
			balance: 1000,
		});

		await accountPage.submitForm();
		await accountPage.waitForToast('Conta criado(a) com sucesso!');
	});

	test('newly created account appears in DataTable', async () => {
		await accountPage.clickCreateButton();

		await accountPage.fillForm({
			name: 'Conta Nova Listagem',
			type: 'SAVINGS',
			balance: 500,
		});

		await accountPage.submitForm();
		await accountPage.waitForToast('Conta criado(a) com sucesso!');

		await accountPage.search('Conta Nova Listagem');
		const newRow = await accountPage.getRowByName('Conta Nova Listagem');
		await expect(newRow).toBeVisible();
	});

	test('submitting with invalid data shows validation errors', async () => {
		await accountPage.clickCreateButton();

		await accountPage.fillForm({
			name: '',
			type: 'CHECKING',
			balance: 0,
		});

		await accountPage.submitForm();

		const nameError = await accountPage.getValidationError('name');
		expect(nameError).toBeTruthy();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await accountPage.clickCreateButton();

		const isOpen = await accountPage.isModalOpen();
		expect(isOpen).toBe(true);

		await accountPage.cancelForm();

		await accountPage.page.getByRole('dialog').waitFor({ state: 'hidden' });
		const isOpenAfter = await accountPage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 6. Account Editing
// ---------------------------------------------------------------------------

test.describe('Account Editing', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('clicking edit icon opens modal with title "Editar Conta"', async () => {
		await accountPage.search('Conta Corrente BB');
		await accountPage.clickEditButton('Conta Corrente BB');

		const modalTitle = await accountPage.getModalTitle();
		expect(modalTitle).toBe('Editar Conta');
	});

	test('form fields pre-populated with existing data', async () => {
		await accountPage.search('Conta Corrente BB');
		await accountPage.clickEditButton('Conta Corrente BB');

		await accountPage.page.getByRole('dialog').locator('[name="name"]').waitFor({ state: 'visible' });

		const name = await accountPage.getFormFieldValue('name');
		expect(name).toBe('Conta Corrente BB');

		const type = await accountPage.getFormFieldValue('type');
		expect(type).toBe('Conta Corrente');
	});

	test('modifying and submitting shows success toast', async () => {
		await accountPage.search('Conta Corrente BB');
		await accountPage.clickEditButton('Conta Corrente BB');

		const dialog = accountPage.page.getByRole('dialog');
		await dialog.locator('[name="name"]').fill('Conta Corrente BB Editada');

		await accountPage.submitForm();
		await accountPage.waitForToast('Conta atualizado(a) com sucesso!');
	});

	test('DataTable reflects updated data', async () => {
		await accountPage.search('Conta Corrente BB Editada');
		const updatedRow = await accountPage.getRowByName('Conta Corrente BB Editada');
		await expect(updatedRow).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 7. Account Viewing
// ---------------------------------------------------------------------------

test.describe('Account Viewing', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('clicking view icon opens modal with title "Detalhes da Conta"', async () => {
		await accountPage.search('Poupança Nubank');
		await accountPage.clickViewButton('Poupança Nubank');

		const modalTitle = await accountPage.getModalTitle();
		expect(modalTitle).toBe('Detalhes da Conta');
	});

	test('all form fields are disabled (read-only)', async () => {
		await accountPage.search('Poupança Nubank');
		await accountPage.clickViewButton('Poupança Nubank');

		expect(await accountPage.isFieldDisabled('name')).toBe(true);
		expect(await accountPage.isFieldDisabled('type')).toBe(true);
		expect(await accountPage.isFieldDisabled('balance')).toBe(true);
	});

	test('no submit button visible', async () => {
		await accountPage.search('Poupança Nubank');
		await accountPage.clickViewButton('Poupança Nubank');

		const isVisible = await accountPage.isSubmitButtonVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 8. Account Deletion
// ---------------------------------------------------------------------------

test.describe('Account Deletion', () => {
	let accountPage: AccountPage;

	test.beforeEach(async ({ page }) => {
		accountPage = new AccountPage(page);
		await accountPage.goto();
	});

	test('clicking delete icon shows confirmation popover', async () => {
		await accountPage.search('Carteira');
		await accountPage.clickDeleteButton('Carteira');

		const popoverText = accountPage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await accountPage.search('Conta Teste E2E');
		await accountPage.clickDeleteButton('Conta Teste E2E');
		await accountPage.confirmDelete();

		await accountPage.waitForToast('Conta excluído(a) com sucesso!');
	});

	test('deleted account removed from DataTable', async () => {
		await accountPage.search('Carteira');

		const rowBefore = await accountPage.getRowByName('Carteira');
		await expect(rowBefore).toBeVisible();

		await accountPage.clickDeleteButton('Carteira');
		await accountPage.confirmDelete();
		await accountPage.waitForToast('Conta excluído(a) com sucesso!');

		await accountPage.page.locator('table').waitFor({ state: 'visible' });
		await accountPage.search('Carteira');

		const emptyState = await accountPage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});
