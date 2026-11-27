import { describe, expect, it } from 'vitest';
import { pickAdminEditor } from '@/vue/Composables/Admin/Editor/loadAdminEditor';
import type { AdminEditor } from '@/ts/Admin/Editor/AdminEditor.type';

const editor = (id: string) => async (): Promise<AdminEditor> => ({ id, component: {} });

const core = {
  '../../../Components/Admin/Editors/NatheoEditor/editor.ts': editor('natheo_editor'),
};

describe('loadAdminEditor', () => {
  it('prend Natheo-Editor sans éditeur dans la surcharge', async () => {
    expect((await pickAdminEditor(core)()).id).toBe('natheo_editor');
  });

  it('prend l’éditeur de la surcharge', async () => {
    const overwrite = { '../../../../overwrite/vue/Components/Admin/Editors/Maison/editor.ts': editor('maison') };
    expect((await pickAdminEditor(core, overwrite)()).id).toBe('maison');
  });

  it('refuse deux éditeurs dans la surcharge', () => {
    const overwrite = {
      '../overwrite/Editors/Un/editor.ts': editor('un'),
      '../overwrite/Editors/Deux/editor.ts': editor('deux'),
    };
    expect(() => pickAdminEditor(core, overwrite)).toThrow(/Un seul éditeur/);
  });

  it('échoue sans Natheo-Editor ni surcharge', () => {
    expect(() => pickAdminEditor({ '../Editors/Autre/editor.ts': editor('autre') })).toThrow();
  });
});
