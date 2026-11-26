# Roles

## Scopes et routes natives (26/11/2026)

Back et BO uniquement. Les interfaces d’administration sont exclusivement dans le BO.

Les anciennes pages de sondage `Page roles` dans App et Front ont été retirées. Tooling retire automatiquement leurs relais et sources générés à la prochaine synchronisation, si leurs empreintes sont intactes. Les endpoints PHP de ping et les contrôles métier restent inchangés.

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


### Test d'intégration CRUD/ACL (16/11/2026)

`RoleAdministrationHttpTest` utilise SQLite en mémoire et un vrai
EntityManager : création d'un rôle avec ACL, mise à jour et remplacement
des droits, refus de suppression d'un rôle affecté, protection des rôles
natifs et rollback de création si une permission inconnue est fournie.
Le scénario HTTP complet dans XT est encore nécessaire.


### Dépendance déclarative envers ACLs (16/11/2026)

Le manifeste de Roles déclare `acls: '*'` en complément de la dépendance
Composer existante : les opérations de gestion des rôles peuvent lire et
modifier les permissions et ne doivent pas prétendre fonctionner sans le
contrat ACLs. L'ordre du Tooling respecte cette dépendance sans réécrire
les migrations initiales.


### Limites de longueur des données Roles (16/11/2026)

Les valeurs `code` et `label` sont validées en amont de Doctrine et dans
l'entité conformément au schéma existant (100 et 150 octets). Une entrée
trop longue est rejetée en HTTP 400, et non renvoyée à une erreur de
troncature SQL pouvant varier selon les SGBD. Aucun changement de schéma.

## Fallback français des libellés d'interface (17/11/2026)

Les libellés intégrés de l'interface sont sélectionnés à partir de la langue d'interface (code ISO du navigateur ou du compte). Lorsque la locale n'a pas de dictionnaire intégré, les libellés français sont utilisés ; l'anglais reste disponible explicitement pour `en`. Le fallback ne modifie ni la langue ISO du site ni celle du compte et n'impose pas l'existence de traductions avant l'installation. Le repli doit être réalisé à la clé lors de l'introduction des dictionnaires dynamiques ; l'éditeur BO des traductions et le chargement fichier/BDD ne sont pas fournis par cette seule correction.

Vérifications : tests de sélection FR/EN/autres locales et tests de build/CI. La validation finale nécessite les crash-tests locaux sur le parcours Setup, BO et App.


## Traductions BO par module (18/11/2026)

`bo/src/lib/translations/<lang>.ts` livre les libellés du module Roles. Tooling les installe sous `bo/src/lib/translations/roles/<lang>.ts`, puis les enregistre dans Languages avec la convention `bo.roles.<clé>`. Le module conserve ses clés ; le BO conserve les personnalisations publiées. CI et crash-tests XT restent nécessaires.

## Branding et sécurité

Les écrans du module lisent le nom public via `getProjectBrand()` du BO et affichent `$brandName`. Le layout effectue la requête ; le module ne crée ni état ni requête indépendante. L'intégration est vérifiée par la QA ; le CT doit vérifier les titres avec un nom de projet personnalisé. La QA PHP exécute également `composer audit --locked` après installation, sans exclusion d'avis.
