<template>
  <div class="wizard flex items-end pb-4">
    <div class="h-1.5 flex-1 rounded-3xl bg-turquoise" />
    <div
      v-for="step in steps"
      :key="step.id"
      class="wizard-step"
      :class="step.id === 1 ? 'flex-[2_1_0%]' : 'flex-1'"
    >
      <div class="wizard-step__header">
        <div
          class="wizard-step__header__title pb-3 text-xs font-bold leading-[22px] tracking-normal text-n-50"
        >
          <span class="inline-block -translate-x-1/2">
            {{
              step.name == 'Validating'
                ? completedSteps.includes(step.id)
                  ? translatedData['common.common.validated']
                  : translatedData['common.common.validating']
                : completedSteps.includes(step.id)
                ? store.state.bulkActivityPublishStatus.publishing
                    .hasFailedActivities.ids.length > 0
                  ? translatedData['common.common.failed']
                  : translatedData['common.common.published']
                : completedSteps.length == 0
                ? translatedData['common.common.publish']
                : translatedData['common.common.publishing']
            }}
          </span>
        </div>
        <div
          class="wizard-step__progress relative flex items-center"
          :class="completedSteps.includes(step.id) ? 'active' : ''"
        >
          <div
            class="w-full rounded-3xl"
            :class="[
              completedSteps.includes(step.id)
                ? 'h-1.5 bg-turquoise'
                : 'h-1 bg-[#CDF8FA]',
            ]"
          ></div>
          <span
            class="absolute -left-1 z-[1] flex h-4 w-4 items-center justify-center rounded-full text-xs font-bold"
            :class="[
              completedSteps.includes(step.id)
                ? 'bg-turquoise'
                : 'bg-[#CDF8FA]',
              step.id == 3 ? 'hidden' : '',
            ]"
          >
            <template v-if="completedSteps.includes(step.id)">
              <span v-if="step.name == 'Validating'">
                <svg
                  width="11"
                  height="8"
                  viewBox="0 0 11 8"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    fill-rule="evenodd"
                    clip-rule="evenodd"
                    d="M10.5 1.27749L4.32875 8L0.5 4.92893L1.70773 3.4531L4.12809 5.39449L9.08023 0L10.5 1.27749Z"
                    fill="#155366"
                  />
                </svg>
              </span>
              <span v-if="step.name == 'Publish'">
                <span
                  v-if="
                    store.state.bulkActivityPublishStatus.publishing
                      .hasFailedActivities.ids.length > 0
                  "
                >
                  <svg-vue icon="cross" class="mt-2 ml-1 h-4 w-4" />
                </span>
                <span v-else>
                  <svg
                    width="11"
                    height="8"
                    viewBox="0 0 11 8"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M10.5 1.27749L4.32875 8L0.5 4.92893L1.70773 3.4531L4.12809 5.39449L9.08023 0L10.5 1.27749Z"
                      fill="#155366"
                    />
                  </svg>
                </span>
              </span>
            </template>
            <template v-else>
              {{ step.id }}
            </template>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { defineProps, inject } from 'vue';
import { useStore } from 'Store/activities';

const translatedData = inject('translatedData') as Record<string, string>;
const store = useStore();
const steps = [
  {
    name: 'Validating',
    id: 1,
  },
  {
    name: 'Publish',
    id: 2,
  },
];

defineProps({
  completedSteps: {
    type: Array,
    default: () => [],
  },
});
</script>
