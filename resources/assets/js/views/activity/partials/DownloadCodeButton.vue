<template>
  <div>
    <button
      class="button secondary-btn flex items-center space-x-1"
      @click="downloadCode"
    >
      <svg-vue icon="download-file" />
      <div class="pt-0.5 font-bold">
        {{ translatedData['workflow_frontend.download.download_code'] }}
      </div>
      <div class="group relative">
        <svg-vue class="text-[4px] text-n-30" icon="question-mark" />
        <div
          class="invisible absolute -bottom-6 -left-[148px] z-50 w-[352px] translate-y-full rounded bg-eggshell p-4 text-left normal-case opacity-0 duration-200 group-hover:visible group-hover:opacity-100"
        >
          <p class="mb-2 font-bold text-bluecoral">
            {{ translatedData['workflow_frontend.download.what_is_code'] }}
          </p>
          <p class="mb-1.5 text-n-50">
            {{
              translatedData[
                'workflow_frontend.download.codes_are_basically_identifiers_for_activity'
              ]
            }}
          </p>
        </div>
      </div>
    </button>
    <Toast
      v-if="toastVisibility"
      :type="toastMessageType"
      class="toast"
      :message="toastMessage"
    />
  </div>
</template>

<script setup lang="ts">
import { useStore } from 'Store/activities';
import Toast from '../../../components/ToastMessage.vue';
import { inject, Ref, ref } from 'vue';

import axios from 'axios';

const store = useStore();
const toastVisibility = ref(false);
const toastMessage = ref('');
const toastMessageType = ref(false);
const translatedData = inject('translatedData') as Ref;

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
  link.download = `identifiers.xlsx`;
  link.click();
};
</script>
