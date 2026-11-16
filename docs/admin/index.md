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
