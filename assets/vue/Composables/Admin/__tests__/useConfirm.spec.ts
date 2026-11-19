import { describe, expect, it } from 'vitest';
import { useConfirm } from '@/vue/Composables/Admin/useConfirm';

describe('useConfirm', () => {
  it('résout la promesse avec la réponse de l’utilisateur', async () => {
    const { confirm, answer, current } = useConfirm();
    const pending = confirm({ title: 'Supprimer ?', tone: 'danger' });
    expect(current.value?.title).toBe('Supprimer ?');
    answer(true);
    expect(await pending).toBe(true);
    expect(current.value).toBeNull();
  });

  it('refuse la demande encore ouverte quand une autre arrive', async () => {
    const { confirm, answer } = useConfirm();
    const first = confirm({ title: 'Première' });
    const second = confirm({ title: 'Seconde' });
    expect(await first).toBe(false);
    answer(true);
    expect(await second).toBe(true);
  });
});
