<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Paginated, StockOverview } from '@/types';

const { products } = defineProps<{
    products: Paginated<StockOverview>;
}>();

const expandedRows = ref<Set<string>>(new Set());

const toggleRow = (uuid: string) => {
    if (expandedRows.value.has(uuid)) {
        expandedRows.value.delete(uuid);
    } else {
        expandedRows.value.add(uuid);
    }
}

const navigateToPage = (page: number): void => {
    const url = products.meta.links.find((l) => l.page === page)?.url;
    if (url) {
        router.visit(url, { preserveScroll: true });
    }
}
</script>

<template>
    <Head title="Stock Overview" />

    <AppLayout>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-semibold">Stock Overview</h1>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-75">Product</TableHead>
                            <TableHead class="text-right"
                                >Allocated To Orders</TableHead
                            >
                            <TableHead class="text-right"
                                >Physical Qty</TableHead
                            >
                            <TableHead class="text-right">Threshold</TableHead>
                            <TableHead class="text-right"
                                >Immediate Despatch</TableHead
                            >
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="products.data.length === 0">
                            <TableRow>
                                <TableCell
                                    colspan="5"
                                    class="text-center text-muted-foreground"
                                >
                                    No products found.
                                </TableCell>
                            </TableRow>
                        </template>
                        <template
                            v-for="product in products.data"
                            :key="product.uuid"
                        >
                            <TableRow
                                class="cursor-pointer"
                                @click="toggleRow(product.uuid)"
                            >
                                <TableCell class="font-medium">
                                    <span
                                        class="mr-2 inline-block w-4 text-center"
                                    >
                                        {{
                                            expandedRows.has(product.uuid)
                                                ? '▼'
                                                : '▶'
                                        }}
                                    </span>
                                    {{ product.title }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ product.allocatedToOrders }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ product.physicalQuantity }}
                                </TableCell>
                                <TableCell class="text-right">
                                    {{ product.totalThreshold }}
                                </TableCell>
                                <TableCell
                                    class="text-right font-semibold"
                                    :class="{
                                        'text-red-600':
                                            product.immediateDespatch < 0,
                                        'text-green-600':
                                            product.immediateDespatch > 0,
                                    }"
                                >
                                    {{ product.immediateDespatch }}
                                </TableCell>
                            </TableRow>
                            <template v-if="expandedRows.has(product.uuid)">
                                <TableRow
                                    v-for="warehouse in product.warehouses"
                                    :key="warehouse.name"
                                    class="bg-muted/50"
                                >
                                    <TableCell
                                        class="pl-12 text-muted-foreground"
                                    >
                                        └ {{ warehouse.name }}
                                    </TableCell>
                                    <TableCell />
                                    <TableCell
                                        class="text-right text-muted-foreground"
                                    >
                                        {{ warehouse.quantity }}
                                    </TableCell>
                                    <TableCell
                                        class="text-right text-muted-foreground"
                                    >
                                        {{ warehouse.threshold }}
                                    </TableCell>
                                    <TableCell />
                                </TableRow>
                            </template>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <div
                v-if="products.meta.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ products.meta.from }} to
                    {{ products.meta.to }} of {{ products.meta.total }} products
                </p>

                <Pagination
                    :total="products.meta.total"
                    :items-per-page="products.meta.per_page"
                    :page="products.meta.current_page"
                    :sibling-count="1"
                    @update:page="navigateToPage"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="
                                    item.value === products.meta.current_page
                                "
                            />
                            <PaginationEllipsis v-else :index="index" />
                        </template>

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
