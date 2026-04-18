<script setup lang="ts">
import { computed } from 'vue';

import { store } from '@/actions/App/Domain/Transfer/Controllers/TransferPageController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';

import type { Account } from '@/domain/Account/types/account';
import type { Transfer } from '@/domain/Transfer/types/transfer';
import { transferSchema } from '@/domain/Transfer/validations/transfer-schema';

const props = defineProps<{
	item?: Transfer;
	readonly?: boolean;
	accounts: Account[];
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const action = computed(() => store.url());
const method = computed(() => 'post' as const);

const initialValues = computed(() => ({
	from_account_uid: props.item?.from_account?.uid ?? '',
	to_account_uid: props.item?.to_account?.uid ?? '',
	amount: props.item?.amount ?? 0,
	occurred_at: props.item?.occurred_at?.substring(0, 10) ?? new Date().toISOString().substring(0, 10),
	description: props.item?.description ?? '',
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="transferSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="from_account_uid" label="Conta Origem">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue placeholder="Selecione a conta" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="account in accounts" :key="account.uid" :value="account.uid">
										{{ account.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>

					<ValidatedField name="to_account_uid" label="Conta Destino">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue placeholder="Selecione a conta" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="account in accounts" :key="account.uid" :value="account.uid">
										{{ account.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>
				</div>

				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="amount" label="Valor">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="number"
								step="0.01"
								min="0.01"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>

					<ValidatedField name="occurred_at" label="Data">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="date"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>
				</div>

				<ValidatedField name="description" label="Descrição">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Descrição (opcional)"
							:disabled="props.readonly"
						/>
					</template>
				</ValidatedField>

				<div v-if="!props.readonly" class="flex justify-end gap-2">
					<Button type="button" variant="outline" @click="emit('cancel')">
						Cancelar
					</Button>
					<Button type="submit" :disabled="processing">
						Criar
					</Button>
				</div>
			</div>
		</template>
	</ValidatedInertiaForm>
</template>
