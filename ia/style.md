# Style

## Forme

### Couleurs

Définir ces couleurs comme variables globales :

| Variable | Valeur |
|---|---|---
| `colorPrimary` | **`#FC5400`** |
| `colorSecondary` | **`#244848`** |
| `colorDark` | **`#242430`** |
| `colorLight` | **`#6C9CB4`** |
| `colorMiddle` | **`#D8D8D8`** |

Respecter les contrastes WCAG 2.1 AA : 4,5:1 pour le texte courant et 3:1 pour le grand texte.

Si tu veux plus de couleurs :

--color-1 : 002424
--color-2 : 244848
--color-3 : 243C48
--color-4 : 6C9CB4
--color-5 : 244860
--color-6 : 606C78
--color-7 : 6084A8
--color-8 : 242430
--color-9 : B43C18
--color-10 : #FC5400
--color-11 : #54483C
--color-12 : #D8D8D8

### Typographie

Utiliser deux polices maximum :

- Titres : **Poppins**.
- Textes : **Inter**.

Les polices peuvent être chargées depuis Google Fonts ou localement.

Échelle typographique desktop :

- H1 : 48–64 px.
- H2 : 36–44 px.
- H3 : 26–32 px.
- H4 : 20–24 px.
- H5 : 18–20 px.
- Texte courant : 16 px minimum.

Utiliser `clamp()` pour adapter les tailles aux différentes résolutions.

Respecter la hiérarchie sémantique HTML et utiliser un seul H1.

### Espacements

Utiliser un système cohérent :

- XS : 4 px.
- SM : 8 px.
- MD : 16 px.
- LG : 24 px.
- XL : 32 px.
- 2XL : 48 px.
- 3XL : 64 px.

### Grille responsive

- Desktop, au-dessus de 1024 px : 12 colonnes.
- Tablette, de 768 à 1024 px : 8 colonnes.
- Mobile, de 481 à 767 px : 4 colonnes.
- Petit mobile, jusqu'à 480 px : une colonne si nécessaire.

Résolutions de contrôle :

`1920, 1280, 1024, 768 et 320 px`.

Utiliser un conteneur central, des marges fluides et des gouttières cohérentes. Éviter tout débordement horizontal.

### Icônes

- Utiliser une bibliothèque open source comme Lucide.
- Privilégier des icônes fines, sobres et linéaires.
- Limiter leur utilisation aux éléments qui le nécessitent.
- Ne pas ajouter systématiquement des icônes dans les boutons.

### Images

- Privilégier le format WebP.
- Largeur maximale : 2000 px.
- Optimiser le poids des fichiers.
- Utiliser le chargement différé pour les images hors écran.
- Ne pas appliquer le chargement différé à l'image principale visible immédiatement.
- Prévoir les dimensions des images pour éviter les décalages de mise en page.
- Ajouter des textes alternatifs pertinents.
- Respecter le contexte décrit dans `content.md`.

## Organisation SCSS indicative

- `_variables.scss`
- `_reset.scss`
- `_typography.scss`
- `_layout.scss`
- `_header.scss`
- `_navigation.scss`
- `_sections.scss`
- `_forms.scss`
- `_footer.scss`
- `_utilities.scss`

Adapter cette organisation à l'environnement réel.

## Ordre des propriétés CSS/SCSS

1. Layout et positionnement.
2. Dimensions.
3. Marges et espacements.
4. Couleurs et arrière-plans.
5. Typographie.
6. Bordures et effets.
7. Animations et transitions.
8. Visibilité et interaction.

Privilégier un code lisible, maintenable et réutilisable.

## Responsive design

Adopter une approche mobile-first.

Tester les résolutions suivantes :

`320, 360, 480, 530, 640, 768, 840, 960, 1024, 1280, 1440 et 1920 px`.

Vérifier :

- Navigation mobile.
- Lisibilité des titres.
- Marges et espacements.
- Images et ratios.
- Compositions image/texte.
- Boutons et formulaires.
- Newsletter et footer.
- Absence de débordement horizontal.

Ne pas simplement réduire la version desktop : adapter les compositions et les interactions.
