# AgriChain Rwanda - Smart Agribusiness Supply Chain & Quality Grading Platform

PHP 8 + MySQL + HTML/CSS/JavaScript. No frameworks.

## Install (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\agrichain-rwanda`
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open http://localhost/phpmyadmin -> **Import** -> choose `database/agrichain_rwanda.sql` -> **Import**.
4. Open http://localhost/agrichain-rwanda/test-db.php (should show green + row counts), then delete that file later.
5. Open http://localhost/agrichain-rwanda/

If your folder has another name or Apache uses another port, change `BASE_URL` in `config/database.php`.
If MySQL has a root password, set it in `config/database.php`.

## Demo accounts (password for all: `password`)
| Role | Email |
|---|---|
| Admin | admin@agrichain.rw |
| Quality inspector | inspector@agrichain.rw |
| Farmer | habimana@agrichain.rw |
| Buyer | kigalifresh@agrichain.rw |

## Photos (optional)
The site works without photos (it shows emoji placeholders). To add real photos, copy JPG files into:
- `images/backgrounds/hero.jpg`  - homepage hero background (wide landscape photo)
- `images/products/` - named `potatoes.jpg, maize.jpg, coffee.jpg, beans.jpg, tomatoes.jpg, cassava.jpg, cabbage.jpg, carrots.jpg`
Farmers can also upload an image when adding a product.

## Folder map
- `config/` database connection | `includes/` header, navbar, footer, auth, helpers
- `admin/`, `farmer/`, `buyer/` role dashboards | `css/style.css`, `js/script.js`
- Public pages in the root: index, about, marketplace, product-details, quality, farmers, buyers, supply-chain, contact, login, register

## Quality grade rule (PHP `calc_grade()` and JavaScript use the same rule)
average of appearance, size, cleanliness scores (out of 10):
A = average >= 8 and damage <= 5% | B = average >= 6 and damage <= 12% | C = otherwise
