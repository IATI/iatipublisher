<template>
  <div class="elements-detail">
    <div v-if="!isEveryValueNull(atData)">
      <div
        v-for="(at, i) in atData"
        :key="i"
        class="item"
        :class="{
          'mb-4': i !== Object.keys(atData).length - 1,
        }"
      >
        <div class="category">
          <span>{{
            type.aidTypeVocabulary[at.aid_type_vocabulary] ?? ''
          }}</span>
          <span
            v-if="!type.aidTypeVocabulary[at.aid_type_vocabulary]"
            class="text-xs italic text-light-gray"
          >
            N/A
          </span>
        </div>
        <div class="ml-4">
          <table class="mb-3">
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'code') }}</td>
              <td>
                <div class="text-sm">
                  <span v-if="at.aid_type_code">
                    {{ type.aidType[at.aid_type_code] }}
                  </span>
                  <span v-else-if="at.cash_and_voucher_modalities">
                    {{
                      type.cashAndVoucherModalities[
                        at.cash_and_voucher_modalities
                      ]
                    }}
                  </span>
                  <span v-else-if="at.earmarking_category">
                    {{ type.earMarkingCategory[at.earmarking_category] }}
                  </span>
                  <span v-else-if="at.earmarking_modality">
                    {{ type.earMarkingModality[at.earmarking_modality] }}
                  </span>
                  <span v-else>
                    <span class="text-xs italic text-light-gray">N/A</span>
                  </span>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div v-else>
      <span class="text-xs italic text-light-gray">N/A</span>
    </div>
  </div>
</template>

<script lang="ts">
import { getTranslatedElement, isEveryValueNull } from 'Composable/utils';
import { defineComponent, toRefs, inject } from 'vue';

export default defineComponent({
  name: 'TransactionAidType',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    interface ArrayObject {
      aid_type_code: string;
      aid_type_vocabulary: string;
      cash_and_voucher_modalities: string;
      earmarking_category: string;
      earmarking_modality: string;
    }

    interface TypesInterface {
      aidType: [];
      aidTypeVocabulary: [];
      cashAndVoucherModalities: [];
      earMarkingCategory: [];
      earMarkingModality: [];
    }

    const { data } = toRefs(props);
    const atData = data.value as ArrayObject[];

    const type = inject('types') as TypesInterface;
    const translatedData = inject('translatedData') as Record<string, string>;

    return {
      atData,
      type,
      isEveryValueNull,
      translatedData,
    };
  },
  methods: { getTranslatedElement },
});
</script>
