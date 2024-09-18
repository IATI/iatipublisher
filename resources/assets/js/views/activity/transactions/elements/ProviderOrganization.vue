<template>
  <div class="elements-detail wider">
    <div class="ml-4">
      <table class="mb-3">
        <tbody>
          <tr>
            <td>
              {{
                getTranslatedElement(
                  translatedData,
                  'organisation_identifier_code'
                )
              }}
            </td>
            <td>
              <div class="text-sm">
                {{ PoData[0].organization_identifier_code ?? '' }}
                <span
                  v-if="!PoData[0].organization_identifier_code"
                  class="text-xs italic text-light-gray"
                  >N/A</span
                >
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'description') }}</td>
            <td>
              <div
                v-for="(po, i) in PoData[0].narrative"
                :key="i"
                class="title-content mb-4"
                :class="{
                  'mb-4': i !== PoData[0].narrative.length - 1,
                }"
              >
                <div v-if="po.narrative" class="language mb-1.5">
                  (
                  {{
                    po.language
                      ? `${getTranslatedLanguage(translatedData)} : ${
                          type.languages[po.language]
                        }`
                      : `${getTranslatedLanguage(
                          translatedData
                        )} : ${getTranslatedMissing(translatedData)}`
                  }})
                </div>
                <div class="text-sm">
                  {{ po.narrative ?? '' }}
                  <span
                    v-if="!po.narrative"
                    class="text-xs italic text-light-gray"
                    >N/A</span
                  >
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'provider_activity_id') }}
            </td>
            <td>
              <div class="text-sm">
                {{ PoData[0].provider_activity_id ?? '' }}
                <span
                  v-if="!PoData[0].provider_activity_id"
                  class="text-xs italic text-light-gray"
                  >N/A</span
                >
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'type') }}</td>
            <td>
              <div class="text-sm">
                {{
                  PoData[0].type ? type.organizationType[PoData[0].type] : ''
                }}
                <span
                  v-if="!PoData[0].type"
                  class="text-xs italic text-light-gray"
                  >N/A</span
                >
              </div>
            </td>
          </tr>
        </tbody>
      </table>
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
  name: 'TransactionProviderOrganisation',
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
        narrative: [{ language: string; narrative: string }];
        organization_identifier_code: string;
        provider_activity_id: string;
        type: string;
      };
    }

    interface TypesInterface {
      organizationType: [];
      languages: [];
    }

    const { data } = toRefs(props);

    const PoData = data.value as ArrayObject;

    const type = inject('types') as TypesInterface;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { PoData, type, translatedData };
  },
  methods: {
    getTranslatedMissing,
    getTranslatedLanguage,
    getTranslatedElement,
  },
});
</script>
