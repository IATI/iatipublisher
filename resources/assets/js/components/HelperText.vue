<template>
  <div
    v-if="pathArrays.length > 0"
    class="my-2 flex items-center space-x-2 rounded-lg bg-eggshell py-2 px-4 align-middle"
  >
    <div class="flex items-center">
      <svg-vue icon="exclamation-warning" class="-translate-y-.1 h-6"></svg-vue>
    </div>

    <!-- eslint-disable vue/no-v-html -->
    <div class="flex-grow items-center">
      <div
        v-if="typeof helperText === 'string'"
        class="flex items-center text-xs font-normal text-n-50"
        v-html="helperText"
      ></div>

      <div
        v-else
        class="items-center text-xs font-normal text-n-50 hover:cursor-pointer"
      >
        <div
          class="strong flex items-center justify-between align-middle text-bluecoral"
          @click="toggleShowAccordian"
        >
          <span>
            {{
              translatedData[
                'workflow_frontend.bulk_publish.this_element_uses_deprecated_codelist_values'
              ]
            }}
          </span>

          <span :class="{ 'rotate-180 transform': showAccordianItems }">
            <svg-vue icon="dropdown-arrow" class="h-2"></svg-vue>
          </span>
        </div>

        <div
          v-if="showAccordianItems"
          class="mt-2 flex-grow rounded-md bg-white p-2"
        >
          <div v-for="(item, index) in pathArrays" :key="index" class="mb-1">
            <div>• {{ item }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, ref } from 'vue';

const props = defineProps({
  helperText: {
    type: [String, Boolean],
    required: true,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;

const showAccordianItems = ref(false);
const hasTruePath = typeof props.helperText === 'string';

const pathArrays = hasTruePath ? [] : findTruePaths(props.helperText);

const toggleShowAccordian = () => {
  showAccordianItems.value = !showAccordianItems.value;
};

function findTruePaths(
  obj: any,
  path: string[] = [],
  paths: string[] = []
): string[] {
  function snakeToKebab(str: string): string {
    return str.replace(/_/g, '-');
  }

  function isSnakeCase(str: string): boolean {
    return str.includes('_');
  }

  function getOrdinal(n: number): string {
    const s = ['th', 'st', 'nd', 'rd'];
    const v = n % 100;
    return n + (s[(v - 20) % 10] || s[v] || s[0]);
  }

  if (typeof obj === 'string') {
    paths.push(path.join(' ➤ '));
  } else if (Array.isArray(obj)) {
    obj.forEach((item, index) => {
      findTruePaths(item, [...path, getOrdinal(index + 1)], paths);
    });
  } else if (obj !== null && typeof obj === 'object') {
    for (const key in obj) {
      // eslint-disable-next-line no-prototype-builtins
      if (obj.hasOwnProperty(key)) {
        const newKey = isSnakeCase(key) ? snakeToKebab(key) : key;
        findTruePaths(obj[key], [...path, newKey], paths);
      }
    }
  }
  return paths;
}
</script>

<style lang="scss">
.rotate-180 {
  transform: rotate(180deg);
  transition: transform 0.3s ease;
}
</style>
