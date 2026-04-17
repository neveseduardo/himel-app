export type AccountType = 'CHECKING' | 'SAVINGS' | 'CASH' | 'OTHER';

export interface Account {
	uid: string;
	name: string;
	type: AccountType;
	balance: number;
	created_at: string;
}
