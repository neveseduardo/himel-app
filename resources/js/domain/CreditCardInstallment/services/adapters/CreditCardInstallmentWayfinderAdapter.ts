import { router } from '@inertiajs/vue3';

import {
	destroy,
	index,
	markAsPaid,
	show,
	store,
	update,
} from '@/actions/App/Domain/CreditCardInstallment/Controllers/CreditCardInstallmentController';

import type { CreditCardInstallmentServicePort } from '../CreditCardInstallmentServicePort';

export class CreditCardInstallmentWayfinderAdapter implements CreditCardInstallmentServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: Record<string, unknown>): Promise<void> {
		router.post(store.url(), data as Record<string, string>);
	}

	async show(uid: string): Promise<void> {
		router.get(show.url(uid));
	}

	async update(uid: string, data: Record<string, unknown>): Promise<void> {
		router.put(update.url(uid), data as Record<string, string>);
	}

	async destroy(uid: string): Promise<void> {
		router.delete(destroy.url(uid));
	}

	async markAsPaid(uid: string): Promise<void> {
		router.patch(markAsPaid.url(uid), {});
	}
}
