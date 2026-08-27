-- ============================================================
-- JUTTAPASAL DATABASE BACKUP
-- Database: juttapasal
-- Compatible with MySQL 8.x / MariaDB 10.4+
-- ============================================================

-- Create database
DROP DATABASE IF EXISTS juttapasal;
CREATE DATABASE juttapasal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE juttapasal;

-- Disable foreign key checks while creating/loading database
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABLE: Users
-- ============================================================

DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    ID INT(11) NOT NULL AUTO_INCREMENT,
    First_Name VARCHAR(255) NOT NULL,
    Last_Name VARCHAR(255) NOT NULL,
    Date_of_Birth DATE NOT NULL,
    Gender ENUM('M','F') NOT NULL,
    Address VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('user','admin') NOT NULL DEFAULT 'user',
    Status ENUM('active','blocked') NOT NULL DEFAULT 'active',

    PRIMARY KEY (ID),
    UNIQUE KEY Email (Email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- TABLE: product
-- ============================================================

DROP TABLE IF EXISTS product;

CREATE TABLE product (
    Product_id INT(11) NOT NULL AUTO_INCREMENT,
    Brand VARCHAR(255) NOT NULL,
    Category VARCHAR(50) NOT NULL DEFAULT 'Men',
    Description VARCHAR(255) DEFAULT NULL,
    Sizes VARCHAR(255) DEFAULT NULL,
    Price INT(11) NOT NULL,
    Quantity INT(11) NOT NULL,
    Discount INT(11) NOT NULL DEFAULT 0,
    Rating DECIMAL(2,1) NOT NULL DEFAULT 0.0,
    Image_url VARCHAR(255) NOT NULL,

    PRIMARY KEY (Product_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- TABLE: orders
-- ============================================================

DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
    Order_id INT(11) NOT NULL AUTO_INCREMENT,
    User_id INT(11) NOT NULL,
    Receiver_Name VARCHAR(255) NOT NULL,
    Receiver_Phone VARCHAR(20) NOT NULL,
    City VARCHAR(100) NOT NULL,
    Delivery_Address TEXT NOT NULL,
    Postal_Code VARCHAR(20) DEFAULT NULL,
    Total_Amount DECIMAL(10,2) NOT NULL,

    Status ENUM(
        'processing',
        'packing',
        'shipping',
        'delivered',
        'cancelled'
    ) NOT NULL DEFAULT 'processing',

    Payment_Method ENUM(
        'cod',
        'esewa',
        'khalti'
    ) NOT NULL,

    Payment_Status ENUM(
        'unpaid',
        'paid'
    ) NOT NULL DEFAULT 'unpaid',

    Ref_Id VARCHAR(100) DEFAULT NULL,
    Transaction_Uuid VARCHAR(100) DEFAULT NULL,
    Created_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (Order_id),
    KEY User_id (User_id),

    CONSTRAINT orders_ibfk_1
        FOREIGN KEY (User_id)
        REFERENCES Users (ID)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- TABLE: order_items
-- ============================================================

DROP TABLE IF EXISTS order_items;

CREATE TABLE order_items (
    Order_Item_id INT(11) NOT NULL AUTO_INCREMENT,
    Order_id INT(11) NOT NULL,
    Product_id INT(11) DEFAULT NULL,
    Product_Name VARCHAR(255) NOT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Quantity INT(11) NOT NULL,

    PRIMARY KEY (Order_Item_id),
    KEY Order_id (Order_id),
    KEY Product_id (Product_id),

    CONSTRAINT order_items_ibfk_1
        FOREIGN KEY (Order_id)
        REFERENCES orders (Order_id)
        ON DELETE CASCADE,

    CONSTRAINT order_items_ibfk_2
        FOREIGN KEY (Product_id)
        REFERENCES product (Product_id)
        ON DELETE SET NULL
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- TABLE: cart_item
-- ============================================================

DROP TABLE IF EXISTS cart_item;

CREATE TABLE cart_item (
    User_id INT(11) NOT NULL,
    Product_id INT(11) NOT NULL,
    Item_quantity INT(11) NOT NULL,

    PRIMARY KEY (User_id, Product_id),
    KEY Product_id (Product_id),

    CONSTRAINT cart_item_ibfk_1
        FOREIGN KEY (User_id)
        REFERENCES Users (ID),

    CONSTRAINT cart_item_ibfk_2
        FOREIGN KEY (Product_id)
        REFERENCES product (Product_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- TABLE: wish_list
-- ============================================================

DROP TABLE IF EXISTS wish_list;

CREATE TABLE wish_list (
    User_id INT(11) NOT NULL,
    Product_id INT(11) NOT NULL,

    PRIMARY KEY (User_id, Product_id),
    KEY Product_id (Product_id),

    CONSTRAINT wish_list_ibfk_1
        FOREIGN KEY (User_id)
        REFERENCES Users (ID),

    CONSTRAINT wish_list_ibfk_2
        FOREIGN KEY (Product_id)
        REFERENCES product (Product_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;


-- ============================================================
-- INSERT DATA: Users
-- ============================================================

INSERT INTO Users
    (ID, First_Name, Last_Name, Date_of_Birth, Gender, Address, Email, Password, Role, Status)
VALUES
    (
        1,
        'Hello',
        'Suman',
        '2022-04-02',
        'F',
        'ddmhbd',
        'hellowitsmesuman123@gmail.com',
        '$2y$10$ZeuwjjxxiJWWrHqDRx.gceDSmAmPUMpAsFR9RyukWFzllgCASIACS',
        'user',
        'active'
    ),
    (
        2,
        'Suman',
        'Thapa',
        '2029-08-09',
        'M',
        'ratnapark',
        'sumanthapa302702@gmail.com',
        '$2y$10$/lj8YL0LhR9YVjcLdhPTR.oIUCczqP9hi3unsrS2UK4qvriIslQ6.',
        'admin',
        'active'
    );


-- ============================================================
-- INSERT DATA: product
-- ============================================================

INSERT INTO product
    (
        Product_id,
        Brand,
        Category,
        Description,
        Sizes,
        Price,
        Quantity,
        Discount,
        Rating,
        Image_url
    )
VALUES
    (
        1,
        'Nike',
        'Men',
        'Men''s Kyrie Infinity Basketball Shoe',
        NULL,
        460,
        5,
        0,
        0.0,
        'images/product1.png'
    ),
    (
        2,
        'Nike',
        'Men',
        'Men''s Jordan',
        NULL,
        500,
        7,
        0,
        0.0,
        'images/product2.png'
    ),
    (
        3,
        'Nike',
        'Men',
        'Unisex Air Force 1',
        NULL,
        660,
        10,
        0,
        0.0,
        'images/product3.png'
    ),
    (
        4,
        'Addidas',
        'Men',
        'Tennis shoe',
        NULL,
        360,
        4,
        0,
        0.0,
        'images/product4.png'
    ),
    (
        5,
        'Puma',
        'Men',
        'Casual Sneakers',
        NULL,
        260,
        3,
        0,
        0.0,
        'images/product5.png'
    ),
    (
        6,
        'Mango',
        'Men',
        'Men''s black boots',
        NULL,
        160,
        2,
        0,
        0.0,
        'images/product6.png'
    ),
    (
        7,
        'Vance',
        'Men',
        'Black boot collection',
        NULL,
        760,
        5,
        0,
        0.0,
        'images/product7.png'
    ),
    (
        8,
        'Nike',
        'Men',
        'Men''s Zoom Basketball Shoes',
        NULL,
        860,
        3,
        0,
        0.0,
        'images/product8.png'
    ),
    (
        9,
        'Nike',
        'Men',
        'Men''s Kyrie Basketball Shoes',
        NULL,
        1070,
        3,
        0,
        0.0,
        'images/product9.png'
    ),
    (
        10,
        'Nike',
        'Men',
        'Men''s Kyrie Basketball Shoes',
        NULL,
        560,
        12,
        0,
        0.0,
        'images/main-nike-pic.png'
    ),
    (
        11,
        'Nike',
        'Men',
        'Air Zoom Pegasus - everyday running comfort',
        NULL,
        780,
        8,
        10,
        4.5,
        'images/n1.avif'
    ),
    (
        12,
        'Nike',
        'Men',
        'React Infinity - cushioned long-distance runner',
        NULL,
        920,
        6,
        0,
        4.7,
        'images/n2.avif'
    ),
    (
        13,
        'Nike',
        'Women',
        'Air Max Wave - retro street style',
        NULL,
        650,
        10,
        15,
        4.3,
        'images/n3.avif'
    ),
    (
        14,
        'Nike',
        'Men',
        'Metcon Trainer - built for the gym',
        NULL,
        870,
        5,
        0,
        4.6,
        'images/n4.avif'
    ),
    (
        15,
        'Nike',
        'Kids',
        'Revolution - lightweight kids running shoe',
        NULL,
        320,
        15,
        5,
        4.2,
        'images/n5.avif'
    ),
    (
        16,
        'Puma',
        'Men',
        'Velocity Nitro - responsive daily trainer',
        NULL,
        540,
        9,
        0,
        4.1,
        'images/shoe-gallery-pic1.jpg'
    ),
    (
        17,
        'Adidas',
        'Women',
        'Ultraboost - energy-return knit sneaker',
        NULL,
        890,
        5,
        20,
        4.8,
        'images/shoe-gallery-pic2.jpg'
    ),
    (
        18,
        'Vance',
        'Men',
        'Old Skool - classic skate silhouette',
        NULL,
        410,
        12,
        0,
        4.0,
        'images/shoe-gallery-pic3.jpg'
    ),
    (
        19,
        'Nike',
        'Unisex',
        'Air Force 1 - the timeless court classic',
        NULL,
        660,
        11,
        0,
        4.9,
        'images/shoe-gallery-pic4.jpg'
    ),
    (
        20,
        'Adidas',
        'Kids',
        'Superstar Kids - shell-toe for little feet',
        NULL,
        290,
        14,
        10,
        4.4,
        'images/shoe-gallery-pic5.jpg'
    ),
    (
        21,
        'Suman Jutta',
        'Men',
        'This is my my first repository',
        '38',
        12000,
        15,
        15,
        5.0,
        'images/WhatsApp Image 2026-02-13 at 23.31.57.png'
    );


-- ============================================================
-- INSERT DATA: orders
-- ============================================================

INSERT INTO orders
    (
        Order_id,
        User_id,
        Receiver_Name,
        Receiver_Phone,
        City,
        Delivery_Address,
        Postal_Code,
        Total_Amount,
        Status,
        Payment_Method,
        Payment_Status,
        Ref_Id,
        Transaction_Uuid,
        Created_At
    )
VALUES
    (
        1,
        1,
        'Hello Suman',
        '9810124811',
        'Kathmandu',
        'Kathmandu Nepal',
        '0069',
        1220.00,
        'processing',
        'cod',
        'unpaid',
        NULL,
        NULL,
        '2026-08-27 06:30:35'
    ),
    (
        2,
        1,
        'Hello Suman',
        '9810124811',
        'Kathmandu',
        'ratnapark',
        '6969',
        1020.00,
        'processing',
        'khalti',
        'paid',
        'PamAUuKd67Xy9XyCDLdgjf',
        'NfSkytYquy2yfZZa9DJ3QK',
        '2026-08-27 12:24:30'
    ),
    (
        3,
        1,
        'Hello Suman',
        '9810124811',
        'Kathmandu',
        'ratnapark',
        '6969',
        1010.00,
        'processing',
        'esewa',
        'paid',
        '000GWN7',
        '20260827-142602-1',
        '2026-08-27 12:26:24'
    );


-- ============================================================
-- INSERT DATA: order_items
-- ============================================================

INSERT INTO order_items
    (
        Order_Item_id,
        Order_id,
        Product_id,
        Product_Name,
        Price,
        Quantity
    )
VALUES
    (
        1,
        1,
        9,
        'Nike - Men''s Kyrie Basketball Shoes',
        1070.00,
        1
    ),
    (
        2,
        2,
        14,
        'Nike - Metcon Trainer - built for the gym',
        870.00,
        1
    ),
    (
        3,
        3,
        8,
        'Nike - Men''s Zoom Basketball Shoes',
        860.00,
        1
    );


-- ============================================================
-- cart_item has no existing data
-- ============================================================


-- ============================================================
-- wish_list has no existing data
-- ============================================================


-- Reset AUTO_INCREMENT values
ALTER TABLE Users AUTO_INCREMENT = 3;
ALTER TABLE product AUTO_INCREMENT = 22;
ALTER TABLE orders AUTO_INCREMENT = 4;
ALTER TABLE order_items AUTO_INCREMENT = 4;


-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- VERIFICATION
-- ============================================================

SELECT 'Database created successfully!' AS Message;

SELECT
    TABLE_NAME,
    TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'juttapasal'
ORDER BY TABLE_NAME;