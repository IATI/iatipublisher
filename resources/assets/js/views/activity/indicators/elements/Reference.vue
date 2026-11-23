<template>
  <tr>
    <td>Reference</td>
    <td v-if="!isEveryValueNull(refData)">
      <div
        v-for="(ref, r) in refData"
        :key="r"
        :class="{
          'mb-1.5': Number(r) !== Object.keys(refData).length - 1,
        }"
      >
        <span>
          Vocabulary:
          {{ refType.indicatorVocabulary[ref.vocabulary] ?? '' }}
          <span
            v-if="!refType.indicatorVocabulary[ref.vocabulary]"
            class="text-xs italic text-light-gray"
            >N/A</span
          >,
        </span>
        <span>
          Code: {{ ref.code ?? '' }}
          <span v-if="!ref.code" class="text-xs italic text-light-gray"
            >N/A</span
          >
        </span>
        <span v-if="ref.indicator_uri">
          ,Indicator URI:
          <a target="_blank" :href="ref.indicator_uri">
            {{ ref.indicator_uri }}
          </a>
        </span>
      </div>
    </td>
    <td v-else><span class="text-xs italic text-light-gray">N/A</span></td>
  </tr>
</template>

<script lang="ts">
import { isEveryValueNull } from 'Composable/utils';
import { defineComponent, toRefs } from 'vue';

export default defineComponent({
  name: 'IndicatorReference',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
    refType: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);

    /**
     * Typescript interface
     */
    interface ReferenceArray {
      [index: number]: {
        vocabulary: number;
        code: string;
        indicator_uri: string;
      };
    }
    const refData = data.value as ReferenceArray;
    return { refData, isEveryValueNull };
  },
});
</script>
