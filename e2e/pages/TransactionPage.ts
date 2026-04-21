import type { Locator, Page } from '@playwright/test';

export interface TransactionFormData {
	account_uid: string;
	category_uid: string;
	amount: number;
	direction: 'INFLOW' | 'OUTFLOW';
	status: 'PENDING' | 'PAID';
	description: string;
	occurred_at: string;
	due_date: string;
	paid_at: string;
}

export class TransactionPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/finance/transactions', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', {
			name: 'Transações',
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
			(resp) => resp.url().includes('transactions') && resp.status() === 200
		);
		await this.page.getByRole('button', { name: 'Buscar' }).click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async clearSearch(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('transactions') && resp.status() === 200
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
			(resp) => resp.url().includes('transactions') && resp.status() === 200
		);
		await this.getNextButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async goToPreviousPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('transactions') && resp.status() === 200
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

	async fillForm(data: TransactionFormData): Promise<void> {
		const dialog = this.page.getByRole('dialog');

		// account_uid — first Select (nth 0)
		if (data.account_uid) {
			await dialog.getByRole('combobox').nth(0).click();
			await this.page.getByRole('option', { name: data.account_uid }).click();
		}

		// category_uid — second Select (nth 1)
		if (data.category_uid) {
			await dialog.getByRole('combobox').nth(1).click();
			await this.page.getByRole('option', { name: data.category_uid }).click();
		}

		// amount — number input
		await dialog.locator('[name="amount"]').fill(String(data.amount));

		// direction — third Select (nth 2)
		if (data.direction) {
			const directionMap: Record<string, string> = {
				INFLOW: 'Entrada',
				OUTFLOW: 'Saída',
			};
			await dialog.getByRole('combobox').nth(2).click();
			await this.page.getByRole('option', { name: directionMap[data.direction] }).click();
		}

		// status — fourth Select (nth 3)
		if (data.status) {
			const statusMap: Record<string, string> = {
				PENDING: 'Pendente',
				PAID: 'Pago',
			};
			await dialog.getByRole('combobox').nth(3).click();
			await this.page.getByRole('option', { name: statusMap[data.status] }).click();
		}

		// description — text input
		await dialog.locator('[name="description"]').fill(data.description);

		// occurred_at — date input
		await dialog.locator('[name="occurred_at"]').fill(data.occurred_at);

		// due_date — date input (optional)
		if (data.due_date) {
			await dialog.locator('[name="due_date"]').fill(data.due_date);
		}

		// paid_at — date input (optional)
		if (data.paid_at) {
			await dialog.locator('[name="paid_at"]').fill(data.paid_at);
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

	// ---------------------------------------------------------------------------
	// Form field helpers
	// ---------------------------------------------------------------------------

	async getFormFieldValue(field: string): Promise<string> {
		const dialog = this.page.getByRole('dialog');

		const comboboxMap: Record<string, number> = {
			account_uid: 0,
			category_uid: 1,
			direction: 2,
			status: 3,
		};

		if (field in comboboxMap) {
			return dialog.getByRole('combobox').nth(comboboxMap[field]).innerText();
		}

		return dialog.locator(`[name="${field}"]`).inputValue();
	}

	async isFieldDisabled(field: string): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');

		const comboboxMap: Record<string, number> = {
			account_uid: 0,
			category_uid: 1,
			direction: 2,
			status: 3,
		};

		if (field in comboboxMap) {
			return dialog.getByRole('combobox').nth(comboboxMap[field]).isDisabled();
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
			account_uid: 'Conta',
			category_uid: 'Categoria',
			amount: 'Valor',
			direction: 'Direção',
			status: 'Status',
			description: 'Descrição',
			occurred_at: 'Data',
		};

		const label = labelMap[field];
		if (!label) throw new Error(`Unknown field: ${field}`);

		const fieldContainer = this.page
			.getByRole('dialog')
			.locator(`label:has-text("${label}")`)
			.locator('..');
		const errorSpan = fieldContainer.locator('.text-destructive');
		await errorSpan.waitFor({ state: 'visible', timeout: 5_000 });
		return errorSpan.innerText();
	}
}
