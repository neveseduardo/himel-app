import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
	update,
} from '@/actions/App/Domain/CreditCard/Controllers/CreditCardPageController';

import type { CreditCardServicePort } from '../CreditCardServicePort';

export class CreditCardWayfinderAdapter implements CreditCardServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: {
		name: string;
		closing_day: number;
		due_day: number;
		card_type: string;
		last_four_digits: string;
	}): Promise<void> {
		router.post(store.url(), data);
	}

	async update(uid: string, data: {
		name: string;
		closing_day: number;
		due_day: number;
		card_type: string;
		last_four_digits: string;
	}): Promise<void> {
		router.put(update.url(uid), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
