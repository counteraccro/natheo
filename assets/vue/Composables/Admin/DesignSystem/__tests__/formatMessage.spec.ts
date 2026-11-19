import { describe, expect, it } from 'vitest';
import { formatMessage } from '@/vue/Composables/Admin/DesignSystem/formatMessage';

describe('formatMessage', () => {
  it('remplace les paramètres', () => {
    expect(formatMessage('{from} à {to} sur {total}', { from: 1, to: 20, total: 48 })).toBe('1 à 20 sur 48');
  });

  it('choisit le pluriel', () => {
    const message = '{count, plural, =0 {Aucun résultat} one {# résultat} other {# résultats}} dans toute la base';
    expect(formatMessage(message, { count: 0 })).toBe('Aucun résultat dans toute la base');
    expect(formatMessage(message, { count: 1 })).toBe('1 résultat dans toute la base');
    expect(formatMessage(message, { count: 12 })).toBe('12 résultats dans toute la base');
    expect(formatMessage('{count, plural, one {# item} other {# items}}', { count: 1 }, 'en')).toBe('1 item');
  });
});
