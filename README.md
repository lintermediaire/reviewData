# Revue de données — module PrestaShop

Module back-office qui lance une série de contrôles SQL sur la base de la boutique et liste les anomalies trouvées : références orphelines, doublons, stocks négatifs, produits incomplets, etc.

Il sert à repérer rapidement les incohérences d'une base PrestaShop (après une migration, un import ou une suppression manuelle en base) sans écrire de requêtes à la main.

| | |
|---|---|
| **Nom technique** | `reviewdata` |
| **Version** | 1.0.0 |
| **Compatibilité** | PrestaShop 1.7.6 → 9.x  ⚠️ (réellement testé sur 9.2.0) |
| **Auteur** | Digital Leader |
| **Emplacement BO** | Paramètres avancés › Revue de données |

## Fonctionnement

À chaque ouverture de la page, le module :

1. charge la liste des contrôles définis dans [config/checks.php](config/checks.php) ;
2. remplace `%s` par le préfixe des tables (`_DB_PREFIX_`) et exécute chaque requête en **lecture seule**, sur l'esclave SQL s'il est configuré (`_PS_USE_SQL_SLAVE_`) ;
3. limite chaque résultat à **50 lignes** (au-delà, le compteur affiche `50+`) ;
4. affiche uniquement les contrôles qui renvoient des lignes ou une erreur. Les contrôles sans anomalie sont comptés mais masqués.

Le tableau de bord présente :

- un résumé : nombre de contrôles, anomalies listées, erreurs SQL ;
- la base et le préfixe analysés, avec la date d'exécution ;
- la liste des contrôles à traiter, avec le détail des lignes concernées (repliable) ;
- un champ de filtre pour rechercher un contrôle par son texte.

Une requête en erreur (table absente, colonne renommée selon la version de PrestaShop…) ne bloque pas les autres : elle est affichée en rouge et enregistrée dans les journaux PrestaShop (Paramètres avancés › Logs).

## Contrôles inclus

20 contrôles, répartis par domaine.

### Produits

| Contrôle | Ce qui est détecté |
|---|---|
| Produits sans boutique | Produit sans ligne dans `product_shop` |
| Catégorie par défaut introuvable | `id_category_default` pointant vers une catégorie inexistante |
| `product_shop` orphelins | Ligne `product_shop` dont le produit n'existe pas |
| Produits sans traduction | Aucune ligne dans `product_lang` |
| Catégorie par défaut non rattachée | Catégorie par défaut absente de `category_product` |
| Produits sans ligne de stock | Pas de ligne `stock_available` pour la déclinaison 0 |
| Produits actifs à prix nul | Produit actif avec un prix ≤ 0 |
| Doublons d'URL rewrite | Même `link_rewrite` pour une même langue et une même boutique |
| Produits actifs sans image de couverture | Aucune image marquée `cover` dans la boutique |
| Déclinaisons orphelines | Déclinaison dont le produit parent n'existe pas |
| Stocks négatifs | Quantité `stock_available` inférieure à 0 |

### Catégories

| Contrôle | Ce qui est détecté |
|---|---|
| Catégories au parent manquant | `id_parent` pointant vers une catégorie inexistante |
| Liens catégorie-produit orphelins | Ligne `category_product` sans catégorie ou sans produit |

### Commandes, clients et paniers

| Contrôle | Ce qui est détecté |
|---|---|
| Commandes sans client | Commande dont le client n'existe plus |
| Commandes sans détail | Commande sans aucune ligne `order_detail` |
| Lignes liées à un produit supprimé | Ligne de commande pointant vers un produit inexistant |
| Adresses sans client | Adresse rattachée à un client supprimé (hors client 0) |
| Emails clients en double | Même email plusieurs fois dans une boutique (invités exclus) |
| Paniers liés à un produit supprimé | Ligne `cart_product` pointant vers un produit inexistant |

### Modules

| Contrôle | Ce qui est détecté |
|---|---|
| Hooks liés à un module manquant | Ligne `hook_module` dont le module n'existe plus |

## Installation

1. Copier le dossier `reviewdata/` dans le répertoire `modules/` de la boutique.
   Le dossier `ia/` contient les notes de conception et peut être exclu.
2. Dans le back-office : **Modules › Gestionnaire de modules**, rechercher « Revue de données » puis **Installer**.
3. L'onglet **Paramètres avancés › Revue de données** est créé. Le bouton **Configurer** du module y mène aussi.

La désinstallation supprime l'onglet. Le module ne crée aucune table et ne modifie aucune donnée.

## Ajouter un contrôle

Ajouter une entrée au tableau retourné par [config/checks.php](config/checks.php) :

```php
[
    'id'          => 'product-no-name',          // identifiant unique, utilisé dans les logs
    'category'    => 'Produits',                 // regroupement affiché
    'title'       => 'Produits sans nom',
    'description' => 'Traductions dont le nom est vide.',
    'columns'     => [                           // colonne SQL => libellé affiché
        'id_product' => 'ID produit',
        'id_lang'    => 'ID langue',
    ],
    'sql' => 'SELECT id_product, id_lang FROM %sproduct_lang WHERE name = \'\'',
],
```

SQL étant la ligne qui nous intéress le plus dans ce cas-ci. A voir dans une autre version si on ne ferait pas d'autres catégories. Le nom est utile pour le système de recherche.

Règles à respecter :

- écrire `%s` devant chaque nom de table, sans préfixe en dur (`%sproduct`, pas `ps_product`) ;
- ne pas mettre de `LIMIT` : le module l'ajoute lui-même ;
- n'écrire que des `SELECT` : la requête est exécutée telle quelle ;
- chaque clé de `columns` doit correspondre à une colonne (ou un alias) renvoyée par la requête.

## Structure

```
reviewdata/
├── reviewdata.php                     Classe du module : installation, onglet BO, redirection « Configurer »
├── config/
│   └── checks.php                     Liste des contrôles SQL
├── controllers/admin/
│   └── AdminReviewDataController.php  Exécution des contrôles et préparation des données
├── views/
│   ├── templates/admin/dashboard.tpl  Tableau de bord (Smarty)
│   ├── css/admin.css                  Styles du tableau de bord
│   └── js/admin.js                    Filtre de recherche des contrôles
└── ia/                                Notes de conception (prompt, requêtes d'origine, charte)
```

Les fichiers `index.php` présents dans chaque dossier empêchent le listage des répertoires, selon la convention PrestaShop.

## Sécurité

- Accès réservé au profil **SuperAdmin** :
  - à l'installation, les droits sur l'onglet sont retirés à tous les autres profils, qui ne le voient donc pas dans le menu ;
  - si on leur redonne la permission dans *Équipe › Permissions*, le contrôleur refuse quand même l'accès (message « permission refusée ») et aucune requête n'est exécutée.
- Accès direct aux fichiers PHP bloqué (`_PS_VERSION_` vérifié).
- Toutes les valeurs affichées sont échappées dans le template.
- Les résultats peuvent contenir des données personnelles (emails clients notamment). C'est pour cela que l'accès est limité au SuperAdmin.
