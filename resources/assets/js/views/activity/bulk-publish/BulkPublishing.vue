<template>
  <div
    v-if="!xlsData && !(downloading && !downloadCompleted)"
    id="publishing_activities"
    :class="isLoading && 'hidden'"
    class="z-50 w-[366px]"
  >
    <div class="bulk-head flex items-center justify-between bg-eggshell p-4">
      <div class="grow text-sm font-bold leading-normal">
        {{
          translatedData[
            'workflow_frontend.bulk_publish.publishing_count_activities'
          ].replace(':count', activities && Object.keys(activities).length)
        }}
      </div>
      <div class="flex shrink-0">
        <div
          class="retry flex cursor-pointer items-center text-crimson-50"
          @click="retryPublishing"
        >
          <svg-vue class="mr-1" icon="redo" />
          <span class="text-xs uppercase">
            {{ translatedData['common.common.retry'] }}
          </span>
        </div>
        <div
          v-if="completed === 'completed'"
          class="cross cursor-pointer"
          @click="closeWindow"
        ></div>
      </div>
    </div>
    <div
      class="bulk-activities max-h-[240px] overflow-y-auto overflow-x-hidden bg-white transition-all duration-500"
    >
      <div>
        <div
          v-for="(value, name, index) in activities"
          :key="index"
          class="item flex px-4 py-3"
        >
          <div class="activity-title grow pr-2 text-sm leading-normal">
            {{ value['activity_title'] }}
          </div>
          <div class="shrink-0 text-xl">
            <svg-vue
              v-if="value['status'] === 'completed'"
              class="text-spring-50"
              icon="tick"
            />
            <svg-vue
              v-else-if="value['status'] === 'failed'"
              class="text-crimson-50"
              icon="times-circle"
            />
            <span v-else class="rolling"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import {
  onMounted,
  ref,
  reactive,
  watch,
  inject,
  onUnmounted,
  defineEmits,
} from 'vue';
import axios from 'axios';
import { detailStore } from 'Store/activities/show';
import { useStore } from 'Store/activities';

const singleStore = useStore();
const emit = defineEmits(['close']);
const store = detailStore();
const xlsData = ref(false);
const downloading = ref(false);
const downloadCompleted = ref(false);
const isLoading = ref(false);

interface RefreshToastMsgTypeface {
  visibility: boolean;
  refreshMessageType: boolean;
  refreshMessage: string;
}

let translatedData = inject('translatedData') as Record<string, string>;
let refreshToastMsg = inject('refreshToastMsg') as RefreshToastMsgTypeface;

interface paInterface {
  value: {
    publishingActivities: paElements;
  };
}

interface paElements {
  activities: actElements;
  organization_id: number;
  job_batch_uuid: string;
  status: string;
  message: string;
}

interface actElements {
  activity_id: number;
  activity_title: string;
  status: string;
}

//inject
let paStorage = inject('paStorage') as paInterface;

let activities = ref(paStorage.value.publishingActivities.activities),
  completed = ref('processing');

let hasFailedActivities = reactive({
  data: {} as actElements,
  ids: [] as number[],
  status: false,
});

let intervalID;

/**
 *   Component lifecycle - onMounted
 */
onMounted(() => {
  completed.value = paStorage.value.publishingActivities.status ?? 'processing';
  bulkPublishStatus();
  if (!(activities.value && Object.keys(activities.value).length > 0)) {
    closeWindow();
  }

  //check constantly in a inter for when support button enters the dom
  if (!xlsData.value && !(downloading.value && !downloadCompleted.value)) {
    const checkSupportButton = setInterval(() => {
      const supportButton: HTMLElement = document.querySelector(
        '#launcher'
      ) as HTMLElement;

      if (
        supportButton !== null &&
        activities.value &&
        Object.keys(activities.value).length > 0
      ) {
        supportButton.style.transform = 'translate(-350px ,-20px)';

        supportButton.style.opacity = '0';
        setTimeout(() => {
          supportButton.style.opacity = '1';
        }, 300);
        clearInterval(checkSupportButton);
      }
    }, 10);
  }

  checkXlsstatus();
  checkDownloadStatus();
});

watch(
  () => singleStore.state.bulkPublishLength,
  () => {
    bulkPublishStatus();
  },
  { deep: true }
);

setTimeout(() => {
  const supportButton: HTMLElement = document.querySelector(
    '#launcher'
  ) as HTMLElement;

  if (
    supportButton !== null &&
    activities.value &&
    Object.keys(activities.value).length > 0
  ) {
    supportButton.style.transform = 'translateX(-350px)';
    supportButton.style.opacity = '1';
  }
}, 720);
watch(
  () => singleStore.state.completeXlsDownload,
  (value) => {
    if (value) {
      downloadCompleted.value = true;
    }
  },
  { deep: true }
);
onUnmounted(() => {
  const supportButton: HTMLElement = document.querySelector(
    '#launcher'
  ) as HTMLElement;

  if (supportButton !== null) {
    supportButton.style.transform = 'translateX(0px)';
    supportButton.style.transform = 'translateY(-20px)';
  }
});

const checkXlsstatus = () => {
  axios.get('/import/xls/progress_status').then((res) => {
    xlsData.value = Object.keys(res.data.status).length > 0;
  });
};
const checkDownloadStatus = () => {
  const checkDownload = setInterval(function () {
    axios.get('/activities/download-xls-progress-status').then((res) => {
      downloading.value = !!res.data.status;
      if (res.data.status === 'completed' || !res.data.status) {
        clearInterval(checkDownload);
      }
    });
  }, 500);
};

// watching change in value of completed
watch(completed, async (newValue) => {
  if (newValue === 'completed') {
    clearInterval(intervalID);
    // check for failed publish
    failedActivities(paStorage.value.publishingActivities.activities);
  }
});

watch(
  () => store.state.isLoading,
  (value) => {
    isLoading.value = value;
  }
);

/**
 * Bulk Publish Function
 */
const bulkPublishStatus = () => {
  intervalID = setInterval(() => {
    axios
      .get(
        `/activities/bulk-publish-status?organization_id=${paStorage.value.publishingActivities.organization_id}&&uuid=${paStorage.value.publishingActivities.job_batch_uuid}`
      )
      .then((res) => {
        const response = res.data;

        if (!response.publishing) {
          clearInterval(intervalID);
        }
        if ('data' in response) {
          activities.value = response.data.activities;
          completed.value = response.data.status;

          // saving in local storage
          paStorage.value.publishingActivities.activities =
            response.data.activities;
          paStorage.value.publishingActivities.status = response.data.status;
          paStorage.value.publishingActivities.message = response.data.message;
          if (completed.value === 'completed') {
            clearInterval(intervalID);

            failedActivities(paStorage.value.publishingActivities.activities);
            refreshToastMsg.visibility = true;
            setTimeout(() => {
              refreshToastMsg.visibility = false;
            }, 10000);
          }
        } else {
          completed.value = 'completed';
        }
      });
  }, 2000);
};

/**
 * Closing window
 */
const closeWindow = () => {
  paStorage.value.publishingActivities = {} as paElements;
  emit('close');
  axios.delete(`/activities/delete-bulk-publish-status`);
};

/**
 * Function to collect failed activities
 */

const failedActivities = (nestedObject: actElements) => {
  const failedActivitiesID = [] as number[];
  const asArrayData = nestedObject && Object.entries(nestedObject);

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const filtered = asArrayData?.filter(([key, value]) => {
    if (value && Object.values(value).indexOf('failed') > -1) {
      failedActivitiesID.push(value.activity_id);
      return key;
    }
  });

  const failedActivitiesData = filtered && Object.fromEntries(filtered);

  if (failedActivitiesID.length > 0) {
    hasFailedActivities.status = true;
    hasFailedActivities.ids = failedActivitiesID;
    hasFailedActivities.data = failedActivitiesData as actElements;
    refreshToastMsg.refreshMessageType = false;
    refreshToastMsg.refreshMessage =
      translatedData[
        'workflow_frontend.bulk_publish.some_activities_have_failed_to_publish_refresh_to_see_changes'
      ];
  } else {
    hasFailedActivities.status = false;
    hasFailedActivities.ids = [];
    hasFailedActivities.data = {} as actElements;
  }
};

/**
 * Retry publishing failed activities
 */
const retryPublishing = () => {
  //reset required states
  completed.value = 'processing';

  for (const key in hasFailedActivities.data) {
    hasFailedActivities.data[key].status = 'processing';
  }

  activities.value = hasFailedActivities.data;

  // api endpoint call
  const endpoint = `/activities/start-bulk-publish?activities=[${hasFailedActivities.ids}]`;

  axios.get(endpoint).then((res) => {
    const response = res.data;

    if (response.success) {
      paStorage.value.publishingActivities = response.data;
      bulkPublishStatus();
    }
  });
};
</script>

<style lang="scss" scoped>
.minus {
  @apply flex h-3 w-3 items-center;

  &:before {
    content: '';
    @apply block h-0.5 w-3 rounded-xl bg-blue-50;
  }
}

#publishing_activities {
  @apply fixed bottom-0 right-0;
  filter: drop-shadow(0px 4px 40px rgba(0, 0, 0, 0.1));
}

.rolling {
  @apply inline-block animate-spin rounded-full border-2 border-n-20;
  width: 20px;
  height: 20px;
  border-top-color: white;
}

.cross {
  @apply relative ml-5 h-3 w-3 overflow-hidden;

  &:before,
  &:after {
    content: '';
    @apply absolute left-1/2 top-0 block h-3 w-0.5 -translate-x-1/2 rounded-xl bg-blue-50;
  }

  &:before {
    @apply rotate-45;
  }

  &:after {
    @apply -rotate-45;
  }
}

.activity-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
