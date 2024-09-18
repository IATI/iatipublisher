<template>
  <BtnComponent
    class=""
    text=""
    type="secondary"
    icon="delete"
    @click="deleteValue = true"
  />
  <Modal :modal-active="deleteValue" width="583" @close="deleteToggle">
    <div class="mb-4">
      <div class="title mb-6 flex">
        <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
        <b>
          {{
            translatedData['activity_index.delete_button.delete_activity']
          }}</b
        >
      </div>
      <div class="rounded-lg bg-rose p-4">
        {{
          translatedData[
            'activity_index.delete_button.are_you_sure_you_want_to_delete_this_activity'
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
          @click="deleteValue = false"
        />
        <BtnComponent
          class="space"
          :text="translatedData['common.common.delete']"
          type="primary"
          @click="deleteFunction"
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

// Vuex Store
import { useStore } from 'Store/activities/index';

const translatedData = inject('translatedData') as Record<string, string>;

const store = useStore();

// toggle state for modal popup
let [deleteValue, deleteToggle] = useToggle();

// display/hide validator loader
interface LoaderTypeface {
  value: boolean;
  text: string;
}

const loader: LoaderTypeface = reactive({
  value: false,
  text:
    translatedData?.value?.['common.common.please_wait'] || 'Please wait...',
});

// call api for unpublishing
interface ToastMessageTypeface {
  message: string;
  type: boolean;
}

const toastMessage = inject('toastMessage') as ToastMessageTypeface;

const deleteFunction = () => {
  loader.value = true;
  loader.text = translatedData['activity_index.delete_button.deleting'];
  deleteValue.value = false;
  const deleteEndPoint = `/activity/${store.state.selectedActivities}`;

  axios.delete(deleteEndPoint).then((res) => {
    const response = res.data;
    toastMessage.message = response.message;
    toastMessage.type = response.success;

    if (response.success) {
      window.location.replace('/activities');
    } else {
      setTimeout(() => {
        loader.value = false;
        location.reload();
      }, 1000);
    }
  });
};
</script>
