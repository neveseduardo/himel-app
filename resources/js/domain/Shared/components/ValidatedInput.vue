<script setup lang="ts">
import { Field } from 'vee-validate';

const props = defineProps<{
    name: string;
    label?: string;
    type?: string;
    placeholder?: string;
    required?: boolean;
    disabled?: boolean;
    autofocus?: boolean;
}>();
</script>

<template>
	<Field v-slot="{ field, errorMessage, meta }" :name="name">
		<div class="grid gap-2">
			<Label v-if="label" :for="props.name">
				{{ label }}
				<span v-if="required" class="text-destructive">*</span>
			</Label>
			<Input
				:id="props.name"
				:type="type"
				:name="props.name"
				:value="field.value"
				:placeholder="placeholder"
				:required="required"
				:disabled="disabled"
				:autofocus="autofocus"
				:aria-invalid="meta.touched ? !meta.valid : undefined"
				@input="field.onInput"
				@blur="field.onBlur"
			/>
			<span v-if="errorMessage" class="text-xs text-destructive">
				{{ errorMessage }}
			</span>
		</div>
	</Field>
</template>

<script lang="ts">
</script>
