SET FOREIGN_KEY_CHECKS = 0;

INSERT IGNORE INTO `bookstore` (`store_id`, `name`, `address`, `phone`, `email`, `opening_hours`) VALUES
(1, 'InkCorner', 'Royal Street, Port Louis, Mauritius', '+230 212 0000', 'hello@inkcorner.mu', 'Mon–Sat 8:30 – 17:30');

INSERT IGNORE INTO `admin` (`admin_id`, `name`, `email`, `phone`, `password_hash`) VALUES
(1, 'Store Owner', 'admin@inkcorner.mu', '52500000', '$2y$10$JIZVhKlxjW8ejwSB3YHCBeVWjGxnH8etV/LVnHlW4FJkoL87gXL7y');

INSERT IGNORE INTO `customer` (`customer_id`, `name`, `email`, `phone`, `address`, `password_hash`) VALUES
(1, 'Demo Customer', 'demo@inkcorner.mu', '57001234', '12 Avenue des Fleurs, Vacoas', '$2y$10$IqviDsoVz7kprenRaNGEUemEP9NJXmcgepYs4zx8UvcQeAf9EUE5m');

INSERT IGNORE INTO `product`
(`product_id`, `name`, `description`, `price`, `section`, `category`, `brand`, `image`, `is_on_sale`, `sale_price`, `is_featured`, `created_at`) VALUES
(1,  'Black Fountain Luxury Pen', 'Stainless steel fountain pen with a fine gold-plated nib and gift box. Smooth, even ink flow for everyday writing and calligraphy.', 1200.00, 'stationery', 'Pens', 'Wordsworth & Black', NULL, 0, NULL, 1, '2026-10-20 09:00:00'),
(2,  'Gel Pen Set – 10 Colours', 'Quick-drying 0.7 mm gel pens in ten bright colours. Ideal for notes, journals and colour-coding.', 250.00, 'stationery', 'Pens', 'Pilot', NULL, 0, NULL, 0, '2026-09-10 09:00:00'),
(3,  'HB Graphite Pencils (12 pack)', 'Break-resistant HB pencils for school, sketching and exams.', 150.00, 'stationery', 'Pencils', 'Staedtler', NULL, 0, NULL, 0, '2026-08-01 09:00:00'),
(4,  'Colour Pencils – 24 Colours', 'Soft, richly pigmented colour pencils in a metal tin.', 450.00, 'stationery', 'Pencils', 'Faber-Castell', NULL, 1, 380.00, 0, '2026-09-15 09:00:00'),
(5,  'A4 Copybook – 200 Pages', 'Ruled A4 copybook with margin, 70 gsm paper and a hard cover.', 85.00, 'stationery', 'Copybooks', 'InkCorner Basics', NULL, 0, NULL, 0, '2026-07-01 09:00:00'),
(6,  'Exercise Copybook – 96 Pages', 'Light exercise book for primary and secondary school.', 45.00, 'stationery', 'Copybooks', 'InkCorner Basics', NULL, 1, 35.00, 0, '2026-07-01 09:00:00'),
(7,  'Marble Spiral Notebook A5', 'Marble-cover spiral notebook, 160 dotted pages and an elastic closure.', 395.00, 'stationery', 'Notebooks', 'InkCorner Studio', NULL, 0, NULL, 1, '2026-10-18 09:00:00'),
(8,  'Leather Journal', 'Refillable leather-bound journal with 200 cream pages.', 650.00, 'stationery', 'Notebooks', 'InkCorner Studio', NULL, 0, NULL, 0, '2026-10-05 09:00:00'),
(9,  'Watercolour Paint Set', '24 pan watercolours with two brushes and a mixing palette.', 520.00, 'stationery', 'Art Supplies', 'Winsor & Newton', NULL, 1, 450.00, 0, '2026-09-01 09:00:00'),
(10, 'Scientific Calculator fx-991', 'Approved for exams: 417 functions, solar and battery power.', 1450.00, 'stationery', 'Miscellaneous', 'Casio', NULL, 0, NULL, 0, '2026-08-20 09:00:00'),
(11, 'Geometry Set', 'Compass, protractor, set squares, ruler, eraser and sharpener in a tin.', 175.00, 'stationery', 'Miscellaneous', 'Helix', NULL, 1, 150.00, 0, '2026-08-20 09:00:00'),
(12, 'Back to School Bundle', 'Copybooks, pens, pencils, colour pencils, ruler, calculator and a pencil case – everything for the new term.', 990.00, 'stationery', 'Miscellaneous', 'InkCorner Basics', NULL, 1, 850.00, 1, '2026-10-01 09:00:00'),
(13, 'Pride and Prejudice', 'Jane Austen''s classic novel of manners, paperback edition.', 350.00, 'book', 'Fiction', 'Jane Austen', NULL, 0, NULL, 0, '2026-06-10 09:00:00'),
(14, 'Things Fall Apart', 'Chinua Achebe''s landmark novel, paperback edition.', 425.00, 'book', 'Fiction', 'Chinua Achebe', NULL, 0, NULL, 0, '2026-10-12 09:00:00'),
(15, 'Atomic Habits', 'A practical guide to building good habits and breaking bad ones.', 690.00, 'book', 'Non-fiction', 'James Clear', NULL, 1, 590.00, 0, '2026-09-20 09:00:00'),
(16, 'PHP & MySQL: Server-side Web Development', 'Full-colour guide to building database-driven websites with PHP.', 1850.00, 'book', 'Academic', 'Jon Duckett', NULL, 0, NULL, 1, '2026-10-22 09:00:00'),
(17, 'Charlotte''s Web', 'The much-loved children''s story of friendship, illustrated edition.', 295.00, 'book', 'Children', 'E. B. White', NULL, 0, NULL, 0, '2026-08-05 09:00:00');

INSERT IGNORE INTO `inventory` (`inventory_id`, `product_id`, `quantity`) VALUES
(1, 1, 15), (2, 2, 60), (3, 3, 120), (4, 4, 40), (5, 5, 200), (6, 6, 300),
(7, 7, 25), (8, 8, 3), (9, 9, 18), (10, 10, 12), (11, 11, 50), (12, 12, 20),
(13, 13, 10), (14, 14, 8), (15, 15, 14), (16, 16, 4), (17, 17, 0);

INSERT IGNORE INTO `productvariant` (`variant_id`, `product_id`, `variant_name`, `stock`) VALUES
(1, 1, 'Black & Gold', 8), (2, 1, 'Silver & Gold', 7),
(3, 7, 'Pink marble', 13), (4, 7, 'Blue marble', 12),
(5, 8, 'Brown', 2), (6, 8, 'Black', 1);

INSERT IGNORE INTO `orders` (`order_id`, `customer_id`, `order_date`, `status`, `total_amount`, `shipping_address`, `created_at`) VALUES
(1, 1, '2026-10-20', 'Delivered', 1255.00, '12 Avenue des Fleurs, Vacoas', '2026-10-20 10:15:00'),
(2, 1, '2026-10-24', 'Paid',      1020.00, '12 Avenue des Fleurs, Vacoas', '2026-10-24 16:40:00');

INSERT IGNORE INTO `orderitem` (`order_item_id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`) VALUES
(1, 1, 12, NULL, 1, 850.00), (2, 1, 5, NULL, 3, 85.00), (3, 1, 3, NULL, 1, 150.00),
(4, 2, 12, NULL, 1, 850.00), (5, 2, 5, NULL, 2, 85.00);

INSERT IGNORE INTO `payment` (`payment_id`, `order_id`, `amount`, `payment_date`, `method`, `status`, `transaction_ref`, `card_last4`) VALUES
(1, 1, 1255.00, '2026-10-20', 'Card (Visa)', 'Success', 'SIM-20261020-A1B2C3', '4242'),
(2, 2, 1020.00, '2026-10-24', 'Card (Visa)', 'Success', 'SIM-20261024-D4E5F6', '4242');

INSERT IGNORE INTO `receipt` (`receipt_id`, `payment_id`, `issue_date`, `receipt_number`) VALUES
(1, 1, '2026-10-20', 'RCPT-000001'),
(2, 2, '2026-10-24', 'RCPT-000002');

SET FOREIGN_KEY_CHECKS = 1;
