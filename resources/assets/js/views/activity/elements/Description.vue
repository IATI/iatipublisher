<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="description-type mb-2 text-sm font-bold">
      <span v-if="post.type">
        {{ types.descriptionType[post.type] }}
      </span>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData, 'type')
      }}</span>
    </div>
    <div
      v-for="(item, i) in post.narrative"
      :key="i"
      :class="{ 'mb-4': i !== post.narrative.length - 1 }"
      class="description-content text-sm"
    >
      <div v-if="item.narrative" class="flex flex-col">
        <span v-if="item.language" class="language mb-1.5">
          ({{ getTranslatedLanguage(translatedData) }} :
          {{ types.languages[item.language] }})
        </span>
        <span v-if="item.narrative" class="max-w-[887px]">
          {{ item.narrative }}
        </span>
      </div>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData, 'narrative')
      }}</span>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import { getTranslatedLanguage, getTranslatedMissing } from 'Composable/utils';

export default defineComponent({
  name: 'ActivityDescription',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      descriptionType: [];
      languages: [];
    }
    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, translatedData };
  },
  methods: { getTranslatedMissing, getTranslatedLanguage },
});
</script>
