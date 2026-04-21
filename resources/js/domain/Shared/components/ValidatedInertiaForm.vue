<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import type { ZodSchema } from 'zod';

const props = defineProps<{
    schema: ZodSchema;
    initialValues?: Record<string, unknown>;
    action: string;
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    resetOnSuccess?: boolean | string[];
}>();

const emit = defineEmits<{
    success: [response: unknown];
    error: [errors: Record<string, unknown>];
}>();

const { handleSubmit, values, setErrors, resetForm, errors, isSubmitting } =
    useForm({
    	validationSchema: toTypedSchema(props.schema),
    	initialValues: props.initialValues,
    });

defineExpose({ setErrors });

const onSubmit = handleSubmit((validatedValues) => {
	const method = props.method ?? 'post';

	router.visit(props.action, {
		method: method as 'get' | 'post' | 'put' | 'patch' | 'delete',
		data: validatedValues,
		preserveState: true,
		onSuccess: (page) => {
			const resetOnSuccess = props.resetOnSuccess;
			if (resetOnSuccess === true || Array.isArray(resetOnSuccess)) {
				resetForm({
					values:
                        resetOnSuccess === true ?
                        	{} :
                        	Object.fromEntries(
                        		resetOnSuccess.map((f) => [f, ''])
                        	),
				});
			}

			const pageErrors = (page as any).props?.errors;
			if (pageErrors && Object.keys(pageErrors).length > 0) {
				const flatErrors: Record<string, string> = {};
				for (const [key, value] of Object.entries(pageErrors)) {
					if (Array.isArray(value) && value.length > 0) {
						flatErrors[key] = value[0];
					} else if (typeof value === 'string') {
						flatErrors[key] = value;
					}
				}
				setErrors(flatErrors);
				emit('error', flatErrors);
				return;
			}

			emit('success', page);
		},
		onError: (err) => {
			if (err) {
				const flatErrors: Record<string, string> = {};

				if (typeof err === 'object') {
					for (const [key, value] of Object.entries(err)) {
						if (Array.isArray(value)) {
							flatErrors[key] = value[0];
						} else if (typeof value === 'string') {
							flatErrors[key] = value;
						}
					}
				}

				if (Object.keys(flatErrors).length > 0) {
					setErrors(flatErrors);
				}
			}
			emit('error', err);
		},
	});
});
</script>

<template>
	<form @submit.prevent="onSubmit">
		<slot :errors="errors" :values="values" :processing="isSubmitting" />
	</form>
</template>
