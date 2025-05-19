<template>
  <PageLoader v-if="isLoading" :translated-data="translatedData" />

  <div class="listing__page bg-paper pb-[71px] pt-4">
    <div class="page-title mb-4 w-screen px-10">
      <div class="flex items-end gap-4">
        <div class="title basis-6/12">
          <div class="inline-flex w-[500px] items-center md:w-[600px]">
            <div class="mr-3">
              <a href="/activities">
                <svg-vue icon="arrow-short-left" />
              </a>
            </div>
            <div class="inline-flex min-h-[48px] grow items-center">
              <h4 class="ellipsis__title relative mr-4 font-bold">
                <span class="ellipsis__title overflow-hidden">
                  {{
                    translatedData['workflow_frontend.import.import_activity']
                  }}
                </span>
              </h4>
              <div class="tooltip-btn">
                <button class="">
                  <svg-vue icon="question-mark" />
                  <span>{{
                    translatedData['common.common.what_is_an_activity']
                  }}</span>
                </button>
                <div class="tooltip-btn__content z-[1]">
                  <div class="content">
                    <div
                      class="mb-1.5 text-caption-c1 font-bold text-bluecoral"
                    >
                      {{ translatedData['common.common.what_is_an_activity'] }}
                    </div>
                    <p
                      v-html="
                        translatedData[
                          'common.common.what_is_an_activity_description'
                        ]
                      "
                    ></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="mx-10 flex min-h-[65vh] w-[500px] items-start justify-center rounded-lg border border-n-20 bg-white md:w-[calc(100%_-_80px)]"
    >
      <div class="mt-24">
        <div
          v-if="hasOngoingImportWarning"
          class="border-orangeish my-2 flex w-full items-center rounded-md bg-eggshell px-4 py-6 text-xs font-normal text-n-50"
        >
          <span class="inline">
            {{ translatedData['workflow_frontend.import.cannot_import'] }}&nbsp;
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
          </span>
        </div>

        <div class="mt-2 rounded-lg border border-n-30">
          <p
            class="border-b border-n-30 p-4 text-sm font-bold uppercase text-n-50"
          >
            {{ translatedData['workflow_frontend.import.import_csv_xml_file'] }}
          </p>
          <div class="p-6">
            <div class="mb-4 rounded border border-n-30 px-4 py-3">
              <input
                ref="file"
                type="file"
                class="min-w-[480px] cursor-pointer p-0 text-sm file:cursor-pointer file:rounded-full file:border file:border-solid file:border-spring-50 file:bg-white file:px-4 file:py-0.5 file:text-spring-50 file:outline-none"
              />
            </div>
            <span v-if="error" class="error">{{ error }}</span>
            <div
              class="flex w-fit flex-col items-start gap-4 md:w-[400px] md:flex-row md:items-center lg:w-auto lg:justify-between"
            >
              <BtnComponent
                class="!border-red !border"
                type="primary"
                :text="translatedData['workflow_frontend.import.upload_file']"
                icon="upload-file"
                @click="checkOngoingImports"
              />
              <div class="flex items-center space-x-2.5">
                <button class="relative text-sm text-bluecoral">
                  <svg-vue :icon="'download'" class="mr-1" />
                  <span @click="downloadExcel">
                    {{
                      translatedData[
                        'workflow_frontend.import.download_csv_activity_template'
                      ]
                    }}
                  </span>
                </button>
                <HoverText
                  :hover-text="
                    translatedData[
                      'common.common.this_template_covers_basic_activity_data_and_transactions_all_possible_data_elements_of_the_iati_standard_are_included_for_flexibility'
                    ]
                  "
                  name=""
                  class="hover-text import-activity"
                  position="right"
                  :show-iati-reference="true"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import BtnComponent from 'Components/ButtonComponent.vue';
import HoverText from 'Components/HoverText.vue';
import axios from 'axios';
import { defineProps } from 'vue';
import PageLoader from 'Components/PageLoader.vue';
import type { AxiosResponse } from 'axios';

const file = ref(),
  error = ref(''),
  hasOngoingImportWarning = ref(false),
  ongoingImportType = ref('');

const props = defineProps({
  translatedData: {
    type: Object,
    required: true,
  },
});

const isLoading = ref(false);
async function checkOngoingImports() {
  try {
    const response = await axios.get('/import/check-ongoing-import');

    if (hasOngoingImport(response.data.data)) {
      showHasOngoingImportWarning(response.data.data.import_type);
    } else {
      uploadFile().then();
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

async function uploadFile() {
  isLoading.value = true;
  let activity = file.value.files.length ? file.value.files[0] : '';
  const config = {
    headers: {
      'content-type': 'multipart/form-data',
    },
  };
  let data = new FormData();
  data.append('activity', activity);
  error.value = '';

  let timeout = 5000;
  const isXml = activity.name.endsWith('.xml');

  if (isXml) {
    const fileText = await activity.text();
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(fileText, 'application/xml');
    const activities = xmlDoc.getElementsByTagName('iati-activity');
    const count = activities.length;

    timeout = Math.max(5000, count * 500);
  }

  const timeoutPromise = new Promise<{ timeoutReached: true }>((resolve) =>
    setTimeout(() => resolve({ timeoutReached: true }), timeout)
  );

  try {
    const result = await Promise.race([
      axios.post('/import', data, config),
      timeoutPromise,
    ]);

    if ('timeoutReached' in result) {
      window.location.href = '/import/list';

      return;
    }

    const response = result as AxiosResponse;

    if (response.data?.success && file.value.files.length) {
      window.location.href = '/import/list';
    } else {
      if (hasOngoingImport(response?.data?.errors)) {
        showHasOngoingImportWarning(response.data.errors.import_type);
      } else {
        error.value = Object.values(response.data.errors).join(' ');
      }

      isLoading.value = false;
    }
  } catch (err) {
    error.value = 'Error has occurred while uploading the file.';
    isLoading.value = false;
  }
}

function downloadExcel() {
  axios({
    url: 'import/download/csv',
    method: 'GET',
    responseType: 'arraybuffer',
  }).then((response) => {
    let blob = new Blob([response.data], {
      type: 'application/csv',
    });
    let link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = 'Import_Activity_CSV_Template.csv';
    link.click();
  });
}

function openZendeskLauncher() {
  if (window.zE && window.zE.activate) {
    window.zE.activate();
  }
}

const getTranslatedAnotherImportInProgress = (ongoingImportType: string) => {
  let message =
    props.translatedData['common.common.another_import_in_progress'];

  const url = ongoingImportType === 'xls' ? '/import/xls/list' : '/import/list';

  console.log(props.translatedData['common.common.view_import_list']);
  message = message.replace(
    ':link',
    `<a href="${url}" class="px-1 font-bold">${props.translatedData['common.common.view_import_list']}</a>`
  );

  return message;
};

declare global {
  interface Window {
    /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
    zE: any;
  }
}
</script>

<style lang="scss"></style>
