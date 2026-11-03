import { describe, expect, it } from 'vitest';
import { moduleId } from '../src/lib/modules/symsvel-module-roles';

describe('symsvel-module-roles module', () => {
  it('exports its identifier', () => {
    expect(moduleId).toBe('symsvel-module-roles');
  });
});
