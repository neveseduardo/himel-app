<script setup lang="ts">
import { cn } from '@/domain/Shared/lib/utils';
import { reactiveOmit } from '@vueuse/core';
import type { AlertDialogCancelProps } from 'reka-ui';
import { AlertDialogCancel } from 'reka-ui';
import type { HTMLAttributes } from 'vue';

import { buttonVariants } from '@/domain/Shared/components/ui/button';

const props = defineProps<AlertDialogCancelProps & { class?: HTMLAttributes['class'] }>();

const delegatedProps = reactiveOmit(props, 'class');
</script>

<template>
	<AlertDialogCancel
		v-bind="delegatedProps"
		:class="cn(
			buttonVariants({ variant: 'outline' }),
			'mt-2 sm:mt-0',
			props.class,
		)"
	>
		<slot />
	</AlertDialogCancel>
</template>
