<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category">
      <span v-if="post.tag_vocabulary">{{
        types.tagVocabulary[post.tag_vocabulary]
      }}</span>
      <span v-else class="italic">Vocabulary Missing</span>
    </div>
    <div class="max-w-[887px] text-sm">
      <span v-if="post.tag_vocabulary === '1' || post.tag_vocabulary === '99'">
        <span v-if="post.tag_text">{{ post.tag_text }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData)
        }}</span>
      </span>
      <span v-if="post.tag_vocabulary === '2'">
        <span v-if="post.goals_tag_code">{{
          types.sdgGoals[post.goals_tag_code]
        }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData)
        }}</span>
      </span>
      <span v-if="post.tag_vocabulary === '3'">
        <span v-if="post.targets_tag_code">{{
          types.sdgTarget[post.targets_tag_code]
        }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData)
        }}</span>
      </span>
    </div>
    <table class="ml-5">
      <tbody>
        <tr v-if="post.tag_vocabulary === '99'">
          <td>Vocabulary URI</td>
          <td>
            <a
              v-if="post.vocabulary_uri"
              target="_blank"
              :href="post.vocabulary_uri"
            >
              {{ post.vocabulary_uri }}
            </a>
            <span v-else class="italic">{{
              getTranslatedMissing(translatedData)
            }}</span>
          </td>
        </tr>
        <tr
          v-if="post?.narrative"
          class="multiline"
          :class="{ 'mb-4': k !== post.narrative.length - 1 }"
        >
          <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
          <td>
            <div v-for="(narrative, k) in post.narrative" :key="k">
              <div v-if="narrative.narrative" class="flex flex-col">
                <span v-if="narrative.language" class="language top"
                  >({{ getTranslatedLanguage(translatedData) }}:
                  {{ types.languages[narrative.language] }})</span
                >
                <span class="description">{{ narrative.narrative }}</span>
              </div>
              <span v-else class="italic">{{
                getTranslatedMissing(translatedData)
              }}</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import dateFormat from 'Composable/dateFormat';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from '../../../composable/utils';

export default defineComponent({
  name: 'ActivityTag',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      tagVocabulary: [];
      sdgGoals: [];
      sdgTarget: [];
      languages: [];
    }

    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, dateFormat, translatedData };
  },
  methods: {
    getTranslatedElement,
    getTranslatedLanguage,
    getTranslatedMissing,
  },
});
</script>
