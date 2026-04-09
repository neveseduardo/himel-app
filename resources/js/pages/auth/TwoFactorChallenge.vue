<template>
	<Head title="Autenticação de dois fatores" />

	<div class="space-y-6">
		<template v-if="!showRecoveryInput">
			<Form
				v-slot="{ errors, processing, clearErrors }"
				v-bind="store.form()"
				class="space-y-4"
				reset-on-error
				@error="code = ''"
			>
				<input type="hidden" name="code" :value="code">
				<div
					class="flex flex-col items-center justify-center space-y-3 text-center"
				>
					<div class="flex w-full items-center justify-center">
						<InputOTP
							id="otp"
							v-model="code"
							:maxlength="6"
							:disabled="processing"
							autofocus
						>
							<InputOTPGroup>
								<InputOTPSlot
									v-for="index in 6"
									:key="index"
									:index="index - 1"
								/>
							</InputOTPGroup>
						</InputOTP>
					</div>
					<InputError :message="errors.code" />
				</div>
				<Button type="submit" class="w-full" :disabled="processing">
					Continuar
				</Button>
				<div class="text-center text-sm text-muted-foreground">
					<span>ou você pode </span>
					<button
						type="button"
						class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
						@click="() => toggleRecoveryMode(clearErrors)"
					>
						{{ authConfigContent.buttonText }}
					</button>
				</div>
			</Form>
		</template>

		<template v-else>
			<Form
				v-slot="{ errors, processing, clearErrors }"
				v-bind="store.form()"
				class="space-y-4"
				reset-on-error
			>
				<Input
					name="recovery_code"
					type="text"
					placeholder="Digite o código de recuperação"
					:autofocus="showRecoveryInput"
					required
				/>
				<InputError :message="errors.recovery_code" />
				<Button type="submit" class="w-full" :disabled="processing">
					Continuar
				</Button>

				<div class="text-center text-sm text-muted-foreground">
					<span>ou você pode </span>
					<button
						type="button"
						class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
						@click="() => toggleRecoveryMode(clearErrors)"
					>
						{{ authConfigContent.buttonText }}
					</button>
				</div>
			</Form>
		</template>
	</div>
</template>

<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';

import {
	InputOTP,
	InputOTPGroup,
	InputOTPSlot,
} from '@/components/ui/input-otp';
import { store } from '@/routes/two-factor/login';
import type { TwoFactorConfigContent } from '@/types';

const authConfigContent = computed<TwoFactorConfigContent>(() => {
	if (showRecoveryInput.value) {
		return {
			title: 'Código de recuperação',
			description:
                'Por favor, confirme o acesso à sua conta digitando um dos seus códigos de recuperação de emergência.',
			buttonText: 'entrar usando um código de autenticação',
		};
	}

	return {
		title: 'Código de autenticação',
		description:
            'Digite o código de autenticação fornecido pelo seu aplicativo autenticador.',
		buttonText: 'entrar usando um código de recuperação',
	};
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
	showRecoveryInput.value = !showRecoveryInput.value;
	clearErrors();
	code.value = '';
};

const code = ref<string>('');
</script>
