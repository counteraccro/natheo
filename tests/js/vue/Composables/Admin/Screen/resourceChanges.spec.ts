import { describe, expect, it, vi } from 'vitest';
import { changeConcerns, notifyResourceChange, onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';

describe('resourceChanges', () => {
  it('prévient les écoutes de la même ressource jusqu’à leur arrêt', () => {
    const tags = vi.fn();
    const pages = vi.fn();
    const stop = onResourceChange('/admin/tags', tags);
    const stopPages = onResourceChange('/admin/pages', pages);

    notifyResourceChange('/admin/tags', { ids: [3], deleted: true });
    expect(tags).toHaveBeenCalledWith({ ids: [3], deleted: true });
    expect(pages).not.toHaveBeenCalled();

    stop();
    notifyResourceChange('/admin/tags');
    expect(tags).toHaveBeenCalledOnce();
    stopPages();
  });

  it('dit si l’écriture touche un élément', () => {
    expect(changeConcerns({}, 4)).toBe(true);
    expect(changeConcerns({ ids: ['4'] }, 4)).toBe(true);
    expect(changeConcerns({ ids: [5] }, 4)).toBe(false);
    expect(changeConcerns({}, null)).toBe(false);
  });
});
