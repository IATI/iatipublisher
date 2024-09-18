<!-- eslint-disable vue/no-v-html -->
<template>
  <div>
    <Modal :modal-active="showExistingProcessModal" width="583">
      <div class="popup mb-4">
        <div class="title mb-6 flex items-center text-sm">
          <svg-vue class="mr-1 text-lg text-spring-50" icon="warning" />
          <b>
            {{
              translatedData[
                'common.common.another_activity_is_currently_being_published'
              ]
            }}
          </b>
        </div>
        <div class="rounded-lg bg-[#FFF1F0] p-4">
          <div class="text-sm leading-normal">
            {{
              translatedData[
                'common.common.please_wait_for_previous_bulk_publish_to_complete'
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
          @click="startNewPublishing"
        />
        <BtnComponent
          class="bg-white px-6 uppercase"
          :text="translatedData['common.common.wait_for_completion']"
          type="primary"
          @click="showExistingProcessModal = false"
        />
      </div>
    </Modal>
    <template v-if="!store.state.isPublishedModalMinimized">
      <template v-if="!showExistingProcessModal">
        <Modal
          :modal-active="
            (store.state.publishAlertValue && !showExistingProcessModal) ||
            showValidationPopup ||
            (store.state.showBulkpublish &&
              pa?.publishingActivities &&
              Object.keys(pa?.publishingActivities).length > 0)
          "
          width="825"
          :disable-body-overflow="true"
        >
          <BulkPublishingModal
            :deprecation-status-map="deprecationStatusMap"
            :core-in-completed-activities="coreInCompletedActivities"
            :core-completed-activities="coreCompletedActivities"
            :core-element-loader="coreElementLoader"
            :selected-activities="store.state.selectedActivities"
            :show-validation-popup="showValidationPopup"
            :publishing-activities="pa?.publishingActivities"
            :permalink="permalink"
            @cancel-validation="() => cancelValidation()"
            @cancel-bulk-publishing="() => cancelBulkPublishing()"
            @validate-activities="() => validateActivities()"
          />
        </Modal>
      </template>
    </template>

    <PageLoader v-if="isLoading"></PageLoader>
    <Loader
      v-if="loader"
      :text="loaderText"
      :translated-data="translatedData"
      :class="{ 'animate-loader': loader }"
    />
  </div>
</template>

<script setup lang="ts">
/**
 * Verify core elements
 */
interface actTypeface {
  title: string;
  activity_id: number;
}

import {
  defineProps,
  ref,
  reactive,
  Ref,
  computed,
  onMounted,
  watch,
  provide,
  inject,
  defineExpose,
  watchEffect,
} from 'vue';

import { useStorage } from '@vueuse/core';
import axios from 'axios';
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import Loader from 'Components/sections/ProgressLoader.vue';
import PageLoader from 'Components/Loader.vue';
import BulkPublishingModal from './bulkPublishModal/BulkPublish.vue';
import { useSharedMinimize } from 'Composable/useSharedLocalStorage';
import { useStore } from 'Store/activities';
import { ErrorInterface } from 'Interfaces/ErrorInterface';

defineProps({
  type: { type: String, default: 'primary' },
});

/**
 *  Global State
 */
const store = useStore();
const sharedMinimize = useSharedMinimize();

const translatedData = inject('translatedData') as Record<string, string>;
const bulkPublishStatus = reactive({});
const isLoading = ref(false);
const startPublish = ref(false);
const showExistingProcessModal = ref(false);

const published = ref(false);

// display/hide validator loader
const loader = ref(false);
const loaderText = ref(translatedData['common.common.please_wait']);
const coreElementLoader = ref(false);

// reset step to zero after closing modal
const cancelBulkPublish = async () => {
  await axios.get('/activities/cancel-bulk-publish');
};

const cancelValidation = async () => {
  store.state.validationRunning = false;
  await axios.get(`/activities/delete-validation-status`).then(() => {
    store.dispatch('updateStartValidation', false);
    store.dispatch('updateValidatingActivities', '');
    store.dispatch('updateStartCoreValidation', false);
    localStorage.removeItem('validatingActivities');
    localStorage.removeItem('activityValidating');
    store.state.publishAlertValue = false;
    coreCompletedActivities.value = [];
    coreInCompletedActivities.value = [];
    setTimeout(() => {
      store.state.bulkActivityPublishStatus = {
        ...store.state.bulkActivityPublishStatus,
        iatiValidatorLoader: false,
        validationStats: {
          ...store.state.bulkActivityPublishStatus.validationStats,
          complete: 0,
          total: 0,
          failed: 0,
        },
      };

      store.state.bulkActivityPublishStatus.completedSteps = [];
    }, 1000);
  });
};

const cancelBulkPublishing = async () => {
  store.state.publishAlertValue = false;
  store.state.showBulkpublish = false;
  store.dispatch('updateBulkpublishActivities', {});
  store.dispatch('updateStartCoreValidation', false);

  pa.value = { publishingActivities: {} };
  cancelBulkPublish();
  await axios.delete(`/activities/delete-bulk-publish-status`);
  cancelValidation();
  setTimeout(() => {
    store.state.bulkActivityPublishStatus.completedSteps = [];
    store.state.bulkActivityPublishStatus.publishing = {
      ...store.state.bulkActivityPublishStatus.publishing,
      response: null,
      hasFailedActivities: {
        data: {} as any,
        ids: [],
        status: false,
      },
      activities: null,
    };
    coreCompletedActivities.value = [];
    coreInCompletedActivities.value = [];
  }, 2000);
};

// toast visibility

const errorData = inject('errorData') as ErrorInterface;
const displayToast = (
  message: string,
  type: boolean,
  extraInfo: unknown = null
) => {
  errorData.message = message;
  errorData.type = type;
  errorData.visibility = true;
  errorData.extra_info = extraInfo;
};

const emptybulkPublishStatus = () => {
  for (const status in bulkPublishStatus) {
    delete bulkPublishStatus[status];
  }
};

/**
 * check publish status
 */
const checkPublish = async () => {
  isLoading.value = true;
  let validatorSuccess = false;
  await axios
    .get(`/activities/checks-for-activity-bulk-validation`)
    .then((res) => {
      const response = res.data;
      validatorSuccess = response.success;
    });

  if (!validatorSuccess) {
    showExistingProcessModal.value = true;
    isLoading.value = false;
    return;
  }

  await axios
    .get(`/activities/checks-for-activity-bulk-publish`)
    .then((res) => {
      const response = res.data;

      if (response.success === true) {
        cancelBulkPublish();
        resetStatus();
        store.state.publishAlertValue = true;
        localStorage.setItem('isPublishedModalMinimized', 'false');
        store.state.isPublishedModalMinimized = false;
        localStorage.setItem(
          'vue-use-local-storage',
          '{"publishingActivities":{}}'
        );
        pa.value = { publishingActivities: {} };
        verifyCoreElements();
      } else {
        if (response?.in_progress) {
          emptybulkPublishStatus();

          Object.assign(bulkPublishStatus, response.data.activities);
          showExistingProcessModal.value = true;
        } else {
          displayToast(response.message, response.success);
        }
      }
    })
    .finally(() => (isLoading.value = false));
};

let coreCompletedActivities: Ref<actTypeface[]> = ref([]),
  coreInCompletedActivities: Ref<actTypeface[]> = ref([]),
  permalink = `/activity/`;
let deprecationStatusMap = ref([]);

const handleUnsuccessfulResponse = (response) => {
  cancelValidation();

  if (response.in_progress) {
    coreElementLoader.value = false;

    emptybulkPublishStatus();
    Object.assign(bulkPublishStatus, response.data.activities);
  } else {
    const extraInfo = response.error_type
      ? { error_type: response.error_type }
      : null;
    displayToast(response.message, response.success, extraInfo);
  }
};

const handleSuccessfulResponse = (response) => {
  coreElementLoader.value = false;

  const { core_elements_completion, deprecation_status_map } = response.data;

  coreCompletedActivities.value = core_elements_completion.complete;
  coreInCompletedActivities.value = core_elements_completion.incomplete;
  deprecationStatusMap.value = deprecation_status_map;

  const isFullyCompleted =
    deprecation_status_map.length === 0 &&
    core_elements_completion.incomplete.length === 0 &&
    core_elements_completion.complete.length !== 0;

  if (isFullyCompleted) {
    validateActivities();
  }
};
const verifyCoreElements = () => {
  coreElementLoader.value = true;
  const activities = store.state.selectedActivities.join(',');

  axios
    .get(`/activities/core-elements-completed?activities=[${activities}]`)
    .then((res) => {
      const response = res.data;

      if (response.success) {
        handleSuccessfulResponse(response);
      } else {
        handleUnsuccessfulResponse(response);
      }
    });
};

/**
 * Validating Activities
 */
let validationErrors = ref({});
onMounted(() => {
  axios
    .get(
      `/activities/bulk-publish-status?organization_id=${pa.value?.publishingActivities?.organization_id}&&uuid=${pa.value?.publishingActivities?.job_batch_uuid}`
    )
    .then((res) => {
      if (res.data.publishing) {
        if (pa.value?.publishingActivities && res.data?.data) {
          try {
            const data = res.data.data;
            Object.assign(pa.value.publishingActivities, data);

            if (Object.keys(data).length > 0) {
              if (data.status === 'completed') {
                store.state.bulkActivityPublishStatus.completedSteps = [1, 2];
              } else {
                store.state.bulkActivityPublishStatus.completedSteps = [1];
              }
            }
          } catch (error) {
            console.error('Error parsing data', error);
          }
        }
      } else {
        pa.value = { publishingActivities: {} };
        localStorage.setItem(
          'vue-use-local-storage',
          '{"publishingActivities":{}}'
        );
      }
    })
    .catch((error) => {
      console.error('Error fetching data', error);
    });
});

const stopValidating = async () => {
  await axios.get(`/activities/delete-validation-status`).then(() => {
    store.dispatch('updateStartValidation', false);
    store.dispatch('updateValidatingActivities', '');
    localStorage.removeItem('validatingActivities');
    localStorage.removeItem('activityValidating');
  });
};

const startValidation = async () => {
  try {
    const activities = store.state.selectedActivities.join(',');
    await stopValidating();
    store.dispatch('updateStartValidation', true);

    store.dispatch('updateValidatingActivities', activities);
    localStorage.setItem('validatingActivities', activities);
    store.dispatch('updateStartBulkPublish', false);
    await cancelBulkPublish();

    const res = await axios.post(
      `/activities/validate-activities?activities=[${activities}]`
    );
    const response = res.data;
    store.dispatch('updateValidatingActivitiesNames', response.activities);
    localStorage.setItem(
      'validatingActivitiesNames',
      response.activities.join('|')
    );
    if (response.success) {
      validationErrors.value = response.data;
    } else {
      displayToast(response.message, response.success);
    }
  } catch (error) {
    console.error('Validation error:', error);
  }
};

const validateActivities = async () => {
  store.state.bulkActivityPublishStatus.iatiValidatorLoader = true;
  startValidation();
};

/**
 * Bulk publishing activities
 */

// let selectedActivities: Ref<number[]> = ref([]);
provide('selectedActivities', store.state.selectedActivities);

// local storage for publishing
interface paType {
  publishingActivities: {
    organization_id?: string;
    job_batch_uuid?: string;
    activities?: object;
    status?: string;
    message?: string;
  };
}

const pa: Ref<paType> = useStorage('vue-use-local-storage', {
  publishingActivities: localStorage.getItem('publishingActivities') ?? {},
});

const startBulkPublish = () => {
  store.dispatch('updateStartBulkPublish', true);
  loaderText.value = 'Starting to publish';
  if (pa.value) {
    localStorage.setItem(
      'vue-use-local-storage',
      '{"publishingActivities":{}}'
    );
    pa.value = { publishingActivities: {} };
  } else {
    console.error('pa.value is undefined');
  }
  axios
    .get(
      `/activities/start-bulk-publish?activities=[${store.state.validatingActivities}]`
    )
    .then((res) => {
      store.dispatch('updateStartBulkPublish', true);
      store.dispatch('updateValidatingActivities', '');
      startPublish.value = true;
      const response = res.data;
      if (response.success) {
        pa.value.publishingActivities = response.data;
      } else {
        loader.value = false;
        cancelValidation();
        if (response?.in_progress) {
          emptybulkPublishStatus();
          Object.assign(bulkPublishStatus, response.data.activities);
          Object.assign(
            pa.value.publishingActivities,
            response.data.activities
          );
          store.dispatch(
            'updateBulkpublishActivities',
            response.data.activities
          );
        } else {
          displayToast(response.message, response.success);
        }
      }
      setTimeout(() => {
        loader.value = false;
        published.value = true;
      }, 1000);
    });
};

watch(
  () => pa.value,
  () => {
    store.dispatch(
      'updateBulkPublishLength',
      pa?.value?.publishingActivities?.activities &&
        Object.keys(pa?.value?.publishingActivities?.activities as string[])
          .length
    );
    store.dispatch('updateBulkpublishActivities', pa?.value);
  },
  { deep: true }
);

watch(
  () => store.state.startBulkPublish,
  (value) => {
    if (value) {
      if (store.state.startBulkPublish) {
        startBulkPublish();
      }
    }
  },
  { deep: true }
);

const showValidationPopup = computed(() => {
  if (window.location.pathname !== '/activities') return false;
  return store.state.startValidation || store.state.validationRunning;
});

const startNewPublishing = async () => {
  // Run all three functions in parallel and wait for all of them to complete
  await Promise.all([
    cancelBulkPublish(),
    cancelBulkPublishing(),
    cancelValidation(),
  ]);
  // Perform the other tasks after the previous functions complete
  showExistingProcessModal.value = false;
  // Wait for 3 seconds before running checkPublish
  await new Promise((resolve) => setTimeout(resolve, 1500));
  // Run the final function after the delay
  await checkPublish();
};

const resetStatus = () => {
  store.state.bulkActivityPublishStatus.completedSteps = [];
  store.state.bulkActivityPublishStatus = {
    ...store.state.bulkActivityPublishStatus,
    iatiValidatorLoader: false,
    validationStats: {
      ...store.state.bulkActivityPublishStatus.validationStats,
      complete: 0,
      total: 0,
      failed: 0,
    },
  };

  store.state.bulkActivityPublishStatus.publishing = {
    ...store.state.bulkActivityPublishStatus.publishing,
    response: null,
    hasFailedActivities: {
      data: {} as any,
      ids: [],
      status: false,
    },
    activities: null,
  };
};

watch(
  () => showValidationPopup.value,
  (value) => {
    if (value) {
      store.state.bulkActivityPublishStatus.completedSteps = [];
    }
  }
);

watch(
  () => store.state.startCoreValidation,
  (value) => {
    if (value) {
      verifyCoreElements();
      store.state.publishAlertValue = true;
    }
  }
);

watch(
  () => store.state.startNewPublishing,
  () => {
    startNewPublishing();
  },
  { deep: true }
);

watchEffect(() => {
  if (sharedMinimize.value) {
    store.state.isPublishedModalMinimized = sharedMinimize.value;
  }
});

provide('paStorage', pa);
provide('bulkPublishStatus', bulkPublishStatus);
provide('startPublish', startPublish);
defineExpose({ checkPublish });
</script>
