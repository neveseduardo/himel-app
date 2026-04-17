import type { Direction } from '@/domain/Shared/types/common';

export interface Category {
	uid: string;
	name: string;
	direction: Direction;
	created_at: string;
}
