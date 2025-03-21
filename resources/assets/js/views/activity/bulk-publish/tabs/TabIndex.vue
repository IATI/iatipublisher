<template>
  <div class="mt-3 rounded-lg border-x border-b border-n-20">
    <div class="flex gap-0.5">
      <div v-for="tab in props.tabs" :key="tab.value" class="flex-1">
        <button
          class="text-x inline-block w-full flex-1 rounded-t-lg border-b-4 px-6 py-[14px] font-bold uppercase tracking-normal text-n-50"
          :class="[
            activeTab === tab.value
              ? 'active border-bluecoral bg-[#D0DDE0]'
              : 'border-transparent bg-n-10',
          ]"
          @click="handleActiveTab(tab.value)"
        >
          {{ tab.name }}
        </button>
      </div>
    </div>
    <div class="">
      <div class="px-6 py-4">
        <slot v-if="activeTab === 1" name="tabOne"> </slot>
        <slot v-if="activeTab === 2" name="tabTwo"> </slot>
      </div>
      <div
        v-if="showBottomBanner"
        class="flex items-center gap-1 rounded-b-lg bg-n-10 p-2 text-sm text-n-40"
      >
        <svg
          width="18"
          height="18"
          viewBox="0 0 18 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M9.00135 10.668C8.83654 10.668 8.67542 10.7168 8.53838 10.8084C8.40134 10.9 8.29453 11.0301 8.23145 11.1824C8.16838 11.3347 8.15188 11.5022 8.18403 11.6639C8.21619 11.8255 8.29555 11.974 8.4121 12.0906C8.52864 12.2071 8.67713 12.2865 8.83878 12.3186C9.00043 12.3508 9.16798 12.3343 9.32026 12.2712C9.47253 12.2081 9.60268 12.1013 9.69424 11.9643C9.78581 11.8272 9.83469 11.6661 9.83469 11.5013C9.83469 11.2803 9.74689 11.0683 9.59061 10.912C9.43433 10.7558 9.22237 10.668 9.00135 10.668ZM9.00135 0.667969C7.907 0.667969 6.82337 0.883517 5.81232 1.30231C4.80128 1.7211 3.88262 2.33492 3.1088 3.10875C1.54599 4.67155 0.66802 6.79116 0.66802 9.0013C0.660735 10.9256 1.32701 12.7917 2.55135 14.2763L0.884687 15.943C0.769055 16.0602 0.690725 16.209 0.65958 16.3706C0.628435 16.5323 0.64587 16.6995 0.709687 16.8513C0.778901 17.0012 0.891107 17.1272 1.03206 17.2133C1.17301 17.2993 1.33635 17.3416 1.50135 17.3346H9.00135C11.2115 17.3346 13.3311 16.4567 14.8939 14.8939C16.4567 13.3311 17.3347 11.2114 17.3347 9.0013C17.3347 6.79116 16.4567 4.67155 14.8939 3.10875C13.3311 1.54594 11.2115 0.667969 9.00135 0.667969ZM9.00135 15.668H3.50969L4.28469 14.893C4.36343 14.8158 4.42607 14.7238 4.46898 14.6222C4.5119 14.5206 4.53423 14.4116 4.53469 14.3013C4.53156 14.0815 4.4417 13.8718 4.28469 13.718C3.19351 12.628 2.514 11.1934 2.36193 9.65863C2.20986 8.12384 2.59464 6.58381 3.45071 5.3009C4.30678 4.018 5.58118 3.0716 7.05678 2.62295C8.53239 2.17429 10.1179 2.25114 11.5432 2.8404C12.9685 3.42965 14.1454 4.49486 14.8734 5.85454C15.6014 7.21422 15.8354 8.78426 15.5356 10.2971C15.2358 11.81 14.4208 13.1722 13.2293 14.1515C12.0378 15.1308 10.5437 15.6668 9.00135 15.668ZM9.00135 5.66797C8.78034 5.66797 8.56838 5.75577 8.4121 5.91205C8.25582 6.06833 8.16802 6.28029 8.16802 6.5013V9.0013C8.16802 9.22232 8.25582 9.43428 8.4121 9.59056C8.56838 9.74684 8.78034 9.83463 9.00135 9.83463C9.22237 9.83463 9.43433 9.74684 9.59061 9.59056C9.74689 9.43428 9.83469 9.22232 9.83469 9.0013V6.5013C9.83469 6.28029 9.74689 6.06833 9.59061 5.91205C9.43433 5.75577 9.22237 5.66797 9.00135 5.66797Z"
            fill="#68797E"
          />
        </svg>
        <span>
          {{
            translatedData[
              'workflow_frontend.bulk_publish.try_again_or_write_to_support_for_further_assistance'
            ]
          }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
type Tab = {
  value: number;
  name: string;
};

import { ref, defineProps, defineEmits, inject } from 'vue';
const props = defineProps({
  tabs: {
    type: Array as () => Tab[],
    required: true,
    default: () => [],
  },
  showBottomBanner: {
    type: Boolean,
    required: false,
    default: false,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;
const activeTab = ref(1);

const emit = defineEmits(['activeTab']);
const handleActiveTab = (value: number) => {
  activeTab.value = value;
  emit('activeTab', value);
};
</script>

<style lang="scss" scoped></style>
