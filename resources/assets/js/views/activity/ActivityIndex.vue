<template>
  <div
    id="activity-listing-page"
    class="page-height bg-paper px-5 pb-[71px] pt-4 xl:px-10"
  >
    <div id="activity">
      <Loader v-if="isLoading"></Loader>
      <PageTitle :is-disabled-publish="isDisabledPublish" />
      <div class="overflow-hidden" :class="{ 'bg-white': isEmpty }">
        <ErrorMessage :is-empty="isEmpty"></ErrorMessage>
        <EmptyActivity v-if="isEmpty" />

        <div v-if="!isEmpty" class="mb-4 flex gap-2">
          <div
            class="tooltip-btn flex justify-center"
            :class="currentFilterBy === 'all' ? 'active' : ''"
          >
            <button @click="filterByPublishStatus('all')">
              <svg-vue icon="bill" class="n-10" />
              <span>
                {{ translatedData['activity_index.activity_index.all'] }} ({{
                  allPublishStatusCountMap.all
                }})
              </span>
            </button>
          </div>
          <div
            class="tooltip-btn flex justify-center"
            :class="currentFilterBy === 'published' ? 'active' : ''"
          >
            <button @click="filterByPublishStatus('published')">
              <svg-vue icon="approved-cloud" />
              <span>
                {{ translatedData['common.common.published'] }}
                ({{ allPublishStatusCountMap.published }})
              </span>
            </button>
          </div>
          <div
            class="tooltip-btn flex justify-center"
            :class="
              currentFilterBy === 'ready_for_republishing' ? 'active' : ''
            "
          >
            <button @click="filterByPublishStatus('ready_for_republishing')">
              <svg-vue icon="cancel-cloud" />
              <span>
                {{
                  translatedData[
                    'activity_index.activity_index.ready_for_republishing'
                  ]
                }}
                ({{ allPublishStatusCountMap.ready_for_republishing }})
              </span>
            </button>
          </div>
          <div
            class="tooltip-btn flex justify-center"
            :class="currentFilterBy === 'draft' ? 'active' : ''"
          >
            <button @click="filterByPublishStatus('draft')">
              <svg-vue icon="document-write" />
              <span>
                {{ translatedData['activity_index.activity_index.draft'] }} ({{
                  allPublishStatusCountMap.draft
                }})
              </span>
            </button>
          </div>
        </div>

        <TableLayout
          v-if="!isEmpty"
          :data="activities"
          :loader="tableLoader"
          :current-page="currentPage"
          @show-or-hide="showOrHide"
        />
        <div v-if="!isEmpty" class="mt-6">
          <Pagination
            v-if="activities && activities.last_page > 1"
            :data="activities"
            :reset="paginationReset"
            @fetch-activities="fetchActivities"
          />
        </div>
      </div>
    </div>
    <XlsUploadIndicator
      v-if="
        (xlsData ||
          store.state.startValidation ||
          (downloading && !downloadCompleted) ||
          publishingActivities ||
          startBulkPublish) &&
        !activityStore.state.isLoading
      "
      :total-count="totalCount"
      :processed-count="processedCount"
      :xls-failed="xlsFailed"
      :activity-name="activityName"
      :xls-data="xlsData"
      :completed="uploadComplete"
    />
  </div>

  <OnBoardingIndex
    :currencies="$props.currencies"
    :languages="$props.languages"
    :humanitarian="$props.humanitarian"
    :default-flow-type="$props.defaultFlowType"
    :default-finance-type="$props.defaultFinanceType"
    :default-aid-type="$props.defaultAidType"
    :default-tied-status="$props.defaultTiedStatus"
    :organization-onboarding="$props.organizationOnboarding"
    :organization="$props.organization"
    :organization-type="$props.organizationType"
    :is-first-time="$props.isFirstTime"
  />
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  provide,
  reactive,
  ref,
  watch,
  Ref,
  watchEffect,
} from 'vue';
import { watchIgnorable } from '@vueuse/core';
import axios from 'axios';
import XlsUploadIndicator from 'Components/XlsUploadIndicator.vue';
import OnBoardingIndex from './onboarding/OnBoardingIndex.vue';
import EmptyActivity from './partials/EmptyActivity.vue';
import TableLayout from './partials/TableLayout.vue';
import Pagination from 'Components/TablePagination.vue';
import PageTitle from './partials/PageTitle.vue';
import Loader from 'Components/Loader.vue';
import ErrorMessage from 'Components/ErrorMessage.vue';
import { useStore } from 'Store/activities';
import { detailStore } from 'Store/activities/show';
import { useStorage } from '@vueuse/core';

const store = useStore();
const activityStore = detailStore();
export default defineComponent({
  name: 'ActivityComponent',
  components: {
    EmptyActivity,
    PageTitle,
    Pagination,
    TableLayout,
    Loader,
    ErrorMessage,
    XlsUploadIndicator,
    OnBoardingIndex,
  },
  props: {
    toast: {
      type: Object,
      required: true,
    },
    defaultLanguage: {
      type: Object,
      required: true,
    },
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
    translatedData: {
      type: Object,
      required: true,
    },
    currentLanguage: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    interface ActivitiesInterface {
      data: any;
      last_page: number;
    }
    const activities = reactive({}) as ActivitiesInterface;
    const isLoading = ref(true);
    const activityName = ref('');
    const fileCount = ref(0);
    const downloadCompleted = ref(false);
    const closeModel = ref(false);
    const xlsDownloadStatus = ref('');
    const xlsData = ref(false);
    const downloading = ref(false);
    const startBulkPublish = ref(false);
    const xlsFailed = ref(false);
    const xlsFailedMessage = ref('');
    const processing = ref();
    const publishingActivities = ref();
    const uploadComplete = ref(false);
    const importCompleted = ref(false);
    const totalCount = ref();
    const processedCount = ref();
    const showXlsStatus = ref(true);
    const tableLoader = ref(true);
    const downloadApiUrl = ref('');
    const currentURL = window.location.href;
    const currentFilterBy = ref('');
    let endpoint = '';
    let showEmptyTemplate = false;
    let currentPage = ref(1);
    const validFilterBy = [
      'all',
      'published',
      'ready_for_republishing',
      'draft',
    ];
    const allPublishStatusCountMap = ref({
      all: 0,
      published: 0,
      ready_for_republishing: 0,
      draft: 0,
    });

    const paginationReset = ref(false);
    const isDisabledPublish = ref(false);

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

    if (currentURL.includes('?')) {
      const queryString = window.location.search;
      endpoint = `/activities/page${queryString}`;
    } else {
      endpoint = `/activities/page`;
      showEmptyTemplate = true;
    }

    //for session message
    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    const errorData = reactive({
      visibility: false,
      message: '',
      type: true,
    });

    // for publish button
    const toastMessage = reactive({
      visibility: false,

      message: '',
      type: false,
    });
    const pollingForXlsStatus = () => {
      const checkStatus = setInterval(function () {
        axios.get('/import/xls/status').then((res) => {
          if (res.data.data?.message === 'Started') {
            //reset
            totalCount.value = null;
            processedCount.value = 0;
            xlsFailed.value = false;
            xlsFailedMessage.value = '';
          } else {
            totalCount.value = res.data.data?.total_count;
            processedCount.value = res.data.data?.processed_count;
            xlsFailed.value = !res.data.data?.success;
            xlsFailedMessage.value = res.data.data?.message;
          }

          if (res.data.data?.message === 'Processing') {
            processing.value = true;
          }

          if (
            !res.data?.data?.success ||
            res.data?.data?.message === 'Complete'
          ) {
            uploadComplete.value = true;
            clearInterval(checkStatus);
          }
        });
      }, 2500);
    };
    watch(
      () => store.state.startXlsDownload,
      (value) => {
        if (value) {
          checkDownloadStatus();
        }
      },
      { deep: true }
    );

    watch(
      () => [store.state.startBulkPublish, store.state.bulkpublishActivities],
      (value) => {
        if (value) {
          startBulkPublish.value = true;
          publishingActivities.value =
            store.state.bulkpublishActivities.publishingActivities;
          return;
        }
        startBulkPublish.value = false;
      },
      { deep: true }
    );
    watch(
      () => store.state.completeXlsDownload,
      (value) => {
        if (value) {
          downloadCompleted.value = true;
          store.dispatch('updateStartXlsDownload', false);
        }
      },
      { deep: true }
    );
    watch(
      () => store.state.closeXlsModel,
      (value) => {
        if (value) {
          checkXlsStatus();
        }
      }
    );

    watchEffect(() => {
      const status = Object.values(store.state.selectedActivityStatus).map(
        (item) => item.status
      );
      if (status.every((item) => item === 'published') && status.length > 0) {
        isDisabledPublish.value = true;
      } else {
        isDisabledPublish.value = false;
      }
    });

    const checkXlsStatus = () => {
      axios.get('/import/xls/poll-import-progress-status').then((res) => {
        activityName.value = res?.data?.status?.template;
        xlsData.value = Object.keys(res.data.status).length > 0;

        if (res?.data?.status?.status === 'completed') {
          uploadComplete.value = true;
        } else if (res?.data?.status?.status === 'failed') {
          xlsFailed.value = true;
          xlsFailedMessage.value = res?.data?.status?.message;
        } else if (Object.keys(res.data.status).length > 0) {
          {
            //reset
            totalCount.value = null;
            processing.value = false;
            processedCount.value = 0;
            xlsFailed.value = false;
            xlsFailedMessage.value = '';

            pollingForXlsStatus();
          }
        }
      });
    };
    const checkDownloadStatus = () => {
      downloading.value = false;

      const checkDownload = setInterval(function () {
        axios.get('/activities/download-xls-progress-status').then((res) => {
          fileCount.value = res.data.file_count;
          xlsDownloadStatus.value = res.data.status;
          downloadApiUrl.value = res.data.url;
          downloading.value = !!res.data.status;
          if (
            xlsDownloadStatus.value === 'completed' ||
            xlsDownloadStatus.value === 'failed' ||
            !res.data.status
          ) {
            clearInterval(checkDownload);
          }
        });
      }, 3000);
    };

    watch(
      () => store.state.closeXlsModel,
      () => {
        checkDownloadStatus();
      }
    );

    onMounted(async () => {
      fetchActivitiesCountByPublishStatus();
      publishingActivities.value = pa.value?.publishingActivities;

      checkXlsStatus();
      checkDownloadStatus();

      currentFilterBy.value = getCurrentFilterBy();

      if (props.toast.message !== '') {
        toastData.type = props.toast.type;
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }
    });
    onMounted(async () => {
      tableLoader.value = true;
      axios.get(endpoint).then((res) => {
        const response = res.data;
        Object.assign(activities, response.data);
        isLoading.value = false;
        tableLoader.value = false;

        if (showEmptyTemplate) {
          isEmpty.value = !response.data.data.length;
        }
      });
    });
    watch(
      () => toastData.visibility,
      () => {
        setTimeout(() => {
          toastData.visibility = false;
          ignoreToastUpdate();
        }, 10000);
      }
    );

    const state = reactive({
      showButtons: false,
    });

    const isEmpty = ref(false);

    const showOrHide = (data = Array) => {
      if (data.length > 0) {
        state.showButtons = true;
      } else {
        state.showButtons = false;
      }
    };

    async function fetchActivities(active_page: number) {
      tableLoader.value = true;
      let queryString = '';

      if (window.location.search) {
        queryString = window.location.search;
      }

      await axios
        .get(`/activities/page/${active_page}${queryString}`)
        .then((res) => {
          const response = res.data;
          Object.assign(activities, response.data);
          isEmpty.value = !response.data;
          currentPage.value = active_page;
        });
      tableLoader.value = false;
    }

    const { ignoreUpdates } = watchIgnorable(toastData, () => undefined, {
      flush: 'sync',
    });

    const ignoreToastUpdate = () => {
      ignoreUpdates(() => {
        toastData.message = '';
      });
    };

    // for refresh toast message
    // let refreshToastMsg = ref(false);
    const refreshToastMsg = reactive({
      visibility: false,
      refreshMessageType: true,
      refreshMessage:
        props.translatedData[
          'common.common.activity_has_been_published_successfully_refresh_to_see_changes'
        ],
    });

    function filterByPublishStatus(status) {
      let queryString = window.location.search;
      let params = new URLSearchParams(queryString);

      if (!params.has('q')) {
        params.set('q', '');
      }

      params.set('filterBy', status);

      window.history.pushState(
        {},
        '',
        `${window.location.pathname}?${params.toString()}`
      );
      currentFilterBy.value = status;

      paginationReset.value = true;

      fetchActivities(1);

      setTimeout(() => {
        paginationReset.value = false;
      }, 0);
    }

    function getCurrentFilterBy() {
      let queryString = window.location.search;

      if (queryString.length > 0) {
        let urlParams = new URLSearchParams(queryString);
        let filterBy = urlParams.get('filterBy');

        if (filterBy && validFilterBy.includes(filterBy)) {
          return filterBy;
        }
      }

      return 'all';
    }

    function fetchActivitiesCountByPublishStatus() {
      axios
        .get('/activities/activities_count_by_published_status')
        .then((res) => {
          const response = res.data;
          allPublishStatusCountMap.value.all = response.data.all;
          allPublishStatusCountMap.value.published = response.data.published;
          allPublishStatusCountMap.value.ready_for_republishing =
            response.data.ready_for_republishing;
          allPublishStatusCountMap.value.draft = response.data.draft;
        });
    }

    /**
     * watch
     */
    watchEffect(() => {
      store.state.activitiesList = activities;
    });

    /**
     * Provide
     */
    provide('toastMessage', toastMessage);
    provide('toastData', toastData);
    provide('errorData', errorData);
    provide('refreshToastMsg', refreshToastMsg);
    provide('xlsFailedMessage', xlsFailedMessage);
    provide('processing', processing);
    provide('downloading', downloading);
    provide('fileCount', fileCount as Ref);
    provide('xlsDownloadStatus', xlsDownloadStatus as Ref);
    provide('downloadApiUrl', downloadApiUrl as Ref);
    provide('closeModel', closeModel as Ref);
    provide('activities', publishingActivities as Ref);
    provide('completed', uploadComplete);
    provide('defaultLanguage', props.defaultLanguage);
    provide('translatedData', props.translatedData);
    provide('currentLanguage', props.currentLanguage);

    return {
      store,
      activities,
      state,
      isEmpty,
      isLoading,
      showOrHide,
      fetchActivities,
      toastData,
      toastMessage,
      refreshToastMsg,
      errorData,
      tableLoader,
      xlsData,
      activityName,
      processedCount,
      totalCount,
      showXlsStatus,
      xlsFailed,
      xlsFailedMessage,
      importCompleted,
      downloadCompleted,
      uploadComplete,
      downloading,
      startBulkPublish,
      publishingActivities,
      activityStore,
      pa,
      filterByPublishStatus,
      currentFilterBy,
      allPublishStatusCountMap,
      currentPage,
      paginationReset,
      isDisabledPublish,
    };
  },
});
</script>

<style lang="scss">
.page-height {
  min-height: calc(100vh - 60px);
}
</style>
