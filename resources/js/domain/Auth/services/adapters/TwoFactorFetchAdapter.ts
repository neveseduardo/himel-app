import { qrCode, recoveryCodes, secretKey } from '@/routes/two-factor';

import type { TwoFactorServicePort } from '../TwoFactorServicePort';

function getCsrfToken(): string {
	return decodeURIComponent(
		document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? '',
	);
}

async function httpRequest<T>(route: { url: string; method: string }): Promise<T> {
	const response = await fetch(route.url, {
		method: route.method,
		headers: {
			'Accept': 'application/json',
			'X-Requested-With': 'XMLHttpRequest',
			'X-XSRF-TOKEN': getCsrfToken(),
		},
		credentials: 'same-origin',
	});

	if (!response.ok) {
		throw new Error(`HTTP ${response.status}`);
	}

	return response.json() as Promise<T>;
}

export class TwoFactorFetchAdapter implements TwoFactorServicePort {
	async fetchQrCode(): Promise<{ svg: string; url: string }> {
		return httpRequest(qrCode());
	}

	async fetchSetupKey(): Promise<{ secretKey: string }> {
		return httpRequest(secretKey());
	}

	async fetchRecoveryCodes(): Promise<string[]> {
		return httpRequest(recoveryCodes());
	}
}
