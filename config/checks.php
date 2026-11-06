<?php
declare(strict_types=1);

/*
 * Contrôles SQL du module (source : ia/requetesSql.md, identique à requests.php).
 * Chaque « %s » est remplacé par _DB_PREFIX_ (préfixe des tables de la boutique).
 * Le fichier est inclus via require() par AdminReviewDataController.
 */

// Accès direct interdit : fichier chargé uniquement par le module.
if (!defined('_PS_VERSION_')) {
    http_response_code(404);
    exit;
}

return [
    // ---------------------------------------------------------------- Produits
    [
        'id' => 'product-shop',
        'category' => 'Produits',
        'title' => 'Produits sans boutique',
        'description' => 'Produits sans ligne dans product_shop.',
        'columns' => ['id_product' => 'ID produit'],
        'sql' => 'SELECT p.id_product FROM %sproduct p LEFT JOIN %sproduct_shop ps ON ps.id_product = p.id_product WHERE ps.id_product IS NULL',
    ],
    [
        'id' => 'product-category',
        'category' => 'Produits',
        'title' => 'Catégorie par défaut introuvable',
        'description' => 'Produits dont la catégorie par défaut n’existe pas.',
        'columns' => ['id_product' => 'ID produit', 'id_category_default' => 'ID catégorie'],
        'sql' => 'SELECT p.id_product, p.id_category_default FROM %sproduct p LEFT JOIN %scategory c ON c.id_category = p.id_category_default WHERE c.id_category IS NULL',
    ],
    [
        'id' => 'product-shop-orphan',
        'category' => 'Produits',
        'title' => 'product_shop orphelins',
        'description' => 'Lignes product_shop dont le produit n’existe pas.',
        'columns' => ['id_product' => 'ID produit', 'id_shop' => 'ID boutique'],
        'sql' => 'SELECT ps.id_product, ps.id_shop FROM %sproduct_shop ps LEFT JOIN %sproduct p ON p.id_product = ps.id_product WHERE p.id_product IS NULL',
    ],
    [
        'id' => 'product-lang',
        'category' => 'Produits',
        'title' => 'Produits sans traduction',
        'description' => 'Produits sans aucune ligne dans product_lang.',
        'columns' => ['id_product' => 'ID produit'],
        'sql' => 'SELECT p.id_product FROM %sproduct p LEFT JOIN %sproduct_lang pl ON pl.id_product = p.id_product WHERE pl.id_product IS NULL',
    ],
    [
        'id' => 'product-default-category-link',
        'category' => 'Produits',
        'title' => 'Catégorie par défaut non rattachée',
        'description' => 'Catégorie par défaut absente de category_product pour le produit.',
        'columns' => ['id_product' => 'ID produit', 'id_category_default' => 'ID catégorie'],
        'sql' => 'SELECT p.id_product, p.id_category_default FROM %sproduct p LEFT JOIN %scategory_product cp ON cp.id_product = p.id_product AND cp.id_category = p.id_category_default WHERE cp.id_product IS NULL',
    ],
    [
        'id' => 'product-stock',
        'category' => 'Produits',
        'title' => 'Produits sans ligne de stock',
        'description' => 'Produits sans ligne stock_available (déclinaison 0).',
        'columns' => ['id_product' => 'ID produit'],
        'sql' => 'SELECT p.id_product FROM %sproduct p LEFT JOIN %sstock_available sa ON sa.id_product = p.id_product AND sa.id_product_attribute = 0 WHERE sa.id_stock_available IS NULL',
    ],
    [
        'id' => 'product-zero-price',
        'category' => 'Produits',
        'title' => 'Produits actifs à prix nul',
        'description' => 'Produits actifs dont le prix est inférieur ou égal à 0.',
        'columns' => ['id_product' => 'ID produit', 'id_shop' => 'ID boutique', 'price' => 'Prix'],
        'sql' => 'SELECT id_product, id_shop, price FROM %sproduct_shop WHERE active = 1 AND price <= 0',
    ],
    [
        'id' => 'product-duplicate-rewrite',
        'category' => 'Produits',
        'title' => 'Doublons d’URL rewrite',
        'description' => 'Même link_rewrite pour une même langue et une même boutique.',
        'columns' => ['id_lang' => 'ID langue', 'id_shop' => 'ID boutique', 'link_rewrite' => 'URL', 'nb' => 'Occurrences'],
        'sql' => 'SELECT id_lang, id_shop, link_rewrite, COUNT(*) AS nb FROM %sproduct_lang GROUP BY id_lang, id_shop, link_rewrite HAVING COUNT(*) > 1',
    ],
    [
        'id' => 'product-no-cover',
        'category' => 'Produits',
        'title' => 'Produits actifs sans image de couverture',
        'description' => 'Produits actifs sans image marquée comme couverture dans la boutique.',
        'columns' => ['id_product' => 'ID produit', 'id_shop' => 'ID boutique'],
        'sql' => 'SELECT ps.id_product, ps.id_shop FROM %sproduct_shop ps LEFT JOIN %simage i ON i.id_product = ps.id_product LEFT JOIN %simage_shop ims ON ims.id_image = i.id_image AND ims.id_shop = ps.id_shop AND ims.cover = 1 WHERE ps.active = 1 GROUP BY ps.id_product, ps.id_shop HAVING COUNT(ims.id_image) = 0',
    ],
    [
        'id' => 'product-attribute-orphan',
        'category' => 'Produits',
        'title' => 'Déclinaisons orphelines',
        'description' => 'Déclinaisons dont le produit parent n’existe pas.',
        'columns' => ['id_product_attribute' => 'ID déclinaison', 'id_product' => 'ID produit'],
        'sql' => 'SELECT pa.id_product_attribute, pa.id_product FROM %sproduct_attribute pa LEFT JOIN %sproduct p ON p.id_product = pa.id_product WHERE p.id_product IS NULL',
    ],
    [
        'id' => 'product-negative-stock',
        'category' => 'Produits',
        'title' => 'Stocks négatifs',
        'description' => 'Lignes stock_available dont la quantité est négative.',
        'columns' => ['id_product' => 'ID produit', 'id_product_attribute' => 'ID déclinaison', 'quantity' => 'Quantité'],
        'sql' => 'SELECT id_product, id_product_attribute, quantity FROM %sstock_available WHERE quantity < 0',
    ],

    // -------------------------------------------------------------- Catégories
    [
        'id' => 'category-parent',
        'category' => 'Catégories',
        'title' => 'Catégories au parent manquant',
        'description' => 'Catégories dont le parent référencé n’existe plus.',
        'columns' => ['id_category' => 'ID catégorie', 'id_parent' => 'ID parent'],
        'sql' => 'SELECT c.id_category, c.id_parent FROM %scategory c LEFT JOIN %scategory parent ON parent.id_category = c.id_parent WHERE c.id_parent <> 0 AND parent.id_category IS NULL',
    ],
    [
        'id' => 'category-product',
        'category' => 'Catégories',
        'title' => 'Liens catégorie-produit orphelins',
        'description' => 'Associations dont la catégorie ou le produit est introuvable.',
        'columns' => ['id_category' => 'ID catégorie', 'id_product' => 'ID produit'],
        'sql' => 'SELECT cp.id_category, cp.id_product FROM %scategory_product cp LEFT JOIN %scategory c ON c.id_category = cp.id_category LEFT JOIN %sproduct p ON p.id_product = cp.id_product WHERE c.id_category IS NULL OR p.id_product IS NULL',
    ],

    // ---------------------------------------------------- Clients et commandes
    [
        'id' => 'order-customer',
        'category' => 'Commandes',
        'title' => 'Commandes sans client',
        'description' => 'Commandes dont le client référencé est introuvable.',
        'columns' => ['id_order' => 'ID commande', 'id_customer' => 'ID client'],
        'sql' => 'SELECT o.id_order, o.id_customer FROM %sorders o LEFT JOIN %scustomer c ON c.id_customer = o.id_customer WHERE c.id_customer IS NULL',
    ],
    [
        'id' => 'order-detail',
        'category' => 'Commandes',
        'title' => 'Commandes sans détail',
        'description' => 'Commandes pour lesquelles aucune ligne de détail n’existe.',
        'columns' => ['id_order' => 'ID commande'],
        'sql' => 'SELECT o.id_order FROM %sorders o LEFT JOIN %sorder_detail od ON od.id_order = o.id_order WHERE od.id_order IS NULL',
    ],
    [
        'id' => 'detail-product',
        'category' => 'Commandes',
        'title' => 'Lignes liées à un produit supprimé',
        'description' => 'Lignes de commande dont le produit référencé est introuvable.',
        'columns' => ['id_order' => 'ID commande', 'product_id' => 'ID produit'],
        'sql' => 'SELECT od.id_order, od.product_id FROM %sorder_detail od LEFT JOIN %sproduct p ON p.id_product = od.product_id WHERE p.id_product IS NULL',
    ],
    [
        'id' => 'address-customer',
        'category' => 'Clients',
        'title' => 'Adresses sans client',
        'description' => 'Adresses dont le client associé n’existe plus (hors client 0).',
        'columns' => ['id_address' => 'ID adresse', 'id_customer' => 'ID client'],
        'sql' => 'SELECT a.id_address, a.id_customer FROM %saddress a LEFT JOIN %scustomer c ON c.id_customer = a.id_customer WHERE a.id_customer <> 0 AND c.id_customer IS NULL',
    ],
    [
        'id' => 'duplicate-email',
        'category' => 'Clients',
        'title' => 'Emails clients en double',
        'description' => 'Emails présents plusieurs fois dans une même boutique, hors invités.',
        'columns' => ['email' => 'Email', 'id_shop' => 'ID boutique', 'nb' => 'Occurrences'],
        'sql' => 'SELECT email, id_shop, COUNT(*) AS nb FROM %scustomer WHERE is_guest = 0 GROUP BY email, id_shop HAVING COUNT(*) > 1',
    ],
    [
        'id' => 'cart-product',
        'category' => 'Paniers',
        'title' => 'Paniers liés à un produit supprimé',
        'description' => 'Associations de panier dont le produit référencé est introuvable.',
        'columns' => ['id_cart' => 'ID panier', 'id_product' => 'ID produit'],
        'sql' => 'SELECT cp.id_cart, cp.id_product FROM %scart_product cp LEFT JOIN %sproduct p ON p.id_product = cp.id_product WHERE p.id_product IS NULL',
    ],

    // ----------------------------------------------------------------- Modules
    [
        'id' => 'module-hook',
        'category' => 'Modules',
        'title' => 'Hooks liés à un module manquant',
        'description' => 'Associations de hooks dont le module référencé n’existe plus.',
        'columns' => ['id_module' => 'ID module', 'id_hook' => 'ID hook'],
        'sql' => 'SELECT hm.id_module, hm.id_hook FROM %shook_module hm LEFT JOIN %smodule m ON m.id_module = hm.id_module WHERE m.id_module IS NULL',
    ],
];
