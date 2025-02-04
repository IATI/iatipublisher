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
      <span v-else class="italic">Vocabulary Missing</span>
    </div>
    <div class="text-sm">
      <div v-if="post.policy_marker_vocabulary == '1'">
        <span v-if="post.policy_marker">
          {{ types.policyMarker[post.policy_marker] }}
        </span>
        <span v-else class="italic">Missing</span>
      </div>
      <div v-else>
        <span v-if="post.policy_marker_text">{{
          post.policy_marker_text
        }}</span>
        <span v-else class="italic">Missing</span>
      </div>
    </div>
    <table class="ml-5">
      <tbody>
        <tr v-if="post.policy_marker_vocabulary == '99'">
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
        <tr>
          <td>Significance</td>
          <td>
            <span v-if="post.significance">{{
              types.policySignificance[post.significance]
            }}</span>
            <span v-else class="italic">Missing</span>
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
                  >(Language: {{ types.languages[narrative.language] }})</span
                >
                <span class="description">{{ narrative.narrative }}</span>
              </div>
              <span v-else class="italic">Missing</span>
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

    return { types, dateFormat };
  },
});
</script>
