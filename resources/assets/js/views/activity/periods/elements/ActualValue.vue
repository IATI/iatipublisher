<template>
  <div class="target ml-4 mt-6">
    <div v-for="(tValue, v) in actualValue" :key="v" class="item">
      <table class="mb-3 w-full">
        <tbody>
          <tr>
            <td>
              <span class="category flex">{{
                getTranslatedElement(translatedData, 'actual_value')
              }}</span>
            </td>
            <td v-if="!isEveryValueNull(tValue)">
              <div :class="elementSpacing">
                {{ tValue.value ?? '' }}
                <span
                  v-if="!tValue.value"
                  class="text-xs italic text-light-gray"
                  >N/A</span
                >
              </div>

              <div class="flex" :class="elementSpacing">
                <div>
                  {{
                    getTranslatedElement(translatedData, 'location_reference')
                  }}:&nbsp;
                </div>
                <div>
                  {{
                    getLocation(tValue.location)
                      ? getLocation(tValue.location)
                      : ''
                  }}
                  <span
                    v-if="!getLocation(tValue.location)"
                    class="text-xs italic text-light-gray"
                    >N/A</span
                  >
                </div>
              </div>

              <div class="flex" :class="elementSpacing">
                <div>
                  {{ getTranslatedElement(translatedData, 'dimension') }}:&nbsp;
                </div>
                <div>
                  <div
                    v-for="(dim, d) in tValue.dimension"
                    :key="d"
                    class="dimension"
                  >
                    {{ dim.name ?? '' }}
                    <span
                      v-if="!dim.name"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                    ({{ dim.value ?? ''
                    }}<span
                      v-if="!dim.value"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >)
                  </div>
                </div>
              </div>

              <div class="flex" :class="elementSpacing">
                <div>
                  {{ getTranslatedElement(translatedData, 'comment') }}:&nbsp;
                </div>
                <div>
                  <div
                    v-for="(com, c) in tValue.comment[0].narrative"
                    :key="c"
                    class="description-content"
                    :class="{
                      'mb-1.5': c !== tValue.comment[0].narrative.length - 1,
                    }"
                  >
                    <div class="language subtle-darker mb-1.5">
                      ({{ getTranslatedLanguage(translatedData) }}:
                      {{ com.language ? dlType.language[com.language] : '' }}
                      <span
                        v-if="!com.language"
                        class="text-xs italic text-light-gray"
                        >N/A</span
                      >)
                    </div>
                    <div class="w-[500px] max-w-full">
                      {{ com.narrative ? com.narrative : '' }}
                      <span
                        v-if="!com.narrative"
                        class="text-xs italic text-light-gray"
                        >N/A</span
                      >
                    </div>
                  </div>
                </div>
              </div>
            </td>
            <td v-else>
              <span class="text-xs italic text-light-gray">N/A</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!isEveryValueNull(tValue.document_link)">
        <table class="mb-3 w-full">
          <tbody>
            <tr>
              <td colspan="2">
                <div class="category flex">
                  {{ getTranslatedElement(translatedData, 'document_link') }}
                </div>
                <div
                  class="divider my-4 h-px w-full border-b border-n-20"
                ></div>
              </td>
            </tr>
          </tbody>
        </table>
        <DocumentLink :data="tValue.document_link" :type="dlType" />
      </div>
      <div v-else>
        <table class="mb-3 w-full">
          <tbody>
            <tr>
              <td>
                <div class="category flex">
                  {{ getTranslatedElement(translatedData, 'document_link') }}
                </div>
              </td>
              <td>
                <span class="text-xs italic text-light-gray">N/A</span>
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

// component
import { DocumentLink } from 'Activity/indicators/elements/Index';

//composable
import {
  getLocation,
  getTranslatedElement,
  getTranslatedLanguage,
  isEveryValueNull,
} from 'Composable/utils';

export default defineComponent({
  name: 'ActualValue',
  components: { DocumentLink },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);

    // vue inject
    const translatedData = inject('translatedData') as Record<string, string>;
    const dlType = inject('types');

    const elementSpacing = 'mb-1';
    const actualValue = data.value;

    return {
      actualValue,
      elementSpacing,
      location,
      getLocation,
      dlType,
      isEveryValueNull,
      translatedData,
    };
  },
  methods: { getTranslatedLanguage, getTranslatedElement },
});
</script>
