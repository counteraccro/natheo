<script setup lang="ts" generic="T extends ScreenItem">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Rendu Natheo-Atelier d'un écran de liste, dans le volet : la liste est à gauche (AtelierList), le volet présente
 * l'écran et invite à ouvrir ou créer un élément
 */
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { UiIcon } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import type { AdminScreenProps } from '@/ts/Admin/Theme/AdminTheme.type';
import type { CollectionScreenState } from '@/ts/Admin/Screen/CollectionScreen.type';
import type { ScreenItem } from '@/ts/Admin/Screen/Screen.type';

const props = defineProps<AdminScreenProps<CollectionScreenState<T>>>();

const { t } = useAdminContext();
const router = useRouter();
const descriptor = props.screen.descriptor;
const createHref = computed(() => (descriptor.create ? router.resolve({ name: descriptor.create.route }).href : ''));
</script>

<template>
  <div class="max-w-[760px] px-10 pt-[26px] pb-10 max-md:p-5">
    <h1 class="m-0 text-[22px] leading-tight font-[650] tracking-[-0.01em]">{{ t(descriptor.title) }}</h1>
    <p v-if="descriptor.description" class="m-0 mt-1.5 max-w-[60ch] text-fg-muted">{{ t(descriptor.description) }}</p>
    <div class="mt-6 grid gap-[18px] max-md:hidden">
      <div class="flex items-start gap-3.5 rounded-xl border border-line p-[18px]">
        <UiIcon name="list" class="mt-0.5 size-[18px] shrink-0 text-primary" />
        <p class="m-0 text-fg-muted">
          <strong class="mb-0.5 block text-fg">{{ t('admin_app.atelier.pane_choose_title') }}</strong>
          {{ t('admin_app.atelier.pane_choose_text') }}
        </p>
      </div>
      <a
        v-if="screen.canCreate && descriptor.create"
        :href="createHref"
        class="ui-ring flex items-start gap-3.5 rounded-xl border border-line p-[18px] text-fg-muted no-underline hover:border-line-strong"
      >
        <UiIcon name="plus" class="mt-0.5 size-[18px] shrink-0 text-primary" />
        <span>
          <strong class="mb-0.5 block text-fg">{{ t(descriptor.create.label) }}</strong>
          {{ t('admin_app.atelier.pane_create_text') }}
        </span>
      </a>
    </div>
  </div>
</template>
