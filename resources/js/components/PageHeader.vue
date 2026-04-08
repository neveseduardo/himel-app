<script setup lang="ts">
import type { Component } from 'vue';

import { Button } from '@/components/ui/button';

defineProps<{
	title: string;
	buttonLabel: string;
	buttonIcon?: Component;
}>();

const emit = defineEmits<{
	action: [];
}>();
</script>

<template>
	<div class="flex items-center justify-between">
		<h1 class="text-2xl font-semibold">
			{{ title }}
		</h1>
		<Button size="sm" @click="emit('action')">
			<component :is="buttonIcon" v-if="buttonIcon" class="mr-2 size-4" />
			{{ buttonLabel }}
		</Button>
	</div>
</template>
