CREATE DATABASE ephone;
USE ephone;

CREATE TABLE brands (
    brand_id    CHAR(4)         NOT NULL,
    brand_name  VARCHAR(50)     NOT NULL,
    country     VARCHAR(50)     NOT NULL,
    founded     YEAR            NOT NULL,
    website     VARCHAR(100),
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (brand_id)
);

CREATE TABLE categories (
    category_id     CHAR(4)         NOT NULL,
    category_name   VARCHAR(50)     ENUM('Flagship', 'Mid-range', 'Entry Level') NOT NULL,
    description     TEXT,
    min_price       DECIMAL(15,2),
    max_price       DECIMAL(15,2),
    PRIMARY KEY (category_id)
);

CREATE TABLE products (
    product_id      CHAR(4)         NOT NULL,
    brand_id        CHAR(4)         NOT NULL,
    category_id     CHAR(4)         NOT NULL,
    product_name    VARCHAR(150)    NOT NULL,
    model_number    VARCHAR(50),
    release_year    YEAR            NOT NULL,
    price           DECIMAL(15,2)   NOT NULL,
    stock           INT             NOT NULL DEFAULT 0,
    color_options   VARCHAR(200),              
    os_version      VARCHAR(50),
    is_active       TINYINT(1)      DEFAULT 1,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    CONSTRAINT fk_product_brand    FOREIGN KEY (brand_id)    REFERENCES brands(brand_id),
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(category_id)
);


CREATE TABLE users (
    user_id         INT             NOT NULL AUTO_INCREMENT,
    full_name       VARCHAR(100)    NOT NULL,
    email           VARCHAR(100)    NOT NULL UNIQUE,
    phone           VARCHAR(20),
    password_hash   VARCHAR(255)    NOT NULL,
    address         TEXT,
    city            VARCHAR(50),
    province        VARCHAR(50),
    role            ENUM('customer','admin') DEFAULT 'customer',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
);

CREATE TABLE orders (
    order_id        INT             NOT NULL AUTO_INCREMENT,
    user_id         INT             NOT NULL,
    order_date      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount    DECIMAL(15,2)   NOT NULL,
    shipping_address TEXT           NOT NULL,
    status          ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    notes           TEXT,
    PRIMARY KEY (order_id),
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_details (
    detail_id       INT             NOT NULL AUTO_INCREMENT,
    order_id        INT             NOT NULL,
    product_id      CHAR(4)         NOT NULL,
    quantity        INT             NOT NULL DEFAULT 1,
    unit_price      DECIMAL(15,2)   NOT NULL,
    subtotal        DECIMAL(15,2)   GENERATED ALWAYS AS (quantity * unit_price) STORED,
    PRIMARY KEY (detail_id),
    CONSTRAINT fk_detail_order   FOREIGN KEY (order_id)   REFERENCES orders(order_id),
    CONSTRAINT fk_detail_product FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE payments (
    payment_id      INT             NOT NULL AUTO_INCREMENT,
    order_id        INT             NOT NULL UNIQUE,
    payment_date    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount          DECIMAL(15,2)   NOT NULL,
    method          ENUM('transfer','credit_card','cod','ewallet','paylater') NOT NULL,
    provider        VARCHAR(50),                -- e.g. BCA, Mandiri, GoPay, OVO
    status          ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    transaction_ref VARCHAR(100),
    PRIMARY KEY (payment_id),
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE INDEX idx_products_brand    ON products(brand_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_year     ON products(release_year);
CREATE INDEX idx_orders_user       ON orders(user_id);
CREATE INDEX idx_orders_status     ON orders(status);
CREATE INDEX idx_order_details_ord ON order_details(order_id);
CREATE INDEX idx_order_details_prd ON order_details(product_id);
