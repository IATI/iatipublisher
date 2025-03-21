<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category">
      <span>
        {{
          types.budgetType[post.planned_disbursement_type] ??
          getTranslatedMissing(translatedData, 'type')
        }}
      </span>
    </div>

    <div class="mb-4 ml-5">
      <div class="category">
        <span>{{ getTranslatedElement(translatedData, 'value') }}</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'value_amount') }}</td>
            <td>
              {{
                post.value[0].amount
                  ? Number(post.value[0].amount).toLocaleString() +
                    ' ' +
                    types.currency[post.value[0].currency]
                  : getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'value_date') }}</td>
            <td>
              {{
                post.value[0].value_date
                  ? formatDate(post.value[0].value_date)
                  : getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'period_start') }}</td>
            <td>
              <span>
                {{
                  post.period_start[0].date
                    ? formatDate(post.period_start[0].date)
                    : getTranslatedMissing(translatedData, 'date')
                }}
              </span>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'period_end') }}</td>
            <td>
              <span>
                {{
                  post.period_end[0].date
                    ? formatDate(post.period_end[0].date)
                    : getTranslatedMissing(translatedData, 'date')
                }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="post.provider_org" class="mb-4 ml-5">
      <div class="category">
        <span>{{ getTranslatedElement(translatedData, 'provider_org') }}</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'type') }}</td>
            <td>
              {{
                post.provider_org[0].type
                  ? types.organizationType[post.provider_org[0].type]
                  : getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'provider_activity_id') }}
            </td>
            <td>
              {{
                post.provider_org[0].provider_activity_id ??
                getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'reference') }}</td>
            <td>
              {{
                post.provider_org[0].ref ?? getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.provider_org[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.provider_org[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  (Language:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="post.receiver_org" class="ml-5">
      <div class="category">
        <span>{{ getTranslatedElement(translatedData, 'receiver_org') }}</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'type') }}</td>
            <td>
              {{
                post.receiver_org[0].type
                  ? types.organizationType[post.receiver_org[0].type]
                  : getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'receiver_activity_id') }}
            </td>
            <td>
              {{
                post.receiver_org[0].receiver_activity_id ??
                getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'reference') }}</td>
            <td>
              {{
                post.receiver_org[0].ref ?? getTranslatedMissing(translatedData)
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'narrative') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.receiver_org[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.receiver_org[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  ({{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
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
import moment from 'moment';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

defineProps({
  data: {
    type: Object,
    required: true,
  },
});

interface Types {
  budgetType: [];
  languages: [];
  currency: [];
  organizationType: [];
}

function formatDate(date: Date) {
  return moment(date).format('LL');
}

const types = inject('types') as Types;
const translatedData = inject('translatedData') as Record<string, string>;
</script>
