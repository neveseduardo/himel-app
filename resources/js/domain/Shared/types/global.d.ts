import type { Auth } from '@/types/auth';
import type { ClassValue } from 'clsx';

declare module 'vue' {
	interface ComponentCustomProperties {
		cn: (...inputs: ClassValue[]) => string;
		$inertia: typeof Router;
		$page: Page;
		$headManager: ReturnType<typeof createHeadManager>;
	}
}

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            flash: {
                success?: string;
                error?: string;
            };
            [key: string]: unknown;
        };
    }
}
