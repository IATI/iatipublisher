<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="country_budget_items elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="tb-title category">
      <span v-if="post.sector_vocabulary">{{
        types.sectorVocabulary[post.sector_vocabulary]
      }}</span>
      <span v-else class="italic">Vocabulary Missing</span>
    </div>
    <div class="mb-1 flex space-x-1 text-sm">
      <div>
        <div v-if="post.sector_vocabulary == 1">
          <span v-if="post.code">{{ types.sectorCode[post.code] }}</span>
          <span v-else class="italic">Missing</span>
        </div>
        <div v-else-if="post.sector_vocabulary == 2">
          <span v-if="post.category_code">{{
            types.sectorCategory[post.category_code]
          }}</span>
          <span v-else class="italic">Missing</span>
        </div>
        <div v-else-if="post.sector_vocabulary == 7">
          <span v-if="post.sdg_goal">{{ types.sdgGoals[post.sdg_goal] }}</span>
          <span v-else class="italic">Missing</span>
        </div>
        <div v-else-if="post.sector_vocabulary == 8">
          <span v-if="post.sdg_target">{{
            types.sdgTarget[post.sdg_target]
          }}</span>
          <span v-else class="italic">Missing</span>
        </div>
        <div v-else>
          <span v-if="post.text">{{ post.text }}</span>
          <span v-else class="italic">Missing</span>
        </div>
      </div>
      <span v-if="post.percentage" class="text-sm"
        >({{ roundFloat(post.percentage) }}%)</span
      >
    </div>
    <div class="country_budget_items ml-5">
      <table>
        <tr class="multiline">
          <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
          <td>
            <div
              v-for="(narrative, k) in post.narrative"
              :key="k"
              :class="{ 'mb-0': k !== post.narrative - 1 }"
            >
              <div v-if="narrative.narrative" class="flex flex-col">
                <span v-if="narrative.language" class="language top"
                  >(Language: {{ types.languages[narrative.language] }})</span
                >
                <span class="description">{{ narrative.narrative }}</span>
              </div>
              <span v-else class="italic">Missing</span>
            </div>
          </td>
        </tr>
        <tr
          v-if="
            post.sector_vocabulary === '98' || post.sector_vocabulary === '99'
          "
        >
          <td>Vocabulary URI</td>
          <td>
            <a
              v-if="post.vocabulary_uri"
              target="_blank"
              :href="post.vocabulary_uri"
              >{{ post.vocabulary_uri }}</a
            >
            <span v-else class="italic">Missing</span>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import { getTranslatedElement } from '../../../composable/utils';

export default defineComponent({
  name: 'ActivitySector',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      sectorVocabulary: [];
      sectorCode: [];
      sectorCategory: [];
      sdgGoals: [];
      sdgTarget: [];
      languages: [];
    }

    const types = inject('types') as Types;

    function roundFloat(num: string) {
      return parseFloat(num).toFixed(2);
    }

    return { types, roundFloat };
  },
  methods: { getTranslatedElement },
});
</script>
