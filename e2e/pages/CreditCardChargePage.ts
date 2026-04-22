import type { Locator, Page } from '@playwright/test';

export interface CreditCardChargeFormData {
	credit_card_uid: string;
	description: string;
	amount: number;
	total_installments: number;
	purchase_date: string;
}

export class CreditCardChargePage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/credit-card-charges', {
			waitUntil: 'domcontentloaded',
		});
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', {
			name: 'Compras no Cartão',
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
			(resp) => resp.url().includes('credit-card-charges') && resp.status() === 200
		);
		await this.page.getByRole('button', { name: 'Buscar' }).click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async clearSearch(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('credit-card-charges') && resp.status() === 200
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
			(resp) => resp.url().includes('credit-card-charges') && resp.status() === 200
		);
		await this.getNextButton().click();
		await responsePromise;
		await this.page.locator('table').waitFor({ state: 'visible' });
	}

	async goToPreviousPage(): Promise<void> {
		const responsePromise = this.page.waitForResponse(
			(resp) => resp.url().includes('credit-card-charges') && resp.status() === 200
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

	async clickViewButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		// Row action buttons order: [0] Eye (view)
		await row.getByRole('button').nth(0).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickEditButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		// Row action buttons order: [1] Pencil (edit) — pending UI implementation
		await row.getByRole('button').nth(1).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickDeleteButton(desc: string): Promise<void> {
		const row = await this.getRowByDescription(desc);
		// Row action buttons order: [2] Trash (delete) — pending UI implementation
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

	async fillForm(data: CreditCardChargeFormData): Promise<void> {
		const dialog = this.page.getByRole('dialog');

		// Credit card select (reka-ui Select with dynamic options like "Nubank (•••• 1234)")
		await dialog.getByRole('combobox').click();
		await this.page.getByRole('option', { name: new RegExp(data.credit_card_uid) }).click();

		await dialog.locator('[name="description"]').fill(data.description);
		await dialog.locator('[name="purchase_date"]').fill(data.purchase_date);
		await dialog.locator('[name="amount"]').fill(String(data.amount));
		await dialog.locator('[name="total_installments"]').fill(String(data.total_installments));
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

		if (field === 'credit_card_uid') {
			return dialog.getByRole('combobox').innerText();
		}

		return dialog.locator(`[name="${field}"]`).inputValue();
	}

	async isFieldDisabled(field: string): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'credit_card_uid') {
			return dialog.getByRole('combobox').isDisabled();
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
			credit_card_uid: 'Cartão',
			description: 'Descrição',
			purchase_date: 'Data da Compra',
			amount: 'Valor Total',
			total_installments: 'Parcelas',
		};

		const label = labelMap[field];
		if (!label) throw new Error(`Unknown field: ${field}`);

		// ValidatedField renders: div.grid.gap-2 > label[for=name] + slot + span.text-destructive
		const fieldContainer = this.page
			.getByRole('dialog')
			.locator(`label[for="${field}"]`)
			.locator('..');
		const errorSpan = fieldContainer.locator('.text-destructive');
		await errorSpan.waitFor({ state: 'visible', timeout: 5_000 });
		return errorSpan.innerText();
	}
}
