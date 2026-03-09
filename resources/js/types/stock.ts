export type WarehouseBreakdown = {
    name: string;
    quantity: number;
    threshold: number;
};

export type StockOverview = {
    uuid: string;
    title: string;
    allocatedToOrders: number;
    physicalQuantity: number;
    totalThreshold: number;
    immediateDespatch: number;
    warehouses: WarehouseBreakdown[];
};
