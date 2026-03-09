<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import GetOrdersController from '@/actions/App/Http/Controllers/Orders/GetOrdersController';
import StoreOrderController from '@/actions/App/Http/Controllers/Orders/StoreOrderController';
import GetStockOverviewController from '@/actions/App/Http/Controllers/Stock/GetStockOverviewController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Product } from '@/types';

const { products } = defineProps<{
    products: Product[];
}>();

const form = useForm({
    productUuid: null as string | null,
    quantity: 1,
});

const selectedProduct = computed(() => {
    return (
        products?.find(
            (p: { uuid: string | null }) => p.uuid === form.productUuid,
        ) ?? null
    );
});

const formatTotal = (cents: number): string => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(cents / 100);
};
const submit = () => {
    if (!selectedProduct.value) return;

    form.post(StoreOrderController(), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Place Order" />

    <AppLayout>
        <div
            class="flex h-full flex-1 flex-col items-center justify-center p-4"
        >
            <Card class="w-full max-w-md">
                <CardHeader>
                    <CardTitle>Place Order</CardTitle>
                    <CardDescription>
                        Select a product and quantity to place your order.
                    </CardDescription>
                </CardHeader>

                <form @submit.prevent="submit">
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="product">Product</Label>
                            <Select v-model="form.productUuid">
                                <SelectTrigger id="product" class="w-full">
                                    <SelectValue
                                        placeholder="Select a product"
                                    />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="product in products"
                                        :key="product.uuid"
                                        :value="product.uuid"
                                    >
                                        {{ product.title }} -
                                        {{ product.price.formatted }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p
                                v-if="form.errors.productUuid"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.productUuid }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="quantity">Quantity</Label>
                            <Input
                                id="quantity"
                                v-model.number="form.quantity"
                                type="number"
                                min="1"
                            />
                            <p
                                v-if="form.errors.quantity"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.quantity }}
                            </p>
                        </div>

                        <p class="mb-2 text-sm text-muted-foreground">
                            Total:
                            {{
                                formatTotal(
                                    (selectedProduct?.price.amount ?? 0) *
                                        form.quantity,
                                )
                            }}
                        </p>
                    </CardContent>

                    <CardFooter>
                        <Button
                            type="submit"
                            class="w-full"
                            :disabled="form.processing || !form.productUuid"
                        >
                            {{
                                form.processing
                                    ? 'Placing Order...'
                                    : 'Place Order'
                            }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>

            <div class="mt-4 flex gap-4">
                <Link
                    :href="GetStockOverviewController.url()"
                    class="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                >
                    View Stock
                </Link>
                <Link
                    :href="GetOrdersController.url()"
                    class="text-sm text-muted-foreground underline-offset-4 hover:text-foreground hover:underline"
                >
                    View Orders
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
