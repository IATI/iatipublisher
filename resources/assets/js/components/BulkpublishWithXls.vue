<template>
  <div>
    <div class="flex items-center justify-between">
      <h3
        class="flex items-center gap-2 pb-2 text-base font-bold leading-6 text-n-50"
      >
        <span>
          {{
            translatedData[
              'workflow_frontend.bulk_publish.publishing_activities'
            ]
          }}
        </span>
        <span
          v-if="
            percentageWidth === 100 &&
            store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
              ?.ids?.length === 0
          "
          class="inline-block rounded-full bg-[#CDF8FA] px-2 py-0.5 text-xs font-medium leading-[18px] text-[#3C7080]"
        >
          {{ translatedData['common.common.completed'] }}
        </span>
        <span
          v-else
          class="inline-block rounded-full bg-[#CDF8FA] py-1 px-2 text-xs font-medium text-bluecoral"
        >
          2/2
        </span>
      </h3>
      <button
        v-if="percentageWidth !== 100"
        class="flex items-center gap-1.5 text-xs font-bold text-bluecoral"
        @click="handleMinimize"
      >
        <span>{{ translatedData['common.common.expand'] }}</span>
        <svg-vue class="text-[9px]" icon="open-link" />
      </button>
      <button
        v-else
        class="text-xs font-bold uppercase text-bluecoral"
        @click="
          () => {
            $emit('close');
          }
        "
      >
        <svg-vue icon="cross" class="mt-2 text-lg text-bluecoral" />
        <span>{{ translatedData['common.common.clear'] }}</span>
      </button>
    </div>
    <div class="relative w-full rounded-lg bg-white duration-200">
      <div class="rounded-lg border border-n-20 bg-white p-4">
        <div class="flex items-center justify-between">
          <h3 class="flex items-center space-x-2 text-sm text-n-50">
            <span>{{ translatedData['common.common.activities'] }}</span>
            <span
              class="flex h-6 w-6 items-center justify-center rounded-full bg-lagoon-10 font-medium text-spring-50"
              >{{
                bulkPublishLength > 0
                  ? bulkPublishLength
                  : (store.state.bulkActivityPublishStatus.publishing
                      .activities &&
                      Object.keys(
                        store.state.bulkActivityPublishStatus.publishing
                          .activities
                      ).length) ||
                    0
              }}
            </span>
          </h3>
          <div class="flex items-center gap-3">
            <div
              v-if="
                store.state.bulkActivityPublishStatus.publishing
                  .hasFailedActivities?.ids?.length > 0
              "
              class="retry flex cursor-pointer items-center font-bold text-bluecoral"
              @click="retryPublishing"
            >
              <svg-vue class="mr-1" icon="redo" />
              <span>{{ translatedData['common.common.retry'] }}</span>
            </div>
            <button
              v-if="percentageWidth === 100"
              class="text-xs font-bold capitalize text-bluecoral"
              @click="handleMinimize"
            >
              {{ translatedData['workflow_frontend.validation.view_detail'] }}
            </button>
            <button
              v-else
              class="text-xs font-bold uppercase text-bluecoral"
              @click="
                () => {
                  $emit('close');
                }
              "
            >
              <svg-vue icon="cross" class="mt-2 text-lg text-bluecoral" />
              <span>{{ translatedData['common.common.cancel'] }}</span>
            </button>
          </div>
        </div>
        <template v-if="percentageWidth !== 100">
          <div
            v-if="
              store.state.bulkActivityPublishStatus.publishing
                .hasFailedActivities?.ids?.length === 0
            "
            class="mb-3 flex items-center pt-4"
          >
            <div
              class="flex h-1 flex-1 justify-start rounded-full bg-spring-10"
            >
              <div
                :style="{ width: percentageWidth + '%' }"
                class="h-full rounded-full bg-spring-50"
              ></div>
            </div>
          </div>
        </template>

        <div
          v-if="
            store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
              ?.ids?.length > 0
          "
          class="py-2 text-sm font-medium text-crimson-50"
        >
          <div class="flex items-center gap-6">
            <div class="h-1 w-full bg-crimson-50"></div>
            <svg-vue icon="warning-fill" class="flex-shrink-0 text-lg" />
          </div>
          <div class="pt-2">
            {{ translatedData['common.common.validation_failed'] }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { useStorage } from '@vueuse/core';
import {
  onMounted,
  watch,
  computed,
  ref,
  reactive,
  inject,
  defineEmits,
  onUnmounted,
} from 'vue';
import { useStore } from 'Store/activities';
import axios from 'axios';
import { isJson } from 'Composable/utils';

const store = useStore();
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
const translatedData = inject('translatedData') as Record<string, string>;

onMounted(() => {
  setTimeout(() => {
    emit('hideLoader');
  }, 50);

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
        store.state.bulkActivityPublishStatus.publishing.response =
          response.data;
        store.state.bulkActivityPublishStatus.publishing.activities =
          response.data.activities;
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
          store.state.bulkActivityPublishStatus.completedSteps = [1, 2];
          failedActivities(paStorage.value.publishingActivities.activities);
          if (
            store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
              ?.ids?.length > 0
          ) {
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
        store.state.bulkActivityPublishStatus.publishing.response =
          response.data;
        store.state.bulkActivityPublishStatus.publishing.activities =
          response.data.activities;
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

        if (response.data.status === 'completed') {
          failedActivities(paStorage.value.publishingActivities.activities);
        }

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
};

const retryPublishing = () => {
  completed.value = 'processing';
  store.state.bulkActivityPublishStatus.completedSteps = [1];
  store.state.bulkActivityPublishStatus.publishing.response = null;
  for (const key in store.state.bulkActivityPublishStatus.publishing
    .hasFailedActivities.data) {
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.data[
      key
    ].status = 'processing';
  }

  store.state.bulkActivityPublishStatus.publishing.activities =
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.data;

  // api endpoint call
  const endpoint = `/activities/start-bulk-publish?activities=[${store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.ids}]`;
  store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.status =
    false;
  store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.ids = [];
  store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.data =
    {} as actElements;

  axios.get(endpoint).then((res) => {
    const response = res.data;

    if (response.success) {
      paStorage.value.publishingActivities = response.data;
      bulkPublishStatus();
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
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.status =
      true;
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.ids =
      failedActivitiesID;
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.data =
      failedActivitiesData as actElements;
    refreshToastMsg.refreshMessageType = false;
    refreshToastMsg.refreshMessage =
      translatedData[
        'workflow_frontend.bulk_publish.some_activities_have_failed_to_publish_refresh_to_see_changes'
      ];
  } else {
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.status =
      false;
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.ids =
      [];
    store.state.bulkActivityPublishStatus.publishing.hasFailedActivities.data =
      {} as actElements;
  }
};

const completedActivities = computed(() => {
  let count = 0;
  for (
    let i = 0;
    i <
    (paStorage.value?.publishingActivities?.['activities'] &&
      Object.values(paStorage?.value?.publishingActivities?.['activities'])
        .length);
    i++
  ) {
    if (
      Object.values(
        paStorage?.value?.publishingActivities?.['activities'] as object
      )[i]['status'] === 'completed'
    ) {
      count++;
    }
  }

  return count;
});

const percentageWidth = computed(() => {
  return (
    (completedActivities.value /
      (pa.value?.publishingActivities['activities'] &&
        Object.keys(pa.value?.publishingActivities['activities']).length)) *
    100
  );
});

watch(
  () => [
    store.state.bulkActivityPublishStatus.publishing.activities,
    bulkPublishLength.value,
  ],
  () => {
    if (store.state.bulkActivityPublishStatus.publishing.activities) {
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
  store.state.bulkActivityPublishStatus.publishing.activities =
    localStorage.getItem('bulkPublishActivities');
  store.state.bulkActivityPublishStatus.publishing.activities =
    isJson(store.state.bulkActivityPublishStatus.publishing.activities) &&
    JSON.parse(store.state.bulkActivityPublishStatus.publishing.activities);
};

const setDataToLocalstorage = () => {
  localStorage.setItem(
    'bulkPublishActivities',
    JSON.stringify(paStorage.value)
  );
};

onUnmounted(() => {
  store.dispatch('updateStartBulkPublish', false);
});

const emptybulkPublishStatus = () => {
  for (const status in publishingActivities) {
    delete publishingActivities[status];
  }
};

const handleMinimize = () => {
  store.state.isPublishedModalMinimized = false;
  localStorage.setItem('isPublishedModalMinimized', 'false');
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

watch(
  () => store.state.startPublishingRetry,
  () => {
    retryPublishing();
  }
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
