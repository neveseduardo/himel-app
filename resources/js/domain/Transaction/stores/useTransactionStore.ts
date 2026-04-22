import { defineStore } from 'pinia';
import { ref } from 'vue';

import type { Transaction } from '../types/transaction';

export const useTransactionStore = defineStore('finance-transactions', () => {
	const inflowModalOpen = ref(false);
	const outflowModalOpen = ref(false);
	const modalMode = ref<'create' | 'edit' | 'view'>('create');
	const currentItem = ref<Transaction | null>(null);
	const deletingUid = ref<string | null>(null);

	function openCreateInflowModal() {
		currentItem.value = null;
		modalMode.value = 'create';
		inflowModalOpen.value = true;
	}

	function openCreateOutflowModal() {
		currentItem.value = null;
		modalMode.value = 'create';
		outflowModalOpen.value = true;
	}

	function openEditModal(item: Transaction) {
		currentItem.value = item;
		modalMode.value = 'edit';
		if (item.direction === 'INFLOW') {
			inflowModalOpen.value = true;
		} else {
			outflowModalOpen.value = true;
		}
	}

	function openViewModal(item: Transaction) {
		currentItem.value = item;
		modalMode.value = 'view';
		if (item.direction === 'INFLOW') {
			inflowModalOpen.value = true;
		} else {
			outflowModalOpen.value = true;
		}
	}

	function closeInflowModal() {
		inflowModalOpen.value = false;
		setTimeout(() => {
			currentItem.value = null;
			modalMode.value = 'create';
		}, 200);
	}

	function closeOutflowModal() {
		outflowModalOpen.value = false;
		setTimeout(() => {
			currentItem.value = null;
			modalMode.value = 'create';
		}, 200);
	}

	return {
		inflowModalOpen,
		outflowModalOpen,
		modalMode,
		currentItem,
		deletingUid,
		openCreateInflowModal,
		openCreateOutflowModal,
		openEditModal,
		openViewModal,
		closeInflowModal,
		closeOutflowModal,
	};
});
