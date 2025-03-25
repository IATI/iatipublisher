<template>
  <div class="listing__page bg-paper pb-[71px] pt-4">
    <div class="page-title mb-4 w-screen px-10">
      <div class="flex items-end gap-4">
        <div class="title">
          <div class="flex items-center">
            <div class="mr-3">
              <a href="/activities">
                <svg-vue icon="arrow-short-left" />
              </a>
            </div>
            <div class="flex min-h-[48px] w-full grow items-center">
              <h4 class="ellipsis__title relative mr-4 font-bold">
                <span class="ellipsis__title overflow-hidden">
                  {{
                    translatedData['common.common.import_activities_from_xls']
                  }}
                </span>
              </h4>
            </div>
          </div>
        </div>
        <Toast
          v-if="toastVisibility"
          class="toast -bottom-24 ml-auto"
          :message="toastMessage"
          :type="toastType"
        />
      </div>
    </div>
    <div
      class="mx-10 flex min-h-[65vh] w-[500px] items-start justify-center rounded-lg border border-n-20 bg-white px-4 py-6 md:w-[calc(100%_-_80px)]"
    >
      <div>
        <h6 class="my-8 text-center text-2xl font-bold text-bluecoral">
          {{
            translatedData[
              'workflow_frontend.import.please_select_one_to_proceed'
            ]
          }}
        </h6>
        <div class="mb-12 flex flex-wrap items-center justify-center gap-6">
          <div
            :class="uploadType === 'activity' && '!bg-teal-10  '"
            class="w-[315px] rounded border-2 border-n-30 p-4 text-sm"
          >
            <label class="cursor-pointer">
              <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="export" />
                  <span class="font-bold text-bluecoral">
                    {{
                      translatedData['common.common.basic_activity_elements']
                    }}
                  </span>
                </div>
                <input
                  v-model="uploadType"
                  :value="'activity'"
                  type="radio"
                  name="product"
                />
              </div>
              <p class="h-[125px] text-[13px] tracking-normal text-n-40">
                {{
                  translatedData[
                    'workflow_frontend.import.download_the_template_all_elements_except_result'
                  ]
                }}
              </p>
            </label>
          </div>
          <div
            :class="uploadType === 'result' && '!bg-teal-10  '"
            class="w-[315px] cursor-pointer rounded border-2 border-n-30 p-4 text-sm"
          >
            <label class="cursor-pointer">
              <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="result-icon" />
                  <span class="font-bold text-bluecoral">
                    {{
                      translatedData[
                        'workflow_frontend.import.result_except_indicator_and_period'
                      ]
                    }}
                  </span>
                </div>
                <input
                  v-model="uploadType"
                  :value="'result'"
                  type="radio"
                  name="product"
                />
              </div>
              <p class="h-[120px] text-[13px] tracking-normal text-n-40">
                {{
                  translatedData[
                    'workflow_frontend.import.download_the_template_result_except_indicator_and_period'
                  ]
                }}
              </p>
            </label>
          </div>
          <div
            :class="uploadType === 'indicator' && '!bg-teal-10  '"
            class="w-[315px] cursor-pointer rounded border-2 border-n-30 p-4 text-sm"
          >
            <label class="cursor-pointer">
              <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="indicator-icon" />
                  <span class="font-bold text-bluecoral">
                    {{
                      translatedData['common.common.indicators_except_period']
                    }}
                  </span>
                </div>
                <input
                  v-model="uploadType"
                  :value="'indicator'"
                  type="radio"
                  name="product"
                />
              </div>
              <p class="h-[120px] text-[13px] tracking-normal text-n-40">
                {{
                  translatedData[
                    'workflow_frontend.import.download_the_template_indicator_except_period'
                  ]
                }}
              </p>
            </label>
          </div>
          <div
            :class="uploadType === 'period' && '!bg-teal-10  '"
            class="w-[315px] cursor-pointer rounded border-2 border-n-30 p-4 text-sm"
          >
            <label class="cursor-pointer">
              <div class="mb-2 flex items-center justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="period-icon" />
                  <span class="font-bold text-bluecoral">{{
                    translatedData['common.common.periods']
                  }}</span>
                </div>
                <input
                  v-model="uploadType"
                  :value="'period'"
                  type="radio"
                  name="product"
                />
              </div>
              <p class="h-[120px] text-[13px] tracking-normal text-n-40">
                {{
                  translatedData[
                    'workflow_frontend.import.download_the_template_period'
                  ]
                }}
              </p>
            </label>
          </div>
        </div>

        <div class="flex items-center justify-around">
          <div
            v-if="hasOngoingImportWarning"
            class="border-orangeish my-2 flex max-w-[95%] items-center space-x-2 rounded-md bg-eggshell px-4 py-6 align-middle text-xs font-normal text-n-50"
          >
            {{ translatedData['workflow_frontend.import.cannot_import'] }}
            <template v-if="ongoingImportType === ''">
              {{ ongoingImportType }}
              <a href="#" class="px-1 font-bold" @click="openZendeskLauncher">
                {{ translatedData['common.common.contact_support'] }}
              </a>
            </template>
            <template v-else>
              <span
                v-html="getTranslatedAnotherImportInProgress(ongoingImportType)"
              ></span>
            </template>
          </div>
        </div>

        <div class="mx-auto mb-4 max-w-[565px] rounded bg-eggshell px-6 py-3">
          <div class="flex">
            <div class="w-[30px]">
              <svg-vue class="mr-2.5 text-[20px]" icon="alert-outline" />
            </div>
            <p class="max-w-[520px] text-sm text-n-40">
              {{
                translatedData[
                  'workflow_frontend.import.downloading_identifier_provides_you_code'
                ]
              }}
            </p>
          </div>
          <div class="mt-2 flex justify-end">
            <button
              class="text-sm text-bluecoral underline"
              @click="showDownloadCode = true"
            >
              {{
                translatedData[
                  'workflow_frontend.import.download_identifier_code'
                ]
              }}
            </button>
          </div>
        </div>
        <div>
          <div class="flex justify-center space-x-4">
            <div class="mb-4 h-10 rounded border border-n-30 px-4 py-2">
              <input
                ref="file"
                type="file"
                class="file:-none min-w-[480px] cursor-pointer p-0 text-sm file:cursor-pointer file:rounded-full file:border file:border-solid file:border-spring-50 file:bg-white file:px-4 file:py-0.5 file:text-spring-50"
              />
            </div>
            <BtnComponent
              class="!border-red h-10 !border"
              type="primary"
              :text="translatedData['workflow_frontend.import.upload_file']"
              icon="upload-file"
              :activity-length="activityLength"
              @click="checkOngoingImports"
            />
          </div>
          <div v-if="error" class="error mx-auto max-w-[700px] px-6">
            {{ error }}
          </div>
        </div>
        <p class="mt-6 text-center text-n-50">
          {{
            translatedData[
              'workflow_frontend.import.please_make_sure_to_read_the_instructions'
            ]
          }}
        </p>
        <div
          class="mb-12 mt-5 flex items-center justify-center gap-4 space-x-3"
        >
          <a
            href="/files/Manuals/IATI_Publisher-Import_manual.pdf"
            :download="translatedData['workflow_frontend.import.import_manual']"
            class="flex items-center space-x-1 text-bluecoral"
          >
            <span class="mx-1.5">{{
              translatedData['workflow_frontend.import.read_our_import_manual']
            }}</span>
            <svg-vue class="mr-1" icon="export" />
          </a>
          <span class="text-n-20">|</span>
          <div
            class="relative z-10 flex items-center space-x-2.5"
            @click="showDownloadDropdown = !showDownloadDropdown"
          >
            <button class="relative text-sm text-bluecoral">
              <span>
                {{
                  translatedData[
                    'workflow_frontend.import.download_xls_activity_template'
                  ]
                }}
              </span>
            </button>
            <HoverText
              :hover-text="
                translatedData[
                  'workflow_frontend.import.this_template_contains_all_the_elements'
                ]
              "
              name=""
              class="hover-text import-activity"
              position="right"
              :show-iati-reference="true"
            />
            <svg-vue class="text-[6px] text-bluecoral" icon="dropdown-arrow" />

            <ul
              :class="{
                'visible translate-y-2 opacity-100': showDownloadDropdown,
                'invisible -translate-y-2 opacity-0': !showDownloadDropdown,
              }"
              class="absolute -left-2.5 top-full z-0 w-[110%] rounded bg-n-0 p-2 uppercase text-n-40 shadow-lg duration-75"
            >
              <li
                class="group cursor-pointer rounded-sm text-[10px] font-bold text-n-40 hover:bg-teal-10"
              >
                <a
                  href="/files/Templates/ActivityXLS.xlsx"
                  :download="
                    translatedData['workflow_frontend.import.activity_template']
                  "
                  class="block w-full p-2.5 text-n-40 group-hover:text-n-50"
                >
                  {{
                    translatedData['common.common.basic_activity_elements']
                  }}.xlsx
                </a>
              </li>
              <li
                class="group cursor-pointer whitespace-nowrap rounded-sm text-[10px] font-bold text-n-40 hover:bg-teal-10"
              >
                <a
                  href="/files/Templates/ResultXLS.xlsx"
                  :download="
                    translatedData['workflow_frontend.import.result_template']
                  "
                  class="block w-full p-2.5 text-n-40 group-hover:text-n-50"
                >
                  {{
                    translatedData[
                      'workflow_frontend.import.result_except_indicator_and_period'
                    ]
                  }}.xlsx
                </a>
              </li>
              <li
                class="group cursor-pointer rounded-sm text-[10px] font-bold text-n-40 hover:bg-teal-10"
              >
                <a
                  href="/files/Templates/IndicatorXLS.xlsx"
                  :download="
                    translatedData[
                      'workflow_frontend.import.indicator_template'
                    ]
                  "
                  class="block w-full p-2.5 text-n-40 group-hover:text-n-50"
                  >{{
                    translatedData['common.common.indicators_except_period']
                  }}.xlsx</a
                >
              </li>
              <li
                class="group cursor-pointer rounded-sm text-[10px] font-bold text-n-40 hover:bg-teal-10"
              >
                <a
                  href="/files/Templates/PeriodXLS.xlsx"
                  :download="
                    translatedData['workflow_frontend.import.period_template']
                  "
                  class="block w-full p-2.5 text-n-40 group-hover:text-n-50"
                  >{{ translatedData['common.common.periods'] }}.xlsx</a
                >
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <XlsUploadIndicator
      v-if="
        xlsData || (downloading && !downloadCompleted) || publishingActivities
      "
      :total-count="(totalCount as number)"
      :processed-count="processedCount"
      :xls-failed="xlsFailed"
      :activity-name="activityName"
      :xls-data="xlsData"
      :completed="uploadComplete"
      :publishing-activities="publishingActivities"
    />
    <PublishSelected />
  </div>
  <Loader
    v-if="loader"
    :text="loaderText"
    :translated-data="translatedData"
    :class="{ 'animate-loader': loader }"
  />
  <Modal :no-padding="true" :modal-active="showDownloadCode" width="1220">
    <div class="border-b border-n-20 px-6 py-5">
      <div class="flex justify-between">
        <div>
          <div class="flex items-center space-x-2">
            <h6 class="text-2xl">
              {{ translatedData['common.common.activities'] }}
            </h6>
            <span
              class="rounded-full bg-mint px-2 py-2 text-[10px] font-bold text-spring-50"
              >{{ activities['total'] }}
              {{ translatedData['common.common.activities'] }}</span
            >
          </div>
          <p class="text-xs text-n-40">
            {{
              translatedData[
                'workflow_frontend.import.please_choose_the_activities_for_which'
              ]
            }}
          </p>
        </div>
        <button @click="showDownloadCode = false">
          <svg-vue class="-mt-4 h-[20px] text-n-50" icon="cross"></svg-vue>
        </button>
      </div>
    </div>
    <div class="flex justify-between border-b border-n-20 px-6 py-5">
      <div class="relative">
        <svg-vue
          class="absolute left-3 top-1/2 h-[16px] -translate-y-1/2 text-base text-n-30"
          icon="search"
        ></svg-vue>

        <input
          v-model="searchValue"
          class="search__input mr-3.5 !rounded-full"
          type="text"
          :placeholder="
            translatedData['workflow_frontend.import.search_activity']
          "
          @keyup.enter="fetchActivities(1)"
        />
      </div>
      <BtnComponent
        type="primary"
        :text="
          store.state.selectedActivities.length > 0
            ? translatedData['workflow_frontend.import.download_selected']
            : translatedData['common.common.download_all']
        "
        icon="download"
        @click="downloadCode"
      />
    </div>
    <div>
      <table class="w-full text-xs text-n-40">
        <thead>
          <tr class="border-b border-n-20 text-left">
            <th class="w-[600px] px-6 py-4">
              {{ translatedData['common.common.activity_title'] }}
            </th>
            <th class="px-6 py-4">
              <div
                class="flex cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                @click="sortingDirection"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="
                      direction === 'desc'
                        ? 'descending-arrow'
                        : `ascending-arrow`
                    "
                  />
                </span>
                <span>{{ translatedData['common.common.updated_on'] }}</span>
              </div>
            </th>
            <th class="px-6 py-4">
              {{
                translatedData['common.common.status'].charAt(0).toUpperCase() +
                translatedData['common.common.status'].slice(1)
              }}
            </th>
            <th class="px-6 py-4 text-left">
              <button class="cursor-pointer" @click="selectAll">
                <svg-vue class="text-base" icon="checkbox" />
              </button>
            </th>
          </tr>
        </thead>
        <tbody
          v-if="activities['total'] > 0"
          class="[&>*:nth-child(odd)]:bg-n-10"
        >
          <tr
            v-for="activity in activities.data"
            :key="activity['id']"
            class="w-full border-b border-n-20"
          >
            <td class="px-6 py-4 text-sm text-n-50">
              <div class="ellipsis relative w-full">
                <div
                  class="w-[500px] !max-w-full overflow-hidden text-ellipsis whitespace-nowrap text-n-50"
                >
                  {{ activity['title'][0]['narrative'] }}
                </div>
                <div class="w-52">
                  <span class="ellipsis__title--hover">{{
                    activity['title'][0]['narrative']
                  }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 text-xs text-n-40">
              {{
                dateFormat(activity['updated_at'], 'fromNow', currentLanguage)
              }}
            </td>
            <td>
              <button
                class="inline-flex items-center transition duration-500 hover:text-spring-50"
                :class="{
                  'text-n-40': activity['status'] === 'draft',
                  'text-spring-50': activity['status'] === 'published',
                }"
              >
                <span class="mr-1 text-base">
                  <svg-vue
                    :icon="
                      activity['status'] === 'draft' ? 'document-write' : 'tick'
                    "
                  />
                </span>
                <span class="text-sm leading-relaxed">{{
                  activity['status']
                }}</span>
              </button>
            </td>
            <td class="pl-6">
              <label class="checkbox">
                <input
                  v-model="store.state.selectedActivities"
                  :value="activity['id']"
                  type="checkbox"
                />
                <span class="checkmark" />
              </label>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="activities['total'] === 0" class="mx-auto h-[200px] w-full">
        <p class="my-8 text-center text-lg text-n-40">
          {{ translatedData['common.common.activities_not_found'] }}
        </p>
      </div>
      <div v-if="!isEmpty" class="mx-6 my-4">
        <Pagination
          v-if="activities && activities.last_page > 1"
          :data="activities"
          @fetch-activities="fetchActivities"
        />
      </div>
    </div>
  </Modal>
  <Modal :modal-active="showCancelModel" width="583">
    <div>
      <div class="mb-6 flex items-center space-x-1">
        <svg-vue class="text-crimson-40" icon="warning-fill" />
        <h6 class="text-sm font-bold">
          {{ translatedData['workflow_frontend.import.upload_in_progress'] }}
        </h6>
      </div>

      <div class="rounded-sm bg-rose p-4">
        <p class="text-sm text-n-50">
          {{
            translatedData[
              'workflow_frontend.import.we_are_in_the_process_of_uploading'
            ].replace(':activityTitle', mapActivityName(activityName))
          }}
          {{
            uploadComplete || xlsFailed
              ? translatedData[
                  'workflow_frontend.import.please_wait_for_the_completion_of_previous_import'
                ]
              : translatedData[
                  'workflow_frontend.import.please_wait_for_the_completion_of_previous_import_or_click_import_anyway'
                ]
          }}
        </p>
      </div>

      <div class="mt-6 flex items-center justify-end space-x-4">
        <button
          class="text-xs font-bold uppercase text-n-40"
          @click="
            () => {
              showCancelModel = false;
              uploadType = [];

              file.value = null;
            }
          "
        >
          {{ translatedData['common.common.go_back'] }}
        </button>
        <BtnComponent
          v-if="uploadComplete || xlsFailed"
          :text="translatedData['common.common.import_anyway']"
          type="primary"
          @click="importAnyway"
        />
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import {
  ref,
  onMounted,
  provide,
  computed,
  reactive,
  watch,
  Ref,
  onUnmounted,
} from 'vue';
import BtnComponent from 'Components/ButtonComponent.vue';
import HoverText from 'Components/HoverText.vue';
import Loader from 'Components/sections/ProgressLoader.vue';
import axios from 'axios';
import XlsUploadIndicator from 'Components/XlsUploadIndicator.vue';
import Modal from 'Components/PopupModal.vue';
import Toast from 'Components/ToastMessage.vue';
import dateFormat from 'Composable/dateFormat';
import Pagination from 'Components/TablePagination.vue';
import { useStore } from 'Store/activities';
import { useStorage } from '@vueuse/core';
import PublishSelected from 'Activity/bulk-publish/PublishSelected.vue';
import { getTranslatedElement } from 'Composable/utils';
import { defineProps } from 'vue';
import transactionDate from 'Activity/transactions/elements/TransactionDate.vue';

interface ActivitiesInterface {
  last_page: number;
  data: object;
}
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

const xlsIndicatorMounted = ref(false);

const xlsFailedMessage = ref('');
const uploadType = ref();
const showDownloadDropdown = ref(false);
const activityName = ref('');
const fileCount = ref(0);
const xlsDownloadStatus = ref('');
const downloadCompleted = ref(false);
const publishingActivities = ref();

const toastMessage = ref('');
const toastType = ref(false);
const showDownloadCode = ref(false);
const isEmpty = ref(false);
const xlsFailed = ref(false);
const currentActivity = ref('');
const toastVisibility = ref(false);
const xlsData = ref(false);
const showCancelModel = ref(false);
const activities = reactive({}) as ActivitiesInterface;
const selectAllValue = ref(false);
const uploadComplete = ref(false);
const totalCount = ref<number | null>();
const processedCount = ref(0);
const file = ref();
const error = ref('');
const loader = ref(false);
const loaderText = ref(props.translatedData['common.common.please_wait']);
const store = useStore();
const searchValue: Ref<string | null> = ref('');
const direction = ref('');
const processing = ref();
const hasOngoingImportWarning = ref(false);
const ongoingImportType = ref('');
const props = defineProps({
  translatedData: {
    type: Object,
    required: true,
  },
  currentLanguage: {
    type: String,
    required: true,
  },
});

const sortingDirection = () => {
  direction.value === 'asc'
    ? (direction.value = 'desc')
    : (direction.value = 'asc');
  fetchActivities(1, direction.value);
};

const downloadApiUrl = ref('');
const downloading = ref(false);

const pa: Ref<paType> = useStorage('vue-use-local-storage', {
  publishingActivities: localStorage.getItem('publishingActivities') ?? {},
});

watch(
  () => store.state.selectedActivities,
  (value) => {
    if (value.length < 6) {
      selectAllValue.value = false;
    }
  }
);

const mapActivityName = (name) => {
  switch (name) {
    case 'activity':
      return props.translatedData['common.common.basic_activity_elements'];
    case 'period':
      return 'Period';
    case 'indicator':
      return props.translatedData['common.common.indicators_except_period'];
    case 'result':
      return props.translatedData[
        'workflow_frontend.import.result_except_indicator_and_period'
      ];
    default:
      return name;
  }
};
async function checkOngoingImports() {
  try {
    const response = await axios.get('/import/check-ongoing-import');

    if (hasOngoingImport(response.data.data)) {
      showHasOngoingImportWarning(response.data.data.import_type);
    } else {
      uploadFile();
    }
  } catch (e) {
    console.log(e);
  }
}

function hasOngoingImport(responseDataWithHasImportFlag): boolean {
  return responseDataWithHasImportFlag?.has_ongoing_import ?? false;
}

function showHasOngoingImportWarning(importType: null | string) {
  hasOngoingImportWarning.value = true;
  ongoingImportType.value = importType ? importType : '';
}

watch(
  () => store.state.startBulkPublish,
  (value) => {
    if (value) {
      publishingActivities.value =
        pa.value.publishingActivities &&
        Object.keys(pa.value.publishingActivities);
      return;
    }
  },
  { deep: true }
);

const activityLength = computed(() => {
  return !uploadType?.value?.length;
});
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
  () => store.state.closeXlsModel,
  () => {
    checkDownloadStatus();
  }
);

const checkDownloadStatus = async () => {
  downloading.value = false;

  const checkDownload = setInterval(async function () {
    await axios.get('/activities/download-xls-progress-status').then((res) => {
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

const downloadCode = async () => {
  let apiUrl = '/activities/download-codes/?activities=all';

  if (store.state.selectedActivities.length > 0) {
    const activities = store.state.selectedActivities.join(',');
    apiUrl = `/activities/download-codes/?activities=[${activities}]`;
  }
  const req = await axios({
    method: 'get',
    url: apiUrl,
    responseType: 'blob',
  });

  let blob = new Blob([req.data], {
    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  });

  const link = document.createElement('a');
  link.href = window.URL.createObjectURL(blob);
  link.download = 'identifiers.xlsx';
  link.click();
};

watch(
  () => {
    store.state.cancelUpload;
  },
  () => {
    cancelImport();
  },
  { deep: true }
);

const importAnyway = () => {
  axios.delete(`/import/xls`).then((res) => {
    const response = res.data;
    xlsData.value = false;

    uploadFile();

    uploadType.value = [];
    showCancelModel.value = false;
    toastVisibility.value = true;

    setTimeout(() => (toastVisibility.value = false), 15000);

    toastMessage.value = response.message;
    toastType.value = response.success;
  });
};

const selectAll = () => {
  if (!selectAllValue.value) {
    let ids = [] as number[];
    for (let i = 0; i < Object.values(activities.data).length; i++) {
      ids.push(activities.data[i]['id']);
    }
    store.dispatch('updateSelectedActivities', ids);
    selectAllValue.value = true;
  } else {
    store.dispatch('updateSelectedActivities', []);
    selectAllValue.value = false;
  }
};

function uploadFile() {
  if (!xlsData.value) {
    loader.value = true;
    loaderText.value = props.translatedData['common.common.fetching_xls_file'];

    let activity = file.value.files.length ? file.value.files[0] : '';

    let xlsType = uploadType;
    const config = {
      headers: {
        'content-type': 'multipart/form-data',
      },
    };
    let data = new FormData();
    data.append('activity', activity);
    data.append('xlsType', xlsType.value as string);
    error.value = '';
    axios
      .post('/import/xls', data, config)
      .then((res) => {
        if (file.value.files.length && res?.data?.success) {
          checkXlsStatus();
        } else {
          error.value =
            res.data.errors && Object.values(res.data.errors).join(' ');
        }
      })
      .catch(() => {
        error.value = 'Error has occured while uploading file.';
      })
      .finally(() => {
        loader.value = false;
        uploadType.value = [];

        file.value.value = null;
      });
  } else {
    showCancelModel.value = true;
  }
}

function fetchActivities(active_page: number, direction = '') {
  let apiUrl = `/activities/page/${active_page}`;
  let params = new URLSearchParams();
  params.append('limit', '6');

  if (direction) {
    params.append('orderBy', 'updated_at');
    params.append('direction', direction);
  }

  if (searchValue.value) {
    params.append('q', searchValue.value);
  }

  axios.get(apiUrl, { params: params }).then((res) => {
    const response = res.data;
    Object.assign(activities, response.data);
    isEmpty.value = !response.data.data.length;
  });
}

const cancelImport = () => {
  axios.delete(`/import/xls`).then((res) => {
    xlsData.value = false;
    uploadType.value = [];

    file.value.value = null;

    showCancelModel.value = false;
    const response = res.data;
    toastVisibility.value = true;
    setTimeout(() => (toastVisibility.value = false), 15000);
    toastMessage.value = response.message;
    toastType.value = response.success;
  });
};

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

      if (!res.data?.data?.success || res.data?.data?.message === 'Complete') {
        clearInterval(checkStatus);
      }
      if (res.data?.data?.message === 'Complete') {
        uploadComplete.value = true;
      }
    });
  }, 2500);
};

const checkXlsStatus = () => {
  axios.get('/import/xls/poll-import-progress-status').then((res) => {
    uploadComplete.value = false;
    activityName.value = res?.data?.status?.template;
    currentActivity.value = mapActivityName(activityName.value);
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

const getTranslatedAnotherImportInProgress = (ongoingImportType: string) => {
  let message =
    props.translatedData['common.common.another_import_in_progress'];

  const url = ongoingImportType === 'xls' ? '/import/xls/list' : '/import/list';

  message = message.replace(
    ':link',
    `<a href="${url}" class="px-1 font-bold">view import list</a>`
  );

  return message;
};

function openZendeskLauncher() {
  if (window.zE && window.zE.activate) {
    window.zE.activate();
  }
}

declare global {
  interface Window {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    //@ts-ignore
    zE: any;
  }
}

provide('xlsFailedMessage', xlsFailedMessage);
provide('activityLength', activityLength);
provide('completed', uploadComplete);
provide('processing', processing);
provide('translatedData', props.translatedData);
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

onUnmounted(() => {
  xlsIndicatorMounted.value = false;
});

onMounted(() => {
  fetchActivities(1);
  checkXlsStatus();
  checkDownloadStatus();
  publishingActivities.value =
    pa.value.publishingActivities && Object.keys(pa.value.publishingActivities);

  xlsIndicatorMounted.value = true;
});
provide('xlsFailedMessage', xlsFailedMessage);
provide('activityLength', activityLength);
provide('xlsIndicatorMounted', xlsIndicatorMounted as Ref);
provide('downloading', downloading);
provide('xlsDownloadStatus', xlsDownloadStatus as Ref);
provide('downloadApiUrl', downloadApiUrl as Ref);
provide('activities', publishingActivities as Ref);

provide('fileCount', fileCount as Ref);
</script>
