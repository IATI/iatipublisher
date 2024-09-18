<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="date-type mb-1 flex flex-col space-y-2 text-sm">
      <div>
        <span v-if="post.type" class="font-bold">{{
          types.activityDate[post.type]
        }}</span>
        <span v-else class="text-sm font-bold italic">{{
          getTranslatedMissing(translatedData, 'type')
        }}</span>
      </div>
      <div>
        <span v-if="post.date" class="text-sm font-normal">{{
          formatDate(post.date)
        }}</span>
        <span v-else class="text-sm italic">{{
          getTranslatedMissing(translatedData, 'date')
        }}</span>
      </div>
    </div>
    <div
      v-for="(item, i) in post.narrative"
      :key="i"
      :class="{ 'mb-4': i !== post.narrative.length - 1 }"
      class="date-content elements-detail"
    >
      <table class="ml-5">
        <tr class="multiline">
          <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
          <td>
            <div v-if="item.narrative" class="flex flex-col">
              <span v-if="item.language" class="language top">
                ({{ getTranslatedLanguage(translatedData) }}:
                {{ types.languages[item.language] }})
              </span>
              <span v-if="item.narrative" class="description">
                {{ item.narrative }}
              </span>
            </div>
            <span v-else class="italic">{{
              getTranslatedMissing(translatedData)
            }}</span>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';

import moment from 'moment';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

export default defineComponent({
  name: 'ActivityDate',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      activityDate: [];
      languages: [];
    }
    function formatDate(date: Date) {
      return moment(date).format('LL');
    }

    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, formatDate, translatedData };
  },
  methods: {
    getTranslatedLanguage,
    getTranslatedElement,
    getTranslatedMissing,
  },
});
</script>
