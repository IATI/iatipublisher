<template>
  <div
    v-for="(participating_org, key) in data"
    :key="key"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="recipient_country-code mb-2 text-sm">
      <div v-if="participating_org.country_code" class="space-x-1">
        <span>{{ types.country[participating_org.country_code] }}</span>
        <span v-if="participating_org.percentage" class="text-sm font-normal"
          >({{ roundFloat(participating_org.percentage) }}%)</span
        >
      </div>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData)
      }}</span>
    </div>

    <div
      v-for="(item, i) in participating_org.narrative"
      :key="i"
      :class="{ 'mb-4': i !== participating_org.narrative.length - 1 }"
      class="recipient_country-content text-sm"
    >
      <div v-if="item.narrative" class="flex max-w-[887px] flex-col">
        <span v-if="item.language" class="language mb-1.5">
          ({{ getTranslatedLanguage(translatedData) }}:
          {{ types.languages[item.language] }})
        </span>
        <span>{{ item.narrative }}</span>
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
  name: 'ActivityRecipientCountry',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      country: [];
      languages: [];
    }

    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    function roundFloat(num: string) {
      return parseFloat(num).toFixed(2);
    }

    return { types, roundFloat, translatedData };
  },
  methods: { getTranslatedLanguage, getTranslatedMissing },
});
</script>
