<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Catalogue des composants du design system (environnement dev) : chaque composant dans ses états, dans le layout du
 * thème ; couleur et mode depuis le menu Apparence de la barre du haut
 */
import { computed, reactive, ref } from 'vue';
import {
  AdminBreadcrumb,
  AdminPageHeader,
  UiAlert,
  UiBadge,
  UiButton,
  UiCheckbox,
  UiColorInput,
  UiConfirmDialog,
  UiDataTable,
  UiDialog,
  UiDropdown,
  UiDropdownItem,
  UiDropdownSeparator,
  UiEmptyState,
  UiErrorSummary,
  UiField,
  UiForm,
  UiIcon,
  UiInput,
  UiSelect,
  UiSkeleton,
  UiSwitch,
  UiTabs,
  UiTextarea,
  UiTextEditor,
  UiTooltip,
  notify,
  useFormErrors,
  type ApiViolation,
  type DataTableAction,
  type DataTableColumn,
  type DataTableSort,
} from '@ui';

// Formulaire : l'enregistrement simule la réponse 422 de l'API
const tagLabels = reactive<Record<string, string>>({ fr: 'Version', en: 'Release', es: '' });
const descriptions = reactive<Record<string, string>>({ fr: '', en: '', es: '' });
const EDITOR_SAMPLE = [
  '## Bienvenue',
  '',
  'Un texte **gras**, *italique*, ~~barré~~ et du `code`. Lien vers [une page du site](P#12) et vers [Nathéo](https://natheo.fr).',
  '',
  '- Premier point',
  '- Second point',
  '',
  '> Une citation mise en avant.',
  '',
  '```php',
  "echo 'Bonjour';",
  '```',
  '',
  '| Colonne | Valeur |',
  '| --- | --- |',
  '| A | 1 |',
  '',
  '<div class="note">HTML brut</div>',
  '',
  'Bonjour [[user.login]], voici un attribut {.lead}',
].join('\n');
const contents = reactive<Record<string, string>>({ fr: EDITOR_SAMPLE, en: '', es: '' });
const comment = ref('Un commentaire **court** avec une [adresse](https://natheo.fr).');
const color = ref('#6530e0');
const locale = ref<string | number | null>('fr');
const enabled = ref(true);
const accepted = ref(false);
const formErrors = useFormErrors();
const TAB_PREFIXES = { fr: 'translations[fr]', en: 'translations[en]', es: 'translations[es]' };

const saveForm = (): void => {
  const violations: ApiViolation[] = Object.entries(tagLabels)
    .filter(([, value]) => value.trim() === '')
    .map(([code]) => ({ propertyPath: `translations[${code}].label`, message: 'Cette valeur ne doit pas être vide.' }));
  if (tagLabels.fr.trim() === 'Version') {
    violations.push({ propertyPath: 'translations[fr].label', message: 'Un autre tag porte déjà ce libellé.' });
  }
  formErrors.set(violations);
  if (violations.length === 0) notify('success', 'Tag enregistré');
};

const languageTab = ref('fr');
const tabs = [
  { id: 'fr', label: 'Français' },
  { id: 'en', label: 'English' },
  { id: 'es', label: 'Español' },
];

// Modales
const dialogOpen = ref(false);
const confirmOpen = ref(false);
const deleteOpen = ref(false);

// Grille
interface TagRow extends Record<string, unknown> {
  id: number;
  label: string;
  color: string;
  pages: number;
  disabled: boolean;
}

const allTags: TagRow[] = [
  ['Version', '#6530e0', 12],
  ['Annonce', '#be123c', 4],
  ['Tutoriel', '#0f766e', 9],
  ['API', '#0369a1', 7],
  ['Thèmes', '#a21caf', 3],
  ['Multilingue', '#b45309', 0],
  ['Sécurité', '#15803d', 5],
  ['Base de données', '#475569', 0],
].map(([name, hex, pages], index) => ({
  id: index + 1,
  label: name as string,
  color: hex as string,
  pages: pages as number,
  disabled: index === 5,
}));

const columns: DataTableColumn[] = [
  { key: 'label', label: 'Libellé', sortable: true },
  { key: 'color', label: 'Couleur', hideBelow: 'md' },
  { key: 'pages', label: 'Utilisation', sortable: true, align: 'end' },
  { key: 'disabled', label: 'Statut' },
  { key: 'id', label: 'ID', hidden: true, align: 'end' },
];

const sort = ref<DataTableSort | null>({ key: 'label', direction: 'asc' });
const page = ref(1);
const itemsPerPage = ref(5);
const selected = ref<(string | number)[]>([]);
const loading = ref(false);
const empty = ref(false);
const remoteTerm = ref('');

const filtered = computed(() => {
  if (empty.value) return [];
  const term = remoteTerm.value.toLowerCase();
  const rows = term ? allTags.filter((tag) => tag.label.toLowerCase().includes(term)) : [...allTags];
  if (sort.value) {
    const { key, direction } = sort.value;
    rows.sort(
      (a, b) => String(a[key]).localeCompare(String(b[key]), 'fr', { numeric: true }) * (direction === 'asc' ? 1 : -1)
    );
  }
  return rows;
});
const rows = computed(() =>
  filtered.value.slice((page.value - 1) * itemsPerPage.value, page.value * itemsPerPage.value)
);

const actionsFor = (row: TagRow): DataTableAction[] => [
  { id: 'edit', label: 'Modifier', icon: 'edit' },
  { id: 'toggle', label: row.disabled ? 'Activer' : 'Désactiver', icon: row.disabled ? 'check-circle' : 'ban' },
  { id: 'duplicate', label: 'Dupliquer', icon: 'copy' },
  ...(row.pages === 0 ? [{ id: 'delete', label: 'Supprimer', icon: 'trash' as const, danger: true }] : []),
];

const bulkActions: DataTableAction[] = [
  { id: 'disable', label: 'Désactiver', icon: 'ban' },
  { id: 'delete', label: 'Supprimer', icon: 'trash', danger: true },
];

const onSearch = (term: string): void => {
  loading.value = true;
  setTimeout(() => {
    remoteTerm.value = term;
    page.value = 1;
    loading.value = false;
  }, 600);
};

const onAction = ({ id, row }: { id: string; row: TagRow }): void => {
  if (id === 'delete') deleteOpen.value = true;
  else notify('success', `${row.label} : ${id}`);
};

const onBulk = ({ id, keys }: { id: string; keys: (string | number)[] }): void => {
  notify(id === 'delete' ? 'warning' : 'info', `${keys.length} éléments`, `Action groupée « ${id} »`);
};

const SQL = [
  'SELECT',
  '  t.id,',
  '  COALESCE(tt.label, td.label) AS label, -- libellé de la langue, à défaut celui par défaut',
  '  t.color',
  'FROM',
  '  `tag` t',
  "  LEFT JOIN tag_translation tt ON tt.tag_id = t.id AND (tt.locale = 'fr')",
  "  LEFT JOIN tag_translation td ON td.tag_id = t.id AND (td.locale = 'fr')",
  'WHERE',
  "  LOWER(tt.label) LIKE '%l\\'été%' /* antislash de MySQL */",
  '  AND t.disabled = false',
  'ORDER BY',
  '  tt.label ASC',
  'LIMIT',
  '  5 OFFSET 0',
].join('\n');

const PANEL = 'rounded-lg border border-line bg-bg shadow-panel';
const PANEL_HEAD = 'flex items-center gap-3 border-b border-line px-5 py-4';
const PANEL_TITLE = 'm-0 text-md font-[650]';
</script>

<template>
  <AdminBreadcrumb
    :items="[{ label: 'Tableau de bord', href: '#' }, { label: 'Outils' }, { label: 'Design system' }]"
  />
  <AdminPageHeader
    title="Design system"
    description="Composants du nouveau back-office dans chacun de leurs états. La couleur et le mode se changent dans le menu Apparence."
    icon="sliders"
  >
    <template #actions>
      <UiButton icon="code" href="#grille">Grille</UiButton>
      <UiButton variant="primary" icon="plus">Nouveau tag</UiButton>
    </template>
  </AdminPageHeader>

  <section :class="PANEL">
    <header :class="PANEL_HEAD"><h2 :class="PANEL_TITLE">Boutons</h2></header>
    <div class="flex flex-col gap-4 p-5">
      <div class="flex flex-wrap items-center gap-2">
        <UiButton variant="primary" icon="check">Enregistrer</UiButton>
        <UiButton icon="eye">Prévisualiser</UiButton>
        <UiButton variant="ghost">Annuler</UiButton>
        <UiButton variant="danger" icon="trash">Supprimer</UiButton>
        <UiButton variant="danger-soft" icon="ban">Désactiver</UiButton>
        <UiButton variant="primary" loading>Enregistrer</UiButton>
        <UiButton disabled>Désactivé</UiButton>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <UiButton variant="primary" size="sm" icon="plus">Nouveau</UiButton>
        <UiButton size="sm" icon="copy">Copier</UiButton>
        <UiTooltip text="Modifier le tag">
          <UiButton variant="ghost" size="sm" icon="edit" aria-label="Modifier" />
        </UiTooltip>
        <UiTooltip text="Supprimer le tag">
          <UiButton variant="ghost" size="sm" icon="trash" aria-label="Supprimer" />
        </UiTooltip>
        <UiDropdown label="Plus d'actions" icon="dots" icon-only variant="ghost" size="sm">
          <UiDropdownItem icon="edit">Modifier</UiDropdownItem>
          <UiDropdownItem icon="copy" shortcut="Ctrl D">Dupliquer</UiDropdownItem>
          <UiDropdownItem icon="eye" disabled>Prévisualiser</UiDropdownItem>
          <UiDropdownSeparator />
          <UiDropdownItem icon="trash" danger>Supprimer</UiDropdownItem>
        </UiDropdown>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-[minmax(0,1fr)_360px] items-start gap-5 max-xl:grid-cols-[minmax(0,1fr)]">
    <section :class="PANEL">
      <header :class="PANEL_HEAD"><h2 :class="PANEL_TITLE">Formulaire</h2></header>
      <UiForm :errors="formErrors" class="p-5" @submit="saveForm">
        <UiAlert variant="info" title="Ce tag est affiché sur 12 pages">
          La modification met ces pages à jour dès l'enregistrement. Enregistrer avec un libellé vide ou « Version »
          simule une réponse 422 de l'API.
        </UiAlert>
        <UiErrorSummary />
        <UiTabs v-model="languageTab" :tabs="tabs" label="Langues" :error-prefixes="TAB_PREFIXES">
          <template v-for="tab in tabs" :key="tab.id" #[tab.id]>
            <div class="flex flex-col gap-[18px]">
              <UiField
                :name="`translations[${tab.id}].label`"
                :label="`Libellé (${tab.label})`"
                required
                help="Affiché sur le site public"
              >
                <UiInput v-model="tagLabels[tab.id]" :maxlength="40" counter />
              </UiField>
              <UiField :name="`translations[${tab.id}].description`" :label="`Description (${tab.label})`">
                <UiTextarea v-model="descriptions[tab.id]" :maxlength="200" counter />
              </UiField>
              <UiField :name="`translations[${tab.id}].content`" :label="`Contenu (${tab.label})`">
                <UiTextEditor
                  v-model="contents[tab.id]"
                  :lang="tab.id"
                  placeholder="Saisissez le contenu…"
                  :keywords="[
                    { keyword: '[[user.login]]', label: 'Identifiant' },
                    { keyword: '[[site.name]]', label: 'Nom du site' },
                  ]"
                />
              </UiField>
              <UiField label="Commentaire (préréglage basic)">
                <UiTextEditor v-model="comment" preset="basic" :rows="4" />
              </UiField>
            </div>
          </template>
        </UiTabs>
        <UiField name="color" label="Couleur" help="Code hexadécimal #rrggbb">
          <UiColorInput v-model="color" :suggestions="5" />
        </UiField>
        <UiField name="locale" label="Langue par défaut">
          <UiSelect
            v-model="locale"
            :options="[
              { value: 'fr', label: 'Français' },
              { value: 'en', label: 'English' },
              { value: 'es', label: 'Español' },
            ]"
          />
        </UiField>
        <UiSwitch v-model="enabled" label="Actif" description="Un tag désactivé n'est plus proposé dans les pages." />
        <UiCheckbox
          v-model="accepted"
          label="Afficher dans le nuage de tags"
          description="Sur la page d'accueil du thème"
        />
        <div class="flex justify-end gap-2">
          <UiButton icon="close" @click="formErrors.clearAll()">Effacer les erreurs</UiButton>
          <UiButton type="submit" variant="primary" icon="check">Enregistrer</UiButton>
        </div>
      </UiForm>
    </section>

    <section :class="PANEL">
      <header :class="PANEL_HEAD"><h2 :class="PANEL_TITLE">Badges et modales</h2></header>
      <div class="flex flex-col gap-4 p-5">
        <div class="flex flex-wrap gap-2">
          <UiBadge tone="success">Actif</UiBadge>
          <UiBadge>Désactivé</UiBadge>
          <UiBadge tone="warning">En attente</UiBadge>
          <UiBadge tone="danger">Refusé</UiBadge>
          <UiBadge tone="primary">Brouillon</UiBadge>
          <UiBadge tone="primary" :count="12" />
        </div>
        <div class="flex flex-wrap gap-2">
          <UiButton size="sm" icon="info" @click="dialogOpen = true">Modale</UiButton>
          <UiButton size="sm" icon="check" @click="confirmOpen = true">Confirmation</UiButton>
          <UiButton size="sm" variant="danger-soft" icon="trash" @click="deleteOpen = true">Suppression</UiButton>
        </div>
        <div class="flex flex-wrap gap-2">
          <UiButton size="sm" @click="notify('success', 'Tag enregistré')">Succès</UiButton>
          <UiButton size="sm" @click="notify('info', 'Rien à désactiver')">Info</UiButton>
          <UiButton
            size="sm"
            @click="notify('warning', '3 supprimés, 2 conservés', 'Les tags utilisés restent sélectionnés.')"
          >
            Avertissement
          </UiButton>
          <UiButton size="sm" @click="notify('danger', 'Suppression impossible', 'Ce tag est utilisé sur 12 pages.')"
            >Erreur</UiButton
          >
        </div>
      </div>
    </section>
  </div>

  <section :class="PANEL">
    <header :class="PANEL_HEAD"><h2 :class="PANEL_TITLE">Alertes, état vide et chargement</h2></header>
    <div class="grid grid-cols-[repeat(auto-fit,minmax(min(100%,360px),1fr))] gap-5 p-5">
      <div class="flex flex-col gap-2.5">
        <UiAlert variant="success" title="Tag enregistré">Les pages qui l'affichent sont à jour.</UiAlert>
        <UiAlert variant="info" dismissible>Le tag est affiché sur 12 pages.</UiAlert>
        <UiAlert variant="warning" title="Traduction incomplète">
          Le libellé espagnol est vide.
          <template #action><UiButton size="sm">Compléter</UiButton></template>
        </UiAlert>
        <UiAlert variant="danger" title="Enregistrement impossible">Le libellé est obligatoire.</UiAlert>
      </div>
      <div class="flex flex-col gap-5">
        <UiEmptyState title="Aucun tag" text="Les tags regroupent les pages par sujet. Créez le premier.">
          <UiButton variant="primary" icon="plus">Nouveau tag</UiButton>
        </UiEmptyState>
        <UiSkeleton :lines="4" />
        <UiSkeleton variant="row" :columns="4" />
      </div>
    </div>
  </section>

  <div id="grille" class="flex flex-col gap-3">
    <div class="flex flex-wrap gap-4 text-sm text-fg-muted">
      <UiCheckbox v-model="loading" label="Chargement" />
      <UiCheckbox v-model="empty" label="Aucune donnée" />
    </div>
    <UiDataTable
      v-model:sort="sort"
      v-model:page="page"
      v-model:items-per-page="itemsPerPage"
      v-model:selected="selected"
      label="Tags"
      :columns="columns"
      :rows="rows"
      :total-items="filtered.length"
      :loading="loading"
      :actions-for="actionsFor"
      :bulk-actions="bulkActions"
      :sql="SQL"
      selectable
      @search="onSearch"
      @action="onAction"
      @bulk-action="onBulk"
      @select-all="selected = filtered.map((tag) => tag.id)"
      @save-sql="notify('success', 'Requête enregistrée')"
    >
      <template #cell-label="{ row }">
        <span
          class="inline-flex h-[26px] items-center gap-[7px] rounded-full bg-surface pr-2.5 pl-2 text-sm font-semibold whitespace-nowrap text-fg shadow-[inset_0_0_0_1px_var(--line)]"
        >
          <span class="size-2 rounded-full" :style="{ background: row.color as string }" />
          {{ row.label }}
        </span>
      </template>
      <template #cell-color="{ value }">
        <span class="inline-flex items-center gap-2 text-sm text-fg-muted">
          <span class="size-3.5 rounded-[4px]" :style="{ background: value as string }" />{{ value }}
        </span>
      </template>
      <template #cell-pages="{ value }">{{ value }} pages</template>
      <template #cell-disabled="{ value }">
        <UiBadge :tone="value ? 'neutral' : 'success'">{{ value ? 'Désactivé' : 'Actif' }}</UiBadge>
      </template>
      <template #empty>
        <UiEmptyState title="Aucun tag" text="Créez le premier tag pour regrouper vos pages.">
          <UiButton variant="primary" icon="plus">Nouveau tag</UiButton>
        </UiEmptyState>
      </template>
    </UiDataTable>
  </div>

  <UiDialog v-model:open="dialogOpen" title="Aperçu du tag">
    <template #icon><UiIcon name="tag" class="size-5 text-fg-muted" /></template>
    <p class="m-0 text-fg-muted">Échap ou le bouton Fermer referment cette modale.</p>
    <template #footer>
      <UiButton @click="dialogOpen = false">Fermer</UiButton>
    </template>
  </UiDialog>
  <UiConfirmDialog
    v-model:open="confirmOpen"
    title="Publier les modifications"
    message="La version en ligne sera remplacée par le brouillon."
    @confirm="((confirmOpen = false), notify('success', 'Modifications publiées'))"
  />
  <UiConfirmDialog
    v-model:open="deleteOpen"
    tone="danger"
    title="Supprimer le tag"
    message="Le tag sera supprimé définitivement."
    confirm-label="Supprimer le tag"
    @confirm="((deleteOpen = false), notify('success', 'Tag supprimé'))"
  />
</template>
