import {
	defineConfigWithVueTs,
	vueTsConfigs,
} from '@vue/eslint-config-typescript';
import importPlugin from 'eslint-plugin-import';
import vue from 'eslint-plugin-vue';

export default defineConfigWithVueTs(
	vue.configs['flat/recommended'],
	vueTsConfigs.recommended,
	{
		ignores: [
			'vendor',
			'node_modules',
			'public',
			'bootstrap/ssr',
			'tailwind.config.js',
		],
	},
	{
		plugins: {
			import: importPlugin,
		},

		settings: {
			'import/resolver': {
				typescript: {
					alwaysTryTypes: true,
					project: './tsconfig.json',
				},
			},
		},
		rules: {
			quotes: ['error', 'single'],
			semi: ['error', 'always'],
			'quote-props': 'off',

			'comma-dangle': [
				'error',
				{
					arrays: 'always-multiline',
					objects: 'always-multiline',
					imports: 'always-multiline',
					exports: 'always-multiline',
					functions: 'never',
				},
			],

			'comma-spacing': ['error', { before: false, after: true }],
			'no-var': 'error',
			'prefer-const': 'error',
			eqeqeq: ['error', 'smart'],
			'no-template-curly-in-string': 'error',
			'no-duplicate-imports': 'off',
			'default-param-last': ['error'],
			'arrow-spacing': ['error', { before: true, after: true }],
			'block-spacing': ['error', 'always'],
			'brace-style': ['error', '1tbs', { allowSingleLine: true }],
			'key-spacing': ['error', { mode: 'strict' }],
			'keyword-spacing': ['error', { before: true, after: true }],
			'no-multiple-empty-lines': ['error', { max: 1 }],
			'no-trailing-spaces': ['error', { ignoreComments: true }],
			'no-whitespace-before-property': ['error'],
			'object-curly-newline': ['error', { consistent: true }],
			'object-curly-spacing': ['error', 'always'],
			'operator-linebreak': ['error', 'after'],
			'rest-spread-spacing': ['error', 'never'],
			'space-before-blocks': ['error', 'always'],
			'space-in-parens': ['error', 'never'],
			'space-infix-ops': ['error'],
			'template-curly-spacing': ['error', 'never'],
			camelcase: 'off',
			indent: ['error', 'tab', { SwitchCase: 2 }],
			'no-tabs': 'off',
			'no-console': ['warn', { allow: ['error'] }],
			'no-debugger': ['error'],
			'no-extra-boolean-cast': 'off',
			'@typescript-eslint/no-explicit-any': 'off',
			'import/no-duplicates': 'off',
			'import/order': [
				'error',
				{
					groups: [
						'builtin',
						'external',
						'internal',
						'parent',
						'sibling',
						'index',
					],
					'newlines-between': 'always',
					alphabetize: {
						order: 'asc',
						caseInsensitive: true,
					},
				},
			],
			'vue/html-indent': [
				'error',
				'tab',
				{
					attribute: 1,
					baseIndent: 1,
					closeBracket: 0,
					alignAttributesVertically: true,
				},
			],
			'vue/max-attributes-per-line': 'off',
			'vue/multi-word-component-names': 'off',
			'vue/require-default-prop': 'off',
			'vue/no-template-shadow': 'off',
			'vue/no-v-html': 'off',
		},
	}
);