import { describe, expect, it } from 'vitest';
import { roleLabel } from '@/vue/Composables/Admin/roleLabel';

const t = (key: string): string => key;

describe('roleLabel', () => {
  it('nomme le rôle le plus élevé', () => {
    expect(roleLabel(['ROLE_USER', 'ROLE_ADMIN'], t)).toBe('admin_app.role.admin');
    expect(roleLabel(['ROLE_CONTRIBUTEUR', 'ROLE_USER'], t)).toBe('admin_app.role.contributor');
    expect(roleLabel(['ROLE_SUPER_ADMIN'], t)).toBe('admin_app.role.super_admin');
  });

  it('garde un rôle inconnu brut', () => {
    expect(roleLabel(['ROLE_BUNDLE'], t)).toBe('ROLE_BUNDLE');
    expect(roleLabel([], t)).toBe('');
  });
});
