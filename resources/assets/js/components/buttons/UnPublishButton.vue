<template>
  <BtnComponent
    class=""
    :text="translatedData['common.common.unpublish']"
    :type="type"
    icon="cancel-cloud"
    @click="unpublishValue = true"
  />
  <Modal :modal-active="unpublishValue" width="583" @close="unpublishToggle">
    <div class="mb-4">
      <div class="title mb-6 flex">
        <svg-vue
          class="mr-1 mt-0.5 text-lg text-crimson-40"
          icon="cancel-cloud"
        />
        <b>{{
          translatedData['activity_index.unpublish_button.unpublish_activity']
        }}</b>
      </div>
      <div class="rounded-lg bg-rose p-4">
        {{
          translatedData[
            'activity_index.unpublish_button.are_you_sure_you_want_to_unpublish_this_activity'
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
          @click="unpublishValue = false"
        />
        <BtnComponent
          class="space"
          :text="translatedData['common.common.unpublish']"
          type="primary"
          @click="unPublishFunction"
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
import { defineProps, reactive, inject, onUpdated, toRefs } from 'vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';

//component
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import Loader from 'Components/sections/ProgressLoader.vue';

// Vuex Store
import { detailStore } from 'Store/activities/show';

const props = defineProps({
  type: { type: String, default: 'primary' },
  activityId: { type: Number, required: true },
});

const { activityId } = toRefs(props);

// toggle state for modal popup
let [unpublishValue, unpublishToggle] = useToggle();

//Global State
const store = detailStore();

//activity id
const id = activityId.value;
const translatedData = inject('translatedData') as Record<string, string>;

// display/hide validator loader
interface LoaderTypeface {
  value: boolean;
  text: string;
}

const loader: LoaderTypeface = reactive({
  value: false,
  text: translatedData['common.common.please_wait'],
});

// call api for unpublishing
interface ToastMessageTypeface {
  message: string;
  type: boolean;
}
onUpdated(() => {
  if (loader.value) {
    store.dispatch('updateIsLoading', true);
  } else {
    store.dispatch('updateIsLoading', false);
  }
  if (unpublishValue.value) {
    loader.value = false;
  }
  if (loader.value) {
    unpublishValue.value = false;
  }
});

const toastMessage = inject('toastMessage') as ToastMessageTypeface;

const unPublishFunction = () => {
  unpublishValue.value = false;

  setTimeout(function () {
    loader.value = true;
  }, 500);

  loader.text = translatedData['common.common.unpublishing'];

  axios.post(`/activity/${id}/unpublish`).then((res) => {
    const response = res.data;
    toastMessage.message = response.message;
    toastMessage.type = response.success;
    unpublishValue.value = false;

    setTimeout(() => {
      if (response.success === true) {
        store.dispatch('updateUnPublished', false);
        store.dispatch('updateShowPublished', true);
        store.dispatch('updatePublishErrors', []);
      }
      location.reload();
    }, 1000);
  });
};
</script>
