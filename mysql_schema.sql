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
    product_id      CHAR(4)         NOT NULL PRIMARY KEY,
    brand_id        CHAR(4)         NOT NULL,
    category_id     CHAR(4)         NOT NULL,
    product_name    VARCHAR(150)    NOT NULL,
    model_number    VARCHAR(50),
    release_year    YEAR            NOT NULL,
    price           DECIMAL(15,2)   NOT NULL,
    stock           INT             NOT NULL DEFAULT 0 CHECK (stock >= 0),
    color_options   VARCHAR(200),              
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id)    REFERENCES brands(brand_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE product_specs (
    spec_id         INT             AUTO_INCREMENT PRIMARY KEY,
    product_id      CHAR(4)         NOT NULL,
    display_inch    VARCHAR(50)     NOT NULL,
    chipset         VARCHAR(50)     NOT NULL,
    ram             VARCHAR(50)     NOT NULL,
    storage         VARCHAR(50)     NOT NULL,
    battery         VARCHAR(50)     NOT NULL,
    camera          VARCHAR(50)     NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE users (
    user_id         INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    full_name       VARCHAR(100)    NOT NULL,
    email           VARCHAR(100)    NOT NULL UNIQUE,
    phone           VARCHAR(20),
    password_hash   VARCHAR(255)    NOT NULL,
    address         TEXT,
    city            VARCHAR(50),
    province        VARCHAR(50),
    role            ENUM('customer','admin') DEFAULT 'customer',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE vouchers (
    voucher_id CHAR(4) PRIMARY KEY,
    voucher_code VARCHAR(20) UNIQUE,
    discount_percent INT,
    expired_date DATE
);

CREATE TABLE orders (
    order_id        INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id         INT             NOT NULL,
    order_date      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_amount    DECIMAL(15,2)   NOT NULL,
    shipping_address TEXT           NOT NULL,
    status          ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    voucher_id CHAR(4),
    notes           TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id)

);

CREATE TABLE order_details (
    detail_id       INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_id        INT             NOT NULL,
    product_id      CHAR(4)         NOT NULL,
    quantity        INT             NOT NULL DEFAULT 1,
    unit_price      DECIMAL(15,2)   NOT NULL,
    subtotal        DECIMAL(15,2)   GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (order_id)   REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id CHAR(4) NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id CHAR(4) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE payments (
    payment_id      INT             NOT NULL PRIMARY KEY AUTO_INCREMENT,
    order_id        INT             NOT NULL UNIQUE,
    payment_date    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount          DECIMAL(15,2)   NOT NULL,
    method          ENUM('transfer','credit_card','cod','ewallet','paylater') NOT NULL,
    provider        VARCHAR(50),                -- e.g. BCA, Mandiri, GoPay, OVO
    status          ENUM('pending','success','failed','refunded') DEFAULT 'pending',
    transaction_ref VARCHAR(100),
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id CHAR(4),
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE activity_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity VARCHAR(255),
    activity_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

DELIMITER $$

CREATE TRIGGER trg_reduce_stock
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    UPDATE products
    SET stock = stock - NEW.quantity
    WHERE product_id = NEW.product_id;
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
    p.price,
    p.stock
FROM products p
JOIN brands b
ON p.brand_id = b.brand_id
JOIN categories c
ON p.category_id = c.category_id;

DELIMITER $$

CREATE PROCEDURE GetProductsByBrand(
    IN brandName VARCHAR(50)
)
BEGIN
    SELECT *
    FROM products p
    JOIN brands b
    ON p.brand_id = b.brand_id
    WHERE b.brand_name = brandName;
END$$

DELIMITER ;

CREATE INDEX idx_products_brand    ON products(brand_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_year     ON products(release_year);
CREATE INDEX idx_orders_user       ON orders(user_id);
CREATE INDEX idx_orders_status     ON orders(status);
CREATE INDEX idx_order_details_ord ON order_details(order_id);
CREATE INDEX idx_order_details_prd ON order_details(product_id);



