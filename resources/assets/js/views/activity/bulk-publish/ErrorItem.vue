<template>
  <div
    v-for="(error, c) in data"
    :key="c"
    class="item accordion py-6"
    :class="{
      'border-b border-n-20': Number(c) != data.length - 1,
    }"
  >
    <div class="accordion-title flex items-center">
      <a
        :href="`/activity/${error.activity_id}`"
        target="_blank"
        class="activity-title grow pr-2 text-n-50"
      >
        {{ error.title }}
      </a>
      <div
        v-if="message"
        class="mr-2 flex shrink-0 cursor-pointer"
        @click="accordionToggle"
      >
        <span class="text-xs">{{
          translatedData['common.common.show_more']
        }}</span>
        <span>
          <svg-vue
            class="text-xl text-blue-50 transition-transform duration-500"
            icon="arrow-down"
          ></svg-vue>
        </span>
      </div>
      <label class="checkbox shrink-0">
        <input
          v-model="selectedActivities"
          :value="error.activity_id"
          type="checkbox"
        />
        <span class="checkmark" />
      </label>
    </div>
    <div class="accordion-content">
      <div>
        <ul class="mb-2">
          <li>
            <b>{{ errorCount(error.errors) }} {{ message }}</b>
          </li>
        </ul>
        <a
          :href="`/activity/${error.activity_id}`"
          target="_blank"
          class="inline-flex items-center"
        >
          <span class="mr-1 grow">{{
            translatedData['common.common.show_more']
          }}</span>
          <svg-vue class="shrink-0" icon="external"></svg-vue>
        </a>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { defineProps, inject, ref } from 'vue';

defineProps({
  data: { type: Object, required: true },
  message: { type: String, default: '' },
});

const translatedData = inject('translatedData') as Record<string, string>;
const selectedActivities = inject('selectedActivities') as number[];

const toggle = ref(false);

const accordionToggle = (e: Event) => {
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.closest('.accordion') as HTMLElement
  ).querySelector<HTMLElement>('.accordion-content');
  const elHeight = target?.querySelector('div')?.clientHeight;

  if (toggle.value) {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;
      setTimeout(function () {
        target.style.cssText = ``;
      }, 100);
      toggle.value = false;
    }
  } else {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;

      setTimeout(function () {
        target.style.cssText = `height: auto;`;
      }, 600);

      toggle.value = true;
    }
  }
};

const errorCount = (errors) => {
  let errorCount = '';
  if (errors?.warning) {
    errorCount = `${errors?.error} errors and ${errors?.warning} warnings were found.`;
  } else {
    errorCount = `${errors?.critical} critical errors were found.`;
  }

  return errorCount;
};
</script>

<style lang="scss" scoped>
.accordion-content {
  @apply h-0 overflow-hidden transition-all duration-500;
  div {
    @apply mt-4 border-t border-n-20 py-3;
  }
}

.activity-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
