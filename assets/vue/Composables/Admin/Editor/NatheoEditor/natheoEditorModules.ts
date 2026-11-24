/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modules de Natheo-Editor : ceux du cœur et de la surcharge, triés, filtrés par préréglage
 */
import type { AdminEditorPreset } from '@/ts/Admin/Editor/AdminEditor.type';
import type { NatheoEditorModule } from '@/ts/Admin/Editor/NatheoEditor/NatheoEditor.type';

/** Modules du préréglage basic */
export const BASIC_PRESET = ['history', 'bold', 'italic', 'list', 'link'];

/** Position d'un module qui n'en donne pas */
const DEFAULT_POSITION = 1000;

/**
 * Modules du cœur et de la surcharge, un module de la surcharge remplaçant celui du cœur de même id
 * @param core
 * @param overwrite
 */
export function mergeEditorModules(
  core: NatheoEditorModule[],
  overwrite: NatheoEditorModule[] = []
): NatheoEditorModule[] {
  const ids = new Set(overwrite.map((module) => module.id));
  return [...core.filter((module) => !ids.has(module.id)), ...overwrite].sort(
    (a, b) => (a.position ?? DEFAULT_POSITION) - (b.position ?? DEFAULT_POSITION) || a.id.localeCompare(b.id)
  );
}

/**
 * Modules d'un préréglage : full prend tout, une liste prend ses ids ; les modules always sont toujours gardés
 * @param modules
 * @param preset
 * @param isAvailable dit si le module du back-office requis est visible
 */
export function modulesForPreset(
  modules: NatheoEditorModule[],
  preset: AdminEditorPreset,
  isAvailable: (module: string) => boolean = () => true
): NatheoEditorModule[] {
  const ids = preset === 'full' ? null : preset === 'basic' ? BASIC_PRESET : preset;
  return modules.filter(
    (module) =>
      (module.always === true || ids === null || ids.includes(module.id)) &&
      (module.requires === undefined || isAvailable(module.requires))
  );
}

let registered: NatheoEditorModule[] | null = null;

/**
 * Modules déclarés dans Editors/NatheoEditor/Modules/ du cœur et de la surcharge
 */
export function natheoEditorModules(): NatheoEditorModule[] {
  registered ??= mergeEditorModules(
    Object.values(
      import.meta.glob<NatheoEditorModule>('../../../../Components/Admin/Editors/NatheoEditor/Modules/*/module.ts', {
        eager: true,
        import: 'default',
      })
    ),
    Object.values(
      import.meta.glob<NatheoEditorModule>(
        '../../../../../overwrite/vue/Components/Admin/Editors/NatheoEditor/Modules/*/module.ts',
        { eager: true, import: 'default' }
      )
    )
  );
  return registered;
}
