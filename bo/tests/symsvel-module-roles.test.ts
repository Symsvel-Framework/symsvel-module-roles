import { describe, expect, it } from 'vitest';
import { moduleId } from '../src/lib/modules/roles';

describe('roles module', () => {
  it('exports its identifier', () => {
    expect(moduleId).toBe('roles');
  });
});
