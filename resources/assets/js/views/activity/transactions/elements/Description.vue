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
      ({{
        post.language
          ? `${getTranslatedLanguage(translatedData)} : ${
              type.languages[post.language]
            }`
          : `${getTranslatedLanguage(translatedData)} : N/A`
      }})
    </div>
    <div class="description text-sm">
      {{ post.narrative ?? '' }}
      <span v-if="!post.narrative" class="text-xs italic text-light-gray"
        >N/A</span
      >
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';
import { getTranslatedLanguage } from 'Composable/utils';

export default defineComponent({
  name: 'TransactionDescription',
  components: {},
  props: {
    data: {
      type: [Object, String],
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

    const type = inject('types');
    const translatedData = inject('translatedData') as Record<string, string>;

    return { tdData, type, translatedData };
  },
  methods: { getTranslatedLanguage },
});
</script>
