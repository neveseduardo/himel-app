export interface FormatServicePort {
	formatCurrency(value: number): string;
	formatDate(dateString: string): string;
	formatDateTime(dateString: string): string;
}
