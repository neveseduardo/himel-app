import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
	update,
} from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';

import type { TransactionServicePort } from '../TransactionServicePort';

export class TransactionWayfinderAdapter implements TransactionServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: {
		amount: number;
		direction: string;
		description?: string;
		occurred_at: string;
		due_date?: string;
		account_uid: string;
		category_uid?: string;
	}): Promise<void> {
		router.post(store.url(), data);
	}

	async update(uid: string, data: {
		amount: number;
		direction: string;
		description?: string;
		occurred_at: string;
		due_date?: string;
		account_uid: string;
		category_uid?: string;
	}): Promise<void> {
		router.put(update.url(uid), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
