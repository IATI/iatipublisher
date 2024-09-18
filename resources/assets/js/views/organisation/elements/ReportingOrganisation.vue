<template>
  <div
    v-for="(reporting_org, index) in content"
    :key="index"
    class="item"
    :class="{
      'mb-4 border-b border-n-20 pb-4': Number(index) != content.length - 1,
    }"
  >
    <div class="elements-detail mb-4">
      <div class="category">
        <span>{{
          reporting_org.type
            ? types?.organizationType[reporting_org.type]
            : getTranslatedMissing(translatedData, 'type')
        }}</span>
      </div>
      <table>
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'reference') }}</td>
            <td>
              {{
                reporting_org.ref ??
                getTranslatedMissing(translatedData, 'reference')
              }}
            </td>
          </tr>
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'secondary_reporter') }}
            </td>
            <td>
              {{
                parseInt(reporting_org.secondary_reporter)
                  ? translatedData['common.common.true']
                  : reporting_org.secondary_reporter === '0'
                  ? translatedData['common.common.false']
                  : getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'name') }}</td>
            <td>
              <div
                v-for="(narrative, j) in reporting_org.narrative"
                :key="j"
                class="description-content"
                :class="{
                  'mb-4': j != reporting_org.narrative.length - 1,
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
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';
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
}

const types = inject('orgTypes') as TypesInterface;
const translatedData = inject('translatedData') as Record<string, string>;
</script>
