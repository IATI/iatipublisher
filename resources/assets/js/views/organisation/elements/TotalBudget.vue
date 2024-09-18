<template>
  <div
    v-for="(total_budget, index) in content"
    :key="index"
    class="item"
    :class="{
      'mb-4 border-b border-n-20 pb-4': Number(index) != content.length - 1,
    }"
  >
    <div class="elements-detail mb-4">
      <div class="category flex">
        {{
          types?.budgetType[total_budget.total_budget_status] ??
          getTranslatedMissing(translatedData, 'budget_status')
        }}
      </div>
      <div class="flex text-sm">
        <span v-if="total_budget.value[0].amount">
          {{ Number(total_budget.value['0'].amount).toLocaleString() }}
          {{ total_budget.value['0'].currency }}
        </span>
        <span v-else>{{
          getTranslatedElement(translatedData, 'budget_amount')
        }}</span>
      </div>
      <table>
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'period') }}</td>
            <td>
              {{
                formatDate(total_budget.period_start['0'].date) ??
                getTranslatedMissing(translatedData, 'period_start_date')
              }}
              -
              {{
                formatDate(total_budget.period_end['0'].date) ??
                getTranslatedMissing(translatedData, 'period_end_date')
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'value_date') }}</td>
            <td>
              {{
                formatDate(total_budget.value['0'].value_date) ??
                getTranslatedMissing(translatedData, 'value_date')
              }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="indicator overflow-hidden rounded-t-lg border border-n-20">
      <div class="head flex items-center border-b border-n-20 px-6 py-2">
        <span class="text-xs font-bold text-n-50">{{
          getTranslatedElement(translatedData, 'budget_line')
        }}</span>
      </div>
      <div
        v-for="(budget_line, j) in total_budget.budget_line"
        :key="j"
        :class="{
          'mb-2 border-b border-n-20':
            j !== total_budget.budget_line.length - 1,
        }"
      >
        <div class="indicator-content flex px-6 py-2">
          <div class="elements-detail grow">
            <div class="category flex">
              <span>
                {{ Number(budget_line.value['0'].amount).toLocaleString() }}
                {{ budget_line.value['0'].currency }}
              </span>
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
                        budget_line.ref ??
                        getTranslatedMissing(translatedData, 'reference')
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ getTranslatedElement(translatedData, 'value_date') }}
                    </td>
                    <td>
                      {{
                        formatDate(budget_line.value['0'].value_date) ??
                        getTranslatedMissing(translatedData, 'value_date')
                      }}
                    </td>
                  </tr>
                  <tr>
                    <td>
                      {{ getTranslatedElement(translatedData, 'narrative') }}
                    </td>
                    <td>
                      <div
                        v-for="(narrative, k) in budget_line.narrative"
                        :key="k"
                        class="description-content"
                        :class="{
                          'mb-4': k != budget_line.narrative.length - 1,
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
  organizationType: [];
  budgetType: [];
}

const types = inject('orgTypes') as TypesInterface;
const translatedData = inject('translatedData') as Record<string, string>;

function formatDate(date: Date) {
  return date
    ? moment(date).format('LL')
    : getTranslatedMissing(translatedData, 'date');
}
</script>
