USE ephone;

INSERT INTO brands (brand_id, brand_name, country, founded, website) VALUES
('b001', 'Apple',   'United States', 1976, 'https://www.apple.com'),
('b002', 'Samsung', 'South Korea',   1969, 'https://www.samsung.com'),
('b003', 'Xiaomi',  'China',         2010, 'https://www.mi.com'),
('b004', 'Vivo',    'China',         2009, 'https://www.vivo.com'),
('b005', 'OPPO',    'China',         2004, 'https://www.oppo.com');

INSERT INTO categories (category_id, category_name, description, min_price, max_price) VALUES
('c001', 'Flagship',    'Produk unggulan dengan spesifikasi tertinggi dan fitur premium', 8000000.00,  25000000.00),
('c002', 'Mid-range',   'Produk menengah dengan keseimbangan performa dan harga terbaik', 3000000.00,   7999999.00),
('c003', 'Entry Level', 'Produk terjangkau dengan fitur dasar untuk pengguna pemula',     1000000.00,   2999999.00);

INSERT INTO products (product_id, brand_id, category_id, product_name, release_year, base_price) VALUES
('p001', 'b001', 'c001', 'Apple iPhone 16',              2024, 14999000.00),
('p002', 'b001', 'c001', 'Apple iPhone 16 Pro',          2024, 19499000.00),
('p003', 'b001', 'c001', 'Apple iPhone 16 Pro Max',      2024, 21999000.00),
('p004', 'b001', 'c001', 'Apple iPhone 17',              2025, 16499000.00),
('p005', 'b001', 'c001', 'Apple iPhone 17 Air',          2025, 18999000.00),
('p006', 'b001', 'c001', 'Apple iPhone 17 Pro',          2025, 22499000.00),
('p007', 'b001', 'c001', 'Apple iPhone 17 Pro Max',      2025, 24999000.00),
('p008', 'b001', 'c001', 'Apple iPhone 16e',             2025, 10999000.00),
('p009', 'b002', 'c001', 'Samsung Galaxy S25 Ultra',     2025, 19999000.00),
('p010', 'b002', 'c001', 'Samsung Galaxy S25+',          2025, 16499000.00),
('p011', 'b002', 'c001', 'Samsung Galaxy S25',           2025, 13999000.00),
('p012', 'b002', 'c001', 'Samsung Galaxy S26 Ultra',     2026, 21999000.00),
('p013', 'b002', 'c001', 'Samsung Galaxy S26+',          2026, 17999000.00),
('p014', 'b002', 'c001', 'Samsung Galaxy S26',           2026, 14999000.00),
('p015', 'b002', 'c002', 'Samsung Galaxy A56 5G',        2026,  6499000.00),
('p016', 'b002', 'c002', 'Samsung Galaxy A36 5G',        2026,  4999000.00),
('p017', 'b002', 'c002', 'Samsung Galaxy M56 5G',        2026,  5499000.00),
('p018', 'b002', 'c003', 'Samsung Galaxy A16 5G',        2025,  2799000.00),
('p019', 'b002', 'c003', 'Samsung Galaxy A06 5G',        2026,  1799000.00),
('p020', 'b003', 'c001', 'Xiaomi 15 Ultra',              2025, 17999000.00),
('p021', 'b003', 'c001', 'Xiaomi 15 Pro',                2025, 14999000.00),
('p022', 'b003', 'c001', 'Xiaomi 15S Pro',               2026, 15999000.00),
('p023', 'b003', 'c001', 'Xiaomi Mix Flip 2',            2026, 16999000.00),
('p024', 'b003', 'c002', 'Xiaomi Redmi Note 14 Pro+ 5G', 2025,  4999000.00),
('p025', 'b003', 'c002', 'Xiaomi Redmi Note 15 Pro 5G',  2026,  4499000.00),
('p026', 'b003', 'c002', 'Xiaomi Redmi Note 15 5G',      2026,  3499000.00),
('p027', 'b003', 'c002', 'Xiaomi POCO X7 Pro',           2025,  5499000.00),
('p028', 'b003', 'c003', 'Xiaomi Redmi 15 5G',           2026,  2499000.00),
('p029', 'b003', 'c003', 'Xiaomi POCO C85',              2026,  1799000.00),
('p030', 'b004', 'c001', 'Vivo X200 Ultra',              2025, 17499000.00),
('p031', 'b004', 'c001', 'Vivo X200 Pro',                2025, 14999000.00),
('p032', 'b004', 'c001', 'Vivo X300 Ultra',              2026, 18999000.00),
('p033', 'b004', 'c001', 'Vivo X Fold 4',                2026, 22999000.00),
('p034', 'b004', 'c002', 'Vivo V50 Pro',                 2026,  7499000.00),
('p035', 'b004', 'c002', 'Vivo V50',                     2026,  5999000.00),
('p036', 'b004', 'c002', 'Vivo T4 Pro 5G',               2026,  4499000.00),
('p037', 'b004', 'c003', 'Vivo Y29 5G',                  2026,  2699000.00),
('p038', 'b004', 'c003', 'Vivo Y39 5G',                  2026,  2599000.00),
('p039', 'b004', 'c003', 'Vivo Y18 5G',                  2026,  1799000.00),
('p040', 'b005', 'c001', 'OPPO Find X8 Pro',             2025, 14499000.00),
('p041', 'b005', 'c001', 'OPPO Find X9 Ultra',           2026, 19499000.00),
('p042', 'b005', 'c001', 'OPPO Find N5',                 2026, 22499000.00),
('p043', 'b005', 'c001', 'OPPO Find X8s',                2026, 12999000.00),
('p044', 'b005', 'c002', 'OPPO Reno 13 Pro 5G',          2025,  7499000.00),
('p045', 'b005', 'c002', 'OPPO Reno 14 Pro 5G',          2026,  7999000.00),
('p046', 'b005', 'c002', 'OPPO Reno 14 5G',              2026,  6499000.00),
('p047', 'b005', 'c002', 'OPPO A5 Pro 5G',               2026,  3999000.00),
('p048', 'b005', 'c003', 'OPPO A5 5G',                   2026,  2999000.00),
('p049', 'b005', 'c003', 'OPPO A3',                      2026,  1899000.00);

INSERT INTO product_specs (product_id, model_number, display_inch, chipset, battery, camera) VALUES
('p001', 'A3290',       '6.1 inch OLED',              'Apple A18 Bionic',               '3560 mAh', '48MP + 12MP'),
('p002', 'A3292',       '6.3 inch ProMotion OLED',    'Apple A18 Pro',                  '3650 mAh', '48MP + 48MP + 12MP'),
('p003', 'A3293',       '6.9 inch ProMotion OLED',    'Apple A18 Pro',                  '4685 mAh', '48MP + 48MP + 12MP'),
('p004', 'A3398',       '6.3 inch OLED',              'Apple A19 Bionic',               '3800 mAh', '48MP + 12MP'),
('p005', 'A3399',       '6.6 inch Ultra Thin OLED',   'Apple A19 Bionic',               '3400 mAh', '48MP Single'),
('p006', 'A3400',       '6.3 inch ProMotion OLED',    'Apple A19 Pro',                  '3900 mAh', '48MP + 48MP + 48MP'),
('p007', 'A3401',       '6.9 inch ProMotion OLED',    'Apple A19 Pro',                  '4900 mAh', '48MP + 48MP + 48MP'),
('p008', 'A3288',       '6.1 inch OLED',              'Apple A16 Bionic',               '3279 mAh', '48MP + 12MP'),
('p009', 'SM-S938B',    '6.8 inch Dynamic AMOLED 2X', 'Snapdragon 8 Elite',             '5000 mAh', '200MP + 50MP + 12MP'),
('p010', 'SM-S936B',    '6.7 inch Dynamic AMOLED 2X', 'Snapdragon 8 Elite',             '4900 mAh', '50MP + 10MP + 12MP'),
('p011', 'SM-S931B',    '6.2 inch Dynamic AMOLED 2X', 'Snapdragon 8 Elite',             '4000 mAh', '50MP + 10MP + 12MP'),
('p012', 'SM-S948B',    '6.9 inch Dynamic AMOLED 3X', 'Snapdragon 8 Elite Gen 2',       '5100 mAh', '200MP + 64MP + 50MP'),
('p013', 'SM-S946B',    '6.7 inch Dynamic AMOLED 3X', 'Snapdragon 8 Elite Gen 2',       '5000 mAh', '50MP + 50MP + 12MP'),
('p014', 'SM-S931C',    '6.2 inch Dynamic AMOLED 3X', 'Snapdragon 8 Elite Gen 2',       '4000 mAh', '50MP + 12MP + 12MP'),
('p015', 'SM-A566B',    '6.7 inch Super AMOLED',      'Exynos 1580',                    '5000 mAh', '50MP + 12MP + 5MP'),
('p016', 'SM-A366B',    '6.6 inch Super AMOLED',      'Exynos 1480',                    '5000 mAh', '50MP + 8MP + 5MP'),
('p017', 'SM-M566B',    '6.5 inch IPS LCD',           'MediaTek Dimensity 6100+',       '6000 mAh', '50MP + 2MP'),
('p018', 'SM-A166B',    '6.7 inch Super AMOLED 90Hz', 'Exynos 1330',                    '5000 mAh', '50MP + 5MP + 2MP'),
('p019', 'SM-A065F',    '6.7 inch PLS LCD',           'MediaTek Helio G85',             '5000 mAh', '50MP + 2MP'),
('p020', '2501117C',    '6.73 inch LTPO AMOLED',      'Snapdragon 8 Elite',             '5400 mAh', '50MP Quad Leica'),
('p021', '2501116C',    '6.73 inch LTPO AMOLED',      'Snapdragon 8 Elite',             '5000 mAh', '50MP Triple Leica'),
('p022', '2601001C',    '6.55 inch AMOLED',           'MediaTek Dimensity 9400',        '5100 mAh', '50MP Triple Leica'),
('p023', '2501201C',    '8.03 inch Foldable LTPO',    'Snapdragon 8 Elite',             '5700 mAh', '50MP + 50MP + 50MP'),
('p024', '2409FPN6CI',  '6.67 inch AMOLED Curved',   'Snapdragon 7s Gen 3',            '6200 mAh', '50MP + 50MP + 12MP'),
('p025', '2501FPN6CI',  '6.67 inch AMOLED 1.5K',     'MediaTek Dimensity 8300-Ultra',  '5500 mAh', '200MP + 8MP + 2MP'),
('p026', '2501FPN6CG',  '6.67 inch AMOLED 120Hz',    'Snapdragon 4 Gen 2',             '5000 mAh', '50MP + 8MP'),
('p027', '2501129C',    '6.67 inch CrystalRes AMOLED','MediaTek Dimensity 8400',        '5000 mAh', '64MP + 8MP + 2MP'),
('p028', '2501FPN3CG',  '6.88 inch IPS LCD 120Hz',   'MediaTek Helio G81-Ultra',       '5160 mAh', '50MP + 2MP'),
('p029', '2601101C',    '6.7 inch IPS LCD',           'MediaTek Helio G36',             '5000 mAh', '13MP Dual Camera'),
('p030', 'V2416A',      '6.78 inch AMOLED',           'MediaTek Dimensity 9400',        '6000 mAh', '200MP + 50MP + 50MP Zeiss'),
('p031', 'V2415A',      '6.78 inch AMOLED 1.5K',      'MediaTek Dimensity 9400',        '5800 mAh', '50MP Triple Zeiss'),
('p032', 'V2516A',      '6.82 inch LTPO AMOLED',      'MediaTek Dimensity 9500',        '6100 mAh', '50MP Quad Zeiss'),
('p033', 'V2421A',      '8.03 inch Foldable LTPO',    'Snapdragon 8 Elite',             '5700 mAh', '50MP + 50MP + 50MP'),
('p034', 'V2519A',      '6.78 inch AMOLED 3D Curved', 'MediaTek Dimensity 8300',        '5500 mAh', '50MP Triple OIS'),
('p035', 'V2518A',      '6.78 inch AMOLED Curved',    'Snapdragon 7 Gen 3',             '5000 mAh', '50MP Dual Aura Light'),
('p036', 'V2512T',      '6.7 inch AMOLED 120Hz',      'Snapdragon 6 Gen 1',             '5500 mAh', '50MP + 8MP'),
('p037', 'V2516Y',      '6.56 inch IPS LCD 90Hz',     'MediaTek Dimensity 6300',        '5000 mAh', '50MP + 2MP'),
('p038', 'V2519Y',      '6.6 inch IPS LCD 120Hz',     'Snapdragon 4 Gen 2',             '5000 mAh', '50MP + 2MP'),
('p039', 'V2542A',      '6.56 inch IPS LCD',          'MediaTek Helio G85',             '5000 mAh', '50MP Dual Camera'),
('p040', 'PHV110P',     '6.78 inch AMOLED Curved',    'MediaTek Dimensity 9400',        '5910 mAh', '50MP Quad Hasselblad'),
('p041', 'PJV110',      '6.82 inch AMOLED',           'Snapdragon 8 Elite',             '5600 mAh', '50MP Quad Hasselblad'),
('p042', 'PHW110',      '7.82 inch Foldable OLED',    'Snapdragon 8 Elite',             '4805 mAh', '48MP + 64MP + 48MP'),
('p043', 'PHV120',      '6.59 inch AMOLED Flat',      'MediaTek Dimensity 9400',        '5630 mAh', '50MP Triple Camera'),
('p044', 'PHY910',      '6.7 inch AMOLED Curved',     'MediaTek Dimensity 8350',        '5000 mAh', '50MP + 50MP + 8MP'),
('p045', 'PJY910',      '6.7 inch AMOLED',            'MediaTek Dimensity 8400',        '5000 mAh', '50MP + 50MP + 8MP'),
('p046', 'PJY900',      '6.7 inch AMOLED 120Hz',      'MediaTek Dimensity 7300-Energy', '5000 mAh', '50MP + 8MP + 2MP'),
('p047', 'PHW210',      '6.7 inch OLED Flat',         'MediaTek Dimensity 7050',        '5000 mAh', '50MP Dual Camera'),
('p048', 'PJW210',      '6.67 inch OLED 120Hz',       'Snapdragon 685',                 '5000 mAh', '50MP + 2MP'),
('p049', 'PJV310',      '6.67 inch IPS LCD 90Hz',     'Snapdragon 680',                 '5000 mAh', '50MP Dual Camera');

INSERT INTO product_variants (product_id, color, ram, storage, price, stock) VALUES
('p001', 'Black',       '8GB', '128GB', 14999000.00, 15),
('p001', 'White',       '8GB', '128GB', 14999000.00, 10),
('p001', 'Pink',        '8GB', '128GB', 14999000.00, 10),
('p001', 'Teal',        '8GB', '128GB', 14999000.00,  5),
('p001', 'Ultramarine', '8GB', '128GB', 14999000.00,  5),
('p002', 'Black Titanium',   '8GB', '256GB', 19499000.00, 15),
('p002', 'White Titanium',   '8GB', '256GB', 19499000.00, 13),
('p002', 'Desert Titanium',  '8GB', '256GB', 19499000.00, 10),
('p003', 'Black Titanium',   '8GB', '512GB', 21999000.00, 10),
('p003', 'White Titanium',   '8GB', '512GB', 21999000.00,  8),
('p003', 'Desert Titanium',  '8GB', '512GB', 21999000.00,  7),
('p003', 'Natural Titanium', '8GB', '512GB', 21999000.00,  7),
('p004', 'Midnight',  '12GB', '128GB', 16499000.00, 15),
('p004', 'Starlight', '12GB', '128GB', 16499000.00, 13),
('p004', 'Blue',      '12GB', '128GB', 16499000.00, 12),
('p004', 'Purple',    '12GB', '128GB', 16499000.00, 10),
('p005', 'Slim Silver', '12GB', '256GB', 18999000.00, 15),
('p005', 'Graphite',    '12GB', '256GB', 18999000.00, 13),
('p005', 'Rose Gold',   '12GB', '256GB', 18999000.00, 12),
('p006', 'Space Black',     '12GB', '256GB', 22499000.00, 12),
('p006', 'Titanium Silver', '12GB', '256GB', 22499000.00, 12),
('p006', 'Deep Green',      '12GB', '256GB', 22499000.00, 11),
('p007', 'Space Black',     '16GB', '1TB', 24999000.00, 10),
('p007', 'Titanium Silver', '16GB', '1TB', 24999000.00, 10),
('p007', 'Aurora Gold',     '16GB', '1TB', 24999000.00, 10),
('p008', 'Black',       '8GB', '128GB', 10999000.00, 15),
('p008', 'White',       '8GB', '128GB', 10999000.00, 15),
('p008', 'Ultramarine', '8GB', '128GB', 10999000.00, 14),
('p009', 'Titanium Black',      '12GB', '512GB', 19999000.00, 12),
('p009', 'Titanium Gray',       '12GB', '512GB', 19999000.00, 11),
('p009', 'Titanium Silverblue', '12GB', '512GB', 19999000.00, 10),
('p010', 'Icy Blue', '12GB', '256GB', 16499000.00, 15),
('p010', 'Mint',     '12GB', '256GB', 16499000.00, 14),
('p010', 'Navy',     '12GB', '256GB', 16499000.00, 13),
('p011', 'Icy Blue',      '8GB', '256GB', 13999000.00, 17),
('p011', 'Mint',          '8GB', '256GB', 13999000.00, 16),
('p011', 'Silver Shadow', '8GB', '256GB', 13999000.00, 15),
('p012', 'Titanium Charcoal', '16GB', '512GB', 21999000.00, 10),
('p012', 'Obsidian Black',    '16GB', '512GB', 21999000.00, 10),
('p012', 'Luxury Gold',       '16GB', '512GB', 21999000.00, 10),
('p013', 'Neo Mint',       '12GB', '256GB', 17999000.00, 13),
('p013', 'Phantom Violet', '12GB', '256GB', 17999000.00, 12),
('p013', 'Midnight Sky',   '12GB', '256GB', 17999000.00, 11),
('p014', 'Icy Blue',     '12GB', '128GB', 14999000.00, 15),
('p014', 'Mint',         '12GB', '128GB', 14999000.00, 14),
('p014', 'Shadow Black', '12GB', '128GB', 14999000.00, 13),
('p015', 'Awesome Black',   '8GB', '256GB', 6499000.00, 18),
('p015', 'Awesome Iceblue', '8GB', '256GB', 6499000.00, 17),
('p015', 'Awesome Lime',    '8GB', '256GB', 6499000.00, 15),
('p016', 'Awesome Black',  '8GB', '128GB', 4999000.00, 23),
('p016', 'Awesome Lilac',  '8GB', '128GB', 4999000.00, 22),
('p017', 'Midnight Blue', '8GB', '256GB', 5499000.00, 20),
('p017', 'Mint Green',    '8GB', '256GB', 5499000.00, 20),
('p018', 'Black',       '6GB', '128GB', 2799000.00, 17),
('p018', 'Blue',        '6GB', '128GB', 2799000.00, 16),
('p018', 'Light Green', '6GB', '128GB', 2799000.00, 15),
('p019', 'Black', '4GB', '64GB', 1799000.00, 18),
('p019', 'Blue',  '4GB', '64GB', 1799000.00, 17),
('p019', 'Cream', '4GB', '64GB', 1799000.00, 15),
('p020', 'Black',      '16GB', '512GB', 17999000.00, 11),
('p020', 'White',      '16GB', '512GB', 17999000.00, 10),
('p020', 'Olive Green','16GB', '512GB', 17999000.00, 10),
('p021', 'Black',       '12GB', '256GB', 14999000.00, 13),
('p021', 'White',       '12GB', '256GB', 14999000.00, 13),
('p021', 'Sakura Pink', '12GB', '256GB', 14999000.00, 12),
('p022', 'Titanium Black', '12GB', '256GB', 15999000.00, 12),
('p022', 'Titanium White', '12GB', '256GB', 15999000.00, 12),
('p022', 'Pebble Blue',    '12GB', '256GB', 15999000.00, 10),
('p023', 'Cosmic Black',    '16GB', '512GB', 16999000.00, 15),
('p023', 'Sakura Lavender', '16GB', '512GB', 16999000.00, 15),
('p024', 'Aurora Purple',  '12GB', '512GB', 4999000.00, 23),
('p024', 'Midnight Black', '12GB', '512GB', 4999000.00, 22),
('p025', 'Midnight Black', '12GB', '256GB', 4499000.00, 24),
('p025', 'Polar Blue',     '12GB', '256GB', 4499000.00, 23),
('p026', 'Spearmint Green', '8GB', '256GB', 3499000.00, 25),
('p026', 'Sky Blue',        '8GB', '256GB', 3499000.00, 25),
('p027', 'Classic Black', '12GB', '512GB', 5499000.00, 20),
('p027', 'POCO Yellow',   '12GB', '512GB', 5499000.00, 20),
('p028', 'Midnight Black', '6GB', '128GB', 2499000.00, 21),
('p028', 'Starlight Blue', '6GB', '128GB', 2499000.00, 21),
('p029', 'Black',    '4GB', '128GB', 1799000.00, 23),
('p029', 'Sky Blue', '4GB', '128GB', 1799000.00, 22),
('p030', 'Titanium Black', '16GB', '512GB', 17499000.00, 15),
('p030', 'Titanium Gray',  '16GB', '512GB', 17499000.00, 15),
('p031', 'Titanium Gray',  '12GB', '256GB', 14999000.00, 18),
('p031', 'Cosmos Black',   '12GB', '256GB', 14999000.00, 18),
('p032', 'Titanium Silver', '16GB', '512GB', 18999000.00, 16),
('p032', 'Dark Shadow',     '16GB', '512GB', 18999000.00, 16),
('p033', 'Obsidian Black', '16GB', '512GB', 22999000.00, 15),
('p033', 'Silk Silver',    '16GB', '512GB', 22999000.00, 15),
('p034', 'Titanium Black', '12GB', '512GB', 7499000.00, 20),
('p034', 'Velvet Rose',    '12GB', '512GB', 7499000.00, 20),
('p035', 'Diamond Black', '8GB', '256GB', 5999000.00, 22),
('p035', 'Aqua Green',    '8GB', '256GB', 5999000.00, 22),
('p036', 'Phantom Black', '8GB', '256GB', 4499000.00, 24),
('p036', 'Sage Green',    '8GB', '256GB', 4499000.00, 24),
('p037', 'Mystic Black',  '8GB', '256GB', 2699000.00, 25),
('p037', 'Crystal Blue',  '8GB', '256GB', 2699000.00, 25),
('p038', 'Phantom Black', '6GB', '128GB', 2599000.00, 21),
('p038', 'Sunset Gold',   '6GB', '128GB', 2599000.00, 21),
('p039', 'Space Black', '4GB', '128GB', 1799000.00, 23),
('p039', 'Satin Gold',  '4GB', '128GB', 1799000.00, 23),
('p040', 'Sea Blue',       '12GB', '256GB', 14499000.00, 18),
('p040', 'Titanium Black', '12GB', '256GB', 14499000.00, 17),
('p041', 'Titanium Black', '16GB', '512GB', 19499000.00, 15),
('p041', 'Desert Sand',    '16GB', '512GB', 19499000.00, 15),
('p042', 'Leather Black', '16GB', '512GB', 22499000.00, 15),
('p042', 'Cream White',   '16GB', '512GB', 22499000.00, 15),
('p043', 'Space Black',   '12GB', '256GB', 12999000.00, 19),
('p043', 'Glacier Blue',  '12GB', '256GB', 12999000.00, 19),
('p044', 'Aurora Silver', '12GB', '512GB', 7499000.00, 23),
('p044', 'Graphite Grey', '12GB', '512GB', 7499000.00, 22),
('p045', 'Midnight Blue', '12GB', '256GB', 7999000.00, 21),
('p045', 'Sakura White',  '12GB', '256GB', 7999000.00, 21),
('p046', 'Luminous Blue', '8GB', '256GB', 6499000.00, 23),
('p046', 'Mint Green',    '8GB', '256GB', 6499000.00, 23),
('p047', 'Glow Black',    '8GB', '256GB', 3999000.00, 25),
('p047', 'Luxury Gold',   '8GB', '256GB', 3999000.00, 25),
('p048', 'Starry Black', '8GB', '256GB', 2999000.00, 24),
('p048', 'Aqua Blue',    '8GB', '256GB', 2999000.00, 24),
('p049', 'Glowing Black',   '4GB', '128GB', 1899000.00, 20),
('p049', 'Crystal Purple',  '4GB', '128GB', 1899000.00, 20);

INSERT INTO users (full_name, email, phone, password, address, city, province, role) VALUES
('Abhista Athallah Dyfan', 'abhista.dyfan@gmail.com',  '081234567890', 'abhista123', 'Keputih Gg 2C',  'Surabaya', 'Jawa Timur',  'customer'),
('M. Rifqi Fathurrahman',  'rifqi.fathur@gmail.com',   '081234567891', 'rifqi123',   'Keputih Tegal',  'Surabaya', 'Jawa Timur',  'customer'),
('Ndaru Satria Tama',      'ndaru.satria@gmail.com',   '081234567892', 'ndaru123',   'Gebang Putih',   'Surabaya', 'Jawa Timur',  'customer'),
('Asfia Fahmisan',         'asfia.fahmisan@gmail.com', '081234567893', 'asfia123',   'Mulyosari B-12', 'Surabaya', 'Jawa Timur',  'customer'),
('Super Admin EPhone',     'admin@ephone.id',          '08001234567',  'admin123',   'Tower 2 Lt. 7',  'Jakarta',  'DKI Jakarta', 'admin');

INSERT INTO vouchers (voucher_id, voucher_code, discount_percent, expired_date) VALUES
('v001', 'MABAITS2026',  15, '2026-09-01'),
('v002', 'EPHONEPROMO',  10, '2026-12-31'),
('v003', 'GAJIANHYPE',    5, '2026-07-05'),
('v004', 'IPHONEBARU',    8, '2026-08-15'),
('v005', 'SAMSUNGSERU',  12, '2026-08-20'),
('v006', 'XIAOMIMURAH',   7, '2026-07-25'),
('v007', 'VIVOPAS',       6, '2026-07-30'),
('v008', 'OPPODISKON',    9, '2026-08-10'),
('v009', 'WEEKENDSERU',   5, '2026-06-30'),
('v010', 'CASHBACKMAX',  11, '2026-10-10');

INSERT INTO orders (order_date, total_amount, shipping_address, status, voucher_id, notes) VALUES
('2026-06-01 10:00:00', 12749150.00, 'Keputih Gg 2C, Surabaya',  'delivered', 'v001', 'Kirim ke kosan h-1 quiz'),
('2026-06-02 11:30:00', 19499000.00, 'Keputih Tegal, Surabaya',  'shipped',   NULL,   'Bungkus ekstra bubble wrap'),
('2026-06-03 14:15:00', 20239080.00, 'Gebang Putih, Surabaya',   'confirmed', 'v004', 'Warna Natural Titanium ya'),
('2026-06-04 09:00:00', 16499000.00, 'Mulyosari B-12, Surabaya', 'pending',   NULL,   'Mohon segera diproses'),
('2026-06-04 16:30:00', 17599120.00, 'Keputih Gg 2C, Surabaya',  'delivered', 'v005', 'Jangan dibanting, barang pecah belah'),
('2026-06-05 13:00:00', 17999000.00, 'Keputih Tegal, Surabaya',  'confirmed', NULL,   NULL),
('2026-06-05 19:45:00', 13949070.00, 'Gebang Putih, Surabaya',   'pending',   'v006', NULL),
('2026-06-06 10:20:00',  1799000.00, 'Mulyosari B-12, Surabaya', 'cancelled', NULL,   'Salah pilih varian warna'),
('2026-06-06 21:00:00', 14559090.00, 'Keputih Gg 2C, Surabaya',  'pending',   'v008', 'Kirim pakai gosend instant jika bisa'),
('2026-06-07 08:15:00',  2799000.00, 'Keputih Tegal, Surabaya',  'delivered', NULL,   'Untuk hadiah adik');

INSERT INTO order_details (order_id, user_id, variant_id, quantity, unit_price) VALUES
(1,  1, 1,  1, 14999000.00),
(2,  2, 6,  1, 19499000.00),
(3,  3, 9,  1, 21999000.00),
(4,  4, 13, 1, 16499000.00),
(5,  5, 29, 1, 19999000.00),
(6,  2, 43, 1, 17999000.00),
(7,  3, 61, 1, 14999000.00),
(8,  4, 55, 1,  1799000.00),
(9,  1, 63, 1, 15999000.00),
(10, 2, 49, 1,  2799000.00);

INSERT INTO payments (order_id, payment_date, amount, payment_type, method, status, transaction_ref, paid_at) VALUES
(1,  '2026-06-01 10:05:00', 12749150.00, 'full', 'ewallet',     'success', 'TX-ITS-101', '2026-06-01 10:06:00'),
(2,  '2026-06-02 11:35:00', 19499000.00, 'full', 'transfer',    'success', 'TX-BCA-102', '2026-06-02 11:50:00'),
(3,  '2026-06-03 14:20:00', 20239080.00, 'full', 'credit_card', 'success', 'TX-VISA-103','2026-06-03 14:21:00'),
(4,  '2026-06-04 09:05:00', 16499000.00, 'full', 'paylater',    'pending', 'TX-SPM-104', NULL),
(5,  '2026-06-04 16:35:00', 17599120.00, 'full', 'transfer',    'success', 'TX-MND-105', '2026-06-04 16:55:00'),
(6,  '2026-06-05 13:05:00', 17999000.00, 'full', 'ewallet',     'success', 'TX-OVO-106', '2026-06-05 13:06:00'),
(7,  '2026-06-05 19:50:00', 13949070.00, 'full', 'transfer',    'pending', 'TX-BCA-107', NULL),
(8,  '2026-06-06 10:25:00',  1799000.00, 'full', 'credit_card', 'failed',  'TX-MST-108', NULL),
(9,  '2026-06-06 21:05:00', 14559090.00, 'full', 'ewallet',     'pending', 'TX-LAJ-109', NULL),
(10, '2026-06-07 08:20:00',  2799000.00, 'full', 'cod',         'success', 'TX-COD-110', '2026-06-07 14:00:00');

INSERT INTO ewallet_payments (payment_id, ewallet_name, phone_number, checkout_token) VALUES
(1, 'GoPay',   '081234567890', 'GPY-TOKEN-101'),
(6, 'OVO',     '081234567891', 'OVO-TOKEN-106'),
(9, 'LinkAja', '081234567890', 'LAJ-TOKEN-109');

INSERT INTO transfer_payments (payment_id, bank_name, virtual_account_number, account_name, transfer_proof) VALUES
(2, 'BCA',     '7890123456', 'M. Rifqi Fathurrahman', 'proof_bca_102.jpg'),
(5, 'Mandiri', '8901234567', 'Super Admin EPhone',    'proof_mnd_105.jpg'),
(7, 'BCA',     '7890123458', 'Ndaru Satria Tama',     NULL);

INSERT INTO credit_card_payments (payment_id, card_holder_name, card_brand, card_last4, bank_issuer, auth_code, expire_date) VALUES
(3, 'Ndaru Satria Tama', 'Visa',       '4231', 'BCA',     'AUTH-VISA-103', '2027-12-01'),
(8, 'Asfia Fahmisan',    'Mastercard', '8821', 'Mandiri', NULL,            '2026-09-01');

INSERT INTO paylater_payments (payment_id, provider_name, account_email, installment_month, approval_code) VALUES
(4, 'ShopeePayLater', 'asfia.fahmisan@gmail.com', 3, NULL);

INSERT INTO cod_payments (payment_id, receiver_name, receiver_phone, cod_address, cod_status) VALUES
(10, 'M. Rifqi Fathurrahman', '081234567891', 'Keputih Tegal, Surabaya', 'paid_to_courier');

INSERT INTO cart (user_id, variant_id, quantity) VALUES
(1, 17, 1),   -- iPhone 17 Air Slim Silver
(1, 20, 1),   -- iPhone 17 Pro Space Black
(2, 40, 1),   -- Galaxy S26 Ultra Titanium Charcoal
(2, 51, 2),   -- Galaxy M56 5G Midnight Blue
(3, 65, 1),   -- Xiaomi Mix Flip 2 Cosmic Black
(4, 23, 1),   -- iPhone 17 Pro Max Space Black
(3, 13, 1),   -- iPhone 17 Midnight
(4, 43, 2),   -- Galaxy S26+ Neo Mint
(1, 63, 1),   -- Xiaomi 15S Pro Titanium Black
(2, 59, 1);   -- Xiaomi 15 Ultra Black

INSERT INTO wishlist (user_id, variant_id) VALUES
(1, 23),   -- iPhone 17 Pro Max Space Black
(2, 11),   -- iPhone 16 Pro Max Desert Titanium
(3, 40),   -- Galaxy S26 Ultra Titanium Charcoal
(4, 63),   -- Xiaomi 15S Pro Titanium Black
(1, 6),    -- iPhone 16 Pro Black Titanium
(2, 46),   -- Galaxy A36 5G Awesome Black
(3, 55),   -- Galaxy A06 5G Black
(4, 17),   -- iPhone 17 Air Slim Silver
(1, 40),   -- Galaxy S26 Ultra Titanium Charcoal
(4, 65);   -- Xiaomi Mix Flip 2 Cosmic Black

INSERT INTO reviews (product_id, user_id, rating, comment) VALUES
('p001', 1, 5, 'Keren pol iPhone 16 nya, pelayanan ephone mantap!'),
('p002', 2, 4, 'Barang jos aman, tapi pengiriman agak lama sedikit.'),
('p003', 3, 5, 'Layar ProMotion ngeri mulusnya, top markotop.'),
('p009', 1, 5, 'Kamera S25 Ultra luar biasa jernih zoomnya.'),
('p013', 2, 5, 'S26+ layar tajam, performa flagship, batre awet banget.'),
('p020', 3, 4, 'Xiaomi 15 Ultra spek gahar gila, tapi agak anget pas main game.'),
('p018', 2, 5, 'Dapet diskon pas beli buat kado adik, puas banget.'),
('p034', 4, 5, 'Vivo V50 Pro emang juaranya kalau buat foto portrait malam hari.'),
('p045', 1, 4, 'Reno 14 Pro desainnya cakep dan elegan tipis.'),
('p048', 3, 5, 'OPPO A5 5G entry level yang oke banget buat harian.');

INSERT INTO activity_log (user_id, activity, activity_time) VALUES
(1, 'Melakukan login aplikasi pada perangkat MacBook Air', '2026-06-01 09:50:00'),
(1, 'Menerapkan kode voucher MABAITS2026',                 '2026-06-01 09:58:00'),
(2, 'Mengubah alamat utama pengiriman ke Keputih Tegal',   '2026-06-02 11:15:00'),
(3, 'Menambahkan iPhone 16 Pro Max ke wishlist',           '2026-06-03 13:00:00'),
(4, 'Melakukan pencarian produk kategori Flagship',        '2026-06-04 08:45:00'),
(1, 'Menghapus barang dari keranjang belanja',             '2026-06-04 15:00:00'),
(2, 'Melihat katalog spesifikasi Xiaomi 15 Ultra',         '2026-06-05 12:30:00'),
(4, 'Membatalkan pesanan karena salah pilih variasi',      '2026-06-06 10:22:00'),
(3, 'Melakukan checkout keranjang belanja',                '2026-06-07 19:40:00'),
(1, 'Menulis review bintang 5 pada produk iPhone 16',      '2026-06-08 11:00:00');