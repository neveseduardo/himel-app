import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
} from '@/actions/App/Domain/Transfer/Controllers/TransferPageController';

import type { TransferServicePort } from '../TransferServicePort';

export class TransferWayfinderAdapter implements TransferServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: {
		amount: number;
		occurred_at: string;
		description?: string;
		from_account_uid: string;
		to_account_uid: string;
	}): Promise<void> {
		router.post(store.url(), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
