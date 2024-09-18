<template>
  <div class="relative w-[365px] bg-n-10">
    <div class="flex justify-between">
      <h6 class="mb-2 font-bold">
        {{ translatedData['common.common.publishing_activity'] }}
        <span
          class="inline-block rounded-full bg-[#CDF8FA] py-1 px-2 text-xs font-medium text-bluecoral"
        >
          1/2
        </span>
      </h6>
      <button
        class="flex items-center gap-1.5 text-xs font-bold text-bluecoral"
        @click="handleMinimize"
      >
        <span>{{ translatedData['common.common.expand'] }}</span>
        <svg-vue class="text-[9px]" icon="open-link" />
      </button>
    </div>
    <div class="relative rounded-lg border border-n-20 bg-white p-4">
      <div class="flex justify-between">
        <div class="flex space-x-2">
          <div v-if="percentageWidth == 100" class="pb-3 text-sm text-n-50">
            {{
              translatedData[
                'workflow_frontend.validation.data_checking_complete'
              ]
            }}
            <span>{{
              hasError
                ? translatedData[
                    'workflow_frontend.validation.click_expand_for_details'
                  ]
                : translatedData[
                    'workflow_frontend.validation.click_continue_to_publish'
                  ]
            }}</span>
          </div>
          <div v-else class="text-sm text-n-50">
            {{
              translatedData[
                'common.common.checking_your_data_before_publication'
              ]
            }}
          </div>
          <div
            class="relative mx-2 flex h-5 w-5 items-center justify-center rounded-full bg-lagoon-10 text-xs font-medium text-spring-50"
          >
            {{ publishingActivityCount }}
          </div>
        </div>

        <button
          v-if="percentageWidth !== 100"
          class="flex items-center text-xs font-bold uppercase text-bluecoral"
          @click="validationCancelHandler()"
        >
          <svg-vue
            v-if="!hasError"
            class="mt-2 fill-bluecoral text-lg text-bluecoral"
            icon="cross"
          />
          <span>{{ translatedData['common.common.cancel'] }}</span>
        </button>
      </div>
      <div class="flex items-center justify-between space-x-2">
        <div
          class="my-2 mr-2 h-1.5 w-[283px] flex-1 justify-start rounded-full bg-[#C4C4C4]"
          :class="!hasError ? ' ' : '!mb-2'"
        >
          <div
            :style="{
              width: (percentageWidth ?? 0) + '%',
            }"
            class="h-full rounded-full"
            :class="
              cn('bg-spring-50', {
                'bg-[#E34D5B]': hasError && percentageWidth == 100,
              })
            "
          ></div>
        </div>
        <span v-if="hasError && percentageWidth == 100">
          <svg-vue
            class="mr-1 text-[20px] text-[#E34D5B]"
            icon="warning-fill"
          />
        </span>
      </div>
      <div>
        <div
          v-if="!hasError && percentageWidth === 100"
          class="flex items-start gap-1 border-b border-[#D0DDE0] pt-1 pb-5 text-xs font-bold text-n-50"
        >
          <svg-vue
            icon="warning-activity"
            class="flex-shrink-0 text-base text-[#E34D5B]"
          ></svg-vue>
          <span>
            {{
              translatedData[
                'workflow_frontend.validation.there_may_be_data_quality_issues_with_these_activities'
              ]
            }}
          </span>
        </div>

        <div class="flex justify-center pt-2">
          <div class="flex flex-1 items-center justify-center">
            <button
              v-if="percentageWidth === 100 && !hasError"
              class="flex items-center text-xs font-bold uppercase text-bluecoral"
              @click="validationCancelHandler()"
            >
              <svg-vue icon="cross" class="mt-2 text-lg"></svg-vue>
              <span>{{ translatedData['common.common.cancel'] }}</span>
            </button>
          </div>
          <button
            v-if="!hasError && percentageWidth == 100"
            class="flex flex-1 justify-center rounded border border-bluecoral bg-bluecoral px-3 py-2 text-xs font-bold uppercase text-white disabled:cursor-not-allowed disabled:border-0 disabled:bg-n-30 disabled:text-white"
            :disabled="isAllCriticalErrors"
            @click="startBulkPublish"
          >
            <span>{{ translatedData['common.common.continue'] }}</span>
          </button>
        </div>
      </div>
      <div
        v-if="hasError && percentageWidth === 100"
        class="flex items-center justify-between"
      >
        <span class="text-sm text-[#E34D5B]">{{
          translatedData['common.common.validation_failed']
        }}</span>
        <button
          v-if="hasError && percentageWidth == 100"
          class="flex items-center text-xs font-bold uppercase text-bluecoral"
          @click="validationCancelHandler()"
        >
          <svg-vue
            v-if="!hasError"
            class="mt-2 fill-bluecoral text-lg text-bluecoral"
            icon="cross"
          />
          <span>{{ translatedData['common.common.cancel'] }}</span>
        </button>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import {
  watch,
  defineProps,
  computed,
  ref,
  onMounted,
  defineEmits,
  defineExpose,
  watchEffect,
  inject,
} from 'vue';
import { useStore } from 'Store/activities';
import axios from 'axios';
import { cn } from '../libs/utils';

const store = useStore();
const props = defineProps({
  validationStats: {
    type: Object,
    required: true,
  },
  validationNames: {
    type: Array,
    required: true,
  },
  errorTab: {
    type: Boolean,
    required: true,
    default: false,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;
const emit = defineEmits(['stopValidation', 'proceed']);
const isAllCriticalErrors = ref(false);

//setting percentage of validation progressbar , to maintain consistency when page is reloaded or navigated
const hasError = ref(false);

//setting data from local storage to vuex ,to preserve state when window is reloaded
onMounted(() => {
  //to check if validation need to be show of not when navigated or refreshed
  let showPopup = Boolean(localStorage.getItem('activityValidating'));
  if (showPopup) {
    store.dispatch('updateStartValidation', true);
  }

  let activitiesIds = localStorage.getItem('validatingActivities');
  if (activitiesIds) {
    store.dispatch('updateValidatingActivities', activitiesIds);
  }
});

watch(
  () => props.errorTab,
  (value) => {
    hasError.value = value;
  }
);

watchEffect(() => {
  const activities = Object.keys(
    store.state.bulkActivityPublishStatus.importedActivitiesList
  )
    .map(Number)
    .filter(
      (id) =>
        store.state.bulkActivityPublishStatus.importedActivitiesList[id]
          .top_level_error !== 'critical'
    );

  isAllCriticalErrors.value = activities.length < 1;
});

const validationCancelHandler = async () => {
  emit('stopValidation');
  axios.get(`/activities/delete-validation-status`).then(() => {
    store.dispatch('updateStartValidation', false);
    store.dispatch('updateStartCoreValidation', false);
    store.dispatch('updateValidatingActivities', '');
    localStorage.removeItem('validatingActivities');
    localStorage.removeItem('activityValidating');
    store.state.publishAlertValue = false;
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

const startBulkPublish = () => {
  store.dispatch('updateStartValidation', false);
  // localStorage.removeItem('validatingActivities');
  store.dispatch('updateStartBulkPublish', true);
  emit('proceed');
  localStorage.removeItem('activityValidating');
};

const percentageWidth = computed(() => {
  return (
    ((store.state.bulkActivityPublishStatus.validationStats.complete +
      store.state.bulkActivityPublishStatus.validationStats.failed) /
      store.state.bulkActivityPublishStatus.validationStats.total) *
    100
  );
});

watchEffect(() => {
  localStorage.setItem(
    'validationPercent',
    (
      ((store.state.bulkActivityPublishStatus.validationStats.complete +
        store.state.bulkActivityPublishStatus.validationStats.failed) /
        store.state.bulkActivityPublishStatus.validationStats.total) *
      100
    ).toString()
  );

  if (percentageWidth.value === 100) {
    localStorage.setItem('activityValidating', 'false');
  }
});

const handleMinimize = () => {
  store.state.isPublishedModalMinimized = false;
  localStorage.setItem('isPublishedModalMinimized', 'false');
};

defineExpose({
  validationCancelHandler,
});

const publishingActivityCount = computed(() => {
  const { bulkActivityPublishStatus } = store.state;
  const publishingActivities =
    bulkActivityPublishStatus?.publishing?.activities;
  const publishingStatus =
    bulkActivityPublishStatus?.publishing?.response?.status;
  const validationStatsTotal =
    bulkActivityPublishStatus?.validationStats?.total || 0;

  if (publishingActivities && Object.keys(publishingActivities).length > 0) {
    if (publishingStatus === 'completed' || publishingStatus === 'processing') {
      return Object.keys(publishingActivities).length;
    }
  }

  if (validationStatsTotal > 0) {
    return validationStatsTotal;
  }

  return 0;
});
</script>
