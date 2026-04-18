import type { FormatServicePort } from '../FormatServicePort';

export class FormatIntlAdapter implements FormatServicePort {
	formatCurrency(value: number): string {
		return new Intl.NumberFormat('pt-BR', {
			style: 'currency',
			currency: 'BRL',
		}).format(value);
	}

	formatDate(dateString: string): string {
		return new Intl.DateTimeFormat('pt-BR').format(new Date(dateString));
	}

	formatDateTime(dateString: string): string {
		return new Intl.DateTimeFormat('pt-BR', {
			dateStyle: 'short',
			timeStyle: 'short',
		}).format(new Date(dateString));
	}
}
