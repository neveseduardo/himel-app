export interface NavigationPort {
	navigate(url: string, params: Record<string, string>, options?: Record<string, boolean>): void;
}
