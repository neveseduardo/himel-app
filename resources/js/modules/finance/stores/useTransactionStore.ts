import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Transaction } from '../types/finance'

export const useTransactionStore = defineStore('finance-transactions', () => {
	const isModalOpen = ref(false)
	const modalMode = ref<'create' | 'edit' | 'view'>('create')
	const currentItem = ref<Transaction | null>(null)
	const deletingUid = ref<string | null>(null)

	function openCreateModal() {
		currentItem.value = null
		modalMode.value = 'create'
		isModalOpen.value = true
	}

	function openEditModal(item: Transaction) {
		currentItem.value = item
		modalMode.value = 'edit'
		isModalOpen.value = true
	}

	function openViewModal(item: Transaction) {
		currentItem.value = item
		modalMode.value = 'view'
		isModalOpen.value = true
	}

	function closeModal() {
		isModalOpen.value = false
		setTimeout(() => {
			currentItem.value = null
			modalMode.value = 'create'
		}, 200)
	}

	return {
		isModalOpen,
		modalMode,
		currentItem,
		deletingUid,
		openCreateModal,
		openEditModal,
		openViewModal,
		closeModal,
	}
})
