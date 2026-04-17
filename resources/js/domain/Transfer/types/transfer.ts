export interface Transfer {
	uid: string;
	amount: number;
	occurred_at: string;
	description: string | null;
	from_account?: { uid: string; name: string };
	to_account?: { uid: string; name: string };
}
