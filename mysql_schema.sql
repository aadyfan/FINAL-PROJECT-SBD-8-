CREATE DATABASE ephone;
USE ephone;

CREATE TABLE brands (
    brand_id    CHAR(4)         NOT NULL PRIMARY KEY,
    brand_name  VARCHAR(50)     NOT NULL,
    country     VARCHAR(50)     NOT NULL,
    founded     YEAR            NOT NULL,
    website     VARCHAR(100),
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    category_id     CHAR(4)         NOT NULL PRIMARY KEY,
    category_name   ENUM('Flagship', 'Mid-range', 'Entry Level') NOT NULL,
    description     TEXT,
    min_price       DECIMAL(15,2),
    max_price       DECIMAL(15,2)
);

CREATE TABLE products (
    product_id      CHAR(5)         NOT NULL PRIMARY KEY,
    brand_id        CHAR(4)         NOT NULL,
    category_id     CHAR(4)         NOT NULL,
    product_name    VARCHAR(150)    NOT NULL,
    release_year    YEAR            NOT NULL,
    base_price      DECIMAL(15,2)   NOT NULL,
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id)    REFERENCES brands(brand_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE product_specs (
    spec_id         INT             AUTO_INCREMENT PRIMARY KEY,
    product_id      CHAR(5)         NOT NULL,
    model_number    VARCHAR(50),
    display_inch    VARCHAR(50)     NOT NULL,
    chipset         VARCHAR(50)     NOT NULL,
    battery         VARCHAR(50)     NOT NULL,
    camera          VARCHAR(50)     NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE product_variants (
    variant_id      INT             AUTO_INCREMENT PRIMARY KEY,
    product_id      CHAR(5)         NOT NULL,
    color           VARCHAR(50)     NOT NULL,
    ram             VARCHAR(20)     NOT NULL,
    storage         VARCHAR(20)     NOT NULL,
    price           DECIMAL(15,2)   NOT NULL,
    stock           INT             NOT NULL DEFAULT 0 CHECK (stock >= 0),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE users (
    user_id         INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    full_name       VARCHAR(100)    NOT NULL,
    email           VARCHAR(100)    NOT NULL UNIQUE,
    phone           VARCHAR(20),
    password        VARCHAR(255)    NOT NULL,
    address         TEXT,
    city            VARCHAR(50),
    province        VARCHAR(50),
    role            ENUM('customer','admin') DEFAULT 'customer',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vouchers (
    voucher_id      CHAR(4)         PRIMARY KEY,
    voucher_code    VARCHAR(20)     UNIQUE,
    discount_percent INT,
    expired_date    DATE
);

CREATE TABLE orders (
    order_id        INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id         INT             NOT NULL,
    order_date      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount    DECIMAL(15,2)   NOT NULL,
    shipping_address TEXT           NOT NULL,
    status          ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    voucher_id      CHAR(4),
    notes           TEXT,
    FOREIGN KEY (user_id)    REFERENCES users(user_id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id)
);

CREATE TABLE order_details (
    detail_id       INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_id        INT             NOT NULL,
    variant_id      INT             NOT NULL,
    quantity        INT             NOT NULL DEFAULT 1,
    unit_price      DECIMAL(15,2)   NOT NULL,
    subtotal        DECIMAL(15,2)   GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);

CREATE TABLE cart (
    cart_id         INT             AUTO_INCREMENT PRIMARY KEY,
    user_id         INT             NOT NULL,
    variant_id      INT             NOT NULL,
    quantity        INT             DEFAULT 1,
    FOREIGN KEY (user_id)    REFERENCES users(user_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);

CREATE TABLE wishlist (
    wishlist_id     INT             AUTO_INCREMENT PRIMARY KEY,
    user_id         INT             NOT NULL,
    variant_id      INT             NOT NULL,
    added_at        TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(user_id),
    FOREIGN KEY (variant_id) REFERENCES product_variants(variant_id)
);

CREATE TABLE payments (
    payment_id      INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_id        INT             NOT NULL,
    payment_date    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount          DECIMAL(15,2)   NOT NULL,
    payment_type    ENUM('full','down_payment','installment','refund') NOT NULL DEFAULT 'full',
    method          ENUM('transfer','credit_card','cod','ewallet','paylater') NOT NULL,
    status          ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    transaction_ref VARCHAR(100),
    expired_at      DATETIME,
    paid_at         DATETIME,
    notes           TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE transfer_payments (
    payment_id              INT PRIMARY KEY,
    bank_name               VARCHAR(50)     NOT NULL,
    virtual_account_number  VARCHAR(50),
    account_name            VARCHAR(100),
    transfer_proof          VARCHAR(255),
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE credit_card_payments (
    payment_id      INT             PRIMARY KEY,
    card_holder_name VARCHAR(100)   NOT NULL,
    card_brand      VARCHAR(50),
    card_last4      CHAR(4)         NOT NULL,
    bank_issuer     VARCHAR(50),
    auth_code       VARCHAR(50),
    expire_date     DATE,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE cod_payments (
    payment_id      INT             PRIMARY KEY,
    receiver_name   VARCHAR(100)    NOT NULL,
    receiver_phone  VARCHAR(20)     NOT NULL,
    cod_address     TEXT            NOT NULL,
    cod_status      ENUM('waiting_delivery','paid_to_courier','failed_collection') DEFAULT 'waiting_delivery',
    delivery_note   TEXT,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE ewallet_payments (
    payment_id      INT             PRIMARY KEY,
    ewallet_name    VARCHAR(50)     NOT NULL,
    phone_number    VARCHAR(20),
    checkout_token  VARCHAR(100),
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE paylater_payments (
    payment_id          INT         PRIMARY KEY,
    provider_name       VARCHAR(50) NOT NULL,
    account_email       VARCHAR(100),
    installment_month   INT,
    approval_code       VARCHAR(50),
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id)
        ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE reviews (
    review_id       INT             AUTO_INCREMENT PRIMARY KEY,
    product_id      CHAR(5),
    user_id         INT,
    rating          INT             CHECK (rating >= 1 AND rating <= 5),
    comment         TEXT,
    review_date     DATETIME        DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id)
);

CREATE TABLE activity_log (
    log_id          INT             AUTO_INCREMENT PRIMARY KEY,
    user_id         INT,
    activity        VARCHAR(255),
    activity_time   DATETIME        DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

DELIMITER $$

CREATE TRIGGER trg_reduce_stock
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE product_variants
    SET stock = stock - NEW.quantity
    WHERE variant_id = NEW.variant_id;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_check_stock
BEFORE INSERT ON order_details
FOR EACH ROW
BEGIN
    DECLARE current_stock INT;

    SELECT stock
    INTO current_stock
    FROM product_variants
    WHERE variant_id = NEW.variant_id;

    IF current_stock < NEW.quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Stock tidak mencukupi';
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_restore_stock_on_cancel
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF NEW.status = 'cancelled' AND OLD.status != 'cancelled' THEN
        UPDATE product_variants pv
        JOIN order_details od ON pv.variant_id = od.variant_id
        SET pv.stock = pv.stock + od.quantity
        WHERE od.order_id = NEW.order_id;
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_apply_voucher_discount
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
    DECLARE v_discount INT DEFAULT 0;
    IF NEW.voucher_id IS NOT NULL THEN
        SELECT discount_percent INTO v_discount
        FROM vouchers
        WHERE voucher_id = NEW.voucher_id
          AND expired_date >= CURDATE();
        SET NEW.total_amount = NEW.total_amount * (1 - v_discount / 100);
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER trg_order_log
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO activity_log(user_id, activity)
    VALUES(NEW.user_id, 'Membuat pesanan baru');
END$$

DELIMITER ;

CREATE VIEW product_catalog AS
SELECT
    p.product_id,
    p.product_name,
    b.brand_name,
    c.category_name,
    pv.color,
    pv.ram,
    pv.storage,
    pv.price,
    pv.stock
FROM products p
JOIN brands b         ON p.brand_id    = b.brand_id
JOIN categories c     ON p.category_id = c.category_id
JOIN product_variants pv ON p.product_id = pv.product_id;

DELIMITER $$

CREATE PROCEDURE GetProductsByBrand(
    IN brandName VARCHAR(50)
)
BEGIN
    SELECT
        p.product_id,
        p.product_name,
        b.brand_name,
        pv.color,
        pv.ram,
        pv.storage,
        pv.price,
        pv.stock
    FROM products p
    JOIN brands b            ON p.brand_id    = b.brand_id
    JOIN product_variants pv ON p.product_id  = pv.product_id
    WHERE b.brand_name = brandName;
END$$

DELIMITER ;

CREATE VIEW stock_summary AS
SELECT
    p.product_id,
    p.product_name,
    b.brand_name,
    pv.variant_id,
    pv.color,
    pv.ram,
    pv.storage,
    pv.stock
FROM product_variants pv
JOIN products p ON pv.product_id = p.product_id
JOIN brands b   ON p.brand_id    = b.brand_id;

CREATE INDEX idx_products_brand       ON products(brand_id);
CREATE INDEX idx_products_category    ON products(category_id);
CREATE INDEX idx_products_year        ON products(release_year);
CREATE INDEX idx_variants_product     ON product_variants(product_id);
CREATE INDEX idx_variants_color       ON product_variants(color);
CREATE INDEX idx_orders_user          ON orders(user_id);
CREATE INDEX idx_orders_status        ON orders(status);
CREATE INDEX idx_order_details_ord    ON order_details(order_id);
CREATE INDEX idx_order_details_var    ON order_details(variant_id);