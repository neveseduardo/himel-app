import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	store,
	update,
} from '@/actions/App/Domain/Category/Controllers/CategoryPageController';

import type { CategoryServicePort } from '../CategoryServicePort';

export class CategoryWayfinderAdapter implements CategoryServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: { name: string; direction: string }): Promise<void> {
		router.post(store.url(), data);
	}

	async update(uid: string, data: { name: string; direction: string }): Promise<void> {
		router.put(update.url(uid), data);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}
}
