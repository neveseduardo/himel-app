import type { Direction } from '@/domain/Shared/types/common';

export type TransactionStatus = 'PENDING' | 'PAID' | 'OVERDUE';
export type TransactionSource = 'MANUAL' | 'CREDIT_CARD' | 'FIXED' | 'TRANSFER';

export interface Transaction {
	uid: string;
	amount: number;
	direction: Direction;
	status: TransactionStatus;
	source: TransactionSource;
	description: string | null;
	occurred_at: string;
	due_date: string | null;
	paid_at: string | null;
	account?: { uid: string; name: string };
	category?: { uid: string; name: string };
}
