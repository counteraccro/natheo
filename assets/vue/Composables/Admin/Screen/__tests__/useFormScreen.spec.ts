import { afterEach, describe, expect, it, vi } from 'vitest';
import { createAdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { createFormScreen } from '@/vue/Composables/Admin/Screen/useFormScreen';
import { defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import type { ApiClient } from '@/vue/Composables/Global/Api/ApiClient';
import type { AdminCan } from '@/vue/Composables/Admin/useCan';
import type { TranslationsField } from '@/ts/Admin/Screen/FormScreen.type';

type Tag = {
  id: number;
  color: string;
  disabled: boolean;
  translations: { locale: string; label: string }[];
  permissions?: { edit: boolean; disable: boolean; delete: boolean };
};

const labelField: TranslationsField = {
  type: 'translations',
  name: 'translations',
  field: 'label',
  label: 'tag.form.label',
  copyFromCurrent: true,
};

const descriptor = defineFormScreen<Tag>({
  resource: '/admin/tags',
  title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit' },
  back: 'tag_index',
  editRoute: 'tag_edit',
  leaveKey: 'tag',
  initial: () => ({ color: '#112233', disabled: false, translations: [] }),
  sections: [
    { title: 'tag.form.section_label', fields: [labelField] },
    {
      title: 'tag.form.section_style',
      fields: [
        { type: 'color', name: 'color', label: 'tag.form.color' },
        { type: 'component', name: 'preview', component: {} },
        { type: 'switch', name: 'disabled', label: 'tag.form.disabled' },
      ],
    },
  ],
  update: { operation: 'admin_tag_patch' },
  delete: { operation: 'admin_tag_delete' },
  messages: {
    created: 'tag.notify.created',
    saved: 'tag.notify.saved',
    confirmDelete: 'tag.confirm.delete',
    deleted: 'tag.notify.deleted',
  },
  rowLabel: (item) => item.translations[0]?.label ?? '',
});

const t = (key: string, params: Record<string, unknown> = {}): string =>
  Object.keys(params).length ? `${key} ${JSON.stringify(params)}` : key;

const can: AdminCan = {
  canOperation: () => true,
  can: (action, _operation, item) => item?.permissions?.[action] !== false,
  hasRole: () => false,
};

function screenWith(id: number | null, api: object = {}) {
  const resource = createAdminResource<Tag>(api as unknown as ApiClient, t, '/admin/tags', id, {
    initial: () => descriptor.initial() as Tag,
    leaveKey: 'tag',
  });
  const router = { push: vi.fn(), replace: vi.fn() };
  const confirm = vi.fn().mockResolvedValue(true);
  const screen = createFormScreen(descriptor, {
    t,
    can,
    confirm,
    router,
    resource,
    locales: ['fr', 'en', 'es'],
    currentLocale: 'fr',
  });
  return { screen, resource, router, confirm };
}

afterEach(() => leaveGuard.untrack('tag'));

describe('createFormScreen', () => {
  it('recopie la langue de l’interface dans les autres langues tant que la recopie, cochée à la création, le reste', async () => {
    const { screen, resource } = screenWith(null);
    await resource.load();
    const labels = () => ['fr', 'en', 'es'].map((locale) => screen.translation(labelField, locale));
    expect(screen.title.value).toBe('tag.form.title_new');
    expect(screen.copying.value).toBe(true);
    expect(screen.isCopied(labelField, 'en')).toBe(true);
    expect(screen.isCopied(labelField, 'fr')).toBe(false);

    screen.setTranslation(labelField, 'fr', 'Été');
    expect(labels()).toEqual(['Été', 'Été', 'Été']);

    screen.setCopying(false);
    expect(screen.isCopied(labelField, 'en')).toBe(false);
    screen.setTranslation(labelField, 'en', 'Summer');
    screen.setTranslation(labelField, 'fr', 'Été 2026');
    expect(labels()).toEqual(['Été 2026', 'Summer', 'Été']);

    screen.setCopying(true);
    expect(labels()).toEqual(['Été 2026', 'Été 2026', 'Été 2026']);
    expect(screen.translationPath(labelField, 'es')).toBe('translations[2].label');
  });

  it('suit les modifications non enregistrées', async () => {
    const { screen, resource } = screenWith(null);
    await resource.load();
    expect(screen.dirty.value).toBe(false);
    screen.setTranslation(labelField, 'fr', 'Été');
    expect(screen.dirty.value).toBe(true);
  });

  it('envoie les champs déclarés, langues dans l’ordre du site, puis ouvre l’édition du tag créé', async () => {
    const postResource = vi.fn().mockResolvedValue({
      data: { id: 9, color: '#112233', disabled: false, translations: [{ locale: 'fr', label: 'Été' }] },
      etag: '"v1"',
    });
    const { screen, resource, router } = screenWith(null, { postResource });
    await resource.load();
    screen.setTranslation(labelField, 'fr', 'Été');
    screen.setCopying(false);
    screen.setTranslation(labelField, 'en', 'Summer');
    await screen.save();

    expect(postResource).toHaveBeenCalledWith('/admin/tags', {
      translations: [
        { locale: 'fr', label: 'Été' },
        { locale: 'en', label: 'Summer' },
        { locale: 'es', label: 'Été' },
      ],
      color: '#112233',
      disabled: false,
    });
    expect(router.replace).toHaveBeenCalledWith({ name: 'tag_edit', params: { id: '9' } });
    expect(screen.isNew.value).toBe(false);
  });

  it('ne recopie pas en édition tant que la recopie n’est pas cochée, et suit les droits de l’élément', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: {
        id: 4,
        color: '#000000',
        disabled: false,
        translations: [
          { locale: 'fr', label: 'Été' },
          { locale: 'en', label: 'Summer' },
        ],
        permissions: { edit: true, disable: true, delete: false },
      },
      etag: '"v1"',
    });
    const { screen, resource } = screenWith(4, { getResource });
    await resource.load();
    expect(screen.title.value).toBe('tag.form.title_edit {"id":"4"}');
    expect(screen.copying.value).toBe(false);
    screen.setTranslation(labelField, 'fr', 'Automne');
    expect(screen.translation(labelField, 'en')).toBe('Summer');
    expect(screen.translation(labelField, 'es')).toBe('');
    expect(screen.canEdit.value).toBe(true);
    expect(screen.canDelete.value).toBe(false);
  });

  it('supprime après confirmation puis revient à la liste', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: { id: 4, color: '#000000', disabled: false, translations: [{ locale: 'fr', label: 'Été' }] },
      etag: '"v1"',
    });
    const remove = vi.fn().mockResolvedValue(undefined);
    const { screen, resource, router, confirm } = screenWith(4, { getResource, delete: remove });
    await resource.load();
    expect(screen.canDelete.value).toBe(true);
    await screen.remove();
    expect(confirm.mock.lastCall?.[0]).toMatchObject({ title: 'tag.confirm.delete {"label":"Été"}', tone: 'danger' });
    expect(remove).toHaveBeenCalledWith('/admin/tags/4', { ifMatch: '"v1"' });
    expect(router.push).toHaveBeenCalledWith({ name: 'tag_index' });
  });
});
