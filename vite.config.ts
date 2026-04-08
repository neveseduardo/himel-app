import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	plugins: [
		laravel({
			input: ['resources/css/app.css', 'resources/js/app.ts'],
			ssr: 'resources/js/ssr.ts',
			refresh: true,
		}),
		tailwindcss(),
		vue({
			template: {
				transformAssetUrls: {
					base: null,
					includeAbsolute: false,
				},
			},
		}),
		wayfinder({
			formVariants: true,
		}),
		AutoImport({
			dts: './resources/js/types/auto-imports.d.ts',
			imports: ['vue', '@vueuse/core', 'pinia', 'vee-validate'],
		}),
		Components({
			dts: './resources/js/types/components.d.ts',
			dirs: [
				'./resources/js/components',
				'./resources/js/components/ui',
				'./resources/js/components/shared',
				'./resources/js/components/layouts',
				'./resources/js/modules/auth/components',
			],
		}),
	],
});
