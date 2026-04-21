import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';
import { defineConfig } from 'vite';

export default defineConfig({
	resolve: {
		alias: {
			'@/lib': '/home/eduardo/projetos/himel-app/resources/js/domain/Shared/lib',
			'@/composables': '/home/eduardo/projetos/himel-app/resources/js/domain/Shared/composables',
			'@/types': '/home/eduardo/projetos/himel-app/resources/js/domain/Shared/types',
		},
	},
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
			dts: './resources/js/domain/Shared/types/imports.d.ts',
			imports: ['vue', '@vueuse/core', 'pinia', 'vee-validate'],
		}),
		Components({
			dts: './resources/js/domain/Shared/types/components.d.ts',
			dirs: [
				'./resources/js/domain/Shared/components/ui',
				'./resources/js/domain/Shared/components',
				'./resources/js/domain/Shared/components/layouts',
				'./resources/js/domain/Auth/components',
				'./resources/js/domain/Settings/components',
			],
		}),
	],
});
