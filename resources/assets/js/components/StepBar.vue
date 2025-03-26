<template>
  <div class="step-bar flex flex-col gap-[26px]">
    <div
      v-for="(step, index) in props.steps"
      :key="index"
      :class="[
        'step-bar-item flex cursor-pointer items-center gap-[6px] py-[6px]',
        { active: index + 1 === props.currentStep },
        { completed: step.complete },
      ]"
      @click="emit('change-step', index + 1)"
    >
      <div class="step-outer-circle">
        <div class="step-inner-circle">
          <span v-if="step.complete"
            ><svg-vue icon="step-tick" class="text-xl"
          /></span>
          <span v-else>{{ step.step }}</span>
        </div>
      </div>
      <div class="">
        <p class="text-[10px] leading-[15px] tracking-[-2%]">
          {{ translatedData['activity_index.step_bar.step'] ?? 'Step' }}
          {{ step.step }}
        </p>
        <p class="text-sm font-bold tracking-[-2%]">{{ step.title }}</p>
      </div>
    </div>
  </div>
  <div class="mt-[26px] rounded-lg bg-blue-40 p-[10px] text-sm tracking-[-2%]">
    <span>
      {{
        translatedData[
          'activity_index.step_bar.this_widget_can_be_accessed_from_get_started'
        ]
      }}
    </span>
  </div>
  <div class="mt-4 text-right">
    <label class="checkbox !flex items-center justify-end gap-2">
      <input v-model="checkMark" type="checkbox" />
      <span class="checkmark white" />
      <span class="text-sm">
        {{ translatedData['activity_index.step_bar.dont_show_this_again'] }}
      </span>
    </label>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { defineProps, ref, watch, defineEmits, inject } from 'vue';

const props = defineProps({
  currentStep: {
    type: Number,
    required: true,
  },
  steps: {
    type: Array as () => Array<{
      step: number;
      title: string;
      complete: boolean;
    }>,
    required: true,
  },
});

const emit = defineEmits(['change-step']);

const translatedData = inject('translatedData') as Record<string, string>;
const checkMark = ref(false);

watch(checkMark, async (newVal) => {
  try {
    await axios.post('organisation-onboarding/toggle-dont-show/', {
      value: newVal,
    });
    sessionStorage.setItem('isForceOpenModal', 'false');
  } catch (error) {
    console.error('Error', error);
  }
});
</script>
