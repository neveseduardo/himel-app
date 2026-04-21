<script setup lang="ts">
import { Field } from 'vee-validate';

const props = defineProps<{
    name: string;
    label?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
}>();
</script>

<template>
	<Field v-slot="{ field, errorMessage }" :name="props.name">
		<div class="grid gap-2">
			<Label v-if="label" :for="props.name">
				{{ label }}
				<span v-if="required" class="text-destructive">*</span>
			</Label>
			<slot :value="field.value" :handle-change="field.onInput" />
			<span v-if="errorMessage" class="text-xs text-destructive">
				{{ errorMessage }}
			</span>
		</div>
	</Field>
</template>

<script lang="ts">
</script>
