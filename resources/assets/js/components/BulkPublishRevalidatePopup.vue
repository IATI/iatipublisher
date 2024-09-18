<template>
  <Transition name="fade">
    <div
      v-if="dataChanged"
      class="absolute bottom-0 left-0 z-50 h-full w-full overflow-hidden bg-black bg-opacity-20"
    >
      <Transition name="slide-in">
        <div
          v-if="showSlideIn"
          class="absolute bottom-0 left-0 w-full rounded-t-3xl bg-white p-10"
        >
          <div class="mt-4 flex items-center gap-1 bg-n-10 p-4 text-sm">
            <svg-vue class="text-xl" icon="warning-activity" />
            <!-- !TODO: Remove this -->
            <span>
              {{
                translatedData[
                  'workflow_frontend.bulk_publish.changes_have_been_detected_in_your_activity_data'
                ]
              }}
            </span>
          </div>
          <div class="mt-4 flex items-center justify-end">
            <ButtonComponent
              class="mx-3 bg-white px-3 uppercase"
              type=""
              :text="translatedData['common.common.cancel']"
              @click="emit('cancel')"
            />
            <div class="flex items-center gap-4">
              <ButtonComponent
                type="primary"
                :text="translatedData['common.common.revalidate']"
                @click="emit('reverify')"
              />
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script lang="ts" setup>
import { defineProps, defineEmits, inject } from 'vue';
import ButtonComponent from './ButtonComponent.vue';

defineProps({
  dataChanged: {
    type: Boolean,
    required: true,
  },
  showSlideIn: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['cancel', 'reverify']);
const translatedData = inject('translatedData') as Record<string, string>;
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
}

.slide-in-enter-from,
.slide-in-leave-to {
  transform: translateY(100%);
}

.slide-in-enter-to,
.slide-in-leave-from {
  transform: translateY(0);
}
.slide-in-enter-active,
.slide-in-leave-active {
  transition: all 0.5s ease;
}
</style>
