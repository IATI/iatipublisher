<template>
  <div
    class="flex items-center rounded bg-mint px-1.5 py-1 text-xs font-normal leading-normal text-n-50"
  >
    <svg-vue v-if="icon" :icon="icon" class="mr-1.5 text-base text-spring-50" />
    <span>{{ resolvedText }}</span>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, computed } from 'vue';

const translatedData = inject('translatedData') as Record<string, string>;

const props = defineProps({
  text: { type: String, default: '' },
  icon: { type: String, default: 'approved-cloud' },
});

const resolvedText = computed(() => {
  return (
    props.text ||
    translatedData['common.common.previously_published_on_iati'] ||
    ''
  );
});
</script>
