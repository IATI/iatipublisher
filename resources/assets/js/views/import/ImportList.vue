<template>
  <div class="listing__page bg-paper px-10 pb-[71px] pt-4">
    <div class="page-title mb-6">
      <div class="pb-4 text-caption-c1 text-n-40">
        <div>
          <nav aria-label="breadcrumbs" class="breadcrumb">
            <div class="flex">
              <a class="whitespace-nowrap font-bold" href="/activities">
                Your Activities
              </a>
            </div>
          </nav>
        </div>
      </div>
      <div class="relative py-2">
        <Toast
          v-if="toastVisibility"
          class="toast absolute right-0 bottom-2"
          :message="toastMessage"
          :type="toastType"
        />
      </div>
      <div class="flex items-end gap-4">
        <div class="title max-w-[50%] basis-6/12">
          <div class="inline-flex w-full items-center">
            <div class="inline-flex min-h-[48px] grow flex-wrap items-center">
              <h4 class="ellipsis__title relative mr-4 font-bold">
                <span class="ellipsis__title overflow-hidden">
                  Import Activity
                </span>
              </h4>
              <div class="tooltip-btn">
                <button class="">
                  <svg-vue icon="question-mark" />
                  <span>What is an activity?</span>
                </button>
                <div class="tooltip-btn__content z-[50]">
                  <div class="content">
                    <div
                      class="mb-1.5 text-caption-c1 font-bold text-bluecoral"
                    >
                      What is an activity?
                    </div>
                    <p>
                      You need to provide data about your organisation's
                      development and humanitarian 'activities'. The unit of
                      work described by an 'activity' is determined by the
                      organisation that is publishing the data. For example, an
                      activity could be a donor government providing US$ 50
                      million to a recipient country's government to implement
                      basic education over 5 years. Or an activity could be an
                      NGO spending US$ 500,000 to deliver clean drinking water
                      to 1000 households over 6 months.
                      <br />
                      Therefore your organisation will need to determine how it
                      will divide its work internally into activities. Read the
                      <a
                        target="_blank"
                        rel="noopener noreferrer"
                        href="/publishing-checklist"
                        class="text-bluecoral"
                        ><b>Publishing Checklist</b></a
                      >
                      for more information.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="actions relative flex grow flex-col items-end justify-end">
          <div class="inline-flex justify-end">
            <div class="actions flex grow justify-end">
              <div class="inline-flex justify-center gap-2">
                <button
                  class="rounded bg-n-0 px-4 py-3 text-xs font-bold uppercase text-bluecoral shadow-md"
                  @click="cancelOngoingImports"
                >
                  <span><svg-vue class="pt-1.5 text-2xl" icon="cross" /></span>
                  <span>cancel this import</span>
                </button>
                <BtnComponent
                  :class="[
                    selectedActivities.length === 0
                      ? 'cursor-not-allowed opacity-50'
                      : '',
                  ]"
                  :disabled="selectedActivities.length === 0"
                  type="primary"
                  :text="`Import (${selectedCount}/${activitiesLength})`"
                  icon="download-file"
                  @click.once="importActivities"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Table layout: show after upload complete -->

    <div class="iati-list-table upload-list-table">
      <table>
        <thead>
          <tr class="bg-n-10">
            <th id="title" scope="col">
              <span>Activity Title</span>
            </th>
            <th id="status" scope="col">
              <span class="block text-left">Status</span>
            </th>
            <th id="cb" scope="col">
              <span class="cursor-pointer">
                <svg-vue icon="checkbox" @click="selectAllActivities()" />
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-if="activitiesLength === 0">
            <Placeholder />
          </template>
          <tr
            v-for="(activity, index) in activities"
            v-else
            ref="tableRow"
            :key="index"
            :class="{
              'upload-error': Object.keys(activity['errors']).length > 0,
            }"
          >
            <ListElement
              :width="tableWidth"
              :activity="activity"
              :index="index"
              :selected-activities="JSON.stringify(selectedActivities)"
              @select-element="updateSelectedActivities(index)"
            />
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Loader
    v-if="loader"
    :text="loaderText"
    :class="{ 'animate-loader': loader }"
    :change-text="false"
  />
</template>

<script setup lang="ts">
import { ref, onMounted, reactive, nextTick, onUnmounted } from 'vue';
import BtnComponent from 'Components/ButtonComponent.vue';
import Loader from 'Components/sections/ProgressLoader.vue';
import Placeholder from './ImportPlaceholder.vue';
import ListElement from './ListElement.vue';
import axios from 'axios';
import Toast from 'Components/ToastMessage.vue';

let activities = reactive({});
const selectedActivities: Array<string> = reactive([]);
const selectedCount = ref(0);
const activitiesLength = ref(0);
const loader = ref(false);
const selectAll = ref(false);
const loaderText = ref('Please Wait');
const tableRow = ref({});
const tableWidth = ref({});
const toastMessage = ref('');
const toastType = ref(false);
const toastVisibility = ref(false);

let timer;
const getDimensions = async () => {
  await nextTick();
  tableWidth.value = tableRow?.value['0'].clientWidth;
};

onUnmounted(() => {
  window.removeEventListener('resize', getDimensions);
});
onMounted(() => {
  window.addEventListener('resize', getDimensions);
  loader.value = true;
  loaderText.value = 'Please Wait';
  let count = 0;
  timer = setInterval(() => {
    axios
      .get('/import/get-import-list-data')
      .then((res) => {
        Object.assign(activities, res.data.data);
        activitiesLength.value = res.data.data.length;

        if (res.data.status) {
          clearInterval(timer);
          loader.value = false;
        }

        if (res.data.status === 'error' || (!res.data.data && count >= 40)) {
          clearInterval(timer);
          window.location.href = '/activities';
        }
        count++;

        setTimeout(getDimensions, 200);
      })
      .catch(() => {
        loader.value = false;
        window.location.href = '/activities';
      });
  }, 3000);
});

const cancelOngoingImports = async () => {
  try {
    const res = await axios.delete('/import/delete-ongoing-import');
    const response = res.data;

    toastMessage.value = response.message;
    toastType.value = response.success;
    toastVisibility.value = true;

    setTimeout(() => (toastVisibility.value = false), 1500);

    if (response.success) {
      setTimeout(() => {
        window.location.href = '/activities';
      }, 2000);
    }
  } catch (error) {
    console.error(error);

    toastMessage.value = 'An error occurred while canceling ongoing imports.';
    toastType.value = false;
    toastVisibility.value = true;

    setTimeout(() => (toastVisibility.value = false), 3000);
  }
};

function updateSelectedActivities(activity_id) {
  let index = selectedActivities.indexOf(activity_id);

  if (
    Object.keys(activities[activity_id]['errors']).indexOf('critical') === -1
  ) {
    if (index >= 0) {
      selectedActivities.splice(index, 1);
      selectedCount.value = selectedCount.value - 1;
    } else {
      selectedActivities.push(activity_id);
      selectedCount.value = selectedCount.value + 1;
    }
  }
}

function selectAllActivities() {
  selectAll.value = !selectAll.value;
  selectedCount.value = 0;
  selectedActivities.length = 0;

  Object.keys(activities).forEach((activity_id) => {
    let index = selectedActivities.indexOf(activity_id);
    if (
      Object.keys(activities[activity_id]['errors']).indexOf('critical') === -1
    ) {
      if (selectAll.value) {
        selectedActivities.push(activity_id);
        selectedCount.value = selectedCount.value + 1;
      } else {
        selectedActivities.splice(index, 1);
      }
    }
  });

  if (!selectAll.value) {
    selectedCount.value = 0;
  }
}

function importActivities() {
  loaderText.value = 'Importing .csv/.xml file';
  loader.value = true;

  axios
    .post('/import/activity', {
      activities: selectedActivities,
      filetype: 'csv',
    })
    .then(() => {
      window.location.href = '/activities';
    })
    .catch(() => {
      window.location.href = '/activities';
    });
}
</script>
<style lang="scss" scoped>
.upload-error {
  position: relative !important;
  background: rgba(0, 0, 0, 0) !important;
  z-index: 1;

  &::after {
    position: absolute;
    content: '';
    height: 68px;
    width: 100%;
    border-left: 2px solid #d1001e;
    left: 0;
    top: 0;
    background-color: #fff1f0;
    z-index: -1;
  }
}
</style>
