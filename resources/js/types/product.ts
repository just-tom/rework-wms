export type Money = {
    amount: number;
    formatted: string;
};

export type Product = {
    uuid: string;
    title: string;
    price: Money;
};
