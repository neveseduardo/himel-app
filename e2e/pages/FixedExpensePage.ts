import type { Locator, Page } from '@playwright/test';

export interface FixedExpenseFormData {
	description: string;
	amount: number;
	due_day: number;
	category_uid: string; // category name for combobox selection
	active: boolean;
}

export class FixedExpensePage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/fixed-expenses', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', {
			name: 'Despesas Fixas',
		});
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

	async getRowByDescription(desc: string): Promise<Locator> {
		return this.page.locator('table tbody tr').filter({
			has: this.page.getByText(desc, { exact: true }),
		});
	}

	async getEmptyState(): Promise<Locator> {
		return this.page.getByText('Nenhum registro encontrado.');
	}

	// ---------------------------------------------------------------------------
	// Search & Filter
	// ---------------------------------------------------------------------------

	async search(term: string): Promise<void> {
		const input = this.page.getByPlaceholder('Buscar');
		await input.fill(term);
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('fixed-expenses') && resp.status() === 200
		);
		await this.page.getByRole('button', { name: 'Buscar' }).click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async clearSearch(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('fixed-expenses') && resp.status() === 200
		);
		await this.page.getByRole('button', { name: 'Limpar' }).click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
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
			(resp) => resp.url().includes('fixed-expenses') && resp.status() === 200
		);
		await this.getNextButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async goToPreviousPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('fixed-expenses') && resp.status() === 200
		);
		await this.getPreviousButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// CRUD Modal interactions
	// ---------------------------------------------------------------------------

	async clickCreateButton(): Promise<void> {
		await this.page.getByRole('button', { name: 'Criar' }).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickEditButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		// Row action buttons order: [0] Eye (view), [1] Pencil (edit), [2] Trash (delete)
		await row.getByRole('button').nth(1).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickViewButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		await row.getByRole('button').nth(0).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickDeleteButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		await row.getByRole('button').nth(2).click();
	}

	// ---------------------------------------------------------------------------
	// Modal assertions
	// ---------------------------------------------------------------------------

	async getModalTitle(): Promise<string> {
		const title = this.page.getByRole('dialog').getByRole('heading');
		await title.waitFor({ state: 'visible' });
		return title.innerText();
	}

	async isModalOpen(): Promise<boolean> {
		return this.page.getByRole('dialog').isVisible();
	}

	// ---------------------------------------------------------------------------
	// Form interactions
	// ---------------------------------------------------------------------------

	async fillForm(data: FixedExpenseFormData): Promise<void> {
		const dialog = this.page.getByRole('dialog');

		// Text input
		await dialog.locator('[name="description"]').fill(data.description);
		// Number inputs
		await dialog.locator('[name="amount"]').fill(String(data.amount));
		await dialog.locator('[name="due_day"]').fill(String(data.due_day));

		// Category select (reka-ui Select combobox)
		await dialog.getByRole('combobox').click();
		await this.page.getByRole('option', { name: new RegExp(data.category_uid) }).click();

		// Active checkbox
		const checkbox = dialog.locator('#active');
		const isChecked = await checkbox.isChecked();
		if (isChecked !== data.active) {
			await checkbox.click();
		}
	}

	async submitForm(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		const submitBtn = dialog.getByRole('button', { name: /Criar|Salvar/ });
		await submitBtn.click();
	}

	async cancelForm(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		await dialog.getByRole('button', { name: 'Cancelar' }).click();
	}

	async getFormFieldValue(field: string): Promise<string> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'category_uid') {
			return dialog.getByRole('combobox').innerText();
		}

		if (field === 'active') {
			const isChecked = await dialog.locator('#active').isChecked();
			return String(isChecked);
		}

		return dialog.locator(`[name="${field}"]`).inputValue();
	}

	async isFieldDisabled(field: string): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'category_uid') {
			return dialog.getByRole('combobox').isDisabled();
		}

		if (field === 'active') {
			return dialog.locator('#active').isDisabled();
		}

		return dialog.locator(`[name="${field}"]`).isDisabled();
	}

	async isSubmitButtonVisible(): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');
		const submitBtn = dialog.getByRole('button', { name: /Criar|Salvar/ });
		return submitBtn.isVisible();
	}

	// ---------------------------------------------------------------------------
	// Dialog close helpers
	// ---------------------------------------------------------------------------

	async closeDialogByEsc(): Promise<void> {
		await this.page.keyboard.press('Escape');
		await this.page.getByRole('dialog').waitFor({ state: 'hidden' });
	}

	async closeDialogByOverlay(): Promise<void> {
		const overlay = this.page.locator('[data-slot="dialog-overlay"]');
		await overlay.click({ position: { x: 10, y: 10 } });
		await this.page.getByRole('dialog').waitFor({ state: 'hidden' });
	}

	// ---------------------------------------------------------------------------
	// Delete confirmation
	// ---------------------------------------------------------------------------

	async confirmDelete(): Promise<void> {
		await this.page.getByRole('button', { name: 'Excluir' }).click();
	}

	// ---------------------------------------------------------------------------
	// Toast assertions
	// ---------------------------------------------------------------------------

	async waitForToast(message: string): Promise<void> {
		await this.page
			.getByText(message)
			.waitFor({ state: 'visible', timeout: 5_000 });
	}

	// ---------------------------------------------------------------------------
	// Validation errors
	// ---------------------------------------------------------------------------

	async getValidationError(field: string): Promise<string> {
		const labelMap: Record<string, string> = {
			description: 'Descrição',
			amount: 'Valor',
			due_day: 'Dia Vencimento',
			category_uid: 'Categoria',
		};

		const label = labelMap[field];
		if (!label) throw new Error(`Unknown field: ${field}`);

		// ValidatedField renders: <label> then <input/select> then <span class="text-destructive">
		const fieldContainer = this.page
			.getByRole('dialog')
			.locator(`label:has-text("${label}")`)
			.locator('..');
		const errorSpan = fieldContainer.locator('.text-destructive');
		await errorSpan.waitFor({ state: 'visible', timeout: 5_000 });
		return errorSpan.innerText();
	}
}
