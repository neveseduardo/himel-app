import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useFlashMessages() {
	const page = usePage();

	const success = computed(() => (page.props as Record<string, unknown>).flash?.success as string | undefined);
	const error = computed(() => (page.props as Record<string, unknown>).flash?.error as string | undefined);

	return { success, error };
}
