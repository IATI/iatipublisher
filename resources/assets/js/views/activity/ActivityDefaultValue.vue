<template>
  <section class="section-wrapper activity-default-value">
    <Loader v-if="loaderVisibility" />
    <div class="setting input__field">
      <span class="text-xs font-bold text-n-40">Override default values</span>
      <div class="mb-6 flex flex-wrap items-center justify-between gap-2">
        <div class="mt-4 flex items-center">
          <a :href="`/activity/${activityId}`"><svg-vue icon="left-arrow" /></a>
          <h2 class="ml-3 text-heading-4 font-bold text-n-50">
            Override default values
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
          Use the following form to change the default values such as currency,
          language etc for this specific activity. Changing the values here will
          not change the default values in the setting page.
        </div>
        <div class="register mt-4">
          <div class="register__container mb-0">
            <div>
              <div class="flex justify-between">
                <label for="default-currency">Currency</label>
                <button>
                  <HoverText
                    name="Default Currency"
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
                placeholder="Select from dropdown"
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
                The currency in which you are reporting your financial
                transactions for this activity. Select from dropdown
              </p>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">Language</label>
                <button>
                  <HoverText
                    name="Default Language"
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
                placeholder="Select from dropdown"
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
                The language in which you are reporting this activity. Select
                from dropdown.
              </p>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">Hierarchy</label>
                <button>
                  <HoverText
                    width="w-64"
                    name="Default Hierarchy"
                    hover-text="If you are reporting both programmes (parent activities) and projects (child activities),
                choose the hierarchical level that most of your activities are at. e.g. parent activity = 1; child activity = 2.
                <br>If all your activities are at the same level i.e. you have no child activities, then choose 1."
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <input
                id="default-hierarchy"
                v-model="defaultValues.hierarchy"
                class="register__input mb-2"
                type="text"
                placeholder="Type default hierarchy here"
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
              <p
                v-if="defaultErrors.hierarchy.length === 0"
                class="text-xs text-n-40"
              >
                IATI allows for activities to be reported hierarchically (eg.
                parent - child ; programme - project - sub-project, etc). For
                activities at lower levels, their hierarchy can be edited as you
                are entering them.
              </p>
            </div>

            <div>
              <div class="flex justify-between">
                <label for="default-currency">Budget not provided</label>
                <button>
                  <HoverText
                    width="w-72"
                    name="Budget Not Provided"
                    hover-text="A code indicating the reason why this activity does not contain any iati-activity/budget elements. The attribute MUST only be used when no budget elements are present."
                  />
                </button>
              </div>
              <Multiselect
                id="budget_not_provided"
                v-model="defaultValues.budget_not_provided"
                class="vue__select"
                placeholder="Select budget not provided type here"
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
                <label for="default-currency">Humanitarian</label>
                <button>
                  <HoverText
                    width="w-72"
                    name="Humanitarian"
                    hover-text="Add a 'Humanitarian Flag' to every activity that your organisation publishes data on. This means that your organisation identifies all their activities as wholly or partially addressing a humanitarian crisis or multiple crises. You can later manually add or remove a Humanitarian Flag on individual activities if required."
                    :show-iati-reference="true"
                  />
                </button>
              </div>
              <Multiselect
                id="humanitarian"
                v-model="defaultValues.humanitarian"
                class="vue__select"
                :placeholder="
                  translatedData['common.common.select_humanitarian_here']
                "
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
        <a class="ghost-btn mr-8" :href="`/activity/${activityId}`">Cancel</a>
        <button class="primary-btn save-btn" @click="submitForm()">
          Save default values
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
