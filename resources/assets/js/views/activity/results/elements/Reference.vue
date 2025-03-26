<template>
  <div v-if="!isEveryValueNull(referenceData)">
    <div
      v-for="(ref, r) in referenceData"
      :key="r"
      class="item elements-detail"
      :class="{ 'mb-4': Number(r) !== data.length - 1 }"
    >
      <div class="category flex">{{ type[ref.vocabulary] }}</div>
      <div class="ml-4">
        <table class="mb-3">
          <tbody>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'code') }}</td>
              <td>
                {{ ref.code ? ref.code : '' }}
                <span v-if="!ref.code" class="text-xs italic text-light-gray"
                  >N/A</span
                >
              </td>
            </tr>
            <tr>
              <td>
                {{ getTranslatedElement(translatedData, 'vocabulary_uri') }}
              </td>
              <td>
                <a
                  v-if="ref.vocabulary_uri"
                  target="_blank"
                  :href="ref.vocabulary_uri"
                  >{{ ref.vocabulary_uri }}</a
                >
                <span v-else>
                  <span class="text-xs italic text-light-gray">N/A</span>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div v-else><span class="text-xs italic text-light-gray">N/A</span></div>
</template>

<script lang="ts">
import { getTranslatedElement, isEveryValueNull } from 'Composable/utils';
import { defineComponent, inject, toRefs } from 'vue';

export default defineComponent({
  name: 'ResultReference',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
    type: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    /**
     * Typescript interface
     */
    interface ReferenceArray {
      [index: number]: {
        vocabulary: number;
        code: string;
        vocabulary_uri: string;
      };
    }

    const translatedData = inject('translatedData') as Record<string, string>;
    let { data } = toRefs(props);
    const referenceData = data.value as ReferenceArray;

    return { referenceData, isEveryValueNull, translatedData };
  },
  methods: { getTranslatedElement },
});
</script>
