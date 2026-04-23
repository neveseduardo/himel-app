<script setup lang="ts">
import { storeTransaction, updateTransaction } from '@/actions/App/Domain/Period/Controllers/PeriodPageController';
import { store, update } from '@/actions/App/Domain/Transaction/Controllers/TransactionPageController';
import type { Account } from '@/domain/Account/types/account';
import type { Transaction } from '@/domain/Transaction/types/transaction';
import { inflowTransactionSchema } from '@/domain/Transaction/validations/inflow-transaction-schema';

const props = defineProps<{
	item?: Transaction;
	accounts: Account[];
	periodUid?: string;
	periodDate?: string;
}>();

const emit = defineEmits<{
	success: [];
	cancel: [];
}>();

const isEditing = computed(() => !!props.item);
const action = computed(() => {
	if (isEditing.value && props.periodUid) {
		return updateTransaction.url({ uid: props.periodUid, transactionUid: props.item!.uid });
	}
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
	amount: props.item?.amount ?? 0,
	description: props.item?.description ?? '',
	occurred_at: props.item?.occurred_at?.substring(0, 10) ?? props.periodDate ?? new Date().toISOString().substring(0, 10),
	direction: 'INFLOW' as const,
	...(props.periodUid ? { period_uid: props.periodUid } : {}),
}));
</script>

<template>
	<ValidatedInertiaForm
		:schema="inflowTransactionSchema"
		:initial-values="initialValues"
		:action="action"
		:method="method"
		@success="emit('success')"
	>
		<template #default="{ processing }">
			<div class="space-y-4">
				<ValidatedField name="account_uid" label="Conta">
					<template #default="{ field, handleChange }">
						<Select
							:model-value="field.value as string"
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

				<ValidatedField name="amount" label="Valor">
					<template #default="{ field }">
						<Input
							v-bind="field"
							type="number"
							step="0.01"
							min="0.01"
						/>
					</template>
				</ValidatedField>

				<ValidatedField name="description" label="Descrição">
					<template #default="{ field }">
						<Input
							v-bind="field"
							placeholder="Descrição (opcional)"
						/>
					</template>
				</ValidatedField>

				<ValidatedField name="occurred_at" label="Data">
					<template #default="{ field }">
						<Input
							v-bind="field"
							type="date"
						/>
					</template>
				</ValidatedField>

				<input type="hidden" name="direction" value="INFLOW">

				<div class="flex justify-end gap-2">
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
