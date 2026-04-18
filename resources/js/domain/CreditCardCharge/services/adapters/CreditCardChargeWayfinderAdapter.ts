import { router } from '@inertiajs/vue3';

import {
	index,
	store,
} from '@/actions/App/Domain/CreditCardCharge/Controllers/CreditCardChargePageController';

import type { CreditCardChargeServicePort } from '../CreditCardChargeServicePort';

export class CreditCardChargeWayfinderAdapter implements CreditCardChargeServicePort {
	async fetchAll(filters?: Record<string, string>): Promise<void> {
		router.get(index.url(), filters ?? {}, {
			preserveState: true,
			preserveScroll: true,
		});
	}

	async create(data: {
		description: string;
		total_amount: number;
		installments: number;
		purchase_date: string;
		credit_card_uid: string;
	}): Promise<void> {
		router.post(store.url(), data);
	}
}
