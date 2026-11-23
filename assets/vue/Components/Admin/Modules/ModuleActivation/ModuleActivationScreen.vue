<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Écran d'activation des modules du back-office : un interrupteur par module et par opération d'API
 */
import { onMounted, ref } from 'vue';
import { AdminPageHeader, UiBadge, UiButton, UiIcon, UiSkeleton, UiSwitch, UiTooltip } from '@ui';
import { useAdminContext } from '@/vue/Composables/Admin/useAdminContext';
import { useModuleActivation } from '@/vue/Composables/Admin/ModuleActivation/useModuleActivation';

const context = useAdminContext();
const { t } = context;
const screen = useModuleActivation(context);
const { modules, loading, saving } = screen;
const opened = ref(new Set<string>());

const toggleOpened = (id: string): void => {
  const next = new Set(opened.value);
  if (!next.delete(id)) {
    next.add(id);
  }
  opened.value = next;
};

onMounted(() => void screen.load());
</script>

<template>
  <AdminPageHeader
    :title="t('module_activation.index.title')"
    :description="t('module_activation.index.description')"
    icon="grid"
  />

  <div v-if="loading" class="mt-6 space-y-3">
    <UiSkeleton v-for="index in 4" :key="index" variant="block" />
  </div>

  <ul v-else class="mt-6 space-y-3" :aria-label="t('module_activation.index.title')">
    <li
      v-for="module in modules"
      :key="module.id"
      class="rounded-lg border border-line bg-bg shadow-panel"
      :class="{ 'bg-row-disabled': module.disabled }"
    >
      <div class="flex items-start gap-4 px-5 py-4">
        <div class="min-w-0 flex-1">
          <UiSwitch
            :model-value="!module.disabled"
            :label="module.label"
            :description="module.description"
            :disabled="module.locked || saving.has(module.id)"
            @update:model-value="screen.setModuleDisabled(module, !$event)"
          />
        </div>
        <UiTooltip v-if="module.locked" :text="t('module_activation.index.module_locked')">
          <span class="inline-flex text-fg-subtle">
            <UiIcon name="lock" class="size-4" :label="t('module_activation.index.module_locked')" />
          </span>
        </UiTooltip>
        <UiBadge v-else-if="module.disabled" tone="warning">{{ t('module_activation.index.disabled') }}</UiBadge>
      </div>

      <div v-if="module.operations.length > 0" class="border-t border-line px-5 py-3">
        <UiButton
          variant="ghost"
          size="sm"
          :icon="opened.has(module.id) ? 'chevron-down' : 'chevron-right'"
          :aria-expanded="opened.has(module.id)"
          :aria-controls="`module-operations-${module.id}`"
          @click="toggleOpened(module.id)"
        >
          {{ t('module_activation.index.operations', { count: module.operations.length }) }}
        </UiButton>

        <ul v-if="opened.has(module.id)" :id="`module-operations-${module.id}`" class="mt-2 divide-y divide-line">
          <li v-for="operation in module.operations" :key="operation.id" class="flex items-center gap-4 py-2.5">
            <div class="min-w-0 flex-1">
              <UiSwitch
                :model-value="!operation.disabled"
                :label="screen.kindLabel(operation)"
                :description="`${operation.method} ${operation.path}`"
                :disabled="operation.locked || saving.has(operation.id)"
                @update:model-value="screen.setOperationDisabled(module, operation, !$event)"
              />
            </div>
            <UiTooltip v-if="operation.locked" :text="t('module_activation.index.operation_locked')">
              <span class="inline-flex text-fg-subtle">
                <UiIcon name="lock" class="size-4" :label="t('module_activation.index.operation_locked')" />
              </span>
            </UiTooltip>
          </li>
        </ul>
        <p v-if="opened.has(module.id) && module.disabled" class="mt-2 text-sm text-fg-subtle">
          {{ t('module_activation.index.module_disabled_hint') }}
        </p>
      </div>
    </li>
  </ul>
</template>
