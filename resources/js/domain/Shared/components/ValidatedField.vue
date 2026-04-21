<script setup lang="ts">
import { Field } from 'vee-validate';

const props = defineProps<{
    name: string;
    label?: string;
    rules?: string;
}>();
</script>

<template>
	<Field
		v-slot="{ field, errorMessage, meta }"
		:name="props.name"
		:rules="props.rules"
	>
		<div class="grid gap-2">
			<label
				v-if="props.label"
				:for="props.name"
				class="text-sm font-medium"
			>
				{{ props.label }}
			</label>
			<slot
				:field="field"
				:value="field.value"
				:handle-change="field.onInput"
				:error="errorMessage"
				:meta="meta"
			/>
			<span v-if="errorMessage" class="text-xs text-destructive">
				{{ errorMessage }}
			</span>
		</div>
	</Field>
</template>
