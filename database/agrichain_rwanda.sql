-- AGRICHAIN RWANDA DATABASE
CREATE DATABASE IF NOT EXISTS agrichain_rwanda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agrichain_rwanda;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS supply_chain;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS quality_inspections;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS buyers;
DROP TABLE IF EXISTS farmers;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS locations;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    province VARCHAR(50) NOT NULL,
    district VARCHAR(80) NOT NULL,
    sector   VARCHAR(80) NOT NULL,
    UNIQUE KEY unique_location (district, sector)
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','farmer','buyer','inspector') NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE farmers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    location_id INT NOT NULL,
    farm_location VARCHAR(150) NOT NULL,
    main_products VARCHAR(200) NOT NULL,
    farm_size_ha DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id)
) ENGINE=InnoDB;

CREATE TABLE buyers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    location_id INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120) NOT NULL,
    business_type VARCHAR(80) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES locations(id)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    category_id INT NOT NULL,
    location_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    quantity_kg DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    price_per_kg DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    quality_grade ENUM('A','B','C','Pending') NOT NULL DEFAULT 'Pending',
    availability ENUM('Available','Low Stock','Sold Out') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id)   REFERENCES farmers(id)    ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (location_id) REFERENCES locations(id)
) ENGINE=InnoDB;

CREATE TABLE quality_inspections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    inspector_id INT NOT NULL,
    inspection_date DATE NOT NULL,
    appearance_score TINYINT NOT NULL,
    size_score TINYINT NOT NULL,
    cleanliness_score TINYINT NOT NULL,
    moisture_level DECIMAL(5,2) NOT NULL,
    damage_percentage DECIMAL(5,2) NOT NULL,
    final_grade ENUM('A','B','C') NOT NULL,
    comments TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id)   REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (inspector_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    delivery_location VARCHAR(150) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status ENUM('Pending','Confirmed','Processing','In Transit','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (buyer_id) REFERENCES buyers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_kg DECIMAL(10,2) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE supply_chain (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    order_id INT NULL,
    collection_point VARCHAR(150) NOT NULL,
    inspection_status ENUM('Pending','Completed') NOT NULL DEFAULT 'Pending',
    storage_status ENUM('Pending','Stored','Released') NOT NULL DEFAULT 'Pending',
    transportation_status ENUM('Pending','In Transit','Arrived') NOT NULL DEFAULT 'Pending',
    destination VARCHAR(150) NULL,
    delivery_status ENUM('Pending','Delivered') NOT NULL DEFAULT 'Pending',
    current_stage TINYINT NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SEED DATA (all sample accounts use the password: password)
INSERT INTO locations (province, district, sector) VALUES
('Northern','Musanze','Muhoza'),('Northern','Musanze','Kinigi'),('Western','Rubavu','Gisenyi'),
('Western','Nyamasheke','Kagano'),('Eastern','Nyagatare','Nyagatare'),('Eastern','Kayonza','Mukarange'),
('Southern','Huye','Ngoma'),('Kigali','Gasabo','Kimironko'),('Kigali','Kicukiro','Gikondo'),
('Kigali','Nyarugenge','Nyarugenge');

INSERT INTO users (full_name, email, phone, password, role) VALUES
('System Administrator','admin@agrichain.rw','0788000001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('Grace Uwase','inspector@agrichain.rw','0788000002','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','inspector'),
('Jean Baptiste Habimana','habimana@agrichain.rw','0788111111','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','farmer'),
('Marie Claire Mukamana','mukamana@agrichain.rw','0788222222','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','farmer'),
('Emmanuel Niyonzima','niyonzima@agrichain.rw','0788333333','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','farmer'),
('Patrick Nsengimana','kigalifresh@agrichain.rw','0788444444','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','buyer'),
('Diane Ingabire','hotelmilles@agrichain.rw','0788555555','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','buyer');

INSERT INTO farmers (user_id, location_id, farm_location, main_products, farm_size_ha) VALUES
(3,1,'Muhoza Hillside Farm, Musanze','Irish Potatoes, Beans, Cabbage',3.50),
(4,5,'Nyagatare Plains Farm','Maize, Tomatoes',8.00),
(5,4,'Kagano Coffee Cooperative Plot','Coffee, Cassava',5.25);

INSERT INTO buyers (user_id, location_id, company_name, contact_person, business_type) VALUES
(6,8,'Kigali Fresh Market Ltd','Patrick Nsengimana','Wholesale Market'),
(7,10,'Hotel des Mille Collines Kitchen','Diane Ingabire','Hotel / Restaurant');

INSERT INTO categories (name, description) VALUES
('Tubers','Potatoes, cassava and other root crops'),
('Cereals','Maize, sorghum, rice and other grains'),
('Cash Crops','Coffee, tea and other export crops'),
('Legumes','Beans, peas and soybeans'),
('Vegetables','Tomatoes, cabbage, carrots and more'),
('Fruits','Avocado, banana, pineapple and more');

INSERT INTO products (farmer_id, category_id, location_id, name, description, quantity_kg, price_per_kg, image, quality_grade, availability) VALUES
(1,1,1,'Irish Potatoes','Fresh Kinigi-variety potatoes from Musanze volcanic soils.',5000,450,'potatoes.jpg','A','Available'),
(2,2,5,'Maize','Dry, clean yellow maize from Nyagatare.',8000,520,'maize.jpg','B','Available'),
(3,3,4,'Arabica Coffee','Washed Arabica coffee beans from Nyamasheke highlands.',1500,2800,'coffee.jpg','A','Available'),
(1,4,1,'Beans','Red kidney beans, sorted and sun-dried.',2000,900,'beans.jpg','B','Available'),
(2,5,5,'Tomatoes','Fresh ripe tomatoes, harvested this week.',800,600,'tomatoes.jpg','B','Low Stock'),
(3,1,4,'Cassava','Cassava roots suitable for flour processing.',3000,300,'cassava.jpg','C','Available'),
(1,5,1,'Cabbage','Large green cabbages, awaiting inspection.',1200,350,'cabbage.jpg','Pending','Available'),
(1,5,2,'Carrots','Sweet orange carrots from Kinigi.',900,500,'carrots.jpg','A','Available');

INSERT INTO quality_inspections (product_id, inspector_id, inspection_date, appearance_score, size_score, cleanliness_score, moisture_level, damage_percentage, final_grade, comments) VALUES
(1,2,'2026-09-15',9,9,9,14.00,2.00,'A','Uniform size, clean skin, very low damage.'),
(2,2,'2026-09-16',7,7,8,13.50,6.00,'B','Good grain, slight discoloration on some kernels.'),
(3,2,'2026-09-17',9,8,9,11.00,1.50,'A','Excellent bean quality, properly washed and dried.'),
(4,2,'2026-09-18',8,7,7,15.00,5.00,'B','Some broken beans, otherwise acceptable.'),
(5,2,'2026-09-19',7,7,7,90.00,8.00,'B','Ripe, a few soft tomatoes. Sell quickly.'),
(6,2,'2026-09-20',5,5,5,60.00,15.00,'C','Suitable for flour processing, not for fresh market.'),
(8,2,'2026-09-21',9,9,9,88.00,2.50,'A','Bright color, firm texture.');

INSERT INTO orders (buyer_id, order_date, delivery_location, total_amount, status) VALUES
(1,'2026-09-25 09:30:00','Kimironko Market, Gasabo, Kigali',90000.00,'Delivered'),
(2,'2026-09-28 14:10:00','Nyarugenge, Kigali',280000.00,'In Transit'),
(1,'2026-10-05 11:45:00','Kimironko Market, Gasabo, Kigali',123000.00,'Pending');

INSERT INTO order_items (order_id, product_id, quantity_kg, unit_price, subtotal) VALUES
(1,1,200,450,90000.00),(2,3,100,2800,280000.00),(3,2,150,520,78000.00),(3,4,50,900,45000.00);

INSERT INTO supply_chain (product_id, order_id, collection_point, inspection_status, storage_status, transportation_status, destination, delivery_status, current_stage) VALUES
(1,1,'Musanze Collection Center','Completed','Released','Arrived','Kimironko Market, Kigali','Delivered',7),
(3,2,'Nyamasheke Coffee Hub','Completed','Released','In Transit','Nyarugenge, Kigali','Pending',5),
(2,3,'Nyagatare Grain Depot','Completed','Stored','Pending','Kimironko Market, Kigali','Pending',4),
(4,NULL,'Musanze Collection Center','Completed','Stored','Pending',NULL,'Pending',4),
(7,NULL,'Musanze Collection Center','Pending','Pending','Pending',NULL,'Pending',2);

INSERT INTO notifications (user_id, message) VALUES
(3,'Your product "Irish Potatoes" has been graded A.'),
(3,'Your order #1 has been delivered successfully.'),
(5,'New order received for Arabica Coffee (100 kg).'),
(6,'Your order #3 is pending confirmation.'),
(1,'New product "Cabbage" is waiting for quality inspection.');
