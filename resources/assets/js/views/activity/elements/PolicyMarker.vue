<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category">
      <span v-if="post.policy_marker_vocabulary">{{
        types.policyMarkerVocabulary[post.policy_marker_vocabulary]
      }}</span>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData, 'vocabulary')
      }}</span>
    </div>
    <div class="text-sm">
      <div v-if="post.policy_marker_vocabulary == '1'">
        <span v-if="post.policy_marker">
          {{ types.policyMarker[post.policy_marker] }}
        </span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData)
        }}</span>
      </div>
      <div v-else>
        <span v-if="post.policy_marker_text">{{
          post.policy_marker_text
        }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData)
        }}</span>
      </div>
    </div>
    <table class="ml-5">
      <tbody>
        <tr v-if="post.policy_marker_vocabulary == '99'">
          <td>{{ getTranslatedElement(translatedData, 'vocabulary_uri') }}</td>
          <td>
            <a
              v-if="post.vocabulary_uri"
              target="_blank"
              :href="post.vocabulary_uri"
              >{{ post.vocabulary_uri }}</a
            >
            <span v-else class="italic">{{
              getTranslatedMissing(translatedData)
            }}</span>
          </td>
        </tr>
        <tr>
          <td>{{ getTranslatedElement(translatedData, 'significance') }}</td>
          <td>
            <span v-if="post.significance">{{
              types.policySignificance[post.significance]
            }}</span>
            <span v-else class="italic">{{
              getTranslatedMissing(translatedData)
            }}</span>
          </td>
        </tr>
        <tr
          class="multiline"
          :class="{ 'mb-4': k !== post.narrative.length - 1 }"
        >
          <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
          <td>
            <div v-for="(narrative, k) in post.narrative" :key="k">
              <div v-if="narrative.narrative" class="flex flex-col">
                <span v-if="narrative.language" class="language top"
                  >({{ getTranslatedLanguage(translatedData) }} :
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
} from 'Composable/utils';

export default defineComponent({
  name: 'PolicyMarker',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      policyMarkerVocabulary: [];
      policyMarker: [];
      policySignificance: [];
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
