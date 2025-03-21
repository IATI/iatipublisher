<template>
  <div>
    <h3 class="pb-2 text-base font-bold leading-6 text-n-50">
      {{ translatedData['common.common.publishing'] }}
    </h3>
    <div class="relative w-full rounded-lg bg-white duration-200">
      <div class="rounded-lg border border-n-20 bg-white p-4">
        <div class="flex items-center justify-between pb-4">
          <h3 class="flex items-center space-x-2 text-sm text-n-50">
            <span>
              {{ translatedData['common.common.activities'] }}
            </span>
            <span
              class="flex h-6 w-6 items-center justify-center rounded-full bg-lagoon-10 text-lagoon-50"
            >
              0
            </span>
          </h3>
        </div>
        <ShimmerLoading />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useStorage } from '@vueuse/core';
import { onMounted, watch, ref, reactive, inject, defineEmits } from 'vue';
import { useStore } from 'Store/activities';
import axios from 'axios';
import { isJson } from 'Composable/utils';
import ShimmerLoading from './ShimmerLoading.vue';

const store = useStore();
const translatedData = inject('translatedData') as Record<string, string>;

let pa = useStorage('vue-use-local-storage', {
  publishingActivities: localStorage.getItem('publishingActivities') ?? {},
});

interface actElements {
  activity_id: number;
  activity_title: string;
  status: string;
}

interface RefreshToastMsgTypeface {
  visibility: boolean;
  refreshMessageType: boolean;
  refreshMessage: string;
}

const bulkPublishLength = ref(0);
const openModel = ref(false);
const paStorage = ref({
  publishingActivities: {
    status: {},
    activities: {},
    message: {},
  },
});

const publishingActivities = reactive(
  paStorage.value.publishingActivities?.['activities']
);
const completed = ref();
const emit = defineEmits([
  'close',
  'toggle',
  'activityPublishedData',
  'hideLoader',
]);

let refreshToastMsg = inject('refreshToastMsg') as RefreshToastMsgTypeface;
let activities = ref();

let hasFailedActivities = reactive({
  data: {} as actElements,
  ids: [] as number[],
  status: false,
});

onMounted(() => {
  emit('hideLoader');

  paStorage.value = store.state.bulkpublishActivities;

  completed.value =
    paStorage?.value?.publishingActivities?.status ?? 'processing';
  bulkPublishStatus();
});

const pollingForBulkpublishData = () => {
  bulkPublishLength.value = store.state.bulkPublishLength;
  const intervalID = setInterval(() => {
    axios.get(`/activities/bulk-publish-status`).then((res) => {
      const response = res.data;

      if (!response.publishing) {
        clearInterval(intervalID);
      }
      if ('data' in response) {
        activities.value = response.data.activities;
        completed.value = response.data.status;
        emit('activityPublishedData', response.data);
        // saving in local storage
        paStorage.value = {
          publishingActivities: {
            activities: response.data.activities,
            status: response.data.status,
            message: response.data.message,
          },
        };

        if (completed.value === 'completed') {
          clearInterval(intervalID);

          failedActivities(paStorage.value.publishingActivities.activities);
          if (hasFailedActivities?.ids?.length > 0) {
            refreshToastMsg.visibility = true;
            refreshToastMsg.refreshMessageType = false;
            refreshToastMsg.refreshMessage =
              translatedData[
                'workflow_frontend.bulk_publish.some_activities_have_failed_to_publish_refresh_to_see_changes'
              ];
          } else {
            refreshToastMsg.visibility = true;
            refreshToastMsg.refreshMessage =
              translatedData[
                'common.common.activity_has_been_published_successfully_refresh_to_see_changes'
              ];
            setTimeout(() => {
              refreshToastMsg.visibility = false;
            }, 10000);
          }
        }
      } else {
        completed.value = 'completed';
      }
    });
  }, 3000);
};

const bulkPublishStatus = async () => {
  let count = 0;
  const checkStatus = setInterval(() => {
    axios.get(`/activities/bulk-publish-status`).then((res) => {
      const response = res.data;
      if ('data' in response) {
        activities.value = response.data.activities;
        completed.value = response.data.status;
        emit('activityPublishedData', response.data);
        // saving in local storage

        paStorage.value = {
          publishingActivities: {
            activities: response.data.activities,
            status: response.data.status,
            message: response.data.message,
          },
        };

        if (response.data.status !== 'completed') {
          pollingForBulkpublishData();
        }
        clearInterval(checkStatus);
      } else {
        completed.value = 'completed';
      }
    });
    if (count > 5) {
      clearInterval(checkStatus);
    }

    count++;
  }, 1000);

  await axios.get(`/activities/bulk-publish-status`).then((res) => {
    const response = res.data;
    if (!response.publishing) {
      emit('close');
    }

    if ('data' in response) {
      activities.value = response.data.activities;
      completed.value = response.data.status;
      emit('activityPublishedData', response.data);
      // saving in local storage
      paStorage.value = {
        publishingActivities: {
          activities: response.data.activities,
          status: response.data.status,
          message: response.data.message,
        },
      };

      if (response.data.status !== 'completed') {
        pollingForBulkpublishData();
      }
    } else {
      completed.value = 'completed';
    }
  });
};

const failedActivities = (nestedObject: object) => {
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

  if (failedActivitiesID?.length > 0) {
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

watch(
  () => [activities.value, bulkPublishLength.value],
  () => {
    if (activities.value) {
      emit('hideLoader');
    }
  }
);

watch(
  () => store.state.bulkpublishActivities,
  () => {
    setDataToLocalstorage();
    getDataFromLocalstorage();
  }
);
watch(
  () => openModel.value,
  (value) => emit('toggle', value)
);
const getDataFromLocalstorage = () => {
  activities.value = localStorage.getItem('bulkPublishActivities');
  activities.value = isJson(activities.value) && JSON.parse(activities.value);
};

const setDataToLocalstorage = () => {
  localStorage.setItem(
    'bulkPublishActivities',
    JSON.stringify(paStorage.value)
  );
};

const emptybulkPublishStatus = () => {
  for (const status in publishingActivities) {
    delete publishingActivities[status];
  }
};

watch(
  () => store.state.startBulkPublish,
  (value) => {
    if (value) {
      bulkPublishStatus();
    }
  }
);

watch(
  () => store.state.bulkPublishLength,
  (value) => {
    bulkPublishLength.value = value;
    pa = useStorage('vue-use-local-storage', {
      publishingActivities: localStorage.getItem('publishingActivities') ?? {},
    });
    emptybulkPublishStatus();
    bulkPublishStatus();
    Object.assign(
      publishingActivities,
      pa.value?.publishingActivities['activities']
    );
  },
  { deep: true }
);
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

.activity-title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
