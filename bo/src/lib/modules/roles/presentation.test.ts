import { describe, it, expect } from 'vitest';
import { permissionGroups, roleCopy, validNewRoleCode } from './presentation';
import { boNavigation } from './navigation';

describe('BO Roles', () => {
  it('declares a protected BO navigation probe', () => {
    expect(boNavigation.resource).toBe('roles');
    expect(boNavigation.probe).toBe('/apibo/roles');
  });
  it('localizes role management', () => {
    expect(roleCopy('fr').create).toBe('Nouveau rôle');
    expect(roleCopy('en').delete).toBe('Delete');
    expect(roleCopy('es-MX').create).toBe('Nouveau rôle');
  });
  it('rejects native, malformed or reserved role codes', () => {
    expect(validNewRoleCode('ROLE_EDITOR')).toBe(true);
    expect(validNewRoleCode('ROLE_ADMIN')).toBe(false);
    expect(validNewRoleCode('ROLE_DEV')).toBe(false);
    expect(validNewRoleCode('ROLE_bad')).toBe(false);
  });
  it('groups declared permissions for native checkbox editor', () => {
    const groups = permissionGroups([
      { code: 'admins.list', resource: 'admins', action: 'list', label: 'List', technical: false },
      { code: 'roles.list', resource: 'roles', action: 'list', label: 'List', technical: false }
    ]);
    expect(Object.keys(groups)).toEqual(['admins', 'roles']);
  });
});
