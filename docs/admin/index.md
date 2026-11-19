# SymsvelModuleRoles

Documentation admin.


## Rôles dans le BO (16/11/2026)

Le BO affiche les rôles effectivement enregistrés. Le formulaire modal
permet de créer un rôle personnalisé (`ROLE_*`), de modifier son libellé et,
avec le droit technique requis, de sélectionner les permissions déclarées
par les modules. Les rôles système sont visibles mais leur code/libellé et
leur suppression restent verrouillés. Un rôle utilisé par un administrateur
ne peut pas être supprimé.

La liste reprend `DataTable` (recherche, tri et pagination), les droits
apparaissent dans un fieldset organisé par module. L'édition de droits exige
`acls:manage` technique, y compris si l'utilisateur possède le droit
fonctionnel `roles:update`. Une erreur de droit ne doit pas être masquée.
Les changements de permissions sont transactionnels avec la création/édition
du rôle. CT XT et validation de l'assemblage BO restent nécessaires.

### Normalisation Design System BO (19/11/2026)

Le catalogue des rôles conserve ses modales `Modal`, tableaux `DataTable` et pagination native BO, ses règles ACL et ses appels API asynchrones. Les surfaces, bordures, textes secondaires, champ de code, cases de permission et focus utilisent désormais les jetons sémantiques `--bo-*` communs au BO HP. L'interface fonctionne en mode clair ou sombre sans palette locale parallèle. Aucun changement métier ni navigation forcée, tests/CI et CT XT requis.
