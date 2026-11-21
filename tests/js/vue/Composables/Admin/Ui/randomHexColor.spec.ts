import { describe, expect, it } from 'vitest';
import { randomHexColor } from '@/vue/Composables/Admin/Ui/randomHexColor';

describe('randomHexColor', () => {
  it('rend une couleur #rrggbb, zéros de tête compris', () => {
    for (let i = 0; i < 200; i++) {
      expect(randomHexColor()).toMatch(/^#[0-9a-f]{6}$/);
    }
  });
});
