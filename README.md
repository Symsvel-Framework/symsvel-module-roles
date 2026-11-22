# SymSvel Roles

> [!IMPORTANT]
> **Vous souhaitez utiliser SymSvel ? Rendez-vous sur [SymSvel CLI](https://github.com/Symsvel-Framework/symsvel-cli).** Ce dépôt est un **composant du framework**, pas un point d'installation autonome. Création de projets, installation des modules et mises à jour passent par la CLI. Les éventuelles commandes Composer, npm ou Symfony décrites ci-dessous concernent le travail des mainteneurs, sauf indication explicite contraire.

> [!WARNING]
> **SymSvel est en cours de développement.** Les fonctionnalités et procédures peuvent évoluer ou présenter des anomalies. **Utilisation à vos risques et périls.**

Module natif de gestion des **rôles** du Back Office et de leur relation avec les permissions. Il dépend d'ACLs et possède la table `symsvel_roles`.

## CI (10/11/2026)

`.github/workflows/qa.yml` exécute Composer et les tests du module sur le runner Docker ARM64 d'organisation (`self-hosted`, `linux`, `ARM64`, `symsvel`), avec PHP 8.4 préinstallé. La CI GitHub-hosted originale est conservée et désactivée dans `.github/workflows/qa.yml.stub`. Pour revenir aux runners GitHub, restaurer ce `.stub` sous le nom `qa.yml`. Les jobs locaux nécessitent que le Mac et Docker soient disponibles ; runner réservé aux contributions de confiance. Validation finale soumise à une CI verte.

## Migrations natives (chantier DBAL)

Les sources immuables utilisent désormais des noms `Migration/VersionYYYYMMDDHHMMSS.php`. Le compilateur du Tooling conserve leur version Doctrine et convertit les opérations `Schema` en SQL selon la plateforme DBAL active. La création de la table et le peuplement initial des quatre rôles sont deux migrations distinctes, dans cet ordre. Cette branche est à valider avec le Tooling associé : tests automatisés et crash-tests PostgreSQL/MySQL avant merge. Ne pas utiliser avec l'ancien compilateur.

## Compatibilité MySQL (11/11/2026)

La colonne booléenne des rôles natifs est nommée `is_system` dans la migration de création et dans le seed initial. L'ancien identifiant `system` provoquait une erreur SQL 1064 sous MySQL 8.4 car il est réservé. Les versions et l'ordre des deux migrations restent inchangés. Les installations déjà migrées avec l'ancien schéma doivent être traitées séparément avant toute réexécution ; ne pas effacer manuellement un état de récupération de migration.

Le mapping Doctrine ORM de `back/src/Entity/Role.php` conserve la propriété PHP `$system`, le constructeur et la méthode `isSystem()`, tout en ciblant explicitement la colonne SQL `is_system` avec `#[ORM\Column(name: 'is_system', ...)]`. Ainsi, les appels PHP existants ne changent pas.

## Distribution stable v1.0.0 (20/11/2026)

Le manifeste Composer du tag stable déclare exclusivement des contraintes `^1.0` pour ses dépendances SymSvel : `symsvel-framework/module-acls`. Les références `dev-dev` restent réservées à la branche `dev` et à l'installation de maintenance avec `--dev`. Le tag `v1.0.0` n'est à publier **qu'après** le tag stable de chaque dépendance, son indexation dans Satis et la réussite des contrôles CI ; aucune release n'est produite par ce commit.

---

## Licence MIT et contributions

Ce dépôt est distribué sous [licence MIT](LICENSE). Le code peut être utilisé, modifié et redistribué, y compris dans des projets commerciaux ou propriétaires, dans les conditions de cette licence (notamment la conservation de la notice de copyright et de licence).

**Contribuer :** créer un fork, puis proposer une pull request vers `dev` lorsque cette branche existe, sinon vers la branche par défaut. Expliquer la modification et fournir les tests et la documentation concernés. Les mainteneurs conservent la maîtrise des revues, des validations (CI et crash-tests), de l'intégration et des releases officielles. Une PR ne garantit pas son acceptation.

**Versions officielles :** seules les versions diffusées par l'organisation [Symsvel-Framework](https://github.com/Symsvel-Framework) sont officielles. Les forks sont libres sous MIT, mais ne doivent pas être présentés comme officiellement approuvés par les mainteneurs. Cette distinction n'ajoute aucune restriction aux droits accordés par la licence MIT sur le code.

## Participer et soutenir le projet

SymSvel est un projet en cours de développement. Pour contribuer, proposer une amélioration ou échanger avec le mainteneur : **[xavier@xavier-tezza.fr](mailto:xavier@xavier-tezza.fr)**.

SymSvel vous est utile et vous souhaitez me remercier ? **Offrez-moi une bière ! 🍺** (lien PayPal à compléter avant l'ouverture publique).
