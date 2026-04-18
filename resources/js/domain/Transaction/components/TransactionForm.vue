<script setup lang="ts">
import { computed } from 'vue';

import { storeTransaction } from '@/actions/App/Domain/Period/Controllers/PeriodPageController';
import { store, update } from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ValidatedField from '@/components/ValidatedField.vue';
import ValidatedInertiaForm from '@/components/ValidatedInertiaForm.vue';
import type { Account } from '@/domain/Account/types/account';
import type { Category } from '@/domain/Category/types/category';
import type { Transaction } from '@/domain/Transaction/types/transaction';
import { transactionSchema } from '@/domain/Transaction/validations/transaction-schema';

const props = defineProps<{
	item?: Transaction;
	readonly?: boolean;
	accounts: Account[];
	categories: Category[];
	periodUid?: string;
	periodDate?: string;
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const isEditing = computed(() => !!props.item);
const action = computed(() => {
	if (isEditing.value) {
		return update.url(props.item!.uid);
	}
	if (props.periodUid) {
		return storeTransaction.url(props.periodUid);
	}
	return store.url();
});
const method = computed(() => (isEditing.value ? 'put' : 'post'));

const initialValues = computed(() => ({
	account_uid: props.item?.account?.uid ?? '',
	category_uid: props.item?.category?.uid ?? '',
	amount: props.item?.amount ?? 0,
	direction: props.item?.direction ?? 'OUTFLOW',
	status: props.item?.status ?? 'PENDING',
	source: props.item?.source ?? 'MANUAL',
	description: props.item?.description ?? '',
	occurred_at: props.item?.occurred_at?.substring(0, 10) ?? props.periodDate ?? new Date().toISOString().substring(0, 10),
	due_date: props.item?.due_date?.substring(0, 10) ?? '',
	paid_at: props.item?.paid_at?.substring(0, 10) ?? '',
	...(props.periodUid ? { period_uid: props.periodUid } : {}),
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="transactionSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<div class="grid gap-4 md:grid-cols-2">
					<ValidatedField name="account_uid" label="Conta">
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

					<ValidatedField name="category_uid" label="Categoria">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue placeholder="Selecione a categoria" />
								</SelectTrigger>
								<SelectContent>
									<SelectItem v-for="category in categories" :key="category.uid" :value="category.uid">
										{{ category.name }}
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>
				</div>

				<div class="grid gap-4 md:grid-cols-3">
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

					<ValidatedField name="direction" label="Direção">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="INFLOW">
										Entrada
									</SelectItem>
									<SelectItem value="OUTFLOW">
										Saída
									</SelectItem>
								</SelectContent>
							</Select>
						</template>
					</ValidatedField>

					<ValidatedField name="status" label="Status">
						<template #default="{ field, handleChange }">
							<Select
								:model-value="field.value as string"
								:disabled="props.readonly"
								@update:model-value="handleChange"
							>
								<SelectTrigger>
									<SelectValue />
								</SelectTrigger>
								<SelectContent>
									<SelectItem value="PENDING">
										Pendente
									</SelectItem>
									<SelectItem value="PAID">
										Pago
									</SelectItem>
								</SelectContent>
							</Select>
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

				<div class="grid gap-4 md:grid-cols-3">
					<ValidatedField name="occurred_at" label="Data">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="date"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>

					<ValidatedField name="due_date" label="Vencimento">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="date"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>

					<ValidatedField name="paid_at" label="Data Pagamento">
						<template #default="{ field }">
							<Input
								v-bind="field"
								type="date"
								:disabled="props.readonly"
							/>
						</template>
					</ValidatedField>
				</div>

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
