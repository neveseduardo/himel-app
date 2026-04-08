import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useFinanceFilters(initialFilters: Record<string, string | number | null> = {}) {
	const filters = ref({ ...initialFilters });

	function applyFilters(routeName: string) {
		const cleanFilters = Object.fromEntries(
			Object.entries(filters.value).filter(([, v]) => v !== null && v !== ''),
		);
		router.get(routeName, cleanFilters, { preserveState: true, preserveScroll: true });
	}

	function resetFilters(routeName: string) {
		filters.value = { ...initialFilters };
		router.get(routeName, {}, { preserveState: true });
	}

	return { filters, applyFilters, resetFilters };
}
