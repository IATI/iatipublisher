<template>
  <div v-if="!props.status">
    <div v-if="!isSavingStarted">
      <div>
        <h3 class="pb-[2px] text-[20px] font-bold leading-9 text-n-50">
          {{
            translatedData['onboarding.default_values_step.set_default_values']
          }}
        </h3>
        <div class="text-sm">
          {{
            translatedData[
              'onboarding.default_values_step.these_commonly_occurring_values_can_be_populated_here'
            ]
          }}
        </div>
        <div
          class="mt-3 max-h-[373px] overflow-x-hidden overflow-y-scroll rounded-lg bg-n-10 pt-[20px] pl-[27px] pb-[20px] pr-[18px]"
        >
          <!-- All Values Default Start -->
          <div>
            <p class="text-sm font-bold">
              {{ translatedData['common.common.default_for_all_data'] }}
            </p>
            <div class="grid grid-cols-2 gap-[22px] pt-4">
              <!-- Default Currency Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-currency" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_currency']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_currency']
                        )
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
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.currencies"
                  :value="allDefaultValue.default_currency"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_currency = value)
              "
                />
                <p class="pt-2 text-xs text-n-40">
                  {{
                    translatedData[
                      'onboarding.default_values_step.the_currency_in_which_you_normally_report_your_financial_transactions'
                    ]
                  }}
                </p>
              </div>
              <!-- Default Currency End -->

              <!-- Default Language Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-language" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_language']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_language']
                        )
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
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.languages"
                  :value="allDefaultValue.default_language"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_language = value)
              "
                />
                <p class="pt-2 text-xs text-n-40">
                  {{
                    translatedData[
                      'onboarding.default_values_step.the_language_in_which_you_normally_report'
                    ]
                  }}
                </p>
              </div>
              <!-- Default Language End -->
            </div>
          </div>
          <!-- All Values Default End -->
          <!-- Activity Data Default Start -->
          <div class="pt-6">
            <p class="text-sm font-bold">
              {{
                translatedData[
                  'onboarding.default_values_step.default_for_activity_data'
                ]
              }}
            </p>
            <div class="grid grid-cols-2 gap-[22px] pt-4">
              <!-- Default Hierarchy Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-hierarchy" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_hierarchy']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      width="w-64"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_hierarchy']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.if_you_are_reporting_both_programmes'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <input
                  id="default-hierarchy"
                  v-model="allDefaultValue.hierarchy"
                  class="mb-2 w-full rounded-[4px] border border-n-20 py-2 pl-4 focus:outline-0 focus-visible:outline-0"
                  :class="hierarchyErrors.length > 0 ? 'border-crimson-50' : ''"
                  type="text"
                  :placeholder="
                    translatedData['common.common.type_default_hierarchy_here']
                  "
                />

                <p
                  v-if="hierarchyErrors.length > 0"
                  class="pt-2 text-xs text-crimson-50"
                >
                  {{ hierarchyErrors[0] }}
                </p>
              </div>
              <!-- Default Heirarchy End -->
              <!-- Humanitarian Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="humanitarian" class="text-[14px]">
                    {{
                      toTitleCase(translatedData['elements.label.humanitarian'])
                    }}
                  </label>
                  <button>
                    <HoverText
                      width="w-72"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.humanitarian']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.add_a_humanitarian_flag_to_every_activity'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <Multiselect
                  id="humanitarian"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.humanitarian"
                  :value="allDefaultValue.humanitarian"
                  @update:model-value="
                (value:string) => (allDefaultValue.humanitarian = value)
              "
                />
              </div>
              <!-- Humanitarian End -->
              <!-- Default Flow Type Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-flow-type" class="text-[14px]">{{
                    toTitleCase(
                      translatedData['elements.label.default_flow_type']
                    )
                  }}</label>
                  <button>
                    <HoverText
                      width="w-72"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_flow_type']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.flow_type_is_a_way_to_categorise'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <Multiselect
                  id="default-flow-type"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.defaultFlowType"
                  :value="allDefaultValue.default_flow_type"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_flow_type = value)
              "
                />
                <p class="pt-2 text-xs text-n-40"></p>
              </div>
              <!-- Default Flow Type End -->
              <!-- Default Finance Type Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-finance-type" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_finance_type']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      width="w-72"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_finance_type']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.the_type_of_finance_eg_grant_loan'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <Multiselect
                  id="default-finance-type"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.defaultFinanceType"
                  :value="allDefaultValue.default_finance_type"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_finance_type = value)
              "
                />
              </div>
              <!-- Default Finance Type End -->
              <!-- Default Aid Type Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-aid-type" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_aid_type']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      width="w-72"
                      position="top-left"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_aid_type']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.the_type_of_aid_being_supplied_project_type_intervention'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <Multiselect
                  id="default-aid-type"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.defaultAidType"
                  :value="allDefaultValue.default_aid_type"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_aid_type = value)
              "
                />
              </div>
              <!-- Default Aid Type End -->
              <!-- Default Tied Status Start -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="default-tied-status" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.default_tied_status']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      width="w-72"
                      position="top-left"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.default_tied_status']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.whether_the_aid_is_untied_tied_or_partially_tied'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <Multiselect
                  id="default-tied-status"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="props.defaultTiedStatus"
                  :value="allDefaultValue.default_tied_status"
                  @update:model-value="
                (value:string) => (allDefaultValue.default_tied_status = value)
              "
                />
              </div>
              <!-- Default Tied Status End -->
            </div>
          </div>
          <!-- Activity Data Default End -->
        </div>
        <div class="flex items-center gap-1 pt-3 text-xs text-n-40">
          <svg-vue icon="message-icon" />
          <span
            v-html="
              translatedData[
                'onboarding.default_values_step.you_can_adjust_these_values_later_from_the_default_values_section'
              ]
            "
          >
          </span>
        </div>
      </div>
      <div class="mt-3 flex w-full items-center justify-between">
        <button class="text-xs font-bold text-n-40" @click="previousStep">
          {{ translatedData['common.common.previous'] }}
        </button>
        <div class="flex items-center gap-4">
          <button
            class="text-xs font-bold text-n-40"
            @click="emit(`proceedStep`)"
          >
            {{ translatedData['common.common.skip_to_next_step'] }}
          </button>
          <button class="button primary-btn text-xs" @click="proceedStep">
            {{ translatedData['common.common.save_and_next'] }}
          </button>
        </div>
      </div>
    </div>
    <div v-else>
      <div
        class="flex min-h-[360px] min-w-[733px] items-center justify-center rounded-lg bg-n-10"
      >
        <Transition mode="out-in">
          <div v-if="!isSaved" class="relative">
            <LinesLoader />
          </div>
          <div
            v-else
            class="mt-3 flex w-full flex-col items-center justify-center gap-2"
          >
            <svg-vue icon="green-circle-tick" class="text-[41px]" />
            <span
              class="max-w-[200px] text-center text-sm font-bold text-bluecoral"
            >
              {{
                translatedData[
                  'settings.setting_controller.default_setting_stored_successfully'
                ]
              }}
            </span>
          </div>
        </Transition>
      </div>
    </div>
  </div>

  <div v-else class="h-full min-w-[733px] pt-[130px]">
    <div class="flex h-full flex-col justify-between">
      <div class="rounded-lg bg-n-10 py-[60px] px-[73px]">
        <div class="flex flex-col items-center justify-center text-center">
          <svg-vue icon="green-circle-tick" class="text-[34px]" />
          <div>
            <h2 class="max-w-[587px] py-[5.4px] text-2xl font-bold text-n-50">
              {{
                translatedData[
                  'onboarding.default_values_step.default_values_have_already_been_set'
                ]
              }}
            </h2>
            <p
              class="max-w-[587px] text-sm text-n-50"
              v-html="
                translatedData[
                  'onboarding.default_values_step.if_you_want_to_make_any_changes_go_to_settings'
                ]
              "
            ></p>
          </div>
        </div>
      </div>
      <div class="mb-[30px] self-end">
        <button class="button primary-btn text-xs" @click="emit(`proceedStep`)">
          {{ translatedData['common.common.next'] }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, watchEffect, ref, inject } from 'vue';

import Multiselect from '@vueform/multiselect';
import HoverText from 'Components/HoverText.vue';
import axios from 'axios';
import LinesLoader from 'Components/LinesLoader.vue';
import { toTitleCase } from 'Composable/utils';

const props = defineProps({
  currencies: {
    type: Object,
    required: true,
  },
  languages: {
    type: Object,
    required: true,
  },
  humanitarian: {
    type: Object,
    required: true,
  },
  defaultFlowType: {
    type: Object,
    required: true,
  },
  defaultFinanceType: {
    type: Object,
    required: true,
  },
  defaultAidType: {
    type: Object,
    required: true,
  },
  defaultTiedStatus: {
    type: Object,
    required: true,
  },
  defaultValues: {
    type: Object,
    required: true,
  },
  fetchData: {
    type: Function,
    required: true,
  },
  status: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits([
  'proceedStep',
  'previousStep',
  'completeStep',
  'removeCompletedStep',
]);
const allDefaultValue = ref({
  default_currency: '',
  default_language: '',
  hierarchy: '',
  humanitarian: '',
  default_flow_type: '',
  default_finance_type: '',
  default_aid_type: '',
  default_tied_status: '',
});

const translatedData = inject('translatedData') as Record<string, string>;
const isSavingStarted = ref(false);
const isSaved = ref(false);
const hierarchyErrors = ref([]);

watchEffect(() => {
  if (props.defaultValues) {
    Object.keys(allDefaultValue.value).forEach((key) => {
      if (props.defaultValues[key] !== undefined) {
        allDefaultValue.value[key] = props.defaultValues[key];
      }
    });
  }
});

const proceedStep = () => {
  isSavingStarted.value = true;
  axios
    .post('/setting/store/default', allDefaultValue.value)
    .then(
      (response: {
        data: {
          success: boolean;
          data: {
            default_values: {
              default_currency: string;
              default_language: string;
            };
          };
        };
      }) => {
        if (response.data.success) {
          const defaultValues = response.data.data.default_values;

          setTimeout(() => {
            isSaved.value = true;
          }, 1000);

          setTimeout(() => {
            props.fetchData();
            defaultValueCompletedCheck(defaultValues);
            emit('proceedStep');
          }, 3000);
        }
      }
    )
    .catch((error) => {
      if (error.response.data.errors.hierarchy) {
        hierarchyErrors.value = error.response.data.errors.hierarchy;
      }
      isSavingStarted.value = false;
    });
};

const defaultValueCompletedCheck = (defaultValues: {
  default_currency: string | null | undefined;
  default_language: string | null | undefined;
}) => {
  if (
    defaultValues &&
    defaultValues.default_currency != null &&
    defaultValues.default_currency !== '' &&
    defaultValues.default_language != null &&
    defaultValues.default_language !== ''
  ) {
    emit('completeStep', 2);
  } else {
    emit('removeCompletedStep', 2);
  }
};

const previousStep = () => {
  emit('previousStep');
};
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: all 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: translateY(100%);
}
</style>
