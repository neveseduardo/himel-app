import { router } from '@inertiajs/vue3';

import type { NavigationPort } from '../NavigationPort';

export class InertiaNavigationAdapter implements NavigationPort {
	navigate(url: string, params: Record<string, unknown>, options?: Record<string, boolean>): void {
		router.get(url, params, options);
	}
}
