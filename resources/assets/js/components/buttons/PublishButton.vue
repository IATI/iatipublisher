<!-- eslint-disable vue/no-v-html -->
<template>
  <BtnComponent
    v-if="btnText"
    :text="btnText"
    :type="type"
    icon="approved-cloud"
    @click="checkPublish"
  />
  <Modal
    :modal-active="showExistingProcessModal"
    width="583"
    @close="showExistingProcessModal = false"
  >
    <div class="popup mb-4">
      <div class="title mb-6 flex items-center text-sm">
        <svg-vue class="mr-1 text-lg text-spring-50" icon="warning" />
        <b>{{
          translatedData[
            'common.common.another_activity_is_currently_being_published'
          ]
        }}</b>
      </div>
      <div class="rounded-lg bg-[#FFF1F0] p-4">
        <div class="text-sm leading-normal">
          {{
            translatedData[
              'common.common.please_wait_for_previous_bulk_publish_to_complete_or_cancel_previous_bulk_publish'
            ]
          }}
        </div>
      </div>
    </div>
    <div class="flex justify-between space-x-2">
      <BtnComponent
        class="bg-white px-6 uppercase"
        :text="translatedData['common.common.cancel_previous_bulk_publish']"
        type=""
        @click="startNewPublishing()"
      />
      <BtnComponent
        class="bg-white px-6 uppercase"
        :text="translatedData['common.common.wait_for_completion']"
        type="primary"
        @click="showExistingProcessModal = false"
      />
    </div>
  </Modal>
  <Modal
    :modal-active="publishValue"
    width="583"
    class="outline"
    @close="publishToggle"
    @reset="resetPublishStep"
  >
    <div class="popup mb-4">
      <div v-if="hasDeprecatedValueInUse && publishStep === 0" class="my-6">
        <div class="title mb-4 flex h-5 items-center text-sm">
          <svg-vue
            icon="exclamation-warning"
            class="mr-1 h-full text-lg text-spring-50"
          />
          <b class="h-full">{{
            translatedData[
              'activity_index.publish_button.some_elements_use_deprecated_codelist_values'
            ]
          }}</b>
        </div>
        <div class="rounded-lg bg-eggshell p-4">
          <div class="text-sm leading-normal">
            {{
              translatedData[
                'activity_index.publish_button.certain_elements_in_this_activity_use_deprecated_code_list_values'
              ]
            }}
          </div>
        </div>
      </div>
    </div>
    <div class="flex justify-end">
      <div class="inline-flex">
        <template v-if="coreElementStatus">
          <BtnComponent
            v-if="publishStep == 0"
            class="bg-white px-6 uppercase"
            :text="translatedData['common.common.go_back']"
            type=""
            @click="publishValue = false"
          />
        </template>
        <template v-else>
          <BtnComponent
            v-if="publishStep == 0"
            class="space"
            :text="translatedData['common.common.add_missing_data']"
            type="primary"
            @click="publishValue = false"
          />
        </template>

        <BtnComponent
          v-if="publishStep === 3 || publishStep === 4"
          class="space"
          :text="translatedData['activity_index.publish_button.fix_issues']"
          type="primary"
          @click="resetPublishStep"
        />
      </div>
    </div>
  </Modal>
  <Loader
    v-if="loader"
    :text="loaderText"
    :translated-data="translatedData"
    :class="{ 'animate-loader': loader }"
  />
</template>

<script setup lang="ts">
import {
  defineProps,
  reactive,
  ref,
  onUpdated,
  toRefs,
  computed,
  inject,
} from 'vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';

//component
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import Loader from 'Components/sections/ProgressLoader.vue';

// Vuex Store
import { detailStore } from 'Store/activities/show';
import { useStore } from 'Store/activities';

const props = defineProps({
  type: { type: String, default: 'primary' },
  linkedToIati: { type: Boolean, required: true },
  status: { type: String, required: true },
  coreCompleted: { type: Boolean, required: true },
  activityId: { type: Number, required: true },
  publish: { type: Boolean, required: false, default: true },
  deprecationStatusMap: { type: Object, required: true },
  pa: { type: Object, required: true },
});

const showExistingProcessModal = ref(false);

const { linkedToIati, status, coreCompleted, activityId } = toRefs(props);

onUpdated(() => {
  if (loader.value) {
    store.dispatch('updateIsLoading', true);
  } else {
    store.dispatch('updateIsLoading', false);
  }
  if (loader.value) {
    publishValue.value = false;
  }
  if (publishValue.value) {
    loader.value = false;
  }
  if (publishStep.value === 1) {
    publishValue.value = false;
    setTimeout(function () {
      loader.value = true;
    }, 500);
  }
  if (
    publishStep.value === 3 ||
    publishStep.value === 2 ||
    publishStep.value === 4
  ) {
    loader.value = false;
    publishValue.value = true;
  }
});

/**
 *  Global State
 */
const store = detailStore();
const validationStore = useStore();

//activity id
const id = activityId.value;

// toggle state for modal popup
let [publishValue, publishToggle] = useToggle();

// state for step of the flow
const publishStep = ref(0);

// display/hide validator loader
const loader = ref(false);

// state for first step
// determine if core element completed or not
// true for completed and false for not completed

const coreElementStatus = coreCompleted.value;
const hasDeprecatedValueInUse = checkIfHasDeprecatedValueInUse();
const translatedData = inject('translatedData') as Record<string, string>;

function checkIfHasDeprecatedValueInUse(): boolean {
  function recursiveCheck(item): boolean {
    if (Array.isArray(item)) {
      for (const element of item) {
        if (recursiveCheck(element)) {
          return true;
        }
      }
    } else if (typeof item === 'object' && item !== null) {
      for (const key in item) {
        if (recursiveCheck(item[key])) {
          return true;
        }
      }
    } else if (item !== false) {
      return true;
    }
    return false;
  }

  return recursiveCheck(props.deprecationStatusMap);
}
// Dynamic text for loader
const loaderText = ref(translatedData['common.common.please_wait']);

// reset step to zero after closing modal
const resetPublishStep = () => {
  publishStep.value = 0;
  publishValue.value = false;
};

// reactive variable for errors number
interface Err {
  criticalNumber: number;
  errorNumber: number;
  warningNumber: number;
}

let err: Err = reactive({
  criticalNumber: 0,
  errorNumber: 0,
  warningNumber: 0,
});

const stopBulkpublish = async () => {
  await axios.get('/activities/cancel-bulk-publish');
};

// call api for publishing
interface DataTypeface {
  message: string;
  type: boolean;
  visibility: boolean;
}

const errorData = inject('errorData') as DataTypeface;

/**
 * check publish status
 */
const checkPublish = async () => {
  if (
    props.pa?.publishingActivities &&
    Object.keys(props.pa?.publishingActivities).length > 0
  ) {
    showExistingProcessModal.value = true;
    return;
  }

  try {
    let validatorSuccess = false;
    const validationResponse = await axios.get(
      `/activities/checks-for-activity-bulk-validation`
    );
    validatorSuccess = validationResponse.data.success;

    if (!validatorSuccess) {
      showExistingProcessModal.value = true;
      return;
    }

    const publishResponse = await axios.get(
      `/activities/checks-for-activity-bulk-publish`
    );
    const response = publishResponse.data;

    if (response.success) {
      stopBulkpublish();
      resetStatus();
      validationStore.state.selectedActivities = [id];
      validationStore.dispatch('updateStartCoreValidation', true);

      localStorage.setItem('isPublishedModalMinimized', 'false');
      validationStore.state.isPublishedModalMinimized = false;
      localStorage.setItem(
        'vue-use-local-storage',
        '{"publishingActivities":{}}'
      );
    } else {
      if (response.in_progress) {
        showExistingProcessModal.value = true;
      } else {
        errorData.message = response.message;
        errorData.type = response.success;
        errorData.visibility = true;
      }
    }
  } catch (error) {
    console.error('An error occurred:', error);
    // Handle error appropriately here (e.g., show an error message to the user)
  }
};

const resetStatus = () => {
  validationStore.state.publishAlertValue = false;
  validationStore.state.bulkActivityPublishStatus.completedSteps = [];
  validationStore.state.bulkActivityPublishStatus = {
    ...validationStore.state.bulkActivityPublishStatus,
    iatiValidatorLoader: false,
    validationStats: {
      ...validationStore.state.bulkActivityPublishStatus.validationStats,
      complete: 0,
      total: 0,
      failed: 0,
    },
  };

  validationStore.state.bulkActivityPublishStatus.publishing = {
    ...validationStore.state.bulkActivityPublishStatus.publishing,
    response: null,
    hasFailedActivities: {
      data: {} as any,
      ids: [],
      status: false,
    },
    activities: null,
  };
};

// publish-republish
const publishStatus = reactive({
  linked_to_iati: linkedToIati.value,
  status: status.value,
});

const btnText = computed(() => {
  if (publishStatus.linked_to_iati && publishStatus.status === 'draft') {
    return translatedData['common.common.republish'];
  } else if (
    !publishStatus.linked_to_iati &&
    publishStatus.status === 'draft'
  ) {
    return translatedData['common.common.publish'];
  } else {
    return '';
  }
});

const startNewPublishing = async () => {
  showExistingProcessModal.value = false;
  validationStore.state.startNewPublishing = {
    state: !validationStore.state.startNewPublishing.state,
  };
};
</script>
