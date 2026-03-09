<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref } from 'vue';
import CancelOrderController from '@/actions/App/Http/Controllers/Orders/CancelOrderController';
import DispatchOrderController from '@/actions/App/Http/Controllers/Orders/DispatchOrderController';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
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
import type { Order, Paginated } from '@/types';

const { orders } = defineProps<{
    orders: Paginated<Order>;
}>();

const dispatchOrder = (uuid: string) => {
    router.patch(
        DispatchOrderController.url(uuid),
        {},
        {
            preserveScroll: true,
        },
    );
};

const cancelDialogOpen = ref<Record<string, boolean>>({});

const cancelOrder = (uuid: string) => {
    router.patch(
        CancelOrderController.url(uuid),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                cancelDialogOpen.value[uuid] = false;
            },
        },
    );
};

const navigateToPage = (page: number): void => {
    const url = orders.meta.links.find((l) => l.label === String(page))?.url;
    if (url) {
        router.visit(url, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Orders" />

    <AppLayout>
        <div class="flex flex-1 flex-col gap-4 p-4">
            <h1 class="text-2xl font-semibold">Orders</h1>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Order</TableHead>
                            <TableHead>Product</TableHead>
                            <TableHead class="text-right">Qty</TableHead>
                            <TableHead>Warehouse</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Total</TableHead>
                            <TableHead>Created</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="orders.data.length === 0">
                            <TableRow>
                                <TableCell
                                    colspan="8"
                                    class="text-center text-muted-foreground"
                                >
                                    No orders found.
                                </TableCell>
                            </TableRow>
                        </template>
                        <TableRow
                            v-for="order in orders.data"
                            :key="order.uuid"
                        >
                            <TableCell class="font-mono text-xs">
                                {{ order.uuid.slice(0, 8) }}
                            </TableCell>
                            <TableCell>
                                {{ order.productTitle }}
                            </TableCell>
                            <TableCell class="text-right">
                                {{ order.quantity }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{ order.warehouseName ?? '—' }}
                            </TableCell>
                            <TableCell>
                                <Badge
                                    variant="outline"
                                    :class="order.status.classes"
                                >
                                    {{ order.status.label }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                {{ order.total.formatted }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">
                                {{
                                    dayjs(order.createdAt).format(
                                        'DD MMM YYYY HH:mm',
                                    )
                                }}
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        v-if="order.canDispatch"
                                        size="sm"
                                        variant="outline"
                                        @click="dispatchOrder(order.uuid)"
                                    >
                                        Dispatch
                                    </Button>
                                    <Dialog
                                        v-if="order.canCancel"
                                        v-model:open="
                                            cancelDialogOpen[order.uuid]
                                        "
                                    >
                                        <DialogTrigger as-child>
                                            <Button
                                                size="sm"
                                                variant="destructive"
                                            >
                                                Cancel
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent>
                                            <DialogHeader>
                                                <DialogTitle
                                                    >Cancel Order</DialogTitle
                                                >
                                                <DialogDescription>
                                                    Are you sure you want to
                                                    cancel this order? This
                                                    action cannot be undone.
                                                </DialogDescription>
                                            </DialogHeader>
                                            <DialogFooter class="gap-2">
                                                <DialogClose as-child>
                                                    <Button variant="secondary"
                                                        >Cancel</Button
                                                    >
                                                </DialogClose>
                                                <Button
                                                    variant="destructive"
                                                    @click="
                                                        cancelOrder(order.uuid)
                                                    "
                                                >
                                                    Cancel Order
                                                </Button>
                                            </DialogFooter>
                                        </DialogContent>
                                    </Dialog>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div
                v-if="orders.meta.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Showing {{ orders.meta.from }} to {{ orders.meta.to }} of
                    {{ orders.meta.total }} orders
                </p>

                <Pagination
                    :total="orders.meta.total"
                    :items-per-page="orders.meta.per_page"
                    :page="orders.meta.current_page"
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
                                    item.value === orders.meta.current_page
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
