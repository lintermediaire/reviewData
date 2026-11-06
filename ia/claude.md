# Review

## Contexte

Petite plaforme créée par Claude et exécutant des reqêtes sql qui afficherait des statistiques sur les données d'un prestashop et en vérifier les anomalies possibles.

## Prompt

Je voudrais 5 fichiers pour l'affichage d'un mini dashboard et suivant les directives en en lien avec les 5 piliers du web.
Je suis sur des serveur de test ou en local, donc il faut un minimum de sécurité mais l'affichage des données est autorisée puisque ce sont des données fictives mais qui seront partagées entre les déveoppeurs.

### Contenu

- via un fichier index.php pour la structure html et les données dynamiques.
Les balises head inderdisant l'indexation par exemple et le chargement des les assets (style.css/main.js par exemple)
Puis les balises de structure s'il en faut : header, main aside, footer ...
Pas forcéement de sémantique très poussé (wgaa) ou pour le seo/geo ; ce sahboard n'est pas référencé !

### Style

- via un fichier css. voir le fichier style.md

### Sécurité

- Via un fichier .htaccess protégeant le dossier racine de ce projet // inderdisant l'indexation sur les moteurs de recherche par exemple
HTTPS obligatoire si le certificat est configuré, En-têtes de sécurité appropriés. Protection des fichiers sensibles.
Ne pas exposer de données sensibles ni connecter de services externes sans configuration explicite.

### dev

- utiliser un fichier .env pour la connexion à la bdd distante
Voilà les requêtes à afficher dans une chouette interface.

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

categories

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

clients & commandes

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

modules
-- Hooks associés à un module qui n'existe plus
SELECT hm.id_module, hm.id_hook
FROM ps_hook_module hm
LEFT JOIN ps_module m ON m.id_module = hm.id_module
WHERE m.id_module IS NULL;

- 3 produits sont actifs sans images de couvertures : 2341, 2794, 4218
- Produits : La quantité de produit par produit est à 0 ; 1365 produits ont un stock négatif
- Produits : il y a 3 produits qui ont un prix à 0€ : 3908, 4733, 4790
- COmmandes : il y a 133 commandes sans clients
- Plus d'une centaine de produits (103) pont la même url avec une langue différente (et dans la même boutique)
