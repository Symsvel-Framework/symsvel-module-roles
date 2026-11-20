import { describe, expect, it } from 'vitest';
import fr from '../src/lib/translations/fr';
import en from '../src/lib/translations/en';

describe('Roles dictionaries', () => {
  it('contains the same translated keys in FR and EN', () => {
    expect(Object.keys(fr).sort()).toEqual(Object.keys(en).sort());
    expect(fr.title).toBe('Rôles');
  });
});
