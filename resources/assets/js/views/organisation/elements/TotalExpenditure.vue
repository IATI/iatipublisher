<template>
  <div
    v-for="(total_expenditure, index) in content"
    :key="index"
    :class="{
      'mb-4 border-b border-n-20 pb-4': Number(index) != content.length - 1,
    }"
  >
    <div class="elements-detail mb-4">
      <div class="ml-2 flex text-sm">
        <span v-if="total_expenditure.value[0].amount">
          {{
            total_expenditure.value['0'].amount
              ? Number(total_expenditure.value[0].amount).toLocaleString()
              : getTranslatedMissing(translatedData, 'budget')
          }}
          {{ total_expenditure.value['0'].currency }}
        </span>
        <span v-else>
          {{ getTranslatedMissing(translatedData, 'expenditure_amount') }}</span
        >
      </div>
      <div class="ml-4">
        <table>
          <tbody>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'period') }}</td>
              <td>
                {{ formatDate(total_expenditure.period_start['0'].date) }}
                -
                {{ formatDate(total_expenditure.period_end['0'].date) }}
              </td>
            </tr>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'value_date') }}</td>
              <td>
                {{ formatDate(total_expenditure.value['0'].value_date) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="indicator overflow-hidden rounded-t-lg border border-n-20">
      <div class="head flex items-center border-b border-n-20 px-6 py-2">
        <span class="text-xs font-bold text-n-50">{{
          getTranslatedElement(translatedData, 'expense_line')
        }}</span>
      </div>
      <div
        v-for="(expense_line, j) in total_expenditure.expense_line"
        :key="j"
        class="item"
        :class="{
          'mb-2 border-b border-n-20':
            j !== total_expenditure.expense_line.length - 1,
        }"
      >
        <div class="indicator-content flex px-6 py-2">
          <div class="elements-detail grow">
            <div class="category flex">
              <span v-if="expense_line.value['0'].amount">
                {{ Number(expense_line.value['0'].amount).toLocaleString() }}
                {{ expense_line.value['0'].currency }}
              </span>
              <span v-else>{{
                getTranslatedMissing(translatedData, 'expenditure_line')
              }}</span>
            </div>
            <div class="ml-4">
              <table>
                <tbody>
                  <tr>
                    <td>
                      {{ getTranslatedElement(translatedData, 'reference') }}
                    </td>
                    <td>
                      {{
                        expense_line.ref ??
                        getTranslatedMissing(translatedData, 'reference')
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ getTranslatedElement(translatedData, 'value_date') }}
                    </td>
                    <td>
                      {{ formatDate(expense_line.value['0'].value_date) }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ getTranslatedElement(translatedData, 'narrative') }}
                    </td>
                    <td>
                      <div
                        v-for="(narrative, k) in expense_line.narrative"
                        :key="k"
                        class="description-content"
                        :class="{
                          'mb-4': k != expense_line.narrative.length - 1,
                        }"
                      >
                        <div class="language mb-1.5">
                          ({{
                            narrative.language
                              ? `${getTranslatedLanguage(translatedData)} : ${
                                  types?.languages[narrative.language]
                                }`
                              : `${getTranslatedLanguage(
                                  translatedData
                                )} : ${getTranslatedMissing(translatedData)}`
                          }})
                        </div>
                        <div class="w-[500px] max-w-full">
                          {{
                            narrative.narrative ??
                            getTranslatedMissing(translatedData, 'narrative')
                          }}
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';
import moment from 'moment';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

defineProps({
  content: { type: Object, required: true },
});

interface TypesInterface {
  languages: [];
  budgetType: [];
  regionVocabulary: [];
  country: [];
}

const types = inject('orgTypes') as TypesInterface;
const translatedData = inject('translatedData') as Record<string, string>;

function formatDate(date: Date) {
  return date
    ? moment(date).format('LL')
    : getTranslatedMissing(translatedData, 'date');
}
</script>
