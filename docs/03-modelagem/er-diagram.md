# OrderBox - Diagramas de Relacionamento

## Diagrama Atual

```mermaid
erDiagram

    COMPANIES ||--o{ USERS : possui

    COMPANIES ||--o{ CUSTOMERS : possui

    CUSTOMERS ||--o{ CUSTOMER_ADDRESSES : possui

    CUSTOMERS ||--o{ CUSTOMER_CONTACTS : possui
```

---

## Diagrama Comercial (Planejado)

```mermaid
erDiagram

    COMPANIES ||--o{ USERS : possui

    COMPANIES ||--o{ CUSTOMERS : possui

    CUSTOMERS ||--o{ CUSTOMER_ADDRESSES : possui

    CUSTOMERS ||--o{ CUSTOMER_CONTACTS : possui

    COMPANIES ||--o{ PRODUCTS : possui

    PRODUCTS }o--|| CATEGORIES : pertence

    PRODUCTS }o--|| BRANDS : pertence

    PRODUCTS ||--o{ PRODUCT_PRICES : possui

    COMPANIES ||--o{ ORDERS : possui

    CUSTOMERS ||--o{ ORDERS : realiza

    USERS ||--o{ ORDERS : cria

    ORDERS ||--o{ ORDER_ITEMS : contem

    PRODUCTS ||--o{ ORDER_ITEMS : vendido
```

---

## Diagrama Estoque (Planejado)

```mermaid
erDiagram

    COMPANIES ||--o{ WAREHOUSES : possui

    WAREHOUSES ||--o{ STOCK_BALANCES : controla

    PRODUCTS ||--o{ STOCK_BALANCES : saldo

    WAREHOUSES ||--o{ STOCK_MOVEMENTS : movimenta

    PRODUCTS ||--o{ STOCK_MOVEMENTS : movimentado
```

---

## Diagrama Financeiro (Planejado)

```mermaid
erDiagram

    CUSTOMERS ||--o{ ACCOUNTS_RECEIVABLE : possui

    ORDERS ||--o{ ACCOUNTS_RECEIVABLE : gera

    ACCOUNTS_RECEIVABLE ||--o{ PAYMENTS : recebe
```

---

## Diagrama Completo (Visão Futura)

```mermaid
erDiagram

    COMPANIES ||--o{ USERS : possui

    COMPANIES ||--o{ CUSTOMERS : possui

    CUSTOMERS ||--o{ CUSTOMER_ADDRESSES : possui

    CUSTOMERS ||--o{ CUSTOMER_CONTACTS : possui

    COMPANIES ||--o{ PRODUCTS : possui

    PRODUCTS }o--|| CATEGORIES : pertence

    PRODUCTS }o--|| BRANDS : pertence

    PRODUCTS ||--o{ PRODUCT_PRICES : possui

    COMPANIES ||--o{ ORDERS : possui

    CUSTOMERS ||--o{ ORDERS : realiza

    USERS ||--o{ ORDERS : cria

    ORDERS ||--o{ ORDER_ITEMS : contem

    PRODUCTS ||--o{ ORDER_ITEMS : vendido

    COMPANIES ||--o{ WAREHOUSES : possui

    WAREHOUSES ||--o{ STOCK_BALANCES : controla

    PRODUCTS ||--o{ STOCK_BALANCES : saldo

    WAREHOUSES ||--o{ STOCK_MOVEMENTS : movimenta

    PRODUCTS ||--o{ STOCK_MOVEMENTS : movimentado

    CUSTOMERS ||--o{ ACCOUNTS_RECEIVABLE : possui

    ORDERS ||--o{ ACCOUNTS_RECEIVABLE : gera

    ACCOUNTS_RECEIVABLE ||--o{ PAYMENTS : recebe
```