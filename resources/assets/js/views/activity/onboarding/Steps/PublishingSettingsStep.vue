<template>
  <ToastMessage
    v-if="toastVisibility"
    class="toast fixed top-10 right-10"
    :message="toastMessage"
    :type="toastType"
  />

  <div
    v-if="organizationRegistrationType !== 'new_org'"
    class="h-full pt-[130px]"
  >
    <div v-if="!props.status">
      <div v-if="!isSaving">
        <div class="relative">
          <h3 class="pb-[2px] text-[20px] font-bold leading-9 text-n-50">
            {{
              translatedData[
                'onboarding.publishing_setting_step.complete_publishing_settings'
              ]
            }}
          </h3>
          <div class="text-sm">
            {{
              translatedData[
                'onboarding.publishing_setting_step.link_your_account_to_the_iati_registry'
              ]
            }}
          </div>
          <Transition mode="out-in">
            <div
              v-if="!isSuccess"
              class="mt-3 rounded-lg bg-n-10 py-[20px] px-[27px]"
            >
              <div
                v-if="!isVerifyingToken"
                class="flex max-w-[380px] flex-col gap-2"
              >
                <div class="flex justify-between">
                  <label for="api-token" class="text-[14px]">
                    {{ translatedData['common.common.api_token'] }}
                    <span class="required-icon"> *</span>
                  </label>
                  <button>
                    <HoverText
                      :name="translatedData['common.common.api_token']"
                      :hover-text="
                        translatedData[
                          'common.common.the_api_token_is_a_unique_key_that_is_generated_from_your_organisation'
                        ]
                      "
                      :show-iati-reference="true"
                    />
                  </button>
                </div>
                <div class="relative">
                  <input
                    id="api-token"
                    v-model="apiToken"
                    type="text"
                    class="mt-2 h-12 w-full rounded-[4px] border border-n-30 py-[13px] px-4 text-sm focus-within:outline-0 focus:outline-0"
                    :placeholder="
                      translatedData['common.common.enter_api_token_here']
                    "
                  />
                  <ShimmerLoading
                    v-if="!tokenStatus"
                    class="!absolute top-[56%] !m-0 !ml-2 !h-8 !w-[96%] -translate-y-1/2"
                  />
                  <span
                    v-if="!props.initialRender && tokenStatus"
                    class="absolute top-1/2 right-3 rounded-[4px] px-2 text-xs text-white"
                    :class="{
                      'bg-salmon-50': tokenStatus === 'Incorrect',
                      'bg-spring-40': tokenStatus === 'Correct',
                      'bg-organeish': tokenStatus === 'Pending',
                    }"
                  >
                    {{ tokenStatus }}
                  </span>
                </div>
              </div>
              <!-- If Verifying Token -->
              <div
                v-else
                class="my-4 flex w-full flex-col items-center justify-center bg-[#F1F7F9] py-[36px]"
              >
                <div class="relative">
                  <LinesLoader />
                </div>
                <h3 class="pt-4 font-bold text-bluecoral">
                  {{
                    translatedData[
                      'onboarding.publishing_setting_step.verifying_api_token'
                    ]
                  }}
                </h3>
              </div>

              <button
                v-if="!isVerifyingToken"
                type="button"
                class="mt-3 rounded-[4px] bg-bluecoral py-[11px] px-[38.5px] text-sm font-[700] text-white"
                @click.once="verifyToken"
              >
                {{ translatedData['common.common.verify'] }}
              </button>
            </div>
            <!-- If Success -->
            <div v-else>
              <div
                class="mt-3 flex w-full flex-col items-center justify-center gap-2 rounded-lg bg-n-10 py-[62px]"
              >
                <svg-vue icon="green-circle-tick" class="text-[29px]" />
                <span class="text-sm font-bold text-bluecoral">
                  {{
                    translatedData[
                      'onboarding.publishing_setting_step.api_token_verified'
                    ]
                  }}
                </span>
              </div>
            </div>
          </Transition>

          <div class="flex items-center gap-1 pt-3 text-xs text-n-40">
            <svg-vue icon="message-icon" />
            <span
              v-html="
                translatedData[
                  'onboarding.publishing_setting_step.you_can_always_revisit_and_adjust_these_settings_later'
                ]
              "
            >
            </span>
          </div>
        </div>

        <div
          class="absolute bottom-[30px] right-[40px] flex w-full items-center justify-end"
        >
          <div class="flex items-center gap-4">
            <button
              class="text-xs font-bold text-n-40"
              @click="emit(`proceedStep`)"
            >
              {{ translatedData['common.common.skip_to_next_step'] }}
            </button>
            <button
              class="button primary-btn text-xs disabled:cursor-not-allowed disabled:bg-n-20 disabled:shadow-none"
              :disabled="isSaving || !apiToken"
              @click="proceedStep"
            >
              {{ translatedData['common.common.save_and_next'] }}
            </button>
          </div>
        </div>
      </div>
      <!-- Is Saving -->
      <div v-else>
        <div class="relative rounded-lg bg-n-10 py-[170px] px-[345px]">
          <LinesLoader />
        </div>
      </div>
    </div>

    <!-- If Status is false -->
    <div v-else class="h-full">
      <div class="flex h-full flex-col justify-between">
        <div class="rounded-lg bg-n-10 py-[60px] px-[73px]">
          <div class="flex flex-col text-center">
            <svg-vue icon="green-circle-tick" class="text-[34px]" />
            <div>
              <h2 class="max-w-[587px] py-[5.4px] text-2xl font-bold text-n-50">
                {{
                  translatedData[
                    'onboarding.publishing_setting_step.your_account_has_been_successfully_linked_to_the_iati_registry'
                  ]
                }}
              </h2>
              <p
                class="max-w-[587px] text-sm text-n-50"
                v-html="
                  translatedData[
                    'onboarding.publishing_setting_step.the_api_token_has_been_generated_and_added_successfully'
                  ]
                "
              ></p>
            </div>
          </div>
        </div>
        <div class="mb-[30px] self-end">
          <button
            class="button primary-btn text-xs"
            @click="emit(`proceedStep`)"
          >
            {{ translatedData['common.common.next'] }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <!-- If Registration Type Not New -->
  <div v-else class="h-full pt-[130px]">
    <div class="flex h-full flex-col justify-between">
      <div class="rounded-lg bg-n-10 py-[60px] px-[73px]">
        <div class="flex flex-col text-center">
          <svg-vue icon="green-circle-tick" class="text-[34px]" />
          <div>
            <h2 class="py-[5.4px] text-2xl font-bold text-n-50">
              {{
                translatedData[
                  'onboarding.publishing_setting_step.publishing_settings_completed'
                ]
              }}
            </h2>
            <p class="max-w-[587px] text-sm text-n-50">
              {{
                translatedData[
                  'onboarding.publishing_setting_step.your_iati_registry_account_has_been_linked_and_needs_to_be_approved_before_you_can_publish_data'
                ]
              }}
            </p>
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
import { defineEmits, defineProps, inject, ref, watchEffect } from 'vue';
import LinesLoader from 'Components/LinesLoader.vue';
import axios from 'axios';
import ShimmerLoading from 'Components/ShimmerLoading.vue';
import ToastMessage from 'Components/ToastMessage.vue';

const props = defineProps({
  publisherId: {
    type: String,
    required: true,
  },
  publisherSetting: {
    type: Object,
    required: true,
  },
  organizationId: {
    type: Number,
    required: true,
  },
  fetchData: {
    type: Function,
    required: true,
  },
  initialRender: {
    type: Boolean,
    required: true,
  },
  registrationType: {
    type: String,
    required: true,
  },
  status: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits([
  'proceedStep',
  'changeRender',
  'completeStep',
  'removeCompletedStep',
]);

const translatedData = inject('translatedData') as Record<string, string>;
const apiToken = ref('');
const isVerifyingToken = ref(false);
const isSaving = ref(false);
const isSuccess = ref(false);

const tokenStatus = ref(props.publisherSetting?.token_status || null);

const verifyTokenStatus = ref(false);

const organizationRegistrationType = ref('');

const toastVisibility = ref(false);
const toastMessage = ref('');
const toastType = ref(false);

watchEffect(() => {
  if (typeof props.publisherSetting === 'undefined') {
    tokenStatus.value = ' ';
    return;
  }

  if (props.publisherSetting.token_status) {
    tokenStatus.value = props.publisherSetting.token_status;
  }
});

watchEffect(() => {
  apiToken.value = props?.publisherSetting?.api_token;
});

watchEffect(() => {
  organizationRegistrationType.value = props.registrationType;
});

const verifyToken = () => {
  isVerifyingToken.value = true;
  verifyTokenStatus.value = false;
  emit('changeRender');
  axios
    .post('/setting/verify', {
      api_token: apiToken.value ?? null,
      publisher_id: props.publisherId,
    })
    .then((response: { data: { data: { token_status: string } } }) => {
      verifyTokenStatus.value = true;
      tokenStatus.value = response.data.data.token_status;
      if (
        response.data.data.token_status === 'Correct' ||
        response.data.data.token_status === 'Pending'
      ) {
        isSuccess.value = true;
      }
    })
    .catch((err) => {
      console.log('Error', err);
    })
    .finally(() => {
      isVerifyingToken.value = false;
      setTimeout(() => {
        isSuccess.value = false;
      }, 3000);
    });
};

const proceedStep = async () => {
  isSaving.value = true;
  await axios
    .post('/setting/store/publisher', {
      api_token: apiToken.value ?? null,
      publisher_id: props.publisherId,
      organization_id: props.organizationId,
      publisher_verification: props.publisherSetting?.publisher_verification,
      token_verification: props.publisherSetting?.token_verification,
    })
    .then(
      (response: {
        data: {
          success: boolean;
          message: string | string[];
          data: {
            token_status: string;
          };
        };
      }) => {
        if (response.data.success) {
          if (
            response.data.data.token_status === 'Correct' ||
            response.data.data.token_status === 'Pending'
          ) {
            emit('completeStep', 1);
          } else {
            emit('removeCompletedStep', 1);
          }
          props.fetchData();
          emit('proceedStep');
        } else {
          toastVisibility.value = true;
          setTimeout(() => (toastVisibility.value = false), 3000);
          toastMessage.value = Array.isArray(response.data.message)
            ? response.data.message.join('<br>')
            : response.data.message;

          isSaving.value = false;
        }
      }
    )
    .catch((err) => console.log(err))
    .finally(() => {
      isSaving.value = false;
    });
};
</script>

<style scoped>
.v-enter-active,
.v-leave-active {
  transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
