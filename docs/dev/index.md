# Roles

## Fixtures temporaires du crash-test d'intégration

Ce module déclare les scopes `back`, `bo`, `app` et `front` pour le crash-test global `symsvel create-project --components=all --dev`.

- Symfony : `GET /api/roles/ping` doit retourner `{"module":"roles","status":"ok"}` sans données métier ni authentification propre au controller.
- SvelteKit : chaque interface fournit `src/routes/roles/+page.svelte`, affichant `Page roles`. Le synchroniseur copie la source sous `src/lib/modules/roles/routes/` et crée le relais `src/routes/roles/+page.svelte`.
- Le code métier et ses contrôles d'accès existants restent inchangés.

Ces pages et endpoints sont des sondes d'intégration, pas des interfaces métier définitives. **Limitation provisoire :** les trois scopes Svelte sont déclarés obligatoires par le manifeste ; cette fixture vise uniquement une installation avec tous les composants, pas les installations partielles.

Validation requise : tests automatisés, CI verte, puis crash-test XT (endpoints HTTP et affichage réel des pages BO/App/Front). La présence des fichiers seule ne suffit pas.
