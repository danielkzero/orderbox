# OrderBox - Diagramas de Relacionamento

## Diagrama Atual

```mermaid
erDiagram

    COMPANIES ||--o{ USERS : possui

    COMPANIES ||--o{ AUTHENTICATION_SESSIONS : possui

    USERS ||--o{ AUTHENTICATION_SESSIONS : autentica

    COMPANIES ||--o{ AUTHENTICATION_CHALLENGES : possui

    USERS ||--o{ AUTHENTICATION_CHALLENGES : confirma

    COMPANIES ||--o{ CUSTOMERS : possui

    CUSTOMERS ||--o{ CUSTOMER_ADDRESSES : possui

    CUSTOMERS ||--o{ CUSTOMER_CONTACTS : possui

    COMPANIES ||--o{ CATEGORIES : possui

    COMPANIES ||--o{ BRANDS : possui

    COMPANIES ||--o{ UNITS : possui

    COMPANIES ||--o{ PRODUCTS : possui

    PRODUCTS }o--|| CATEGORIES : pertence

    PRODUCTS }o--o| BRANDS : pertence

    PRODUCTS }o--|| UNITS : utiliza

    PRODUCTS ||--o{ PRODUCT_PRICES : possui

    COMPANIES ||--o{ PRICE_TABLES : possui

    COMPANIES ||--o{ REGIONS : possui

    REGIONS ||--o{ PRICE_TABLES : habilita

    REGIONS ||--o{ CUSTOMERS : classifica

    REGIONS ||--o{ SALES_REPRESENTATIVES : organiza

    PRICE_TABLES ||--o{ PRODUCT_PRICES : define

    COMPANIES ||--o{ SALES_REPRESENTATIVES : possui

    USERS ||--o| SALES_REPRESENTATIVES : representa

    CUSTOMERS ||--o{ CUSTOMER_REPRESENTATIVES : possui

    SALES_REPRESENTATIVES ||--o{ CUSTOMER_REPRESENTATIVES : atende

    SALES_REPRESENTATIVES ||--o{ ORDERS : atende

    COMPANIES ||--o{ ORDERS : possui

    CUSTOMERS ||--o{ ORDERS : realiza

    USERS ||--o{ ORDERS : cria

    PRICE_TABLES ||--o{ ORDERS : utiliza

    ORDERS ||--o{ ORDER_ITEMS : contem

    PRODUCTS ||--o{ ORDER_ITEMS : vendido

    COMPANIES ||--o{ AUDIT_LOGS : possui

    USERS ||--o{ AUDIT_LOGS : realiza
```

---

## Diagrama Mobile

```mermaid
erDiagram

    COMPANIES ||--o{ DEVICES : possui

    USERS ||--o{ DEVICES : utiliza

    COMPANIES ||--o{ SYNC_LOGS : possui

    DEVICES ||--o{ SYNC_LOGS : gera

    COMPANIES ||--o{ SYNC_OPERATIONS : possui

    DEVICES ||--o{ SYNC_OPERATIONS : envia

    SYNC_LOGS ||--o{ SYNC_OPERATIONS : processa

    COMPANIES ||--o{ SYNC_CHANGES : possui
```

---

## Diagrama Futuro

```mermaid
erDiagram

    COMPANIES ||--o{ WAREHOUSES : possui

    WAREHOUSES ||--o{ STOCK_BALANCES : controla

    PRODUCTS ||--o{ STOCK_BALANCES : saldo

    WAREHOUSES ||--o{ STOCK_MOVEMENTS : movimenta

    PRODUCTS ||--o{ STOCK_MOVEMENTS : movimentado

    CUSTOMERS ||--o{ ACCOUNTS_RECEIVABLE : possui

    ORDERS ||--o{ ACCOUNTS_RECEIVABLE : gera

    ACCOUNTS_RECEIVABLE ||--o{ PAYMENTS : recebe

    CUSTOMERS ||--o{ CUSTOMER_MEMBERS : possui

    USERS ||--o{ CUSTOMER_MEMBERS : participa
```
