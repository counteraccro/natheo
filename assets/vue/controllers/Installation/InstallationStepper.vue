<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type { InstallationStep, StepStatus } from '@/ts/Installation/InstallationStepper';

export default defineComponent({
  name: 'InstallionStepper',
  props: {
    currentStep: {
      type: String,
      required: true,
    },
    steps: {
      type: Array as PropType<InstallationStep[]>,
      required: true,
    },
  },
  computed: {
    currentIndex(): number {
      const index = this.steps.findIndex((step) => step.id === this.currentStep);
      return index === -1 ? 0 : index;
    },
  },
  methods: {
    statusFor(index: number): StepStatus {
      if (index < this.currentIndex) return 'done';
      if (index === this.currentIndex) return 'active';
      return 'upcoming';
    },
  },
});
</script>

<template>
  <div class="installer-stepper flex items-start mt-8 mb-10">
    <div v-for="(step, index) in steps" :key="step.id" class="step" :class="statusFor(index)">
      <div v-if="index < steps.length - 1" class="step-line"></div>

      <div class="step-circle">
        <svg v-if="statusFor(index) === 'done'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
        <template v-else>{{ index + 1 }}</template>
      </div>

      <span class="step-label" v-html="step.label.replace(' ', '&nbsp;')"></span>
    </div>
  </div>
</template>

<style scoped></style>
