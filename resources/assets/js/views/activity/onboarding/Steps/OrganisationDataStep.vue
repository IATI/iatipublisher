<!-- eslint-disable vue/no-v-html -->
<template>
  <ToastMessage
    v-if="toastVisibility"
    class="toast fixed top-10 right-10"
    :message="toastMessage"
    :type="toastType"
  />

  <Loader v-if="isLoaderVisible" />

  <div v-if="!props.status">
    <div v-if="!isSaving">
      <!-- Error -->
      <div
        v-if="hasError"
        class="flex gap-2 border-l-[3px] border-crimson-40 bg-rose py-[10px] px-[14px]"
      >
        <div>
          <svg-vue
            class="text-[22px]"
            icon="exclamation-warning-fill"
          ></svg-vue>
        </div>
        <div class="text-xs leading-[20px] tracking-[-2%]">
          <p class="font-bold">
            {{
              translatedData[
                'onboarding.organisation_data_step.verification_required'
              ]
            }}
          </p>
          <ul class="list-disc">
            <li
              v-for="(message, index) in errorMessages"
              :key="index"
              class="translate-x-3"
              v-html="message"
            ></li>
          </ul>
          <em>
            {{
              translatedData[
                'onboarding.organisation_data_step.you_can_skip_this_step_for_now'
              ]
            }}
          </em>
        </div>
      </div>

      <!-- Organization Data Publish -->
      <div>
        <h3 class="pb-[2px] text-[20px] font-bold leading-9 text-n-50">
          {{
            translatedData[
              'onboarding.organisation_data_step.publish_organisation_data'
            ]
          }}
        </h3>
        <div class="text-sm">
          {{
            translatedData[
              'onboarding.organisation_data_step.review_the_basic_information'
            ]
          }}
        </div>
        <div
          class="mt-3 rounded-lg bg-n-10 pt-[20px] pl-[27px] pb-[20px]"
          :class="{ 'max-h-[270px] overflow-y-auto': hasError }"
        >
          <div class="border-b border-n-20 py-4">
            <p class="flex items-center font-bold">
              <svg-vue
                class="text-base text-bluecoral"
                icon="organisation-elements/building"
              ></svg-vue>
              <span class="ml-1 mr-[10px]">{{
                translatedData['elements.name.reporting_org']
              }}</span>
              <svg-vue class="text-base text-camel-50" icon="core"></svg-vue>
            </p>
          </div>
          <div class="pt-[18px] pr-5">
            <div class="flex items-center justify-between">
              <p class="text-sm font-bold text-n-50">
                {{ translatedData['elements.name.reporting_org'] }}
              </p>
              <p class="flex items-center gap-1">
                <HoverText
                  :name="translatedData['elements.name.reporting_org']"
                  :hover-text="
                    translatedData[
                      'common.common.the_organisation_issuing_the_report'
                    ]
                  "
                  :show-iati-reference="true"
                  link="https://iatistandard.org/en/iati-standard/203/organisation-standard/iati-organisations/iati-organisation/reporting-org/"
                />
              </p>
            </div>
            <div class="grid grid-cols-2 pt-4">
              <!-- Reference -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="reference" class="text-[14px]">
                    {{
                      toTitleCase(translatedData['elements.label.reference'])
                    }}
                    <span class="required-icon"> *</span>
                  </label>
                  <button>
                    <HoverText
                      :name="
                        toTitleCase(translatedData['elements.label.reference'])
                      "
                      :hover-text="
                        translatedData[
                          'common.common.provide_your_organisations_iati_identifier'
                        ]
                      "
                      :show-iati-reference="true"
                      link="https://iatistandard.org/en/iati-standard/203/organisation-standard/iati-organisations/iati-organisation/reporting-org/"
                    />
                  </button>
                </div>
                <input
                  id="reference"
                  v-model="organizationData.ref"
                  class="w-full rounded-[4px] border border-n-20 py-2 pl-4 focus:outline-0 focus-visible:outline-0"
                  :class="{ 'border-crimson-50': hasReferenceError }"
                  type="text"
                  :placeholder="translatedData['common.common.type_reference']"
                  @keyup="hasReferenceError = false"
                />
                <span v-if="hasReferenceError" class="text-danger error">{{
                  referenceErrorMessage
                }}</span>
              </div>

              <!-- Type -->
              <div class="w-full max-w-[335px]">
                <div class="flex justify-between pb-2">
                  <label for="type" class="text-[14px]">
                    {{ toTitleCase(translatedData['elements.label.type']) }}
                    <span class="required-icon"> *</span>
                  </label>
                  <button>
                    <HoverText
                      :name="toTitleCase(translatedData['elements.label.type'])"
                      :hover-text="
                        translatedData[
                          'common.common.select_the_type_that_best_describes_your_organisation'
                        ]
                      "
                      :show-iati-reference="true"
                      link="https://iatistandard.org/en/iati-standard/203/organisation-standard/iati-organisations/iati-organisation/reporting-org/"
                    />
                  </button>
                </div>
                <Multiselect
                  id="type"
                  class="vue__select"
                  :placeholder="translatedData['common.common.select_type']"
                  :searchable="true"
                  :options="props.organizationTypeOptions"
                  :value="organizationData.type"
                  @update:model-value="
                (value:string) => (organizationData.type = value)
              "
                />
              </div>

              <!-- Secondary reporter -->
              <div class="w-full max-w-[335px] pt-6">
                <div class="flex justify-between pb-2">
                  <label for="secondary-reporter" class="text-[14px]">
                    {{
                      toTitleCase(
                        translatedData['elements.label.secondary_reporter']
                      )
                    }}
                  </label>
                  <button>
                    <HoverText
                      position="top-left"
                      :name="
                        toTitleCase(
                          translatedData['elements.label.secondary_reporter']
                        )
                      "
                      :hover-text="
                        translatedData[
                          'common.common.if_you_are_publishing_data_about_your_own_organisation'
                        ]
                      "
                      :show-iati-reference="true"
                      link="https://iatistandard.org/en/iati-standard/203/organisation-standard/iati-organisations/iati-organisation/reporting-org/"
                    />
                  </button>
                </div>
                <Multiselect
                  id="secondary-reporter"
                  class="vue__select"
                  :placeholder="
                    translatedData['common.common.select_an_option']
                  "
                  :searchable="true"
                  :options="secondaryReporterOptions"
                  :value="organizationData.secondary_reporter"
                  @update:model-value="
                (value:string) => (organizationData.secondary_reporter = value)
              "
                />
              </div>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-1 pt-3 text-xs text-n-40">
          <svg-vue icon="message-icon" />
          <span>
            {{
              translatedData[
                'onboarding.organisation_data_step.you_can_adjust_these_values_later_from_the_organisation_data_section'
              ]
            }}
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
            @click="emit('proceedStep')"
          >
            {{ translatedData['common.common.skip_to_next_step'] }}
          </button>
          <button class="button primary-btn text-xs" @click="proceedStep">
            {{
              translatedData[
                'onboarding.organisation_data_step.publish_and_next'
              ]
            }}
          </button>
        </div>
      </div>
      <Transition name="slide" mode="out-in">
        <SideHelpText
          :title="helpTitle"
          :content="helpContent"
          :visible="helpVisible"
          @close="helpVisible = false"
        />
      </Transition>
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
                  'onboarding.organisation_data_step.organisation_data_has_been_successfully_published'
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
            <h2 class="max-w-[693px] py-[5.4px] text-2xl font-bold text-n-50">
              {{
                translatedData[
                  'onboarding.organisation_data_step.organisation_data_has_been_successfully_published'
                ]
              }}
            </h2>
            <p
              class="max-w-[587px] text-sm text-n-50"
              v-html="
                translatedData[
                  'onboarding.organisation_data_step.if_you_want_to_make_changes_go_to_organisation_data'
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
import { defineEmits, defineProps, inject, ref } from 'vue';

import Multiselect from '@vueform/multiselect';
import HoverText from 'Components/HoverText.vue';
import SideHelpText from 'Components/SideHelpText.vue';
import axios from 'axios';
import LinesLoader from 'Components/LinesLoader.vue';
import ToastMessage from 'Components/ToastMessage.vue';
import Loader from 'Components/Loader.vue';
import { toTitleCase } from 'Composable/utils';

const props = defineProps({
  organizationTypeOptions: {
    type: Object,
    required: true,
  },
  previousValues: {
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

const secondaryReporterOptions = [
  { value: '1', label: 'True' },
  { value: '0', label: 'False' },
];

const organizationData = ref({
  ref: props.previousValues[0]?.ref || '',
  type: props.previousValues[0]?.type || '',
  secondary_reporter: props.previousValues[0]?.secondary_reporter || '',
});

const helpTitle = ref('');
const helpContent = ref('');
const helpVisible = ref(false);

const isSaving = ref(false);
const isSaved = ref(false);

const hasError = ref(false);
const errorMessages = ref<string[]>([]);

const isLoaderVisible = ref(false);

const toastVisibility = ref(false);
const toastMessage = ref('');
const toastType = ref(false);

const hasReferenceError = ref(false);
const referenceErrorMessage = ref('');
const translatedData = inject('translatedData') as Record<string, string>;

const contentValues = [
  {
    title: translatedData['elements.label.reference'],
    content: '',
  },
  {
    title: translatedData['elements.label.reference'],
    content: '',
  },
  {
    title: translatedData['elements.label.secondary_reporter'],
    content: '',
  },
];

const resendVerificationEmail = () => {
  isLoaderVisible.value = true;
  axios
    .post('/user/verification/email')
    .then((res) => {
      toastVisibility.value = true;
      setTimeout(() => (toastVisibility.value = false), 3000);
      toastMessage.value = res.data.message;
      toastType.value = res.data.success;
      isLoaderVisible.value = false;
    })
    .catch((error) => {
      toastVisibility.value = true;
      setTimeout(() => (toastVisibility.value = false), 3000);
      toastMessage.value = error.data.message;
      toastType.value = false;
      isLoaderVisible.value = false;
    });
};

document.addEventListener('click', (e) => {
  if ((e.target as HTMLElement).classList.contains('resend-verification')) {
    resendVerificationEmail();
  }
});

const transformMessages = (messages: string[]): string[] => {
  return messages.map((message) => {
    switch (message) {
      case translatedData[
        'common.common.your_email_address_has_not_been_verified'
      ]: {
        const text_p1 =
          translatedData[
            'onboarding.organisation_data_step.your_email_address_has_not_been_verified'
          ];
        const text_p2 =
          '<span class="resend-verification text-bluecoral cursor-pointer hover:text-spring-50 underline transition-all duration-[400ms]">';
        const text_p3 =
          translatedData['common.common.resend_verification_email'];
        const text_p4 = '</span>';

        return `${text_p1} ${text_p2} ${text_p3} ${text_p4}`;
      }
      case translatedData[
        'onboarding.organisation_data_step.the_publisher_id_is_not_verified_in_iati_registry'
      ]:
        return translatedData[
          'onboarding.organisation_data_step.your_iati_registry_account_is_pending_approval'
        ];

      default:
        return message;
    }
  });
};

const previousStep = () => {
  emit('previousStep');
};

const proceedStep = () => {
  hasError.value = false;
  isSaving.value = true;
  const finalData = [
    {
      ...organizationData.value,
      narrative: props.previousValues[0]?.narrative || '',
    },
  ];

  axios
    .put('/organisation/reporting_org', {
      reporting_org: finalData,
    })
    .then(() => {
      axios
        .get('organisation/checks-for-organisation-publish')
        .then((response: { data: { success: boolean; message: string[] } }) => {
          if (response.data.success) {
            axios
              .post('/organisation/publish')
              .then(
                (response: {
                  data: { success: boolean; message: string[] };
                }) => {
                  if (response.data.success) {
                    setTimeout(() => {
                      isSaved.value = true;
                    }, 1000);
                    setTimeout(() => {
                      props.fetchData();
                      emit('completeStep', 3);
                      emit('proceedStep');
                    }, 4000);
                  } else {
                    hasError.value = true;
                    errorMessages.value = transformMessages(
                      response.data.message
                    );
                    isSaving.value = false;
                    isSaved.value = false;
                  }
                }
              );
          } else {
            hasError.value = true;
            errorMessages.value = transformMessages(response.data.message);

            isSaving.value = false;
            isSaved.value = false;
          }
        })
        .catch((err) => {
          console.log('Error', err);
          isSaving.value = false;
          isSaved.value = false;
        });
    })
    .catch((err) => {
      emit('removeCompletedStep', 3);
      if (err.response && err.response.data && err.response.data.errors) {
        const errors = err.response.data.errors;

        if (errors['reporting_org.0.ref']) {
          hasReferenceError.value = true;
          referenceErrorMessage.value = errors['reporting_org.0.ref'][0];
        }
      }

      isSaving.value = false;
      isSaved.value = false;
    });
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

.slide-enter-active,
.slide-leave-active {
  transition: all 0.5s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
