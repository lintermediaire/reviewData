# Requetes

sql
-- Produits sans ligne dans product_shop
SELECT p.id_product
FROM ps_product p
LEFT JOIN ps_product_shop ps ON ps.id_product = p.id_product
WHERE ps.id_product IS NULL;

-- Produits dont la catégorie par défaut n'existe pas
SELECT p.id_product, p.id_category_default
FROM ps_product p
LEFT JOIN ps_category c ON c.id_category = p.id_category_default
WHERE c.id_category IS NULL;

Produits

sql
-- product_shop orphelins (sans produit)
SELECT ps.id_product, ps.id_shop
FROM ps_product_shop ps
LEFT JOIN ps_product p ON p.id_product = ps.id_product
WHERE p.id_product IS NULL;

-- Produits sans traduction
SELECT p.id_product
FROM ps_product p
LEFT JOIN ps_product_lang pl ON pl.id_product = p.id_product
WHERE pl.id_product IS NULL;

-- Catégorie par défaut non rattachée au produit dans category_product
SELECT p.id_product, p.id_category_default
FROM ps_product p
LEFT JOIN ps_category_product cp
  ON cp.id_product = p.id_product AND cp.id_category = p.id_category_default
WHERE cp.id_product IS NULL;

-- Produits sans ligne de stock
SELECT p.id_product
FROM ps_product p
LEFT JOIN ps_stock_available sa
  ON sa.id_product = p.id_product AND sa.id_product_attribute = 0
WHERE sa.id_stock_available IS NULL;

-- Produits actifs à prix nul
SELECT id_product, id_shop, price
FROM ps_product_shop
WHERE active = 1 AND price <= 0;

-- Doublons d'URL rewrite (même langue, même boutique)
SELECT id_lang, id_shop, link_rewrite, COUNT(*) AS nb
FROM ps_product_lang
GROUP BY id_lang, id_shop, link_rewrite
HAVING COUNT(*) > 1;

-- Produits actifs sans image de couverture
SELECT ps.id_product, ps.id_shop
FROM ps_product_shop ps
LEFT JOIN ps_image i ON i.id_product = ps.id_product
LEFT JOIN ps_image_shop ims
  ON ims.id_image = i.id_image AND ims.id_shop = ps.id_shop AND ims.cover = 1
WHERE ps.active = 1
GROUP BY ps.id_product, ps.id_shop
HAVING COUNT(ims.id_image) = 0;

-- Déclinaisons orphelines
SELECT pa.id_product_attribute, pa.id_product
FROM ps_product_attribute pa
LEFT JOIN ps_product p ON p.id_product = pa.id_product
WHERE p.id_product IS NULL;

-- Stocks négatifs
SELECT id_product, id_product_attribute, quantity
FROM ps_stock_available
WHERE quantity < 0;

Catégories

sql
-- Catégories dont le parent n'existe pas
SELECT c.id_category, c.id_parent
FROM ps_category c
LEFT JOIN ps_category parent ON parent.id_category = c.id_parent
WHERE c.id_parent <> 0 AND parent.id_category IS NULL;

-- Liens category_product orphelins
SELECT cp.id_category, cp.id_product
FROM ps_category_product cp
LEFT JOIN ps_category c ON c.id_category = cp.id_category
LEFT JOIN ps_product p ON p.id_product = cp.id_product
WHERE c.id_category IS NULL OR p.id_product IS NULL;

Clients et commandes

sql
-- Commandes sans client
SELECT o.id_order, o.id_customer
FROM ps_orders o
LEFT JOIN ps_customer c ON c.id_customer = o.id_customer
WHERE c.id_customer IS NULL;

-- Commandes sans lignes de détail
SELECT o.id_order
FROM ps_orders o
LEFT JOIN ps_order_detail od ON od.id_order = o.id_order
WHERE od.id_order IS NULL;

-- Lignes de commande pointant vers un produit supprimé
SELECT od.id_order, od.product_id
FROM ps_order_detail od
LEFT JOIN ps_product p ON p.id_product = od.product_id
WHERE p.id_product IS NULL;

-- Adresses dont le client n'existe plus
SELECT a.id_address, a.id_customer
FROM ps_address a
LEFT JOIN ps_customer c ON c.id_customer = a.id_customer
WHERE a.id_customer <> 0 AND c.id_customer IS NULL;

-- Emails clients en double (hors invités)
SELECT email, id_shop, COUNT(*) AS nb
FROM ps_customer
WHERE is_guest = 0
GROUP BY email, id_shop
HAVING COUNT(*) > 1;

-- Paniers avec produits inexistants
SELECT cp.id_cart, cp.id_product
FROM ps_cart_product cp
LEFT JOIN ps_product p ON p.id_product = cp.id_product
WHERE p.id_product IS NULL;

Modules

sql
-- Hooks associés à un module qui n'existe plus
SELECT hm.id_module, hm.id_hook
FROM ps_hook_module hm
LEFT JOIN ps_module m ON m.id_module = hm.id_module
WHERE m.id_module IS NULL;
