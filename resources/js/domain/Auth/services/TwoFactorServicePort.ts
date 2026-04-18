export interface TwoFactorServicePort {
	fetchQrCode(): Promise<{ svg: string; url: string }>;
	fetchSetupKey(): Promise<{ secretKey: string }>;
	fetchRecoveryCodes(): Promise<string[]>;
}
