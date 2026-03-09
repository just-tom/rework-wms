export type OrderStatus = {
    label: string;
    value: string;
    classes: string;
};

export type Order = {
    uuid: string;
    status: OrderStatus;
    total: { amount: number; formatted: string };
    productTitle: string | null;
    quantity: number | null;
    warehouseName: string | null;
    createdAt: string;
    canDispatch: boolean;
    canCancel: boolean;
};
