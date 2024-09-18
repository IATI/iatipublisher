<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail spacious"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div
      v-for="(item, i) in post.name"
      :key="i"
      :class="{ 'mb-4': i !== post.name.length - 1 }"
    >
      <div
        v-for="(narrative, j) in item.narrative"
        :key="j"
        class="text-sm"
        :class="{ 'mb-4': j !== item.narrative.length - 1 }"
      >
        <div v-if="narrative.narrative" class="flex flex-col-reverse space-x-1">
          <span>{{ narrative.narrative }}</span>
          <span v-if="narrative.language" class="italic text-n-30"
            >({{ getTranslatedLanguage(translatedData) }}:
            {{ types.languages[narrative.language] }})</span
          >
        </div>
        <span v-else class="text-xs italic text-light-gray"
          >{{ getTranslatedElement(translatedData, 'name') }} N/A</span
        >
      </div>
    </div>
    <div class="ml-5">
      <table>
        <tr>
          <td>{{ getTranslatedElement(translatedData, 'reference') }}</td>
          <td class="text-sm">
            <span v-if="post.ref">{{ post.ref }}</span>
            <span v-else class="text-xs italic text-light-gray">N/A</span>
          </td>
        </tr>
      </table>
    </div>
    <div
      v-for="(item, i) in post.location_reach"
      :key="i"
      class="ml-5"
      :class="{ 'mb-0': i !== post.location_reach.length - 1 }"
    >
      <table>
        <tr>
          <td>{{ getTranslatedElement(translatedData, 'location_reach') }}</td>
          <td>
            <span v-if="item.code">{{
              types.geographicLocationReach[item.code]
            }}</span>
            <span v-else class="text-xs italic text-light-gray">N/A</span>
          </td>
        </tr>
      </table>
    </div>
    <div class="ml-5">
      <div
        v-for="(item, i) in post.location_id"
        :key="i"
        :class="{ 'mb-4': i !== post.location_id.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'location_id') }}</td>
            <td v-if="!isEveryValueNull(item)">
              <div class="flex space-x-1">
                <div class="value">
                  <span v-if="item.vocabulary"
                    >{{ types.geographicVocabulary[item.vocabulary] }},
                  </span>
                  <span v-else class="text-xs italic text-light-gray"
                    >({{
                      getTranslatedElement(translatedData, 'vocabulary')
                    }}
                    N/A)</span
                  >
                </div>
                <div>
                  <span v-if="item.code">code {{ item.code }}</span>
                  <span v-else class="text-xs italic text-light-gray"
                    >({{
                      getTranslatedElement(translatedData, 'code')
                    }}
                    N/A)</span
                  >
                </div>
              </div>
            </td>
            <td v-else>
              <span class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
      <div
        v-for="(item, i) in post.description"
        :key="i"
        :class="{ 'mb-4': i !== post.description.length - 1 }"
      >
        <div
          v-for="(narrative, j) in item.narrative"
          :key="j"
          :class="{ 'mb-4': j !== item.narrative.length - 1 }"
        >
          <table class="w-full">
            <tr class="multiline">
              <td>{{ getTranslatedElement(translatedData, 'description') }}</td>
              <td>
                <div v-if="narrative.narrative" class="flex flex-col">
                  <span
                    v-if="narrative.language"
                    class="language top subtle-darker"
                    >({{ getTranslatedLanguage(translatedData) }}:
                    {{ types.languages[narrative.language] }})</span
                  >
                  <span class="description">{{ narrative.narrative }}</span>
                </div>
                <span v-else class="text-xs italic text-light-gray">N/A</span>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div
        v-for="(item, i) in post.activity_description"
        :key="i"
        :class="{ 'mb-4': i !== post.activity_description.length - 1 }"
      >
        <div
          v-for="(narrative, j) in item.narrative"
          :key="j"
          :class="{ 'mb-4': j !== item.narrative.length - 1 }"
        >
          <table class="w-full">
            <tr class="multiline">
              <td>
                {{
                  getTranslatedElement(translatedData, 'activity_description')
                }}
              </td>
              <td>
                <div v-if="narrative.narrative" class="flex flex-col">
                  <span
                    v-if="narrative.language"
                    class="language top subtle-darker"
                    >({{ getTranslatedLanguage(translatedData) }}:
                    {{ types.languages[narrative.language] }})</span
                  >
                  <span class="description">{{ narrative.narrative }}</span>
                </div>
                <span v-else class="text-xs italic text-light-gray">N/A</span>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div
        v-for="(item, i) in post.administrative"
        :key="i"
        :class="{ 'mb-4': i !== post.administrative.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'administrative') }}
            </td>
            <td v-if="!isEveryValueNull(item)">
              <div class="flex">
                <div>
                  <span v-if="item.vocabulary"
                    >Vocabulary -
                    {{ types.geographicVocabulary[item.vocabulary] }}
                  </span>
                  <span v-else class="text-xs italic text-light-gray"
                    >({{
                      getTranslatedElement(translatedData, 'vocabulary')
                    }}
                    N/A)</span
                  >
                </div>
                <div>
                  <span v-if="item.code"
                    >, code {{ types.country[item.code] }}</span
                  >
                  <span v-else class="ml-1 text-xs italic text-light-gray">
                    ({{
                      getTranslatedElement(translatedData, 'code')
                    }}
                    N/A)</span
                  >
                </div>
                <div>
                  <span v-if="item.level">, level {{ item.level }}</span>
                  <span v-else class="ml-1 text-xs italic text-light-gray">
                    ({{
                      getTranslatedElement(translatedData, 'level')
                    }}
                    N/A)</span
                  >
                </div>
              </div>
            </td>
            <td v-else>
              <span class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
      <div
        v-for="(item, i) in post.point"
        :key="i"
        class="flex space-x-1"
        :class="{ 'mb-4': i !== post.point.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'point') }}</td>
            <td v-if="!isEveryValueNull(item)">
              <div class="flex space-x-1">
                <div>
                  <span v-if="item.srs_name">({{ item.srs_name }})</span>
                  <span v-else class="text-xs italic text-light-gray">
                    ({{
                      getTranslatedElement(translatedData, 'srs_name')
                    }}
                    N/A)</span
                  >
                </div>
                <div>
                  <span v-if="item.pos[0].latitude">
                    latitude {{ item.pos[0].latitude }},
                  </span>
                  <span v-else class="text-xs italic text-light-gray">
                    ({{
                      getTranslatedElement(translatedData, 'latitude')
                    }}
                    N/A)</span
                  >
                </div>
                <div>
                  <span v-if="item.pos[0].longitude"
                    >longitude {{ item.pos[0].longitude }}</span
                  >
                  <span v-else class="text-xs italic text-light-gray">
                    ({{
                      getTranslatedElement(translatedData, 'longitude')
                    }}
                    N/A)</span
                  >
                </div>
              </div>
            </td>
            <td v-else>
              <span class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
      <div
        v-for="(item, i) in post.exactness"
        :key="i"
        :class="{ 'mb-4': i !== post.exactness.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'exactness') }}</td>
            <td>
              <span v-if="item.code">{{
                types.geographicExactness[item.code]
              }}</span>
              <span v-else class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
      <div
        v-for="(item, i) in post.location_class"
        :key="i"
        :class="{ 'mb-4': i !== post.location_class.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'location_class') }}
            </td>
            <td>
              <span v-if="item.code">{{
                types.geographicLocationClass[item.code]
              }}</span>
              <span v-else class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
      <div
        v-for="(item, i) in post.feature_designation"
        :key="i"
        :class="{ 'mb-4': i !== post.feature_designation.length - 1 }"
      >
        <table class="w-full">
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'feature_designation') }}
            </td>
            <td>
              <span v-if="item.code">{{ types.locationType[item.code] }}</span>
              <span v-else class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  getTranslatedElement,
  getTranslatedLanguage,
  isEveryValueNull,
} from 'Composable/utils';
import { defineComponent, inject } from 'vue';

export default defineComponent({
  name: 'ActivityLocation',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      geographicVocabulary: [];
      geographicLocationReach: [];
      country: [];
      geographicExactness: [];
      geographicLocationClass: [];
      locationType: [];
      languages: [];
    }
    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, isEveryValueNull, translatedData };
  },
  methods: { getTranslatedElement, getTranslatedLanguage },
});
</script>
