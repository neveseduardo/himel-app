import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
	update,
} from '@/actions/App/Domain/Account/Controllers/AccountPageController';

import type { AccountServicePort } from '../AccountServicePort';

export class AccountWayfinderAdapter implements AccountServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: { name: string; type: string; balance?: number }): Promise<void> {
		router.post(store.url(), data);
	}

	async update(uid: string, data: { name: string; type: string; balance?: number }): Promise<void> {
		router.put(update.url(uid), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
