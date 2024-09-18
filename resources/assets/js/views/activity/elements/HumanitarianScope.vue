<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category">
      <span v-if="post.type">
        {{
          types.humanitarianScopeType[post.type] ??
          getTranslatedMissing(translatedData)
        }}
      </span>
      <span v-else>Vocabulary {{ getTranslatedMissing(translatedData) }}</span>
    </div>
    <div class="ml-5">
      <table>
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'vocabulary') }}</td>
            <td>
              {{
                types.humanitarianScopeVocabulary[post.vocabulary] ??
                getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr v-if="post.vocabulary === '99'">
            <td>
              {{ getTranslatedElement(translatedData, 'vocabulary_uri') }}
            </td>
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
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'code') }}</td>
            <td>
              {{ post.code ?? getTranslatedMissing(translatedData) }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.narrative"
                :key="k"
                class="description-content"
                :class="{ 'mb-4': k !== post.narrative.length - 1 }"
              >
                <div class="language mb-1.5">
                  ( {{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

defineProps({
  data: {
    type: Object,
    required: true,
  },
});

interface Types {
  humanitarianScopeVocabulary: [];
  languages: [];
  humanitarianScopeType: [];
}

const types = inject('types') as Types;
const translatedData = inject('translatedData') as Record<string, string>;
</script>
