<template>
  <div class="flex h-full flex-col justify-around pt-[97px]">
    <div>
      <h3 class="pb-[2px] text-[20px] font-bold leading-9 text-n-50">
        {{ translatedData['onboarding.activity_step.create_an_activity'] }}
      </h3>

      <div
        class="mt-3 rounded-lg bg-n-10 pt-[20px] pl-[27px] pb-[20px] pr-[62px]"
      >
        <ul class="w-full max-w-[655px]">
          <li class="flex gap-2">
            <svg-vue
              class="mt-1 text-base text-bluecoral"
              icon="organisation-elements/org_identifier"
            ></svg-vue
            ><span
              >{{
                translatedData[
                  'onboarding.activity_step.add_your_first_activity'
                ]
              }}.</span
            >
          </li>
          <li class="flex items-baseline gap-2 py-5">
            <svg-vue
              icon="core-square"
              class="translate-y-1 text-base text-bluecoral"
            ></svg-vue>

            <span class="text-base">
              {{
                translatedData[
                  'onboarding.activity_step.populate_the_core_data_elements_about_your_activity'
                ]
              }}
            </span>
          </li>
          <li class="flex gap-2">
            <svg-vue
              class="mt-1 text-base text-bluecoral"
              icon="tick-cloud-square"
            ></svg-vue
            ><span>{{
              translatedData[
                'onboarding.activity_step.publish_your_activity_when_ready'
              ]
            }}</span>
          </li>
        </ul>
      </div>
    </div>
    <div class="mt-3 flex w-full items-center justify-between text-base">
      <button class="text-sm font-bold text-n-40" @click="previousStep">
        {{ translatedData['common.common.previous'] }}
      </button>
      <div class="flex items-center gap-4">
        <button class="button primary-btn text-xs" @click="proceedStep">
          {{ translatedData['common.common.get_started'] }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { defineEmits, inject } from 'vue';

const translatedData = inject('translatedData') as Record<string, string>;
const emit = defineEmits(['proceedStep', 'previousStep']);

const proceedStep = () => {
  axios
    .get('/organisation-onboarding/complete-activity')
    .then(() => {
      emit('proceedStep');
    })
    .catch((err) => {
      console.log(err);
    });
};

const previousStep = () => {
  emit('previousStep');
};
</script>
