import { router } from '@inertiajs/vue3';

import type { PaginationMeta } from '../types/finance';

export function usePagination() {
	function goToPage(routeName: string, page: number, currentFilters: Record<string, unknown> = {}) {
		router.get(routeName, { ...currentFilters, page }, { preserveState: true, preserveScroll: true });
	}

	function hasNextPage(meta: PaginationMeta): boolean {
		return meta.current_page < meta.last_page;
	}

	function hasPrevPage(meta: PaginationMeta): boolean {
		return meta.current_page > 1;
	}

	return { goToPage, hasNextPage, hasPrevPage };
}
