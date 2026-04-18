export interface NavigationPort {
	navigate(url: string, params: Record<string, unknown>, options?: Record<string, boolean>): void;
}
