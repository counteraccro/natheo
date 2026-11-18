<script setup lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Notifications du back-office : en haut à droite, la plus récente en tête, trois visibles au plus ; chacune se ferme
 * après sa durée, décomptée seulement quand elle est visible et suspendue au survol et au focus
 */
import { computed, onBeforeUnmount, reactive, watch } from 'vue';
import UiIcon from '@/vue/Components/Global/Ui/UiIcon.vue';
import UiButton from '../Form/UiButton.vue';
import { useUiLabels } from '@/vue/Composables/Admin/Ui/useUiLabels';
import { dismissNotification, notificationState } from '@/vue/Composables/Global/toast';
import { FEEDBACK_ICONS, FEEDBACK_SOFT, FEEDBACK_TEXT, feedbackRole } from '@/vue/Composables/Admin/Ui/feedbackStyles';

const MAX_VISIBLE = 3;

const labels = useUiLabels();
const visible = computed(() => notificationState.items.slice(0, MAX_VISIBLE));

interface Countdown {
  timer?: ReturnType<typeof setTimeout>;
  remaining: number;
  startedAt: number;
}
const countdowns = new Map<number, Countdown>();
const paused = reactive(new Set<number>());

const start = (id: number): void => {
  const countdown = countdowns.get(id);
  if (!countdown || countdown.timer) return;
  countdown.startedAt = Date.now();
  countdown.timer = setTimeout(() => dismissNotification(id), countdown.remaining);
};

const pause = (id: number): void => {
  paused.add(id);
  const countdown = countdowns.get(id);
  if (!countdown?.timer) return;
  clearTimeout(countdown.timer);
  countdown.timer = undefined;
  countdown.remaining -= Date.now() - countdown.startedAt;
};

const resume = (id: number): void => {
  paused.delete(id);
  start(id);
};

watch(
  visible,
  (items) => {
    const ids = new Set(items.map((item) => item.id));
    for (const [id, countdown] of countdowns) {
      if (!ids.has(id)) {
        clearTimeout(countdown.timer);
        countdowns.delete(id);
        paused.delete(id);
      }
    }
    for (const item of items) {
      if (!countdowns.has(item.id)) {
        countdowns.set(item.id, { remaining: item.duration, startedAt: 0 });
        start(item.id);
      }
    }
  },
  { immediate: true }
);

onBeforeUnmount(() => countdowns.forEach((countdown) => clearTimeout(countdown.timer)));
</script>

<template>
  <section
    :aria-label="labels.toast.region"
    class="pointer-events-none fixed top-[72px] right-5 z-[80] flex w-[min(380px,calc(100vw-40px))] flex-col gap-2.5 max-md:top-[68px] max-md:right-2 max-md:left-2 max-md:w-auto"
  >
    <div
      v-for="item in visible"
      :key="item.id"
      :role="feedbackRole(item.type)"
      class="pointer-events-auto relative flex animate-[ui-toast-in_.22s_var(--ease)] items-start gap-3 overflow-hidden rounded-md bg-raised py-3.5 pr-3 pl-3.5 text-fg shadow-overlay"
      @mouseenter="pause(item.id)"
      @mouseleave="resume(item.id)"
      @focusin="pause(item.id)"
      @focusout="resume(item.id)"
    >
      <span
        class="grid size-7 shrink-0 place-items-center rounded-full"
        :class="[FEEDBACK_SOFT[item.type], FEEDBACK_TEXT[item.type]]"
      >
        <UiIcon :name="FEEDBACK_ICONS[item.type]" class="size-4" />
      </span>
      <div class="min-w-0 flex-1">
        <p class="m-0 mt-[3px] font-[650]">{{ item.title }}</p>
        <p v-if="item.text" class="m-0 mt-0.5 text-sm text-fg-muted">{{ item.text }}</p>
      </div>
      <UiButton
        variant="ghost"
        size="sm"
        icon="close"
        :aria-label="labels.toast.close"
        class="-my-1"
        @click="dismissNotification(item.id)"
      />
      <span
        aria-hidden="true"
        class="absolute bottom-0 left-0 h-0.5 bg-current opacity-35"
        :class="FEEDBACK_TEXT[item.type]"
        :style="{
          animation: `ui-progress ${item.duration}ms linear forwards`,
          animationPlayState: paused.has(item.id) ? 'paused' : 'running',
        }"
      />
    </div>
  </section>
</template>
