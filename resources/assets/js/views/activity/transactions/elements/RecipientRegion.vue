<template>
  <div class="elements-detail wider">
    <div
      v-for="(cou, c) in country"
      :key="c"
      class="item"
      :class="{
        'mb-4': c !== Object.keys(country).length - 1,
      }"
    >
      <div class="category">
        <span>{{ type.regionVocabulary[cou.region_vocabulary] }}</span>
      </div>
      <div class="ml-4">
        <table class="mb-3">
          <tbody>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'code') }}</td>
              <td>
                <span v-if="cou.region_vocabulary === '1'">{{
                  cou.region_code
                    ? type.regionCode[cou.region_code]
                    : getTranslatedMissing(translatedData, 'code')
                }}</span>
                <span v-else
                  >{{ cou.custom_code ?? '' }}
                  <span
                    v-if="!cou.custom_code"
                    class="text-xs italic text-light-gray"
                    >N/A</span
                  >
                </span>
              </td>
            </tr>
            <tr v-if="cou.vocabulary_uri">
              <td>
                {{ getTranslatedElement(translatedData, 'vocabulary_uri') }}
              </td>
              <td>
                <a target="_blank" :href="cou.vocabulary_uri">{{
                  cou.vocabulary_uri
                }}</a>
              </td>
            </tr>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'description') }}</td>
              <td>
                <div
                  v-for="(sd, i) in cou.narrative"
                  :key="i"
                  class="title-content mb-4"
                  :class="{
                    'mb-4': i !== cou.narrative.length - 1,
                  }"
                >
                  <div v-if="sd.narrative" class="language mb-1.5">
                    ({{
                      sd.language
                        ? `${getTranslatedLanguage(translatedData)} : ${
                            type.languages[sd.language]
                          }`
                        : `${getTranslatedLanguage(translatedData)} : N/A`
                    }})
                  </div>
                  <div class="text-sm">
                    {{ sd.narrative ?? '' }}
                    <span
                      v-if="!sd.narrative"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

export default defineComponent({
  name: 'TransactionRecipientRegion',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    interface ArrayObject {
      [index: number]: {
        region_vocabulary: number;
        region_code: string;
        custom_code: string;
        vocabulary_uri: string;
        narrative: [{ language: string; narrative: string }];
      };
    }

    interface TypesInterface {
      regionCode: [];
      languages: [];
    }

    const { data } = toRefs(props);
    const country = data.value as ArrayObject;

    const type = inject('types') as TypesInterface;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { country, type, translatedData };
  },
  methods: {
    getTranslatedLanguage,
    getTranslatedElement,
    getTranslatedMissing,
  },
});
</script>
