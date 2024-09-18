<!-- eslint-disable vue/no-v-html -->
<template>
  <BtnComponent
    v-if="!publishStatus.is_published || publishStatus.status === 'draft'"
    class=""
    :text="btnText"
    type="primary"
    icon="approved-cloud"
    @click="checkPublish"
  />
  <Modal :modal-active="publishValue" width="583" @close="publishToggle">
    <div class="popup mb-4">
      <div class="title mb-6 flex">
        <svg-vue
          class="mr-1 mt-0.5 text-lg"
          :class="{
            'text-spring-50': publishStateChange.alertState,
            'text-crimson-40': !publishStateChange.alertState,
          }"
          :icon="publishStateChange.icon"
        />
        <b>{{ publishStateChange.title }}</b>
      </div>
      <div
        class="rounded-lg bg-mint p-4"
        :class="{
          'bg-mint': publishStateChange.alertState,
          'bg-[#FFF1F0]': !publishStateChange.alertState,
        }"
      >
        <div
          class="text-sm leading-normal"
          v-html="publishStateChange.description"
        ></div>
      </div>
    </div>
    <div class="flex justify-end">
      <div class="inline-flex">
        <BtnComponent
          v-if="!mandatoryElementStatus"
          class="bg-white px-6 uppercase"
          :text="translatedData['common.common.add_missing_data']"
          type=""
          @click="publishValue = false"
        />
        <BtnComponent
          class="space"
          :text="translatedData['common.common.continue']"
          type="primary"
          @click="publishFunction"
        />
      </div>
    </div>
  </Modal>

  <BtnComponent
    v-if="publishStatus.is_published"
    class="ml-4"
    :text="translatedData['common.common.unpublish']"
    type="primary"
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
        <b>{{ translatedData['common.common.unpublish_organisation'] }}</b>
      </div>
      <div class="rounded-lg bg-rose p-4">
        {{
          translatedData[
            'common.common.are_you_sure_you_want_to_unpublish_this_organisation'
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
    v-if="loader"
    :text="loaderText"
    :translated-data="translatedData"
    :class="{ 'animate-loader': loader }"
  />
</template>

<script setup lang="ts">
import { reactive, ref, computed, inject } from 'vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';
//component
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import Loader from 'Components/sections/ProgressLoader.vue';
// toggle state for modal popup
let [publishValue, publishToggle] = useToggle();
let [unpublishValue, unpublishToggle] = useToggle();

const translatedData = inject('translatedData') as Record<string, string>;

// display/hide validator loader
const loader = ref(false);
// state for first step
// determine if core element completed or not
// true for completed and false for not completed
const mandatoryElementStatus = inject('mandatoryCompleted') as boolean;
// Dynamic text for loader
const loaderText = ref(translatedData['common.common.please_wait']);

// computed function to change content of modal
const publishStateChange = computed(() => {
  const publishState = reactive({
    title: '',
    description: '',
    icon: '',
    alertState: mandatoryElementStatus,
  });
  // different content for step 1 based on coreElement status
  if (mandatoryElementStatus) {
    publishState.title = translatedData['common.common.core_elements_complete'];
    publishState.description =
      translatedData[
        'common.common.congratulations_all_the_core_elements_are_complete'
      ];
    publishState.icon = 'tick';
  } else {
    publishState.title =
      translatedData['common.common.core_elements_not_complete'];
    publishState.description =
      translatedData[
        'common.common.there_is_missing_data_in_some_of_the_core_elements'
      ];
    publishState.icon = 'warning-fill';
  }
  return publishState;
});
// call api for publishing
interface DataTypeface {
  message: string;
  type: boolean;
  visibility: boolean;
}
const toastData = inject('toastData') as DataTypeface;
const errorData = inject('errorData') as DataTypeface;

/**
 * check publish status
 */

const checkPublish = () => {
  axios.get(`/organisation/checks-for-organisation-publish`).then((res) => {
    const response = res.data;
    if (response.success === true) {
      publishValue.value = true;
    } else {
      errorData.message = response.message;
      errorData.type = response.success;
      errorData.visibility = true;
    }
  });
};

const publishFunction = () => {
  loader.value = true;
  loaderText.value = translatedData['common.common.publishing'];
  publishValue.value = false;

  axios.post(`/organisation/publish`).then((res) => {
    const response = res.data;
    loader.value = false;
    toastData.message = response.message;
    toastData.type = response.success;
    toastData.visibility = true;

    setTimeout(() => {
      loader.value = false;
    }, 2000);
    if (response.success) {
      publishStatus.is_published = true;
      publishStatus.status = 'published';
    }
  });
};
const unPublishFunction = () => {
  unpublishValue.value = false;

  loader.value = true;
  loaderText.value = translatedData['common.common.unpublishing'];
  axios.post(`/organisation/unpublish`).then((res) => {
    const response = res.data;
    toastData.message = response.message;
    toastData.type = response.success;
    toastData.visibility = true;

    setTimeout(() => {
      loader.value = false;
    }, 2000);
    if (response.success) {
      publishStatus.is_published = false;
    }
  });
};
interface PublishStatusTypeface {
  is_published: boolean;
  status: string;
}
// publish-republish
const publishStatus = inject('publishStatus') as PublishStatusTypeface;

const btnText = computed(() => {
  if (publishStatus.is_published && publishStatus.status === 'draft') {
    return translatedData['common.common.republish'];
  } else {
    return translatedData['common.common.publish'];
  }
});
</script>
