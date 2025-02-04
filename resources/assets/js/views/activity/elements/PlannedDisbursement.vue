<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category">
      <span>
        {{ types.budgetType[post.planned_disbursement_type] ?? 'Type Missing' }}
      </span>
    </div>

    <div class="mb-4 ml-5">
      <div class="category">
        <span>Value</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>Value Amount</td>
            <td>
              {{
                post.value[0].amount
                  ? Number(post.value[0].amount).toLocaleString() +
                    ' ' +
                    types.currency[post.value[0].currency]
                  : 'Missing'
              }}
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'value_date') }}</td>
            <td>
              {{
                post.value[0].value_date
                  ? formatDate(post.value[0].value_date)
                  : 'Missing'
              }}
            </td>
          </tr>
          <tr>
            <td>Period Start</td>
            <td>
              <span>
                {{
                  post.period_start[0].date
                    ? formatDate(post.period_start[0].date)
                    : 'Date Missing'
                }}
              </span>
            </td>
          </tr>
          <tr>
            <td>Period End</td>
            <td>
              <span>
                {{
                  post.period_end[0].date
                    ? formatDate(post.period_end[0].date)
                    : 'Date Missing'
                }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="post.provider_org" class="mb-4 ml-5">
      <div class="category">
        <span>Provider org</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>Type</td>
            <td>
              {{
                post.provider_org[0].type
                  ? types.organizationType[post.provider_org[0].type]
                  : 'Missing'
              }}
            </td>
          </tr>
          <tr>
            <td>Provider Activity ID</td>
            <td>
              {{ post.provider_org[0].provider_activity_id ?? 'Missing' }}
            </td>
          </tr>
          <tr>
            <td>Reference</td>
            <td>
              {{ post.provider_org[0].ref ?? 'Missing' }}
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
                      : 'Missing'
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{ narrative.narrative ?? 'Missing' }}
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="post.receiver_org" class="ml-5">
      <div class="category">
        <span>Receiver org</span>
      </div>
      <table class="ml-5">
        <tbody>
          <tr>
            <td>Type</td>
            <td>
              {{
                post.receiver_org[0].type
                  ? types.organizationType[post.receiver_org[0].type]
                  : 'Missing'
              }}
            </td>
          </tr>
          <tr>
            <td>Receiver Activity ID</td>
            <td>
              {{ post.receiver_org[0].receiver_activity_id ?? 'Missing' }}
            </td>
          </tr>
          <tr>
            <td>Reference</td>
            <td>
              {{ post.receiver_org[0].ref ?? 'Missing' }}
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
                  (Language:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : 'Missing'
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{ narrative.narrative ?? 'Missing' }}
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
</script>
