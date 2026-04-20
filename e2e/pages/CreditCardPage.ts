import type { Locator, Page } from '@playwright/test';

export interface CreditCardFormData {
  name: string;
  closing_day: number;
  due_day: number;
  card_type: 'PHYSICAL' | 'VIRTUAL';
  last_four_digits: string;
}

export class CreditCardPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	// ---------------------------------------------------------------------------
	// Navigation
	// ---------------------------------------------------------------------------

	async goto(): Promise<void> {
		await this.page.goto('/finance/credit-cards');
		await this.page.waitForTimeout(1000);
	}

	// ---------------------------------------------------------------------------
	// Page assertions
	// ---------------------------------------------------------------------------

	async getPageTitle(): Promise<string> {
		const heading = this.page.getByRole('heading', {
			name: 'Cartões de Crédito',
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

	async getRowByName(name: string): Promise<Locator> {
		return this.page.locator('table tbody tr').filter({
			has: this.page.getByText(name, { exact: true }),
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
		await this.page.getByRole('button', { name: 'Buscar' }).click();
		await this.page.waitForTimeout(1000);
	}

	async clearSearch(): Promise<void> {
		await this.page.getByRole('button', { name: 'Limpar' }).click();
		await this.page.waitForTimeout(1000);
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
		await this.getNextButton().click();
		await this.page.waitForTimeout(1000);
	}

	async goToPreviousPage(): Promise<void> {
		await this.getPreviousButton().click();
		await this.page.waitForTimeout(1000);
	}

	// ---------------------------------------------------------------------------
	// CRUD Modal interactions
	// ---------------------------------------------------------------------------

	async clickCreateButton(): Promise<void> {
		await this.page.getByRole('button', { name: 'Criar' }).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickEditButton(cardName: string): Promise<void> {
		const row = await this.getRowByName(cardName);
		// Row action buttons order: [0] Eye (view), [1] Pencil (edit), [2] Trash (delete)
		await row.getByRole('button').nth(1).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickViewButton(cardName: string): Promise<void> {
		const row = await this.getRowByName(cardName);
		await row.getByRole('button').nth(0).click();
		await this.page.getByRole('dialog').waitFor({ state: 'visible' });
	}

	async clickDeleteButton(cardName: string): Promise<void> {
		const row = await this.getRowByName(cardName);
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

	async fillForm(data: CreditCardFormData): Promise<void> {
		const dialog = this.page.getByRole('dialog');

		await dialog.locator('[name="name"]').fill(data.name);
		await dialog.locator('[name="closing_day"]').fill(String(data.closing_day));
		await dialog.locator('[name="due_day"]').fill(String(data.due_day));

		// Card type select
		await dialog.getByRole('combobox').click();
		const optionLabel = data.card_type === 'PHYSICAL' ? 'Físico' : 'Virtual';
		await this.page.getByRole('option', { name: optionLabel }).click();

		await dialog.locator('[name="last_four_digits"]').fill(data.last_four_digits);
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

		if (field === 'card_type') {
			return dialog.getByRole('combobox').innerText();
		}

		return dialog.locator(`[name="${field}"]`).inputValue();
	}

	async isFieldDisabled(field: string): Promise<boolean> {
		const dialog = this.page.getByRole('dialog');

		if (field === 'card_type') {
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
			name: 'Nome',
			closing_day: 'Dia Fechamento',
			due_day: 'Dia Vencimento',
			card_type: 'Tipo',
			last_four_digits: 'Últimos 4 dígitos',
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
