<script setup lang="ts">

export interface Column {
	key: string;
	label: string;
}

defineProps<{
	columns: Column[];
	data: Record<string, unknown>[];
	loading?: boolean;
}>();
</script>

<template>
	<div class="rounded-md border">
		<Table>
			<TableHeader>
				<TableRow>
					<TableHead v-for="col in columns" :key="col.key">
						{{ col.label }}
					</TableHead>
				</TableRow>
			</TableHeader>
			<TableBody>
				<template v-if="loading">
					<TableRow v-for="i in 5" :key="i">
						<TableCell v-for="col in columns" :key="col.key">
							<Skeleton class="h-4 w-full" />
						</TableCell>
					</TableRow>
				</template>
				<template v-else-if="data.length === 0">
					<TableEmpty :colspan="columns.length">
						Nenhum registro encontrado.
					</TableEmpty>
				</template>
				<template v-else>
					<TableRow v-for="(row, idx) in data" :key="idx">
						<TableCell v-for="col in columns" :key="col.key">
							<slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
								{{ row[col.key] }}
							</slot>
						</TableCell>
					</TableRow>
				</template>
			</TableBody>
		</Table>
	</div>
</template>
