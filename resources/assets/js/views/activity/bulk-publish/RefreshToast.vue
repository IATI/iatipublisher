<template>
  <div
    class="inline-flex items-center rounded-lg border px-5 py-3 text-xs leading-normal text-n-50"
    :class="className"
  >
    <svg-vue
      class="mr-5"
      :icon="props.type ? 'check-circle' : 'times-circle'"
    />
    <span class="mr-5">{{ props.message }}</span>
    <a href="" class="inline-flex items-center uppercase" :class="className2">
      <svg-vue icon="redo" class="mr-1" />
      <span>{{ translatedData['common.common.refresh'] }}</span>
    </a>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, ref } from 'vue';

const props = defineProps({
  message: { type: String, required: true },
  type: { type: Boolean, required: true },
});

const translatedData = inject('translatedData') as Record<string, string>;

const className = ref('');
className.value = props.type
  ? 'bg-mint border-spring-10'
  : 'bg-crimson-10 border-crimson-20';

const className2 = ref('');
className2.value = props.type
  ? 'text-spring-50 hover:text-spring-50'
  : 'text-crimson-50 hover:text-crimson-50';
</script>
