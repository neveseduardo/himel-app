<script setup lang="ts">
import { computed } from 'vue';

import { store, update } from '@/actions/App/Domain/Account/Controllers/AccountPageController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';

import type { Account } from '@/domain/Account/types/account';
import { accountSchema } from '../validations/account-schema';

const props = defineProps<{
	item?: Account;
	readonly?: boolean;
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const isEditing = computed(() => !!props.item);
const action = computed(() =>
	isEditing.value ? update.url(props.item!.uid) : store.url()
);
const method = computed(() => (isEditing.value ? 'put' : 'post'));

const initialValues = computed(() => ({
	name: props.item?.name ?? '',
	type: props.item?.type ?? 'CHECKING',
	balance: props.item?.balance ?? 0,
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="accountSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<ValidatedField name="name" label="Nome">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Nome da conta"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<ValidatedField name="type" label="Tipo">
					<template #default="{ field, handleChange }">
						<Select
							:model-value="field.value as string"
							:disabled="props.readonly"
							@update:model-value="handleChange"
						>
							<SelectTrigger>
								<SelectValue placeholder="Selecione o tipo" />
							</SelectTrigger>
							<SelectContent>
								<SelectItem value="CHECKING">
									Conta Corrente
								</SelectItem>
								<SelectItem value="SAVINGS">
									Poupança
								</SelectItem>
								<SelectItem value="CASH">
									Dinheiro
								</SelectItem>
								<SelectItem value="OTHER">
									Outro
								</SelectItem>
							</SelectContent>
						</Select>
					</template>
				</ValidatedField>

				<ValidatedField name="balance" label="Saldo Inicial">
					<template #default="{ field }">
						<Input
							v-bind="field"
							type="number"
							step="0.01"
							min="0"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<div v-if="!props.readonly" class="flex justify-end gap-2">
					<Button type="button" variant="outline" @click="emit('cancel')">
						Cancelar
					</Button>
					<Button type="submit" :disabled="processing">
						{{ isEditing ? 'Salvar' : 'Criar' }}
					</Button>
				</div>
			</div>
		</template>
	</ValidatedInertiaForm>
</template>
