<template>
  <!-- Modal -->
  <Transition name="fade">
    <div
      v-if="modalState"
      class="fixed inset-0 z-[999999] flex items-center justify-center bg-black bg-opacity-40"
    >
      <div class="relative flex bg-white">
        <!-- Left -->
        <div class="max-w-[365px] bg-bluecoral px-[35px] py-12 text-white">
          <h3 class="text-[28px] font-bold leading-9">
            {{ translatedData['onboarding.onboarding_index.get_started_with'] }}
            <br />
            <span class="flex items-center gap-3">
              <span> IATI Publisher </span>
              <span>
                <svg-vue icon="hand-wave" />
              </span>
            </span>
          </h3>
          <p class="pt-[2px] text-xs">
            {{
              translatedData[
                'onboarding.onboarding_index.to_get_you_started_with_publishing'
              ]
            }}
          </p>
          <StepBar
            :current-step="step"
            :steps="organizationSteps"
            @change-step="changeStep"
          />
        </div>
        <!-- Right -->
        <div
          class="relative flex w-[900px] items-center justify-center px-[40px]"
        >
          <Transition mode="out-in">
            <div v-if="step === 1" class="h-full">
              <PublishingSettingsStep
                :publisher-id="props.organization.publisher_id"
                :organization-id="props.organization.id"
                :registration-type="props.organization.registration_type"
                :publisher-setting="publisherSetting"
                :fetch-data="fetchData"
                :initial-render="initialRender"
                :status="
                  organizationSteps?.find(
                    (onboardingStep) => onboardingStep.step === 1
                  )?.complete ?? false
                "
                @proceed-step="proceedStep"
                @change-render="handleChangeRender"
                @complete-step="completeStep"
                @remove-completed-step="removeCompletedStep"
              />
            </div>

            <div
              v-else-if="step === 2"
              :class="{
                'h-full': organizationSteps?.find(
                  (onboardingStep) => onboardingStep.step === 2
                )?.complete,
              }"
              class="w-full"
            >
              <DefaultValuesStep
                :currencies="props.currencies"
                :languages="props.languages"
                :humanitarian="props.humanitarian"
                :default-flow-type="props.defaultFlowType"
                :default-finance-type="props.defaultFinanceType"
                :default-aid-type="props.defaultAidType"
                :default-tied-status="props.defaultTiedStatus"
                :default-values="defaultValue"
                :status="
                  organizationSteps?.find(
                    (onboardingStep) => onboardingStep.step === 2
                  )?.complete ?? false
                "
                :fetch-data="fetchData"
                @proceed-step="proceedStep"
                @previous-step="previousStep"
                @complete-step="completeStep"
                @remove-completed-step="removeCompletedStep"
              />
            </div>

            <div
              v-else-if="step === 3"
              :class="{
                'h-full': organizationSteps?.find(
                  (onboardingStep) => onboardingStep.step === 3
                )?.complete,
              }"
              class="w-full"
            >
              <OrganisationDataStep
                :organization-type-options="props.organizationType"
                :previous-values="props.organization.reporting_org"
                :fetch-data="fetchData"
                :status="
                  organizationSteps?.find(
                    (onboardingStep) => onboardingStep.step === 3
                  )?.complete ?? false
                "
                @proceed-step="proceedStep"
                @previous-step="previousStep"
                @complete-step="completeStep"
                @remove-completed-step="removeCompletedStep"
              />
            </div>

            <div v-else class="h-full self-start">
              <ActivityStep
                :status="
                  organizationSteps?.find(
                    (onboardingStep) => onboardingStep.step === 4
                  )?.complete
                "
                @proceed-step="proceedStep"
                @previous-step="previousStep"
              />
            </div>
          </Transition>
        </div>
        <!-- Close Button -->
        <button class="absolute top-4 right-4" @click.once="closeModal">
          <svg-vue class="text-black" icon="cancel-cross" />
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, defineProps, watchEffect, onMounted, inject } from 'vue';
import { useStorage } from '@vueuse/core';

import StepBar from 'Components/StepBar.vue';
import PublishingSettingsStep from './Steps/PublishingSettingsStep.vue';
import DefaultValuesStep from './Steps/DefaultValuesStep.vue';
import OrganisationDataStep from './Steps/OrganisationDataStep.vue';
import ActivityStep from './Steps/ActivityStep.vue';
import axios from 'axios';

interface OrganizationSteps {
  step: number;
  title: string;
  complete: boolean;
}

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
  organizationOnboarding: {
    type: Object,
    required: true,
  },
  organization: {
    type: Object,
    required: true,
  },
  organizationType: {
    type: Object,
    required: true,
  },
  isFirstTime: {
    type: Boolean,
    required: true,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;
const step = ref(1);
const modalState = ref(true);
const initialRender = ref(true);

const publisherSetting = ref({});
const defaultValue = ref({});

const isModelCloseClicked = useStorage(
  'isModelCloseClicked',
  false,
  sessionStorage
);

const isForceOpenModal =
  sessionStorage.getItem('isForceOpenModal') === 'true' ? true : false;

const organizationSteps = ref<OrganizationSteps[]>(
  props.organizationOnboarding.steps_status
);

const firstIncompleteStep =
  organizationSteps.value.findIndex(
    (step: { complete: boolean }) => !step.complete
  ) + 1;

if (!props.isFirstTime && firstIncompleteStep > 0) {
  step.value = firstIncompleteStep;
}

const fetchData = () => {
  axios
    .get('/setting/data')
    .then((res) => {
      publisherSetting.value = res?.data?.data?.publishing_info;
      defaultValue.value = {
        ...res?.data?.data?.activity_default_values,
        ...res?.data?.data?.default_values,
      };
    })
    .catch((err) => console.log('Error', err));
};

if (isForceOpenModal) {
  modalState.value = true;
} else if (
  props.organizationOnboarding.completed_onboarding ||
  props.organizationOnboarding.dont_show_again ||
  isModelCloseClicked.value
) {
  modalState.value = false;
}

watchEffect(() => {
  if (modalState.value) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = 'auto';
  }
});

const closeModal = () => {
  isModelCloseClicked.value = true;
  sessionStorage.setItem('isForceOpenModal', 'false');
  modalState.value = false;
};

const proceedStep = () => {
  initialRender.value = false;
  if (step.value < 4) {
    step.value++;
  } else {
    closeModal();
  }
};

const previousStep = () => {
  initialRender.value = false;
  step.value--;
};

const handleChangeRender = () => {
  initialRender.value = false;
};

const completeStep = (step: number) => {
  organizationSteps.value[step - 1].complete = true;
};

const removeCompletedStep = (step: number) => {
  organizationSteps.value[step - 1].complete = false;
};

const changeStep = (index: number) => {
  step.value = index;
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  scale: 0;
}

.v-enter-active,
.v-leave-active {
  transition: all 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
}
</style>
