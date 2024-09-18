<template>
  <section class="section-wrapper activity-default-value">
    <Loader v-if="loaderVisibility" />
    <div class="setting input__field">
      <span class="text-xs font-bold text-n-40">
        {{ translatedData['common.common.override_default_values'] }}
      </span>
      <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
        <div class="mt-4 flex items-center">
          <a :href="`/activity/${activityId}`"><svg-vue icon="left-arrow" /></a>
          <h2 class="ml-3 text-heading-4 font-bold text-n-50">
            {{ translatedData['common.common.override_default_values'] }}
          </h2>
        </div>
        <div class="flex w-full justify-end lg:w-[auto]">
          <Toast
            v-if="toastVisibility"
            :message="toastMessage"
            :type="toastType"
          />
        </div>
      </div>
      <div class="setting__container overflow-x-hidden">
        <div class="mb-8 text-xs text-n-40">
          {{
            translatedData[
              'common.common.use_the_following_form_to_change_the_default_values'
            ]
          }}
        </div>
        <div class="register mt-4">
          <div class="register__container mb-0">
            <div>
              <div class="flex justify-between">
                <label for="default-currency">{{
                  getTranslatedElement(translatedData, 'currency')
                }}</label>
                <button>
                  <HoverText
                    :name="
                      getTranslatedElement(translatedData, 'default_currency')
                    "
                    :hover-text="
                      translatedData[
                        'common.common.the_currency_in_which_you_report_your_financial_transactions'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <Multiselect
                id="default-currency"
                v-model="defaultValues.default_currency"
                class="vue__select"
                :placeholder="translatedData['common.common.select_an_option']"
                :options="currencies"
                :searchable="true"
              />
              <div v-if="defaultErrors.default_currency.length > 0">
                <div
                  v-for="(error, e) in defaultErrors?.default_currency"
                  :key="e"
                  class="error"
                  role="alert"
                >
                  <span class="text-xs">{{ error }}</span>
                </div>
              </div>
              <p
                v-if="defaultErrors.default_currency.length === 0"
                class="text-xs text-n-40"
              >
                {{
                  translatedData[
                    'common.common.the_currency_in_which_you_are_reporting_your_financial_transactions_for_this_activity'
                  ]
                }}
                {{ translatedData['common.common.select_an_option'] }}
              </p>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">{{
                  getTranslatedLanguage(translatedData)
                }}</label>
                <button>
                  <HoverText
                    :name="
                      getTranslatedElement(translatedData, 'default_language')
                    "
                    :hover-text="
                      translatedData[
                        'common.common.the_language_in_which_you_provide_data_on_your_activities'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <Multiselect
                id="default-language"
                v-model="defaultValues.default_language"
                class="vue__select"
                :placeholder="translatedData['common.common.select_an_option']"
                :searchable="true"
                :options="props.languages"
              />
              <div v-if="defaultErrors.default_language.length > 0">
                <div
                  v-for="(error, e) in defaultErrors.default_language"
                  :key="e"
                  class="error"
                  role="alert"
                >
                  <span class="text-xs">{{ error }}</span>
                </div>
              </div>
              <p
                v-if="defaultErrors.default_language.length === 0"
                class="text-xs text-n-40"
              >
                {{
                  translatedData[
                    'common.common.the_language_in_which_you_are_reporting_this_activity'
                  ]
                }}
                {{ translatedData['common.common.select_an_option'] }}
              </p>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">{{
                  getTranslatedElement(translatedData, 'hierarchy')
                }}</label>
                <button>
                  <HoverText
                    width="w-64"
                    :name="
                      getTranslatedElement(translatedData, 'default_hierarchy')
                    "
                    :hover-text="
                      translatedData[
                        'common.common.if_you_are_reporting_both_programmes_parent_activity_1_and_projects_child_activity_2'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <input
                id="default-hierarchy"
                v-model="defaultValues.hierarchy"
                class="register__input mb-2"
                type="text"
                :placeholder="
                  translatedData['common.common.type_default_hierarchy_here']
                "
              />
              <div v-if="defaultErrors.hierarchy.length > 0">
                <div
                  v-for="(error, e) in defaultErrors.hierarchy"
                  :key="e"
                  class="error"
                  role="alert"
                >
                  <span class="text-xs">{{ error }}</span>
                </div>
              </div>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">{{
                  getTranslatedElement(translatedData, 'budget_not_provided')
                }}</label>
                <button>
                  <HoverText
                    width="w-72"
                    :name="
                      getTranslatedElement(
                        translatedData,
                        'budget_not_provided'
                      )
                    "
                    :hover-text="
                      translatedData[
                        'common.common.if_you_are_not_able_to_provide_budgets_for_this_activity_select_the_reason_here'
                      ]
                    "
                  />
                </button>
              </div>
              <Multiselect
                id="budget_not_provided"
                v-model="defaultValues.budget_not_provided"
                class="vue__select"
                :placeholder="translatedData['common.common.select_an_option']"
                :options="budgetNotProvided"
                :searchable="true"
              />
              <div v-if="defaultErrors.budget_not_provided.length > 0">
                <div
                  v-for="(error, e) in defaultErrors.budget_not_provided"
                  :key="e"
                  class="error"
                  role="alert"
                >
                  <span class="text-xs">{{ error }}</span>
                </div>
              </div>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">
                  {{ getTranslatedElement(translatedData, 'humanitarian') }}
                </label>
                <button>
                  <HoverText
                    width="w-72"
                    :name="getTranslatedElement(translatedData, 'humanitarian')"
                    :hover-text="
                      translatedData[
                        'common.common.select_yes_if_this_activity_wholly_or_partially_addresses_a_humanitarian_crisis'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <Multiselect
                id="humanitarian"
                v-model="defaultValues.humanitarian"
                class="vue__select"
                :placeholder="translatedData['common.common.select_an_option']"
                :options="humanitarian"
                :searchable="true"
              />
              <div v-if="defaultErrors.humanitarian.length > 0">
                <div
                  v-for="(error, e) in defaultErrors.humanitarian"
                  :key="e"
                  class="error"
                  role="alert"
                >
                  <span class="text-xs">{{ error }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="fixed bottom-0 left-0 w-full bg-eggshell py-5 pr-40 shadow-dropdown"
    >
      <div class="flex items-center justify-end">
        <a class="ghost-btn mr-8" :href="`/activity/${activityId}`">{{
          translatedData['common.common.cancel']
        }}</a>
        <button class="primary-btn save-btn" @click="submitForm()">
          {{ translatedData['common.common.save_default_values'] }}
        </button>
      </div>
    </div>
  </section>
</template>
<script setup lang="ts">
import { defineProps, ref, onMounted } from 'vue';

import Multiselect from '@vueform/multiselect';
import axios from 'axios';

import Loader from 'Components/Loader.vue';
import Toast from 'Components/ToastMessage.vue';
import { getTranslatedElement, getTranslatedLanguage } from 'Composable/utils';

/**
 * Props
 */
const props = defineProps({
  currencies: {
    type: [String, Object],
    required: true,
  },
  languages: {
    type: [String, Object],
    required: true,
  },
  activityId: { type: Number, required: true },
  budgetNotProvided: {
    type: [String, Object],
    required: true,
  },
  humanitarian: {
    type: [String, Object],
    required: true,
  },
  translatedData: {
    type: Object,
    required: true,
  },
});

/**
 * Reactive variables
 */

const defaultValues = ref({
    budget_not_provided: '',
    default_currency: '',
    default_language: '',
    hierarchy: '',
    humanitarian: '',
  }),
  defaultErrors = ref({
    budget_not_provided: [],
    default_currency: [],
    default_language: [],
    hierarchy: [],
    humanitarian: [],
  });

const errorReset = defaultErrors.value;

/**
 * On Mounted
 */
onMounted(async () => {
  const { data } = await axios.get(
    `/activity/${props.activityId}/default_values/data`
  );
  const defaultData = data.data;
  if (defaultData) {
    defaultValues.value = defaultData;
  }
});

/**
 * Ref
 */
const loaderVisibility = ref(false),
  toastVisibility = ref(false),
  toastMessage = ref(''),
  toastType = ref(false);

/**
 * Submit form
 *
 */

function submitForm() {
  loaderVisibility.value = true;
  defaultErrors.value = errorReset;
  axios
    .put(`/activity/${props.activityId}/default_values`, defaultValues.value)
    .then((res) => {
      const response = res.data;
      loaderVisibility.value = false;
      toastVisibility.value = true;
      setTimeout(() => (toastVisibility.value = false), 5000);
      toastMessage.value = response?.message;
      toastType.value = response?.success;
      loaderVisibility.value = false;
    })
    .catch((error) => {
      defaultErrors.value = {
        ...defaultErrors.value,
        ...error.response.data?.errors,
      };

      toastVisibility.value = true;
      toastMessage.value = error.response.data?.message;
      toastType.value = false;
      loaderVisibility.value = false;
    });
}
</script>
