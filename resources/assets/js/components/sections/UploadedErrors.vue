<template>
  <div
    class="relative mb-4 p-4"
    :class="
      index === 'error' ? 'error-border bg-rose' : 'warning-border bg-eggshell'
    "
  >
    <div
      class="flex cursor-pointer items-center justify-between"
      @click="accordionToggle"
    >
      <div class="flex items-center space-x-2">
        <svg-vue
          :class="index === 'error' ? 'text-crimson-40' : 'text-camel-40'"
          icon="alert"
        />
        <span class="text-sm font-bold capitalize"
          >{{ errorLength(index) }}

          {{ index }}</span
        >
      </div>
      <svg-vue
        icon="dropdown-arrow"
        class="ml-1 mt-1.5 text-[6px] duration-200"
        :class="{ 'rotate-180': active, '': !active }"
      />
    </div>
    <div class="container">
      <div class="error-container">
        <div v-if="index === 'error'" class="pl-3 text-xs italic">
          (
          {{
            translatedData[
              'common.common.the_fields_with_errors_are_not_uploaded_to_our_system_during_import'
            ]
          }}
          )
        </div>
        <div v-else class="pl-3 text-xs italic">
          (
          {{
            translatedData[
              'common.common.the_fields_with_warnings_are_stored_in_our_system'
            ]
          }}
          )
        </div>

        <div
          v-for="(error, errorIndex) in item"
          :key="errorIndex"
          class="error-element my-2 py-4"
        >
          <div class="mb-1 font-bold capitalize">{{ errorIndex }}</div>
          <div v-for="(errorList, listIndex) in error" :key="listIndex">
            <div class="list-index text-sm">
              {{
                listIndex.toString().split('.').join(' > ').split('_').join(' ')
              }}
            </div>
            <div class="mb-2 pl-6 text-sm">{{ errorList }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { defineProps, inject, ref } from 'vue';

const active = ref(false);
const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  index: {
    type: String,
    required: true,
  },
});
const translatedData = inject('translatedData') as Record<string, string>;
const toggle = ref(false);
const errorLength = (currentError) => {
  let count = 0;

  if (props.index === currentError) {
    for (const type in props.item) {
      count += Object.keys(props.item[type]).length;
    }
  }
  return count;
};

const accordionToggle = (e: Event) => {
  active.value = !active.value;

  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.container');
  const elHeight = target?.querySelector('.error-container')?.clientHeight;

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
</script>
<style lang="scss" scoped>
.error-border {
  &::after {
    content: ' ';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #e34d5b;
  }
}
.warning-border {
  &::after {
    content: ' ';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 2px;
    background-color: #f4b784;
  }
}
.error-container {
  overflow: hidden;
  transition: height 0.3s ease-out;
  height: auto;
}

.list-index {
  position: relative;
  padding-left: 24px;

  &:after {
    content: ' ';
    z-index: 10;
    position: absolute;
    height: 4px;
    width: 4px;
    border-radius: 10px;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: #2a2f30;
  }
}
.container {
  @apply h-0 overflow-hidden px-4 transition-all duration-500;
}
.error-element:not(:last-of-type) {
  border-bottom: 1px solid #d5dcde;
}
</style>
