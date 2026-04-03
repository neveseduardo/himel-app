<script setup lang="ts">
import { useVModel } from '@vueuse/core';
import { Eye, EyeOff } from 'lucide-vue-next';
import type { HTMLAttributes } from 'vue';
import { ref } from 'vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps<{
    defaultValue?: string;
    modelValue?: string;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

const modelValue = useVModel(props, 'modelValue', emit, {
	passive: true,
	defaultValue: props.defaultValue,
});

const type = ref<'password' | 'text'>('password');

function toggle() {
	type.value = type.value === 'password' ? 'text' : 'password';
}
</script>

<template>
	<div class="relative">
		<Input
			v-model="modelValue"
			data-slot="input"
			:type="type"
			class="pe-10"
			:class="props.class"
		/>
		<Button
			type="button"
			variant="ghost"
			size="sm"
			class="absolute inset-y-0 end-0 h-full px-2 hover:bg-transparent"
			@click="toggle"
		>
			<Eye
				v-if="type === 'password'"
				class="size-4 text-muted-foreground"
			/>
			<EyeOff v-else class="size-4 text-muted-foreground" />
			<span class="sr-only">
				{{ type === "password" ? "Mostrar senha" : "Ocultar senha" }}
			</span>
		</Button>
	</div>
</template>
