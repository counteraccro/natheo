import { afterEach, describe, expect, it, vi } from 'vitest';
import { createAdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { ApiError } from '@/vue/Composables/Global/Api/ApiError';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import * as toast from '@/vue/Composables/Global/toast';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';

type Tag = { id?: number; label: string };

function apiWith(methods: Partial<Record<'getResource' | 'postResource' | 'patchResource' | 'delete', unknown>>) {
  return methods as unknown as ApiClient;
}

const t = (key: string): string => key;

afterEach(() => leaveGuard.untrack('tag'));

describe('createAdminResource', () => {
  it('lit la ressource avec son ETag et suit le formulaire', async () => {
    const getResource = vi.fn().mockResolvedValue({ data: { id: 3, label: 'Été' }, etag: '"v1"' });
    const resource = createAdminResource<Tag>(apiWith({ getResource }), t, '/admin/tags', 3, { leaveKey: 'tag' });
    await resource.load();
    expect(getResource).toHaveBeenCalledWith('/admin/tags/3');
    expect(resource.etag.value).toBe('"v1"');
    expect(leaveGuard.isDirty('tag')).toBe(false);
    resource.data.value!.label = 'Automne';
    expect(leaveGuard.isDirty('tag')).toBe(true);
  });

  it('enregistre avec If-Match, garde le nouvel ETag et valide le formulaire', async () => {
    const patchResource = vi.fn().mockResolvedValue({ data: { id: 3, label: 'Automne' }, etag: '"v2"' });
    const resource = createAdminResource<Tag>(
      apiWith({
        getResource: vi.fn().mockResolvedValue({ data: { id: 3, label: 'Été' }, etag: '"v1"' }),
        patchResource,
      }),
      t,
      '/admin/tags',
      3,
      { leaveKey: 'tag' }
    );
    await resource.load();
    resource.data.value!.label = 'Automne';
    expect(await resource.save()).toEqual({ id: 3, label: 'Automne' });
    expect(patchResource).toHaveBeenCalledWith('/admin/tags/3', { id: 3, label: 'Automne' }, { ifMatch: '"v1"' });
    expect(resource.etag.value).toBe('"v2"');
    expect(leaveGuard.isDirty('tag')).toBe(false);
  });

  it('crée la ressource depuis ses données initiales et retient son identifiant', async () => {
    const postResource = vi.fn().mockResolvedValue({ data: { id: 9, label: 'Neuf' }, etag: '"v1"' });
    const resource = createAdminResource<Tag>(apiWith({ postResource }), t, '/admin/tags', null, {
      initial: () => ({ label: '' }),
      leaveKey: 'tag',
    });
    await resource.load();
    expect(resource.data.value).toEqual({ label: '' });
    resource.data.value!.label = 'Neuf';
    await resource.save();
    expect(postResource).toHaveBeenCalledWith('/admin/tags', { label: 'Neuf' });
    expect(resource.id.value).toBe(9);
  });

  it('signale un conflit sur un 412', async () => {
    const notify = vi.spyOn(toast, 'notify');
    const resource = createAdminResource<Tag>(
      apiWith({
        getResource: vi.fn().mockResolvedValue({ data: { id: 3, label: 'Été' }, etag: '"v1"' }),
        patchResource: vi.fn().mockRejectedValue(new ApiError(412, 'Precondition Failed')),
      }),
      t,
      '/admin/tags',
      3,
      { leaveKey: 'tag' }
    );
    await resource.load();
    expect(await resource.save()).toBeNull();
    expect(resource.conflict.value).toBe(true);
    expect(notify).toHaveBeenCalledWith(
      'warning',
      'admin_app.resource.conflict_title',
      'admin_app.resource.conflict_text'
    );
    expect(leaveGuard.isDirty('tag')).toBe(false);
  });

  it('range les violations d’un 422 par champ', async () => {
    const body = { violations: [{ propertyPath: 'label', message: 'Cette valeur ne doit pas être vide.' }] };
    const resource = createAdminResource<Tag>(
      apiWith({ postResource: vi.fn().mockRejectedValue(new ApiError(422, 'Unprocessable', null, body)) }),
      t,
      '/admin/tags',
      null,
      { initial: () => ({ label: '' }), leaveKey: 'tag' }
    );
    await resource.load();
    await resource.save();
    expect(resource.errors.messagesFor('label')).toEqual(['Cette valeur ne doit pas être vide.']);
    expect(resource.conflict.value).toBe(false);
  });

  it('supprime avec If-Match et arrête le suivi', async () => {
    const remove = vi.fn().mockResolvedValue(undefined);
    const resource = createAdminResource<Tag>(
      apiWith({
        getResource: vi.fn().mockResolvedValue({ data: { id: 3, label: 'Été' }, etag: '"v1"' }),
        delete: remove,
      }),
      t,
      '/admin/tags',
      3,
      { leaveKey: 'tag' }
    );
    await resource.load();
    resource.data.value!.label = 'Modifié';
    expect(await resource.remove()).toBe(true);
    expect(remove).toHaveBeenCalledWith('/admin/tags/3', { ifMatch: '"v1"' });
    expect(leaveGuard.isDirty('tag')).toBe(false);
  });
});
