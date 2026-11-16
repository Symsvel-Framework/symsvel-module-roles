# Roles

## Fixtures temporaires du crash-test d'intégration

Ce module déclare les scopes `back`, `bo`, `app` et `front` pour le crash-test global `symsvel create-project --components=all --dev`.

- Symfony : `GET /api/roles/ping` doit retourner `{"module":"roles","status":"ok"}` sans données métier ni authentification propre au controller.
- SvelteKit : chaque interface fournit `src/routes/roles/+page.svelte`, affichant `Page roles`. Le synchroniseur copie la source sous `src/lib/modules/roles/routes/` et crée le relais `src/routes/roles/+page.svelte`.
- Le code métier et ses contrôles d'accès existants restent inchangés.

Ces pages et endpoints sont des sondes d'intégration, pas des interfaces métier définitives. **Limitation provisoire :** les trois scopes Svelte sont déclarés obligatoires par le manifeste ; cette fixture vise uniquement une installation avec tous les composants, pas les installations partielles.

Validation requise : tests automatisés, CI verte, puis crash-test XT (endpoints HTTP et affichage réel des pages BO/App/Front). La présence des fichiers seule ne suffit pas.

## Expérimentation migrations Schema (10/11/2026)

La source `Migration/001_roles.php` définit désormais la structure de table avec l'API `Doctrine\\DBAL\\Schema\\Schema` (colonnes, clé primaire, index unique), sans SQL PostgreSQL pour le DDL. Il s'agit d'une **expérience contrôlée**, pas encore du contrat de production.

**Limitation connue avant CT :** le compilateur actuel de `symsvel-tooling` extrait les corps `up()/down()` et les insère dans une migration Doctrine. Il ne génère pas de SQL à partir des mutations de `Schema`. En particulier, les changements de structure risquent de ne pas être exécutés sur la base. Aucun résultat PostgreSQL/MySQL n'est encore validé.

Les quatre rôles système (`ROLE_DEV`, `ROLE_SUPER_ADMIN`, `ROLE_ADMIN`, `ROLE_USER`) restent insérés au moyen de `addSql()`, à titre provisoire pour ne pas perdre les données initiales. Cette instruction peut échouer si la table n'a pas été créée.

À vérifier sur un **XT réinitialisé**, sans aucune réparation locale : compilation, exécution, tables réellement présentes, index, rôles système, état du Setup et diagnostic/rollback en cas d'échec. Toute correction devra se faire dans le framework et être documentée avec tests et CI.

### Visibilité des tests ignorés

La CI affiche les raisons des tests PHPUnit ignorés avec `--display-skipped` (directement ou via le script Composer QA). Un test ignoré ne devient pas bloquant pour autant : les motifs sont examinés pour distinguer prérequis absents et couverture manquante.


## Administration BO des rôles (16/11/2026)

La précédente route `GET /apibo/roles` était protégée par un attribut
Symfony `IsGranted('ROLE_ADMIN')` sans contrat BO explicite. Elle est
remplacée par le **BoAccessGate** partagé, qui valide d'abord le jeton BO,
puis le droit `roles:list`. Les endpoints ajoutés sont :

- `GET /apibo/roles` : rôles réels de `symsvel_roles`, avec affectations
  explicites `permissions` (codes `resource.action`).
- `POST /apibo/roles` : créer un rôle `ROLE_*` non natif, avec libellé et
  liste optionnelle de permissions, dans une transaction DBAL/ORM.
- `PATCH /apibo/roles/{id}` : modifier le libellé et/ou les permissions.
- `DELETE /apibo/roles/{id}` : supprimer seulement un rôle non natif,
  **non utilisé par un administrateur**, en nettoyant ses droits et les
  permissions dynamiques `roles.assign:ROLE_*` associées.

Les mutations suivent les ACL `roles:create/update/delete`. Les affectations
de droits exigent aussi `acls:manage` **technique** : le super-admin
fonctionnel n'en bénéficie pas implicitement. Le formulaire Roles conserve
l'UX XCore de sélection des permissions, sans échapper à ces contrôles.

Les codes système `ROLE_DEV`, `ROLE_SUPER_ADMIN`, `ROLE_ADMIN` et
`ROLE_USER` restent protégés par `RolePolicy` et `RoleManager`.
Le provider `RolePermissionProvider` déclare les actions CRUD et expose
l'affectation des rôles **effectivement persistés** à travers les droits
`roles.assign:ROLE_*`. Seuls les rôles BO (hors DEV et USER) sont
assignables. Le contrôle de l'attribution finale reste côté Admins.

Les migrations initiales et le contrat d'entité Role ne changent pas.
Le crash-test XT et la CI assemblée BO restent nécessaires.


### QA BO assemblée (16/11/2026)

La CI possède un job indépendant `bo-integration`, qui importe les
routes Svelte et les fichiers du module dans le BO hôte `dev` selon
la structure de synchronisation Tooling. Les validations TypeScript,
Svelte, Prettier, ESLint, build statique et Vitest s'exécutent sur
l'assemblage, pas uniquement sur un fichier isolé. Le crash-test XT
reste nécessaire pour les API, les ACL et la navigation réelle.


### Interdiction d'auto-attribution (16/11/2026)

L'édition des permissions depuis Roles applique les mêmes contrôles techniques
que l'écran ACLs. Un acteur non développeur ne peut pas réécrire les droits
de son propre rôle, même s'il possède explicitement `acls.manage`.
La vérification se fait après résolution du rôle en base et avant toute
mutation. Le backend garde l'autorité sur ce contrôle.
