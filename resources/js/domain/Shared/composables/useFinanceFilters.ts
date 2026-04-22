import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useFinanceFilters(initialFilters: Record<string, string> = {}) {
	const filters = ref({ ...initialFilters });

	function applyFilters(routeName: string) {
		const cleanFilters = Object.fromEntries(
			Object.entries(filters.value).filter(
				([k, v]) => k !== 'page' && k !== 'per_page' && v !== null && v !== '' && v !== 'all'
			)
		);
		router.get(routeName, cleanFilters, { preserveState: true, preserveScroll: true });
	}

	function resetFilters(routeName: string) {
		filters.value = {};
		router.get(routeName, {}, { preserveState: true });
	}

	return { filters, applyFilters, resetFilters };
}
