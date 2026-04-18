import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useFlashMessages() {
	const page = usePage();

	const success = computed(() => page.props.flash?.success);
	const error = computed(() => page.props.flash?.error);

	return { success, error };
}
