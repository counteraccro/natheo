import { describe, expect, it } from 'vitest';
import { computePosition } from '@/vue/Composables/Admin/Ui/useFloating';

const viewport = { width: 1280, height: 800 };
const menu = { width: 220, height: 160 };

describe('computePosition', () => {
  it('place le panneau sous le bouton, aligné à droite', () => {
    const position = computePosition({ top: 100, left: 600, width: 32, height: 32 }, menu, viewport, 'bottom-end');
    expect(position).toMatchObject({ top: 140, left: 412, side: 'bottom' });
    expect(position.arrowX).toBe(204);
  });

  it("l'ouvre vers le haut quand la place manque en bas (grille d'une ligne en bas de page)", () => {
    const position = computePosition({ top: 700, left: 600, width: 32, height: 32 }, menu, viewport, 'bottom-end');
    expect(position.side).toBe('top');
    expect(position.top).toBe(700 - 8 - 160);
  });

  it("le garde sous le bouton s'il ne tient nulle part", () => {
    const position = computePosition({ top: 100, left: 600, width: 32, height: 32 }, { width: 220, height: 900 }, viewport, 'bottom-end');
    expect(position.side).toBe('bottom');
  });

  it("le recale dans l'écran et garde la flèche sur le bouton", () => {
    const position = computePosition({ top: 100, left: 4, width: 32, height: 32 }, menu, viewport, 'bottom-end');
    expect(position.left).toBe(8);
    expect(position.arrowX).toBe(14);
    const right = computePosition({ top: 100, left: 1260, width: 16, height: 16 }, menu, viewport, 'bottom-start');
    expect(right.left).toBe(1280 - 220 - 8);
    expect(right.arrowX).toBe(220 - 14);
  });

  it('centre une infobulle au-dessus', () => {
    const position = computePosition({ top: 300, left: 500, width: 40, height: 40 }, { width: 100, height: 30 }, viewport, 'top-center');
    expect(position).toMatchObject({ side: 'top', top: 262, left: 470, arrowX: 50 });
  });
});
