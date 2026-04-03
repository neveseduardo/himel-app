<script setup lang="ts">
import { Field } from 'vee-validate';

const props = defineProps<{
    name: string;
    label?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    autofocus?: boolean;
}>();
</script>

<template>
	<Field v-slot="{ field, errorMessage, meta }" :name="props.name">
		<div class="grid gap-2">
			<Label v-if="label" :for="props.name">
				{{ label }}
				<span v-if="required" class="text-destructive">*</span>
			</Label>
			<PasswordInput
				:id="props.name"
				:model-value="field.value"
				:name="props.name"
				:placeholder="placeholder"
				:required="required"
				:disabled="disabled"
				:autofocus="autofocus"
				:aria-invalid="meta.touched ? !meta.valid : undefined"
				@update:model-value="field.onInput"
				@blur="field.onBlur"
			/>
			<span v-if="errorMessage" class="text-xs text-destructive">
				{{ errorMessage }}
			</span>
		</div>
	</Field>
</template>

<script lang="ts">
import { Label } from '@/components/ui/label';
</script>
