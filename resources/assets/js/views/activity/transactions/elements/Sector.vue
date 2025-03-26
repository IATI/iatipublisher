<template>
  <div class="elements-detail wider">
    <div
      v-for="(sec, s) in sector"
      :key="s"
      class="item"
      :class="{
        'mb-4': s !== Object.keys(sector).length - 1,
      }"
    >
      <div class="category">
        <span>{{
          sec.sector_vocabulary
            ? type.sectorVocabulary[sec.sector_vocabulary]
            : getTranslatedMissing(translatedData, 'vocabulary')
        }}</span>
      </div>
      <div class="ml-4">
        <table class="mb-3">
          <tbody>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'code') }}</td>
              <td>
                <div class="text-sm">
                  <span v-if="sec.text">
                    {{ sec.text ?? '' }}
                    <span
                      v-if="!sec.text"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </span>
                  <span v-else-if="sec.code">
                    {{ sec.code ? type.sectorCode[sec.code] : '' }}
                    <span
                      v-if="!sec.code"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </span>
                  <span v-else-if="sec.category_code">
                    {{
                      sec.category_code
                        ? type.sectorCategory[sec.category_code]
                        : ''
                    }}
                    <span
                      v-if="!sec.category_code"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </span>
                  <span v-else-if="sec.sdg_goal">
                    {{ sec.sdg_goal ? type.unsdgGoals[sec.sdg_goal] : '' }}
                    <span
                      v-if="!sec.sdg_goal"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </span>
                  <span v-else-if="sec.sdg_target">
                    {{
                      sec.sdg_target ? type.unsdgTargets[sec.sdg_target] : ''
                    }}
                    <span
                      v-if="!sec.sdg_target"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </span>
                  <span v-else>
                    <span class="text-xs italic text-light-gray">N/A</span>
                  </span>
                </div>
              </td>
            </tr>
            <tr
              v-if="
                sec.sector_vocabulary === '98' || sec.sector_vocabulary === '99'
              "
            >
              <td>
                {{ getTranslatedElement(translatedData, 'vocabulary_uri') }}
              </td>
              <td>
                <div class="text-sm">
                  <span v-if="sec.vocabulary_uri">
                    <a :href="sec.vocabulary_uri" target="_blank">
                      {{ sec.vocabulary_uri }}
                    </a>
                  </span>
                  <span v-else>
                    <span class="text-xs italic text-light-gray">N/A</span>
                  </span>
                </div>
              </td>
            </tr>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'description') }}</td>
              <td>
                <div
                  v-for="(sd, i) in sec.narrative"
                  :key="i"
                  class="title-content mb-4"
                  :class="{
                    'mb-4': i !== sec.narrative.length - 1,
                  }"
                >
                  <div
                    v-if="sd.narrative"
                    class="language subtle-darker mb-1.5"
                  >
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
  name: 'TransactionSector',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    interface Sector {
      sectorVocabulary: object;
      sectorCode: {};
      sectorCategory: {};
      unsdgGoals: {};
      unsdgTargets: {};
      languages: {};
    }

    interface ArrayObject {
      category_code: string;
      code: string;
      narrative: [{ language: string; narrative: string }];
      sdg_goal: string;
      sdg_target: string;
      sector_vocabulary: string;
      text: string;
      vocabulary_uri: string;
    }

    const { data } = toRefs(props);
    const sector = data.value as ArrayObject[];

    const type = inject('types') as Sector;
    const translatedData = inject('translatedData') as Record<string, string>;

    return {
      sector,
      type,
      translatedData,
    };
  },
  methods: {
    getTranslatedMissing,
    getTranslatedElement,
    getTranslatedLanguage,
  },
});
</script>
