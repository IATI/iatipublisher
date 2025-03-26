<template>
  <div>
    <h3 class="pb-2 text-base font-bold leading-6 text-n-50">
      {{ toTitleCase(translatedData['common.common.downloading']) }}
    </h3>
    <div class="relative rounded-lg border border-n-20 bg-white p-4">
      <button
        v-if="xlsDownloadStatus === 'completed'"
        class="absolute right-0 top-0 -translate-y-1/2 translate-x-1/2 rounded-full bg-white p-[1px]"
        @click="cancelDownload"
      >
        <svg-vue class="text-sm" icon="cross-icon" />
      </button>

      <div
        v-if="xlsDownloadStatus != 'failed'"
        class="flex justify-between space-x-5"
      >
        <p
          v-if="
            xlsDownloadStatus != 'completed' && xlsDownloadStatus != 'cancelled'
          "
          class="text-sm text-n-40"
        >
          {{
            translatedData[
              'workflow_frontend.download.preparing_filecount_files_for_download'
            ].replace(':fileCount', String(fileCount) ?? 0)
          }}
        </p>
        <p v-if="xlsDownloadStatus == 'cancelled'" class="text-sm text-n-40">
          {{
            translatedData['workflow_frontend.download.preparing_for_cancel']
          }}
        </p>
        <p v-if="xlsDownloadStatus == 'completed'" class="text-sm text-n-40">
          {{ translatedData['workflow_frontend.download.zip_file_is_ready'] }}
        </p>

        <spinnerLoader
          v-if="
            xlsDownloadStatus != 'completed' || xlsDownloadStatus === 'failed'
          "
        />
        <button
          v-if="xlsDownloadStatus == 'completed'"
          class="text-xs font-bold uppercase text-spring-50 hover:text-spring-50"
          @click="downloadFile"
        >
          {{ translatedData['common.common.download'] }}
        </button>
      </div>

      <div
        v-if="xlsDownloadStatus != 'failed'"
        class="mt-3 flex items-center space-x-2"
      >
        <div class="flex h-1 w-full justify-start rounded-full bg-spring-10">
          <div
            :style="{ width: percentageWidth + '%' }"
            class="h-full rounded-full bg-spring-50"
          ></div>
        </div>
        <span class="text-sm text-[#344054]">
          {{ Math.trunc(percentageWidth) }}%
        </span>
      </div>
      <div v-else class="flex justify-between space-x-4">
        <div class="flex space-x-2">
          <span class="text-sm text-n-40">
            {{
              translatedData[
                'workflow_frontend.download.preparing_activities_for_download'
              ]
            }}
          </span>
          <span class="text-sm italic text-n-30">{{
            translatedData['common.common.failed']
          }}</span>
        </div>
        <button
          class="text-xs font-bold uppercase text-bluecoral hover:text-bluecoral"
          @click="showRetryDownloadModel = true"
        >
          {{ translatedData['common.common.retry'] }}
        </button>
      </div>
    </div>
  </div>
  <Modal :modal-active="showRetryDownloadModel" width="583">
    <p class="bg-eggshell p-4 text-n-50">
      {{
        translatedData[
          'workflow_frontend.download.are_you_sure_you_want_to_retry'
        ]
      }}
    </p>
    <div class="flex justify-end space-x-5">
      <button class="ghost-btn" @click="showRetryDownloadModel = false">
        {{ translatedData['common.common.cancel'] }}
      </button>
      <button class="primary-btn" @click="retryDownload()">
        {{ translatedData['common.common.retry'] }}
      </button>
    </div>
  </Modal>
</template>
<script setup lang="ts">
import { inject, computed, ref, Ref } from 'vue';
import spinnerLoader from './spinnerLoader.vue';
import Modal from 'Components/PopupModal.vue';
import axios from 'axios';
import { useStore } from 'Store/activities';
import { toTitleCase } from '../composable/utils';

const store = useStore();
const showRetryDownloadModel = ref();
const isLoading = ref();

const downloadFile = () => {
  store.dispatch('updateCompleteXlsDownload', true);
  store.dispatch('updateCancelDownload', true);
  store.dispatch('updateStartXlsDownload', false);

  const apiUrl = `${(downloadApiUrl as Ref).value.split()[0].split('/')[3]}/${
    (downloadApiUrl as Ref).value.split()[0].split('/')[4]
  }`;
  axios({
    method: 'get',
    url: apiUrl,
    responseType: 'blob',
  }).then((res) => {
    const fileName = res.headers['content-disposition'].split('filename=')[1];
    const blob = new Blob([res.data], {});
    const link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
  });
};

const retryDownload = () => {
  xlsDownloadStatus.value = '';
  isLoading.value = true;
  store.dispatch('updateStartXlsDownload', true);
  store.dispatch('updateCancelDownload', false);

  showRetryDownloadModel.value = false;

  const apiUrl = 'activities/retry-xls-download';

  axios.get(apiUrl).finally(() => (isLoading.value = false));
};
const cancelDownload = () => {
  xlsDownloadStatus.value = 'cancelled';
  axios.get('/activities/cancel-xls-download').then((res) => {
    if (res.data.success) {
      store.dispatch('updateCancelDownload', true);
      store.dispatch('updateStartXlsDownload', false);
    }
  });
};

const percentageWidth = computed(() => {
  return ((fileCount as Ref).value / 4) * 100;
});

const fileCount = inject('fileCount');
const xlsDownloadStatus = inject('xlsDownloadStatus') as Ref;
const downloadApiUrl = inject('downloadApiUrl');
const translatedData = inject('translatedData') as Record<string, string>;
</script>
