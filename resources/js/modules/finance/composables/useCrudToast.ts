import { toast } from 'vue-sonner';

type CrudOperation = 'create' | 'update' | 'delete'

export function useCrudToast(entityLabel: string) {
	const successMessages: Record<CrudOperation, string> = {
		create: `${entityLabel} criado(a) com sucesso!`,
		update: `${entityLabel} atualizado(a) com sucesso!`,
		delete: `${entityLabel} excluído(a) com sucesso!`,
	};

	const fallbackErrors: Record<CrudOperation, string> = {
		create: `Erro ao criar ${entityLabel.toLowerCase()}.`,
		update: `Erro ao atualizar ${entityLabel.toLowerCase()}.`,
		delete: `Erro ao excluir ${entityLabel.toLowerCase()}.`,
	};

	function onSuccess(operation: CrudOperation) {
		toast.success(successMessages[operation]);
	}

	function onError(operation: CrudOperation, errors?: Record<string, string>) {
		const message = errors ? Object.values(errors)[0] : fallbackErrors[operation];
		toast.error(message);
	}

	return { onSuccess, onError };
}
