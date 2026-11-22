import { describe, expect, it, vi } from 'vitest';
import { notifyResourceChange, onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';

describe('resourceChanges', () => {
  it('prévient les écoutes de la même ressource jusqu’à leur arrêt', () => {
    const tags = vi.fn();
    const pages = vi.fn();
    const stop = onResourceChange('/admin/tags', tags);
    const stopPages = onResourceChange('/admin/pages', pages);

    notifyResourceChange('/admin/tags');
    expect(tags).toHaveBeenCalledOnce();
    expect(pages).not.toHaveBeenCalled();

    stop();
    notifyResourceChange('/admin/tags');
    expect(tags).toHaveBeenCalledOnce();
    stopPages();
  });
});
