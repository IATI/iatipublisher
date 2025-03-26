<template>
  <div
    v-for="(post, i) in tdData[0].narrative"
    :key="i"
    class="title-content"
    :class="{
      'mb-4': i !== Object.keys(tdData[0].narrative).length - 1,
    }"
  >
    <div v-if="post.narrative" class="language subtle-darker mb-1.5">
      ({{ getTranslatedLanguage(translatedData) }}:
      {{ type[post.language] ? type[post.language] : 'N/A' }})
    </div>
    <div v-else>
      <span class="text-xs italic text-light-gray">N/A</span>
    </div>
    <div class="w-[800px] max-w-[80%] overflow-x-hidden text-ellipsis text-sm">
      {{ post.narrative }}
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, toRefs } from 'vue';
import { getTranslatedLanguage } from 'Composable/utils';

export default defineComponent({
  name: 'ResultTD',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
    type: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    /**
     * Typescript interface
     */
    interface NarrativeArray {
      [index: number]: { language: string; narrative: string };
    }

    interface Narratives {
      [index: number]: {
        narrative: NarrativeArray;
      };
    }

    let { data } = toRefs(props);
    const tdData = data.value as Narratives;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { tdData, translatedData };
  },
  methods: { getTranslatedLanguage },
});
</script>
