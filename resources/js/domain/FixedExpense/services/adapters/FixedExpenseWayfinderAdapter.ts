import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
	update,
} from '@/actions/App/Domain/FixedExpense/Controllers/FixedExpensePageController';

import type { FixedExpenseServicePort } from '../FixedExpenseServicePort';

export class FixedExpenseWayfinderAdapter implements FixedExpenseServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: {
		description: string;
		amount: number;
		due_day: number;
		category_uid?: string;
	}): Promise<void> {
		router.post(store.url(), data);
	}

	async update(uid: string, data: {
		description: string;
		amount: number;
		due_day: number;
		category_uid?: string;
	}): Promise<void> {
		router.put(update.url(uid), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
