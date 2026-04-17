export type CardType = 'PHYSICAL' | 'VIRTUAL';

export interface CreditCard {
	uid: string;
	name: string;
	closing_day: number;
	due_day: number;
	card_type: CardType;
	last_four_digits: string;
}
