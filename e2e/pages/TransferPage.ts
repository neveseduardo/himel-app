import type { Locator, Page } from '@playwright/test';

export interface TransferFormData {
	from_account_uid: string;
	to_account_uid: string;
	amount: number;
	occurred_at: string;
	description: string;
}

export class TransferPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/transfers', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', {
			name: 'Transferências',
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

	async getRowByText(text: string): Promise<Locator> {
		return this.page.locator('table tbody tr').filter({
			has: this.page.getByText(text),
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
			(resp) => resp.url().includes('transfers') && resp.status() === 200
		);
		await this.page.getByRole('button', { name: 'Buscar' }).click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async clearSearch(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('transfers') && resp.status() === 200
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
			(resp) => resp.url().includes('transfers') && resp.status() === 200
		);
		await this.getNextButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async goToPreviousPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('transfers') && resp.status() === 200
		);
		await this.getPreviousButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// CRUD Modal interactions (NO edit — Transfer has no edit route)
	// ---------------------------------------------------------------------------

	async clickCreateButton(): Promise<void> {
		await this.page.getByRole('button', { name: 'Criar' }).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickViewButton(text: string): Promise<void> {
		const row = await this.getRowByText(text);
		// Row action buttons order: [0] Eye (view), [1] Trash (delete) — NO Pencil
		await row.getByRole('button').nth(0).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickDeleteButton(text: string): Promise<void> {
		const row = await this.getRowByText(text);
		// [1] is the Trash button wrapper (inside DeleteConfirmPopover)
		await row.getByRole('button').nth(1).click();
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

	async fillForm(data: TransferFormData): Promise<void> {
		const dialog = this.page.getByRole('dialog');

		// from_account_uid — first Select (nth 0)
		await dialog.getByRole('combobox').nth(0).click();
		await this.page.getByRole('option', { name: data.from_account_uid }).click();

		// to_account_uid — second Select (nth 1)
		await dialog.getByRole('combobox').nth(1).click();
		await this.page.getByRole('option', { name: data.to_account_uid }).click();

		await dialog.locator('[name="amount"]').fill(String(data.amount));
		await dialog.locator('[name="occurred_at"]').fill(data.occurred_at);
		await dialog.locator('[name="description"]').fill(data.description);
	}

	async submitForm(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		const submitBtn = dialog.getByRole('button', { name: /Criar/ });
		await submitBtn.click();
	}

	async cancelForm(): Promise<void> {
		const dialog = this.page.getByRole('dialog');
		await dialog.getByRole('button', { name: 'Cancelar' }).click();
	}

	async getFormFieldValue(field: string): Promise<string> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'from_account_uid') {
			return dialog.getByRole('combobox').nth(0).innerText();
		}
		if (field === 'to_account_uid') {
			return dialog.getByRole('combobox').nth(1).innerText();
		}

		return dialog.locator(`[name="${field}"]`).inputValue();
	}

	async isFieldDisabled(field: string): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'from_account_uid') {
			return dialog.getByRole('combobox').nth(0).isDisabled();
		}
		if (field === 'to_account_uid') {
			return dialog.getByRole('combobox').nth(1).isDisabled();
		}

		return dialog.locator(`[name="${field}"]`).isDisabled();
	}

	async isSubmitButtonVisible(): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');
		const submitBtn = dialog.getByRole('button', { name: /Criar/ });
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
			from_account_uid: 'Conta Origem',
			to_account_uid: 'Conta Destino',
			amount: 'Valor',
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
