<template>
  <BtnComponent
    class=""
    text=""
    type="secondary"
    icon="download-file"
    @click="downloadValue = true"
  />
  <Modal :modal-active="downloadValue" width="583" @close="downloadToggle">
    <div class="mb-4">
      <div class="title mb-6 flex">
        <svg-vue
          class="mr-1 mt-0.5 text-lg text-spring-50"
          icon="download-file"
        />
        <b>{{ translatedData['common.common.download_file'] }}</b>
      </div>
      <div class="rounded-lg bg-mint p-4">
        {{
          translatedData[
            'common.common.click_the_download_button_to_save_the_file'
          ]
        }}
      </div>
    </div>
    <div class="flex justify-end">
      <div class="inline-flex">
        <BtnComponent
          class="bg-white px-6 uppercase"
          :text="translatedData['common.common.go_back']"
          type=""
          @click="downloadValue = false"
        />
        <BtnComponent
          class="space"
          :text="translatedData['common.common.download']"
          type="primary"
          @click="downloadFunction()"
        />
      </div>
    </div>
  </Modal>
  <Loader
    v-if="loader.value"
    :text="loader.text"
    :translated-data="translatedData"
    :class="{ 'animate-loader': loader }"
  />
</template>

<script setup lang="ts">
import { reactive, inject } from 'vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';

//component
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import Loader from 'Components/sections/ProgressLoader.vue';

// toggle state for modal popup
let [downloadValue, downloadToggle] = useToggle();

//activity id
const id = inject('activityID');

// display/hide validator loader
interface LoaderTypeface {
  value: boolean;
  text: string;
}

const loader: LoaderTypeface = reactive({
  value: false,
  text: 'Please Wait',
});

// call api for unpublishing
interface ToastMessageTypeface {
  message: string;
  type: boolean;
}

const toastMessage = inject('toastMessage') as ToastMessageTypeface;

const downloadFunction = () => {
  loader.value = true;
  loader.text = 'Downloading';

  axios.delete(`/activity/${id}`).then((res) => {
    const response = res.data;
    toastMessage.message = response.message;
    toastMessage.type = response.success;
  });
};
</script>
