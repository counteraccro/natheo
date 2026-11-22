<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Palette de commandes de Natheo-Atelier : écrans du menu et raccourcis « Nouveau » filtrés au clavier, recherche dans
 * tout le site quand rien ne correspond (combobox et listbox dans une boîte de dialogue modale)
 */
import { computed, nextTick, ref, useId, watch } from 'vue';
import { UiIcon } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { SidebarItem } from '@/ts/Admin/Layout/AdminSidebar.type';
import type { HeaderShortcut } from '@/ts/Admin/Layout/AdminHeader.type';

interface Command {
  id: string;
  label: string;
  /** Section du menu, affichée à droite */
  where?: string;
  group: 'screens' | 'create' | 'search';
  run(): void;
}

const props = defineProps<{ items: SidebarItem[]; shortcuts: HeaderShortcut[] }>();
const open = defineModel<boolean>('open', { default: false });
const emit = defineEmits<{ openHref: [href: string]; search: [term: string] }>();

const { t } = useAdminContext();
const dialog = ref<HTMLDialogElement | null>(null);
const input = ref<HTMLInputElement | null>(null);
const query = ref('');
const activeIndex = ref(0);
const listId = `atelier-palette-${useId()}`;

/**
 * Texte comparable : minuscules, sans accents
 * @param text
 */
const normalize = (text: string): string =>
  text
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase();

/**
 * Feuilles du menu avec le libellé de leur section
 * @param items
 * @param section
 */
const leaves = (items: SidebarItem[], section?: string): { item: SidebarItem; section?: string }[] =>
  items.flatMap((item) =>
    item.children?.length
      ? leaves(item.children, section ?? item.label)
      : item.href
        ? [{ item, section: section === item.label ? undefined : section }]
        : []
  );

const commands = computed<Command[]>(() => {
  const term = normalize(query.value.trim());
  const matches = (label: string, where?: string): boolean =>
    term === '' || normalize(`${label} ${where ?? ''}`).includes(term);
  const screens = leaves(props.items)
    .filter(({ item, section }) => matches(item.label, section))
    .map(({ item, section }): Command => ({
      id: `screen-${item.id}`,
      label: item.label,
      where: section,
      group: 'screens',
      run: () => emit('openHref', item.href as string),
    }));
  const create = props.shortcuts
    .filter((shortcut) => matches(shortcut.label))
    .map((shortcut): Command => ({
      id: `create-${shortcut.id}`,
      label: shortcut.label,
      group: 'create',
      run: () => emit('openHref', shortcut.href),
    }));
  const found = [...screens, ...create];
  if (found.length > 0 || term === '') return found;
  const raw = query.value.trim();
  return [
    {
      id: 'search',
      label: t('admin_app.atelier.search_site', { term: raw }),
      group: 'search',
      run: () => emit('search', raw),
    },
  ];
});

const groups = computed(() =>
  (['screens', 'create', 'search'] as const)
    .map((group) => ({
      group,
      label: {
        screens: t('admin_app.atelier.group_screens'),
        create: t('admin_app.atelier.group_create'),
        search: t('admin_app.atelier.group_search'),
      }[group],
      commands: commands.value.filter((command) => command.group === group),
    }))
    .filter((entry) => entry.commands.length > 0)
);

const optionId = (command: Command): string => `${listId}-${command.id}`;
const activeCommand = computed(() => commands.value[activeIndex.value]);

watch(query, () => (activeIndex.value = 0));
watch(open, async (value) => {
  if (value) {
    query.value = '';
    dialog.value?.showModal?.();
    await nextTick();
    input.value?.focus();
  } else {
    dialog.value?.close?.();
  }
});

const close = (): void => {
  open.value = false;
};

const run = (command: Command | undefined): void => {
  if (command === undefined) return;
  close();
  command.run();
};

const onKeydown = (event: KeyboardEvent): void => {
  const count = commands.value.length;
  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault();
    if (count === 0) return;
    activeIndex.value = (activeIndex.value + (event.key === 'ArrowDown' ? 1 : -1) + count) % count;
    void nextTick(() => document.getElementById(optionId(activeCommand.value!))?.scrollIntoView({ block: 'nearest' }));
  } else if (event.key === 'Enter') {
    event.preventDefault();
    run(activeCommand.value);
  }
};
</script>

<template>
  <dialog
    ref="dialog"
    :aria-label="t('admin_app.atelier.palette_label')"
    class="ui-dialog fixed top-[12vh] m-0 mx-auto w-[min(620px,calc(100vw-32px))] overflow-hidden rounded-[14px] border-0 bg-bg p-0 text-fg shadow-dialog"
    @close="close"
    @cancel.prevent="close"
    @click.self="close"
  >
    <div class="flex h-[54px] items-center gap-2.5 border-b border-line px-4 text-fg-subtle">
      <UiIcon name="search" class="size-[18px]" />
      <input
        ref="input"
        v-model="query"
        type="text"
        role="combobox"
        aria-autocomplete="list"
        :aria-expanded="commands.length > 0"
        :aria-controls="listId"
        :aria-activedescendant="activeCommand ? optionId(activeCommand) : undefined"
        :aria-label="t('admin_app.atelier.palette_input')"
        :placeholder="t('admin_app.atelier.command')"
        class="h-full min-w-0 flex-1 border-0 bg-transparent text-base text-fg outline-none placeholder:text-fg-subtle"
        @keydown="onKeydown"
      />
      <kbd
        class="rounded-[5px] border border-b-2 border-line-strong bg-bg px-1.5 font-sans text-xs font-semibold text-fg-muted"
      >
        {{ t('admin_app.atelier.key_escape') }}
      </kbd>
    </div>
    <div
      :id="listId"
      role="listbox"
      :aria-label="t('admin_app.atelier.palette_label')"
      class="max-h-[380px] overflow-auto p-1.5"
    >
      <div v-for="entry in groups" :key="entry.group" role="group" :aria-labelledby="`${listId}-${entry.group}`">
        <div :id="`${listId}-${entry.group}`" class="px-2.5 pt-2.5 pb-1 text-xs font-semibold text-fg-subtle">
          {{ entry.label }}
        </div>
        <div
          v-for="command in entry.commands"
          :id="optionId(command)"
          :key="command.id"
          role="option"
          :aria-selected="activeCommand?.id === command.id"
          class="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg px-2.5 aria-selected:bg-primary-soft aria-selected:text-on-primary-soft"
          @mousemove="activeIndex = commands.indexOf(command)"
          @click="run(command)"
        >
          <UiIcon
            :name="command.group === 'create' ? 'plus' : command.group === 'search' ? 'search' : 'chevron-right'"
            class="size-4 shrink-0"
          />
          <span class="truncate">{{ command.label }}</span>
          <span v-if="command.where" class="ml-auto shrink-0 text-[12.5px] opacity-80">{{ command.where }}</span>
        </div>
      </div>
    </div>
    <div
      class="flex gap-4 border-t border-line px-4 py-2.5 text-[12.5px] text-fg-subtle max-md:hidden"
      aria-hidden="true"
    >
      <span>{{ t('admin_app.atelier.hint_choose') }}</span>
      <span>{{ t('admin_app.atelier.hint_open') }}</span>
      <span>{{ t('admin_app.atelier.hint_search') }}</span>
    </div>
  </dialog>
</template>
