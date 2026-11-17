export type BoRole = {
  id: number;
  code: string;
  label: string;
  native: boolean;
  permissions: string[];
};
export type BoRolesResponse = { data: BoRole[] };

export type BoPermission = {
  code: string;
  resource: string;
  action: string;
  label: string;
  technical: boolean;
};
export type BoPermissionCatalog = { data: BoPermission[]; can_manage: boolean };

export function validNewRoleCode(code: string): boolean {
  return /^ROLE_[A-Z][A-Z0-9_]*$/.test(code) &&
    !['ROLE_DEV', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'].includes(code);
}

export function permissionGroups(items: readonly BoPermission[]): Record<string, BoPermission[]> {
  return items.reduce<Record<string, BoPermission[]>>((groups, permission) => {
    (groups[permission.resource] ??= []).push(permission);
    return groups;
  }, {});
}

const copy = {
  fr: {
    title: 'Rôles', heading: 'Gestion des rôles',
    intro: 'Les rôles BO et leurs autorisations déclarées par les modules.',
    create: 'Nouveau rôle', edit: 'Modifier le rôle', delete: 'Supprimer',
    code: 'Code', label: 'Libellé', native: 'Natif', custom: 'Personnalisé',
    kind: 'Type', access: 'Permissions', actions: 'Actions',
    search: 'Rechercher', searchHint: 'Libellé, code…', refresh: 'Actualiser',
    none: 'Aucun rôle.', loading: 'Chargement…',
    permissions: 'Droits associés', technical: 'Technique', 
    protected: 'Rôle natif protégé', noManage: 'Vous ne disposez pas du droit technique de modifier les ACL.',
    save: 'Enregistrer', cancel: 'Annuler', saving: 'Enregistrement…',
    codeRule: 'Commence par ROLE_, avec lettres majuscules, chiffres et _. Le code ne change plus ensuite.',
    invalidCode: 'Code de rôle invalide ou réservé.',
    confirmDelete: 'Supprimer ce rôle ? Les rôles utilisés par un administrateur ne sont pas supprimables.',
    created: 'Rôle créé.', updated: 'Rôle modifié.', deleted: 'Rôle supprimé.',
    conflict: 'Rôle déjà présent, protégé ou attribué à un administrateur.',
    invalid: 'Saisie invalide.', forbidden: 'Action non autorisée.',
    session: 'Session expirée. Reconnectez-vous.', failure: 'Opération impossible.'
  },
  en: {
    title: 'Roles', heading: 'Manage roles',
    intro: 'BO roles and their module-declared permissions.',
    create: 'New role', edit: 'Edit role', delete: 'Delete',
    code: 'Code', label: 'Label', native: 'Native', custom: 'Custom',
    kind: 'Type', access: 'Permissions', actions: 'Actions',
    search: 'Search', searchHint: 'Label, code…', refresh: 'Refresh',
    none: 'No roles.', loading: 'Loading…',
    permissions: 'Assigned permissions', technical: 'Technical', 
    protected: 'Protected native role', noManage: 'You cannot manage technical ACL grants.',
    save: 'Save', cancel: 'Cancel', saving: 'Saving…',
    codeRule: 'Start with ROLE_, uppercase letters, numbers and _. Codes cannot change later.',
    invalidCode: 'Invalid or reserved role code.',
    confirmDelete: 'Delete this role? Roles assigned to administrators cannot be deleted.',
    created: 'Role created.', updated: 'Role updated.', deleted: 'Role deleted.',
    conflict: 'Role already exists, is protected or is assigned to an administrator.',
    invalid: 'Invalid input.', forbidden: 'Permission denied.',
    session: 'Session expired. Sign in again.', failure: 'Operation failed.'
  }
};

export type RoleCopy = typeof copy.fr;
export function roleCopy(language: string | null): RoleCopy {
  return language?.toLowerCase().split('-')[0] === 'en' ? copy.en : copy.fr;
}
