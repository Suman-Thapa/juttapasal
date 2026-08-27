-- Seeds the 10 images you already have in /images as real products,
-- so the homepage "Our Collection" section has something to pull from.
-- Run this AFTER sql/schema_updates.sql (needs Category/Discount/Rating columns).

INSERT INTO product (Brand, Category, Description, Price, Quantity, Image_url, Discount, Rating) VALUES
("Nike",    "Men",   "Air Zoom Pegasus - everyday running comfort",      780, 8,  "images/n1.avif", 10, 4.5),
("Nike",    "Men",   "React Infinity - cushioned long-distance runner",  920, 6,  "images/n2.avif", 0,  4.7),
("Nike",    "Women", "Air Max Wave - retro street style",                 650, 10, "images/n3.avif", 15, 4.3),
("Nike",    "Men",   "Metcon Trainer - built for the gym",                870, 7,  "images/n4.avif", 0,  4.6),
("Nike",    "Kids",  "Revolution - lightweight kids running shoe",        320, 15, "images/n5.avif", 5,  4.2),
("Puma",    "Men",   "Velocity Nitro - responsive daily trainer",         540, 9,  "images/shoe-gallery-pic1.jpg", 0,  4.1),
("Adidas",  "Women", "Ultraboost - energy-return knit sneaker",           890, 5,  "images/shoe-gallery-pic2.jpg", 20, 4.8),
("Vance",   "Men",   "Old Skool - classic skate silhouette",              410, 12, "images/shoe-gallery-pic3.jpg", 0,  4.0),
("Nike",    "Unisex","Air Force 1 - the timeless court classic",          660, 11, "images/shoe-gallery-pic4.jpg", 0,  4.9),
("Adidas",  "Kids",  "Superstar Kids - shell-toe for little feet",        290, 14, "images/shoe-gallery-pic5.jpg", 10, 4.4);
