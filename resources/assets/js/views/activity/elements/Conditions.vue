<template>
  <div>
    <div v-if="data.condition_attached == '1'" class="elements-detail">
      <div class="category">
        <span>Attached - </span>
        <span>
          <span v-if="data.condition_attached == '0'">No</span>
          <span v-else-if="data.condition_attached == '1'">Yes</span>
        </span>
      </div>
      <div
        v-for="(post, key) in data.condition"
        :key="key"
        :class="{ 'mb-4': Number(key) !== data.condition.length - 1 }"
      >
        <div class="mb-2 text-sm font-bold">
          <div v-if="post.condition_type">
            {{ types.conditionType[post.condition_type] }}
          </div>
          <span v-else class="italic">
            {{ getTranslatedMissing(translatedData, 'type') }}
          </span>
        </div>
        <table class="ml-5">
          <tbody>
            <tr
              v-for="(item, i) in post.narrative"
              :key="i"
              class="multiline"
              :class="{ 'mb-4': i !== post.narrative.length - 1 }"
            >
              <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
              <td>
                <div v-if="item.narrative" class="flex flex-col">
                  <span v-if="item.language" class="language top"
                    >({{ getTranslatedLanguage(translatedData) }} :
                    {{ types.languages[item.language] }})</span
                  >
                  <span v-if="item.narrative" class="description">{{
                    item.narrative
                  }}</span>
                </div>
                <span v-else class="italic">
                  {{ getTranslatedMissing(translatedData) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <span v-else class="text-sm italic">{{
      getTranslatedElement(translatedData, 'conditions_not_attached')
    }}</span>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import dateFormat from 'Composable/dateFormat';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

export default defineComponent({
  name: 'ActivityConditions',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      conditionType: [];
      languages: [];
    }

    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, dateFormat, translatedData };
  },
  methods: {
    getTranslatedLanguage,
    getTranslatedElement,
    getTranslatedMissing,
  },
});
</script>
