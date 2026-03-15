-- Seed: Drinks section + categories + menu items for The Lusso Restaurant (restaurant@lussohotelsabuja.com)
-- Run this AFTER migration.sql. Run ONCE only; re-running will create duplicate categories and items.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET NAMES utf8mb4 */;

-- Get restaurant ID (The Lusso Restaurant)
SET @rid = (SELECT id FROM restaurants WHERE email = 'restaurant@lussohotelsabuja.com' LIMIT 1);

-- Exit if restaurant not found or Drinks section already exists
SET @sid = (SELECT id FROM sections WHERE restaurant_id = @rid AND slug = 'drinks' LIMIT 1);

-- Insert Drinks section only if not exists
INSERT INTO sections (restaurant_id, name, slug, display_order, is_active)
SELECT @rid, 'Drinks', 'drinks', 1, 1 FROM DUAL
WHERE @rid IS NOT NULL AND @sid IS NULL;

SET @sid = (SELECT id FROM sections WHERE restaurant_id = @rid AND slug = 'drinks' LIMIT 1);

-- Only insert categories and items if we have a section
INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Soft Drinks / Water', 'soft-drinks-water', 1, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat1 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Juices', 'juices', 2, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat2 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Energy Drinks', 'energy-drinks', 3, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat3 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Beers', 'beers', 4, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat4 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Aperitif', 'aperitif', 5, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat5 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Gin', 'gin', 6, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat6 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Whisky Regular Blend', 'whisky-regular-blend', 7, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat7 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Whisky Single Malt', 'whisky-single-malt', 8, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat8 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Whisky Premium Blend', 'whisky-premium-blend', 9, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat9 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Whisky American Irish', 'whisky-american-irish', 10, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat10 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Vodka', 'vodka', 11, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat11 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Rum', 'rum', 12, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat12 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Cognac', 'cognac', 13, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat13 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Tequila', 'tequila', 14, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat14 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Liquor', 'liquor', 15, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat15 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Hot Beverages', 'hot-beverages', 16, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat16 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'White Wine', 'white-wine', 17, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat17 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Red Wine', 'red-wine', 18, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat18 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Rosé Wine', 'rose-wine', 19, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat19 = LAST_INSERT_ID();

INSERT INTO categories (restaurant_id, section_id, name, slug, display_order, is_active)
SELECT @rid, @sid, 'Champagne', 'champagne', 20, 1 FROM DUAL WHERE @sid IS NOT NULL LIMIT 1;
SET @cat20 = LAST_INSERT_ID();

-- Menu items: only insert when section was created (we have valid category IDs from LAST_INSERT_ID in same session)
-- Use @rid, @cat1..@cat20. Price 0 = on request (e.g. Diet Coke, Legend, etc.)

-- Soft Drinks / Water (@cat1)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat1, 'Still Water Large', 'still-water-large', 4000, 1, 1),
(@rid, @cat1, 'Still Water Small', 'still-water-small', 3000, 2, 1),
(@rid, @cat1, 'Perrier Sparkling Water Large', 'perrier-sparkling-water-large', 30000, 3, 1),
(@rid, @cat1, 'Perrier Sparkling Water Small', 'perrier-sparkling-water-small', 17000, 4, 1),
(@rid, @cat1, 'Soft Drinks (Coca Cola, Sprite, Tonic, Bitter Lemon, Soda Water, Fanta, Pepsi, Mirinda)', 'soft-drinks-mix', 3500, 5, 1),
(@rid, @cat1, 'Diet Coke', 'diet-coke', 0, 6, 1),
(@rid, @cat1, 'Maltina', 'maltina', 4500, 7, 1),
(@rid, @cat1, 'Amstel Malta', 'amstel-malta', 4500, 8, 1),
(@rid, @cat1, 'Malta Guinness', 'malta-guinness', 4500, 9, 1),
(@rid, @cat1, 'Fayrous', 'fayrous', 4500, 10, 1);

-- Juices (@cat2)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat2, 'Fresh Juice Large', 'fresh-juice-large', 7000, 1, 1),
(@rid, @cat2, 'Fresh Juice Small', 'fresh-juice-small', 5000, 2, 1),
(@rid, @cat2, 'Fresh Fruit Punch Large', 'fresh-fruit-punch-large', 7000, 3, 1),
(@rid, @cat2, 'Fresh Fruit Punch Small', 'fresh-fruit-punch-small', 5500, 4, 1),
(@rid, @cat2, 'Packet Juice Large', 'packet-juice-large', 4500, 5, 1),
(@rid, @cat2, 'Packet Juice Small', 'packet-juice-small', 4000, 6, 1),
(@rid, @cat2, 'Juice Packet', 'juice-packet', 12000, 7, 1),
(@rid, @cat2, 'Cranberry Packet', 'cranberry-packet', 25000, 8, 1),
(@rid, @cat2, 'Cranberry Glass', 'cranberry-glass', 9000, 9, 1);

-- Energy Drinks (@cat3)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat3, 'Power Horse', 'power-horse', 6000, 1, 1),
(@rid, @cat3, 'Red Bull', 'red-bull', 6500, 2, 1),
(@rid, @cat3, 'Climax', 'climax', 6000, 3, 1);

-- Beers (@cat4)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat4, 'Star', 'star', 5000, 1, 1),
(@rid, @cat4, 'Heineken', 'heineken', 5500, 2, 1),
(@rid, @cat4, 'Heineken Draught Large', 'heineken-draught-large', 5500, 3, 1),
(@rid, @cat4, 'Heineken Draught Small', 'heineken-draught-small', 4500, 4, 1),
(@rid, @cat4, 'Budweiser', 'budweiser', 5500, 5, 1),
(@rid, @cat4, 'Guinness Extra Smooth', 'guinness-extra-smooth', 5000, 6, 1),
(@rid, @cat4, 'Guinness Stout 60cl', 'guinness-stout-60cl', 6000, 7, 1),
(@rid, @cat4, 'Guinness Stout Medium', 'guinness-stout-medium', 5000, 8, 1),
(@rid, @cat4, 'Star Radler Citrus', 'star-radler-citrus', 4500, 9, 1),
(@rid, @cat4, 'Trophy', 'trophy', 4500, 10, 1),
(@rid, @cat4, '33 Export', '33-export', 4500, 11, 1),
(@rid, @cat4, 'Life Beer', 'life-beer', 4500, 12, 1),
(@rid, @cat4, 'Hero Beer', 'hero-beer', 4500, 13, 1),
(@rid, @cat4, 'Gulder', 'gulder', 5000, 14, 1),
(@rid, @cat4, 'Goldberg', 'goldberg', 4500, 15, 1),
(@rid, @cat4, 'Legend', 'legend', 0, 16, 1),
(@rid, @cat4, 'Tiger Beer', 'tiger-beer', 4500, 17, 1),
(@rid, @cat4, 'Origin Beer', 'origin-beer', 5000, 18, 1),
(@rid, @cat4, 'Smirnoff Double Black', 'smirnoff-double-black', 0, 19, 1),
(@rid, @cat4, 'Smirnoff Ice 60cl', 'smirnoff-ice-60cl', 6000, 20, 1),
(@rid, @cat4, 'Smirnoff Ice 35cl', 'smirnoff-ice-35cl', 4500, 21, 1),
(@rid, @cat4, 'Desperado', 'desperado', 5000, 22, 1),
(@rid, @cat4, 'Flying Fish', 'flying-fish', 4500, 23, 1),
(@rid, @cat4, 'Castle Lite', 'castle-lite', 5000, 24, 1);

-- Aperitif (@cat5)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat5, 'Martini Bianco', 'martini-bianco', 50000, 1, 1),
(@rid, @cat5, 'Martini Rorro', 'martini-rorro', 60000, 2, 1),
(@rid, @cat5, 'Martini Extra Dry', 'martini-extra-dry', 60000, 3, 1),
(@rid, @cat5, 'Aperol Aperitivo', 'aperol-aperitivo', 80000, 4, 1),
(@rid, @cat5, 'Campari', 'campari', 70000, 5, 1);

-- Gin (@cat6)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat6, 'Gordon', 'gordon', 35000, 1, 1),
(@rid, @cat6, 'Beefeater', 'beefeater', 45000, 2, 1),
(@rid, @cat6, 'Bombay Sapphire', 'bombay-sapphire', 80000, 3, 1),
(@rid, @cat6, 'Hendrick', 'hendrick', 140000, 4, 1),
(@rid, @cat6, 'Tanqueray 10', 'tanqueray-10', 180000, 5, 1),
(@rid, @cat6, 'Monkey 47', 'monkey-47', 140000, 6, 1);

-- Whisky Regular Blend (@cat7)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat7, 'Johnnie Walker Red Label', 'johnnie-walker-red-label', 50000, 1, 1),
(@rid, @cat7, 'Famous Grouse', 'famous-grouse', 55000, 2, 1),
(@rid, @cat7, 'Ballantine', 'ballantine', 50000, 3, 1);

-- Whisky Single Malt (@cat8)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat8, 'Macallan 12 years', 'macallan-12-years', 250000, 1, 1),
(@rid, @cat8, 'Macallan 15 years', 'macallan-15-years', 600000, 2, 1),
(@rid, @cat8, 'Macallan 18 years', 'macallan-18-years', 1300000, 3, 1),
(@rid, @cat8, 'Glenfiddich 12 years', 'glenfiddich-12-years', 220000, 4, 1),
(@rid, @cat8, 'Glenfiddich 15 years', 'glenfiddich-15-years', 330000, 5, 1),
(@rid, @cat8, 'Glenfiddich 18 years', 'glenfiddich-18-years', 450000, 6, 1),
(@rid, @cat8, 'Singleton 12 years', 'singleton-12-years', 200000, 7, 1),
(@rid, @cat8, 'Singleton 15 years', 'singleton-15-years', 300000, 8, 1),
(@rid, @cat8, 'Singleton 18 years', 'singleton-18-years', 650000, 9, 1);

-- Whisky Premium Blend (@cat9)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat9, 'Chivas Regal 12 years', 'chivas-regal-12-years', 120000, 1, 1),
(@rid, @cat9, 'Chivas Regal 18 years', 'chivas-regal-18-years', 310000, 2, 1),
(@rid, @cat9, 'Smokey Monkey', 'smokey-monkey', 200000, 3, 1),
(@rid, @cat9, 'Chivas Regal 25 years', 'chivas-regal-25-years', 80000, 4, 1),
(@rid, @cat9, 'Chivas Regal Royal Salute 21 years', 'chivas-regal-royal-salute-21-years', 0, 5, 1),
(@rid, @cat9, 'Johnnie Walker Black Label', 'johnnie-walker-black-label', 120000, 6, 1),
(@rid, @cat9, 'Johnnie Walker Gold Label', 'johnnie-walker-gold-label', 240000, 7, 1),
(@rid, @cat9, 'Johnnie Walker Platinum Label', 'johnnie-walker-platinum-label', 450000, 8, 1),
(@rid, @cat9, 'Johnnie Walker Blue Label', 'johnnie-walker-blue-label', 130000, 9, 1);

-- Whisky American Irish (@cat10)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat10, 'Jameson', 'jameson', 80000, 1, 1),
(@rid, @cat10, 'Jameson Black Barrel', 'jameson-black-barrel', 120000, 2, 1),
(@rid, @cat10, 'Jack Daniel', 'jack-daniel', 90000, 3, 1),
(@rid, @cat10, 'Jack Daniel Gentleman Jack', 'jack-daniel-gentleman-jack', 130000, 4, 1),
(@rid, @cat10, 'Jack Daniel Honey', 'jack-daniel-honey', 100000, 5, 1),
(@rid, @cat10, 'Jack Daniel Single Barrel Select', 'jack-daniel-single-barrel-select', 250000, 6, 1),
(@rid, @cat10, 'Jack Daniel Apple', 'jack-daniel-apple', 90000, 7, 1),
(@rid, @cat10, 'Woodford Reserve', 'woodford-reserve', 150000, 8, 1),
(@rid, @cat10, 'Wild Turkey', 'wild-turkey', 80000, 9, 1);

-- Vodka (@cat11)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat11, 'Smirnoff Red', 'smirnoff-red', 70000, 1, 1),
(@rid, @cat11, 'Smirnoff Blue', 'smirnoff-blue', 75000, 2, 1),
(@rid, @cat11, 'Ciroc', 'ciroc', 130000, 3, 1),
(@rid, @cat11, 'Neft Vodka', 'neft-vodka', 130000, 4, 1),
(@rid, @cat11, 'Absolut Blue', 'absolut-blue', 65000, 5, 1),
(@rid, @cat11, 'Grey Goose', 'grey-goose', 140000, 6, 1);

-- Rum (@cat12)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat12, 'Bacardi', 'bacardi', 60000, 1, 1),
(@rid, @cat12, 'Captain Morgan', 'captain-morgan', 50000, 2, 1),
(@rid, @cat12, 'St James', 'st-james', 99000, 3, 1),
(@rid, @cat12, 'Malibu', 'malibu', 60000, 4, 1);

-- Cognac (@cat13)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat13, 'Remy Martin XO', 'remy-martin-xo', 1200000, 1, 1),
(@rid, @cat13, 'Remy Martin VSOP', 'remy-martin-vsop', 350000, 2, 1),
(@rid, @cat13, 'Hennessy XO', 'hennessy-xo', 1200000, 3, 1),
(@rid, @cat13, 'Hennessy VSOP', 'hennessy-vsop', 320000, 4, 1),
(@rid, @cat13, 'Hennessy VS', 'hennessy-vs', 230000, 5, 1),
(@rid, @cat13, 'Martel VS', 'martel-vs', 150000, 6, 1),
(@rid, @cat13, 'Martel Blue Swift', 'martel-blue-swift', 280000, 7, 1),
(@rid, @cat13, 'Martel XO', 'martel-xo', 980000, 8, 1),
(@rid, @cat13, 'Remy Martin 1738', 'remy-martin-1738', 400000, 9, 1);

-- Tequila (@cat14)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat14, 'Olmeca Gold', 'olmeca-gold', 60000, 1, 1),
(@rid, @cat14, 'El Padrino', 'el-padrino', 60000, 2, 1),
(@rid, @cat14, 'Sierra Gold', 'sierra-gold', 50000, 3, 1),
(@rid, @cat14, 'Sierra White', 'sierra-white', 50000, 4, 1),
(@rid, @cat14, 'Cazcabel Reposado', 'cazcabel-reposado', 160000, 5, 1);

-- Liquor (@cat15)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat15, 'Galliano', 'galliano', 0, 1, 1),
(@rid, @cat15, 'Grand Marnier', 'grand-marnier', 0, 2, 1),
(@rid, @cat15, 'Cointreau', 'cointreau', 0, 3, 1),
(@rid, @cat15, 'Baileys Irish Cream', 'baileys-irish-cream', 75000, 4, 1),
(@rid, @cat15, 'Amarula', 'amarula', 80000, 5, 1),
(@rid, @cat15, 'Amaretto', 'amaretto', 80000, 6, 1),
(@rid, @cat15, 'Tia Maria', 'tia-maria', 80000, 7, 1),
(@rid, @cat15, 'Sambuca', 'sambuca', 75000, 8, 1),
(@rid, @cat15, 'Drambuie', 'drambuie', 75000, 9, 1),
(@rid, @cat15, 'Kahlua', 'kahlua', 0, 10, 1),
(@rid, @cat15, 'Grappa Nonino', 'grappa-nonino', 0, 11, 1);

-- Hot Beverages (@cat16)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat16, 'Americano', 'americano', 6000, 1, 1),
(@rid, @cat16, 'Cappuccino', 'cappuccino', 6500, 2, 1),
(@rid, @cat16, 'Espresso', 'espresso', 6000, 3, 1),
(@rid, @cat16, 'Double Espresso', 'double-espresso', 6500, 4, 1),
(@rid, @cat16, 'Café Latte', 'cafe-latte', 6500, 5, 1),
(@rid, @cat16, 'Macchiato', 'macchiato', 6000, 6, 1),
(@rid, @cat16, 'Hot Chocolate', 'hot-chocolate', 6500, 7, 1),
(@rid, @cat16, 'Assorted Tea', 'assorted-tea', 6500, 8, 1),
(@rid, @cat16, 'Caramel Frappe', 'caramel-frappe', 6500, 9, 1),
(@rid, @cat16, 'Strawberry Frappe', 'strawberry-frappe', 6500, 10, 1),
(@rid, @cat16, 'Banana Frappe', 'banana-frappe', 6500, 11, 1);

-- White Wine (@cat17)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat17, 'Man Sauvignon Blanc South Africa', 'man-sauvignon-blanc-south-africa', 80000, 1, 1),
(@rid, @cat17, 'Klein Constantia KC Sauvignon Blanc', 'klein-constantia-kc-sauvignon-blanc', 80000, 2, 1),
(@rid, @cat17, 'Maison Castel', 'maison-castel', 90000, 3, 1),
(@rid, @cat17, 'Riunite Moscato', 'riunite-moscato', 80000, 4, 1),
(@rid, @cat17, 'Protea Pinot Grigio', 'protea-pinot-grigio', 80000, 5, 1),
(@rid, @cat17, 'Bosio Moscato Vino Spumante Dolce', 'bosio-moscato-vino-spumante-dolce', 150000, 6, 1),
(@rid, @cat17, 'Klein Constantia Estate Sauvignon', 'klein-constantia-estate-sauvignon', 80000, 7, 1),
(@rid, @cat17, 'Protea Chenin Blanc', 'protea-chenin-blanc', 70000, 8, 1),
(@rid, @cat17, 'Protea Sauvignon Blanc', 'protea-sauvignon-blanc', 70000, 9, 1),
(@rid, @cat17, 'Protea Chardonnay', 'protea-chardonnay', 70000, 10, 1),
(@rid, @cat17, 'Clarington Unwood Chardonnay South Africa', 'clarington-unwood-chardonnay-south-africa', 90000, 11, 1),
(@rid, @cat17, 'Painted Wolf Viognier Breedkloof Teardrop', 'painted-wolf-viognier-breedkloof-teardrop', 90000, 12, 1),
(@rid, @cat17, 'Paul Cluver Riesling South Africa', 'paul-cluver-riesling-south-africa', 90000, 13, 1),
(@rid, @cat17, 'Vodeling Sweet Carolyn', 'vodeling-sweet-carolyn', 12000, 14, 1);

-- Red Wine (@cat18)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat18, 'Man Cabernet Sauvignon South Africa', 'man-cabernet-sauvignon-south-africa', 70000, 1, 1),
(@rid, @cat18, 'Escudo Rojo', 'escudo-rojo', 160000, 2, 1),
(@rid, @cat18, 'Cooper & Thief', 'cooper-thief', 180000, 3, 1),
(@rid, @cat18, 'Penfolds Father Grand Tawny 10', 'penfolds-father-grand-tawny-10', 160000, 4, 1),
(@rid, @cat18, 'Painted Wolf Syrah Swartland', 'painted-wolf-syrah-swartland', 80000, 5, 1),
(@rid, @cat18, 'Protea Merlot', 'protea-merlot', 70000, 6, 1),
(@rid, @cat18, 'Protea Shiraz', 'protea-shiraz', 70000, 7, 1),
(@rid, @cat18, 'Protea Cabernet Sauvignon', 'protea-cabernet-sauvignon', 80000, 8, 1),
(@rid, @cat18, 'Jordan The Prospector South Africa', 'jordan-the-prospector-south-africa', 150000, 9, 1),
(@rid, @cat18, 'Chateau Pouyanne France', 'chateau-pouyanne-france', 10000, 10, 1),
(@rid, @cat18, 'Gran Castellflorit Spain', 'gran-castellflorit-spain', 70000, 11, 1),
(@rid, @cat18, 'Saumur Champigny Cabernet Franc France', 'saumur-champigny-cabernet-franc-france', 70000, 12, 1);

-- Rosé Wine (@cat19)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat19, 'Gran Castellflorit Spain', 'gran-castellflorit-spain-rose', 70000, 1, 1),
(@rid, @cat19, 'Painted Wolf The Den Dry Rosé', 'painted-wolf-the-den-dry-rose', 130000, 2, 1);

-- Champagne (@cat20)
INSERT INTO menu_items (restaurant_id, category_id, name, slug, price, display_order, is_available) VALUES
(@rid, @cat20, 'Moet et Chandon Brut Imperial', 'moet-et-chandon-brut-imperial', 520000, 1, 1),
(@rid, @cat20, 'Moet et Chandon Nectar Imperial Rosé', 'moet-et-chandon-nectar-imperial-rose', 750000, 2, 1),
(@rid, @cat20, 'Moet Chandon Ice Imperial', 'moet-chandon-ice-imperial', 750000, 3, 1),
(@rid, @cat20, 'Dom Perignon Vintage Brut', 'dom-perignon-vintage-brut', 1500000, 4, 1),
(@rid, @cat20, 'Dom Perignon Vintage Rosé', 'dom-perignon-vintage-rose', 2500000, 5, 1),
(@rid, @cat20, 'Veuve Clicquot Brut', 'veuve-clicquot-brut', 550000, 6, 1),
(@rid, @cat20, 'Veuve Clicquot Rich', 'veuve-clicquot-rich', 750000, 7, 1);
