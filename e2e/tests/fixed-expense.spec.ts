import { expect, test } from '@playwright/test';

import { FixedExpensePage } from '../pages/FixedExpensePage';

// ---------------------------------------------------------------------------
// 1. FixedExpense Listing (Task 5.1)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Listing', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('page renders with title "Despesas Fixas"', async () => {
		const title = await fixedExpensePage.getPageTitle();
		expect(title).toBe('Despesas Fixas');
	});

	test('DataTable displays seeded fixed expense records', async () => {
		await fixedExpensePage.search('Aluguel');
		const aluguelRow = await fixedExpensePage.getRowByDescription('Aluguel');
		await expect(aluguelRow).toBeVisible();

		await fixedExpensePage.clearSearch();
		await fixedExpensePage.search('Internet');
		const internetRow = await fixedExpensePage.getRowByDescription('Internet');
		await expect(internetRow).toBeVisible();

		await fixedExpensePage.clearSearch();
		await fixedExpensePage.search('Academia');
		const academiaRow = await fixedExpensePage.getRowByDescription('Academia');
		await expect(academiaRow).toBeVisible();
	});

	test('each row displays description, formatted amount, due day, and status badge', async () => {
		await fixedExpensePage.search('Aluguel');
		const aluguelRow = await fixedExpensePage.getRowByDescription('Aluguel');
		await expect(aluguelRow).toContainText('Aluguel');
		await expect(aluguelRow).toContainText('1.500,00');
		await expect(aluguelRow).toContainText('10');
		await expect(aluguelRow).toContainText('Ativa');

		await fixedExpensePage.clearSearch();
		await fixedExpensePage.search('Internet');
		const internetRow = await fixedExpensePage.getRowByDescription('Internet');
		await expect(internetRow).toContainText('Internet');
		await expect(internetRow).toContainText('120,00');
		await expect(internetRow).toContainText('15');
		await expect(internetRow).toContainText('Ativa');

		await fixedExpensePage.clearSearch();
		await fixedExpensePage.search('Academia');
		const academiaRow = await fixedExpensePage.getRowByDescription('Academia');
		await expect(academiaRow).toContainText('Academia');
		await expect(academiaRow).toContainText('89,90');
		await expect(academiaRow).toContainText('5');
		await expect(academiaRow).toContainText('Inativa');
	});
});

// ---------------------------------------------------------------------------
// 2. FixedExpense Search and Filtering (Task 5.2)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Search and Filtering', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('typing search term filters DataTable to matching expenses only', async () => {
		await fixedExpensePage.search('Aluguel');

		const aluguelRow = await fixedExpensePage.getRowByDescription('Aluguel');
		await expect(aluguelRow).toBeVisible();

		const rows = await fixedExpensePage.getTableRows();
		expect(rows.length).toBe(1);
	});

	test('clearing search returns all fixed expenses', async () => {
		await fixedExpensePage.search('Aluguel');

		let rows = await fixedExpensePage.getTableRows();
		expect(rows.length).toBe(1);

		await fixedExpensePage.clearSearch();

		rows = await fixedExpensePage.getTableRows();
		expect(rows.length).toBeGreaterThan(1);
	});

	test('non-matching search shows empty result', async () => {
		await fixedExpensePage.search('DespesaInexistente');

		const emptyState = await fixedExpensePage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 3. FixedExpense Pagination (Task 5.3)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Pagination', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('pagination controls visible when expenses exceed per-page limit', async () => {
		await expect(fixedExpensePage.getNextButton()).toBeVisible();
		await expect(fixedExpensePage.getPreviousButton()).toBeVisible();
	});

	test('clicking "Próxima" navigates to next page', async () => {
		const firstPageRows = await fixedExpensePage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);

		await fixedExpensePage.goToNextPage();

		const secondPageRows = await fixedExpensePage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);
	});

	test('clicking "Anterior" navigates to previous page', async () => {
		await fixedExpensePage.goToNextPage();

		const secondPageRows = await fixedExpensePage.getTableRows();
		expect(secondPageRows.length).toBeGreaterThan(0);

		await fixedExpensePage.goToPreviousPage();

		const firstPageRows = await fixedExpensePage.getTableRows();
		expect(firstPageRows.length).toBeGreaterThan(0);
	});

	test('"Anterior" disabled on first page', async () => {
		await expect(fixedExpensePage.getPreviousButton()).toBeDisabled();
	});

	test('"Próxima" disabled on last page', async () => {
		// Navigate to the last page by clicking Next until disabled
		while (await fixedExpensePage.getNextButton().isEnabled()) {
			await fixedExpensePage.goToNextPage();
		}
		await expect(fixedExpensePage.getNextButton()).toBeDisabled();
	});
});


// ---------------------------------------------------------------------------
// 4. FixedExpense Dialog Reopen (Task 5.4)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Dialog Reopen', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('modal reopens after closing via ESC', async () => {
		await fixedExpensePage.clickCreateButton();
		await fixedExpensePage.closeDialogByEsc();

		await fixedExpensePage.clickCreateButton();
		const modalTitle = await fixedExpensePage.getModalTitle();
		expect(modalTitle).toBe('Nova Despesa Fixa');
	});

	test('modal reopens after closing via overlay click', async () => {
		await fixedExpensePage.clickCreateButton();
		await fixedExpensePage.closeDialogByOverlay();

		await fixedExpensePage.clickCreateButton();
		const modalTitle = await fixedExpensePage.getModalTitle();
		expect(modalTitle).toBe('Nova Despesa Fixa');
	});
});

// ---------------------------------------------------------------------------
// 5. FixedExpense Creation (Task 5.5)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Creation', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('clicking "Criar" opens modal with title "Nova Despesa Fixa"', async () => {
		await fixedExpensePage.clickCreateButton();

		const modalTitle = await fixedExpensePage.getModalTitle();
		expect(modalTitle).toBe('Nova Despesa Fixa');
	});

	test('filling all fields and submitting shows success toast', async () => {
		await fixedExpensePage.clickCreateButton();

		// Get the first category option dynamically
		const dialog = fixedExpensePage.page.getByRole('dialog');
		await dialog.getByRole('combobox').click();
		const firstOption = fixedExpensePage.page.getByRole('option').first();
		await firstOption.click();

		// Fill the rest of the form
		await dialog.locator('[name="description"]').fill('Despesa Teste E2E');
		await dialog.locator('[name="amount"]').fill('250');
		await dialog.locator('[name="due_day"]').fill('20');

		// Active checkbox — ensure it's checked
		const checkbox = dialog.locator('#active');
		const isChecked = await checkbox.isChecked();
		if (!isChecked) {
			await checkbox.click();
		}

		await fixedExpensePage.submitForm();
		await fixedExpensePage.waitForToast('Despesa fixa criado(a) com sucesso!');
	});

	test('newly created expense appears in DataTable', async () => {
		await fixedExpensePage.clickCreateButton();

		// Get the first category option dynamically
		const dialog = fixedExpensePage.page.getByRole('dialog');
		await dialog.getByRole('combobox').click();
		const firstOption = fixedExpensePage.page.getByRole('option').first();
		await firstOption.click();

		// Fill the rest of the form
		await dialog.locator('[name="description"]').fill('Despesa Nova Listagem');
		await dialog.locator('[name="amount"]').fill('450');
		await dialog.locator('[name="due_day"]').fill('25');

		// Active checkbox — ensure it's checked
		const checkbox = dialog.locator('#active');
		const isChecked = await checkbox.isChecked();
		if (!isChecked) {
			await checkbox.click();
		}

		await fixedExpensePage.submitForm();
		await fixedExpensePage.waitForToast('Despesa fixa criado(a) com sucesso!');

		await fixedExpensePage.page.waitForURL(/fixed-expenses/, { timeout: 10_000 });
		await fixedExpensePage.page.locator('table').waitFor({ state: 'visible' });

		await fixedExpensePage.search('Despesa Nova Listagem');
		const newRow = await fixedExpensePage.getRowByDescription('Despesa Nova Listagem');
		await expect(newRow).toBeVisible();
	});

	test('submitting with invalid data shows validation errors', async () => {
		await fixedExpensePage.clickCreateButton();

		const dialog = fixedExpensePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('');
		await dialog.locator('[name="amount"]').fill('0');

		await fixedExpensePage.submitForm();

		const descriptionError = await fixedExpensePage.getValidationError('description');
		expect(descriptionError).toBeTruthy();
	});

	test('clicking "Cancelar" closes modal without creating', async () => {
		await fixedExpensePage.clickCreateButton();

		const isOpen = await fixedExpensePage.isModalOpen();
		expect(isOpen).toBe(true);

		await fixedExpensePage.cancelForm();

		await fixedExpensePage.page.getByRole('dialog').waitFor({ state: 'hidden' });
		const isOpenAfter = await fixedExpensePage.isModalOpen();
		expect(isOpenAfter).toBe(false);
	});
});


// ---------------------------------------------------------------------------
// 6. FixedExpense Editing (Task 5.6)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Editing', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('clicking edit icon opens modal with title "Editar Despesa Fixa"', async () => {
		await fixedExpensePage.search('Aluguel');
		await fixedExpensePage.clickEditButton('Aluguel');

		const modalTitle = await fixedExpensePage.getModalTitle();
		expect(modalTitle).toBe('Editar Despesa Fixa');
	});

	test('form fields pre-populated with existing data', async () => {
		await fixedExpensePage.search('Aluguel');
		await fixedExpensePage.clickEditButton('Aluguel');

		await fixedExpensePage.page.getByRole('dialog').locator('[name="description"]').waitFor({ state: 'visible' });

		const description = await fixedExpensePage.getFormFieldValue('description');
		expect(description).toBe('Aluguel');

		const dueDay = await fixedExpensePage.getFormFieldValue('due_day');
		expect(dueDay).toBe('10');
	});

	test('modifying and submitting shows success toast', async () => {
		await fixedExpensePage.search('Aluguel');
		await fixedExpensePage.clickEditButton('Aluguel');

		const dialog = fixedExpensePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('Aluguel Editado');

		await fixedExpensePage.submitForm();
		await fixedExpensePage.waitForToast('Despesa fixa atualizado(a) com sucesso!');
	});

	test('DataTable reflects updated data', async () => {
		await fixedExpensePage.search('Internet');
		await fixedExpensePage.clickEditButton('Internet');

		const dialog = fixedExpensePage.page.getByRole('dialog');
		await dialog.locator('[name="description"]').fill('Internet Editado');

		await fixedExpensePage.submitForm();
		await fixedExpensePage.waitForToast('Despesa fixa atualizado(a) com sucesso!');

		await fixedExpensePage.search('Internet Editado');
		const updatedRow = await fixedExpensePage.getRowByDescription('Internet Editado');
		await expect(updatedRow).toBeVisible();
	});
});

// ---------------------------------------------------------------------------
// 7. FixedExpense Viewing (Task 5.7)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Viewing', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('clicking view icon opens modal with title "Detalhes da Despesa Fixa"', async () => {
		await fixedExpensePage.search('Internet');
		await fixedExpensePage.clickViewButton('Internet');

		const modalTitle = await fixedExpensePage.getModalTitle();
		expect(modalTitle).toBe('Detalhes da Despesa Fixa');
	});

	test('all form fields are disabled (read-only)', async () => {
		await fixedExpensePage.search('Internet');
		await fixedExpensePage.clickViewButton('Internet');

		expect(await fixedExpensePage.isFieldDisabled('description')).toBe(true);
		expect(await fixedExpensePage.isFieldDisabled('amount')).toBe(true);
		expect(await fixedExpensePage.isFieldDisabled('due_day')).toBe(true);
		expect(await fixedExpensePage.isFieldDisabled('category_uid')).toBe(true);
		expect(await fixedExpensePage.isFieldDisabled('active')).toBe(true);
	});

	test('no submit button visible', async () => {
		await fixedExpensePage.search('Internet');
		await fixedExpensePage.clickViewButton('Internet');

		const isVisible = await fixedExpensePage.isSubmitButtonVisible();
		expect(isVisible).toBe(false);
	});
});

// ---------------------------------------------------------------------------
// 8. FixedExpense Deletion (Task 5.8)
// ---------------------------------------------------------------------------

test.describe('FixedExpense Deletion', () => {
	let fixedExpensePage: FixedExpensePage;

	test.beforeEach(async ({ page }) => {
		fixedExpensePage = new FixedExpensePage(page);
		await fixedExpensePage.goto();
	});

	test('clicking delete icon shows confirmation popover', async () => {
		await fixedExpensePage.search('Despesa Teste E2E');
		await fixedExpensePage.clickDeleteButton('Despesa Teste E2E');

		const popoverText = fixedExpensePage.page.getByText('Tem certeza?');
		await expect(popoverText).toBeVisible();
	});

	test('confirming deletion shows success toast', async () => {
		await fixedExpensePage.search('Despesa Teste E2E');
		await fixedExpensePage.clickDeleteButton('Despesa Teste E2E');
		await fixedExpensePage.confirmDelete();

		await fixedExpensePage.waitForToast('Despesa fixa excluído(a) com sucesso!');
	});

	test('deleted expense removed from DataTable', async () => {
		await fixedExpensePage.search('Academia');

		const rowBefore = await fixedExpensePage.getRowByDescription('Academia');
		await expect(rowBefore).toBeVisible();

		await fixedExpensePage.clickDeleteButton('Academia');
		await fixedExpensePage.confirmDelete();
		await fixedExpensePage.waitForToast('Despesa fixa excluído(a) com sucesso!');

		await fixedExpensePage.page.locator('table').waitFor({ state: 'visible' });
		await fixedExpensePage.search('Academia');

		const emptyState = await fixedExpensePage.getEmptyState();
		await expect(emptyState).toBeVisible();
	});
});
