import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

import AppLayout from '@/domain/Shared/components/layouts/AppLayout.vue';
import AuthLayout from '@/domain/Shared/components/layouts/AuthLayout.vue';
import SettingsLayout from '@/domain/Shared/components/layouts/settings/Layout.vue';
import { initializeTheme } from '@/domain/Shared/composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
	title: (title) => (title ? `${title} - ${appName}` : appName),
	resolve: (name) => {
		const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', { eager: true });
		const page = pages[`./pages/${name}.vue`];

		if (name === 'Welcome') {
			page.default.layout = undefined;
		} else if (name.startsWith('auth/')) {
			page.default.layout = AuthLayout;
		} else if (name.startsWith('settings/')) {
			page.default.layout = [AppLayout, SettingsLayout];
		} else {
			page.default.layout ??= AppLayout;
		}

		return page;
	},
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(createPinia())
			.mount(el);
	},
	progress: {
		color: '#4B5563',
	},
});

// This will set light / dark mode on page load...
initializeTheme();
