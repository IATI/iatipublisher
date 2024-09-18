<template>
  <div class="status ml-2.5 flex text-xs leading-5" :class="color">
    <b class="mr-2 text-base leading-3">.</b><span>{{ text }}</span>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, toRefs } from 'vue';
const translatedData = inject('translatedData') as Record<string, string>;

const props = defineProps({
  data: { type: Boolean, required: true },
});

const { data } = toRefs(props);

let color = data.value ? 'text-spring-50' : 'text-crimson-50',
  text = data.value
    ? translatedData['common.common.completed']
    : translatedData['common.common.not_completed'];
</script>
