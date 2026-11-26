import { afterEach, describe, expect, it, vi } from 'vitest';
import { createAdminResource } from '@/vue/Composables/Admin/useAdminResource';
import { createFormScreen } from '@/vue/Composables/Admin/Screen/useFormScreen';
import { defineFormScreen } from '@/vue/Composables/Admin/Screen/defineScreen';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import { onResourceChange } from '@/vue/Composables/Admin/Screen/resourceChanges';
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
  read: { operation: 'admin_tag_get' },
  create: { operation: 'admin_tag_post' },
  title: { create: 'tag.form.title_new', edit: 'tag.form.title_edit', view: 'tag.form.title_view' },
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

function screenWith(id: number | null, api: object = {}, screenDescriptor = descriptor) {
  const resource = createAdminResource<Tag>(api as unknown as ApiClient, t, '/admin/tags', id, {
    initial: () => descriptor.initial() as Tag,
    leaveKey: 'tag',
  });
  const router = { push: vi.fn(), replace: vi.fn() };
  const confirm = vi.fn().mockResolvedValue(true);
  const screen = createFormScreen(screenDescriptor, {
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
    const changed = vi.fn();
    const stop = onResourceChange('/admin/tags', changed);
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
    expect(changed).toHaveBeenCalledOnce();
    stop();
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

  it('titre en consultation un élément que l’utilisateur ne peut pas modifier', async () => {
    const getResource = vi.fn().mockResolvedValue({
      data: { id: 4, color: '#000000', disabled: false, translations: [], permissions: { edit: false } },
      etag: '"v1"',
    });
    const { screen, resource } = screenWith(4, { getResource });
    await resource.load();
    expect(screen.canEdit.value).toBe(false);
    expect(screen.title.value).toBe('tag.form.title_view {"id":"4"}');
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

  describe('écriture faite ailleurs', () => {
    const loaded = (disabled: boolean, etag: string) => ({
      data: { id: 4, color: '#000000', disabled, translations: [{ locale: 'fr', label: 'Été' }] },
      etag,
    });

    it('recharge l’élément non modifié, avec sa nouvelle version', async () => {
      const getResource = vi
        .fn()
        .mockResolvedValueOnce(loaded(false, '"v1"'))
        .mockResolvedValueOnce(loaded(true, '"v2"'));
      const { screen, resource } = screenWith(4, { getResource });
      await resource.load();
      await screen.externalChange({ ids: [4] });
      expect(resource.etag.value).toBe('"v2"');
      expect(resource.data.value?.disabled).toBe(true);
      expect(resource.conflict.value).toBe(false);
    });

    it('signale le conflit sans écraser les modifications en cours', async () => {
      const getResource = vi.fn().mockResolvedValue(loaded(false, '"v1"'));
      const { screen, resource } = screenWith(4, { getResource });
      await resource.load();
      screen.setTranslation(labelField, 'fr', 'Automne');
      await screen.externalChange({ ids: [4] });
      expect(getResource).toHaveBeenCalledOnce();
      expect(resource.conflict.value).toBe(true);
      expect(screen.translation(labelField, 'fr')).toBe('Automne');
    });

    it('revient à la liste quand l’élément est supprimé, sans demander de confirmer le départ', async () => {
      const getResource = vi.fn().mockResolvedValue(loaded(false, '"v1"'));
      const { screen, resource, router } = screenWith(4, { getResource });
      await resource.load();
      screen.setTranslation(labelField, 'fr', 'Automne');
      await screen.externalChange({ ids: ['4'], deleted: true });
      expect(router.push).toHaveBeenCalledWith({ name: 'tag_index' });
      expect(leaveGuard.isDirty('tag')).toBe(false);
    });

    it('ignore ses propres écritures et celles des autres éléments', async () => {
      const getResource = vi.fn().mockResolvedValue(loaded(false, '"v1"'));
      const { screen, resource, router } = screenWith(4, { getResource });
      await resource.load();
      await screen.externalChange({ ids: [5], deleted: true });
      await screen.externalChange({ ids: [4], source: resource });
      expect(getResource).toHaveBeenCalledOnce();
      expect(router.push).not.toHaveBeenCalled();
    });
  });

  it('n’affiche une section on: edit que sur un élément enregistré, on: create qu’à la création, et n’envoie que leurs champs', async () => {
    const sectioned = defineFormScreen<Tag>({
      ...descriptor,
      sections: [
        { title: 'always', fields: [labelField] },
        { title: 'edit', on: 'edit', fields: [{ type: 'switch', name: 'disabled' }] },
        { title: 'create', on: 'create', fields: [{ type: 'color', name: 'color' }] },
      ],
    });
    const postResource = vi.fn().mockResolvedValue({
      data: { id: 9, color: '#112233', disabled: false, translations: [] },
      etag: '"v1"',
    });
    const created = screenWith(null, { postResource }, sectioned);
    expect(created.screen.sections.value.map((section) => section.title)).toEqual(['always', 'create']);
    await created.resource.load();
    await created.screen.save();
    expect(Object.keys(postResource.mock.calls[0][1])).toEqual(['translations', 'color']);

    const getResource = vi.fn().mockResolvedValue({
      data: { id: 4, color: '#112233', disabled: false, translations: [] },
      etag: '"v1"',
    });
    const edited = screenWith(4, { getResource }, sectioned);
    await edited.resource.load();
    expect(edited.screen.sections.value.map((section) => section.title)).toEqual(['always', 'edit']);
  });

  it('n’envoie pas un champ masqué par visibleWhen', async () => {
    const conditional = defineFormScreen<Tag>({
      ...descriptor,
      sections: [
        {
          title: 'style',
          fields: [
            { type: 'switch', name: 'disabled' },
            { type: 'color', name: 'color', visibleWhen: (data) => data.disabled === true },
          ],
        },
      ],
    });
    const postResource = vi.fn().mockResolvedValue({
      data: { id: 9, color: '#112233', disabled: false, translations: [] },
      etag: '"v1"',
    });
    const { screen, resource } = screenWith(null, { postResource }, conditional);
    await resource.load();
    await screen.save();
    expect(postResource.mock.calls[0][1]).toEqual({ disabled: false });
  });

  it('se passe de création et de suppression sans leurs textes', async () => {
    const readOnly = defineFormScreen<Tag>({
      ...descriptor,
      create: undefined,
      delete: undefined,
      editRoute: undefined,
      messages: { saved: 'tag.notify.saved' },
    });
    const getResource = vi.fn().mockResolvedValue({
      data: { id: 4, color: '#112233', disabled: false, translations: [] },
      etag: '"v1"',
    });
    const { screen, resource } = screenWith(4, { getResource }, readOnly);
    await resource.load();
    expect(screen.canDelete.value).toBe(false);
    expect(screen.canEdit.value).toBe(true);
  });
});
