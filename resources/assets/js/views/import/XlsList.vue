<template>
  <div class="px-10 py-8">
    <div class="flex flex-wrap justify-between">
      <h6 class="text-3xl font-bold text-n-50">
        Add/Update All <span class="capitalize">{{ status.template }}</span>
      </h6>
      <div class="flex flex-wrap justify-end gap-3">
        <Toast
          v-if="toastVisibility"
          class="toast -bottom-24"
          :message="toastMessage"
          :type="toastType"
        />
        <button
          class="rounded bg-n-0 px-4 py-3 text-xs font-bold uppercase text-bluecoral shadow-md"
          @click="cancelImport"
        >
          <span><svg-vue class="pt-1.5 text-2xl" icon="cross" /></span>
          <span>cancel this import</span>
        </button>
        <button
          :class="selectedActivities.length === 0 && ' cursor-not-allowed'"
          class="rounded bg-bluecoral px-4 py-3 text-xs font-bold uppercase text-n-0"
          @click.once="addActivities"
        >
          <svg-vue class="mr-2 text-sm" icon="up-arrow-outline" />
          <span class="mr-2">add </span> ({{ selectedActivities.length }} /
          {{ activitiesLength ?? 0 }})
        </button>
      </div>
    </div>
    <div class="flex items-center justify-between space-x-4">
      <p class="mt-4 text-sm text-n-40">
        Select from the list below to add {{ status.template }} to the
        publisher. Make your selection and follow the on-screen prompts to
        successfully add/update your selected {{ status.template }}
        <b>
          Please note that you must re-upload any unselected
          {{ status.template }}, and if the import is canceled, you will need to
          upload them again.</b
        >
      </p>
      <!-- <div v-if="globalError" class="relative mt-4">
        <div
          v-if="!showGLobalError"
          class="flex w-[250px] justify-between rounded-l-lg border border-crimson-20 bg-crimson-10 p-4"
        >
          <div class="flex">
            <svg-vue
              class="mr-1 -mt-1.5 text-2xl text-crimson-50"
              icon="warning-fill"
            />
            <span class="text-sm font-bold">
              {{ globalError.length }} global errors found
            </span>
          </div>
          <button
            class="cursor-pointer text-xs uppercase text-bluecoral"
            @click="showGLobalError = true"
          >
            show
          </button>
        </div>
        <div
          v-else
          class="absolute -top-4 right-0 z-[100] w-[450px] rounded-l-lg bg-white p-4"
        >
          <div class="mb-4 flex justify-between">
            <div class="flex">
              <div class="text-sm font-bold">
                {{ globalError.length }} global errors found
              </div>
            </div>

            <button
              class="cursor-pointer text-xs uppercase text-bluecoral"
              @click="showGLobalError = false"
            >
              hide
            </button>
          </div>
          <div class="border-l border-crimson-40 bg-rose p-4">
            <div class="my-2 flex items-center justify-between">
              <div class="flex items-center">
                <svg-vue
                  class="mr-1 -mt-1.5 text-2xl text-crimson-50"
                  icon="alert"
                />
                <div class="text-sm font-bold capitalize">
                  {{ globalError.length }} global errors
                </div>
              </div>
              <svg-vue
                class="mr-1 -mt-1.5 cursor-pointer text-[7px] duration-200"
                icon="dropdown-arrow"
                :class="showGlobalErrorList && ' rotate-180'"
                @click="showGlobalErrorList = !showGlobalErrorList"
              />
            </div>
            <ul v-if="showGlobalErrorList">
              <li
                v-for="error in globalError"
                :key="error"
                class="border-b border-n-20 p-4 text-sm"
              >
                {{ error }}
              </li>
            </ul>
          </div>
        </div>
      </div> -->
    </div>
    <div class="iati-list-table upload-list-table mt-4">
      <table>
        <thead>
          <tr class="bg-n-10">
            <th id="title" class="flex items-center space-x-1" scope="col">
              <span class="cursor-pointer" @click="sort">
                <svg-vue
                  :class="sortOrder === 'descending' ? ' rotate-180' : ''"
                  icon="sort-icon"
                  class="pt-1 text-[5px]"
                />
              </span>
              <span>{{ status.template }} Title</span>
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
          <tr v-if="!importData.length">
            <div class="p-4 text-center">No data found for import</div>
          </tr>
          <tr
            v-for="(activity, index) in importData"
            v-else
            ref="tableRow"
            :key="index"
            :class="{
              'upload-error':
                activity &&
                activity['errors'] &&
                Object.keys(activity['errors']).length > 0,
            }"
          >
            <td class="title" :class="countErrors(index) > 0 && 'xls-error'">
              <XlsListError
                :width="tableWidth"
                :activity="activity"
                :index="index"
                :import-data="importData"
                :status="status"
              />
            </td>
            <td :class="countErrors(index) > 0 && ' xls-error'">
              <span class="text-sm text-n-40">{{
                activity.existing ? 'Existing' : 'New'
              }}</span>
            </td>
            <td
              :class="countErrors(index) > 0 && ' xls-error'"
              class="check-column"
              @click="(event: Event) => event.stopPropagation()"
            >
              <input
                v-if="errorLength('critical', index) === 0"
                v-model="selectedActivities"
                type="checkbox"
                :value="index"
              />
            </td>
            <!-- <td>{{ activity[index].data.title[0] }}</td> -->
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <Loader
    v-if="loader"
    :text="loaderText"
    :class="{ 'animate-loader': loader }"
  />
  <Modal
    :modal-active="showIdentifierErrorModel && showGLobalError"
    width="583"
  >
    <div class="mb-5 flex space-x-2.5">
      <svg-vue class="text-4xl text-crimson-40" icon="warning-fill" />
      <div>
        <h6 class="text-base font-bold">Errors Detected</h6>
        <p class="text-sm text-n-40">
          We detected some errors in the uploaded file.
        </p>
      </div>
    </div>

    <div
      class="mb-6 rounded-sm border-crimson-20 bg-rose p-4 text-sm text-n-50"
    >
      <h6 class="mb-2 text-sm font-bold">Identifier Errors</h6>
      <p class="text-sm text-n-40">
        We have found some identifier errors in the imported file. You cannot
        import data until all the identifier errors are resolved.
      </p>
      <ul class="max-h-[250px] overflow-y-scroll">
        <li
          v-for="error in props.globalError"
          :key="error"
          class="border-b border-n-20 p-4 text-sm"
        >
          {{ error }}
        </li>
      </ul>
    </div>
    <p
      v-if="errorCount.critical + errorCount.error + errorCount.warning > 0"
      class="text-sm text-n-40"
    >
      Additionally, there are {{ errorCount.critical }} critical error,
      {{ errorCount.error }} errors and {{ errorCount.warning }} warning in the
      file
    </p>
    <div class="flex justify-end space-x-3">
      <button class="ghost-btn" @click="cancelImport">Cancel Import</button>
      <BtnComponent
        class=""
        text="download identifier errors"
        type="primary"
        icon="download"
        @click="downloadIdentifierError"
      />
    </div>
  </Modal>
  <Modal
    :modal-active="showCriticalErrorModel && !showIdentifierErrorModel"
    width="583"
  >
    <div class="mb-5 flex space-x-2.5">
      <svg-vue class="text-4xl text-crimson-40" icon="warning-fill" />
      <div>
        <h6 class="text-base font-bold">Errors Detected</h6>
        <p class="text-sm text-n-40">
          We detected some errors in the uploaded file.
        </p>
      </div>
    </div>
    <div
      class="mb-6 rounded-sm border border-crimson-20 bg-rose p-4 text-sm text-n-50"
    >
      <div v-if="showCriticalErrorMessage" class="mb-6">
        <h6 class="mb-2 text-sm font-bold">Critical Errors</h6>
        <p class="text-sm text-n-40">
          Some of the {{ status.template }} contain critical errors and thus,
          cannot be uploaded to IATI Publisher. Please review the errors and
          follow the instructions provided in the user manual.
        </p>
      </div>
    </div>
    <div class="flex justify-end space-x-3">
      <button class="ghost-btn" @click="cancelImport">Cancel Import</button>
      <BtnComponent
        text="Review errors"
        type="primary"
        @click="showCriticalErrorModel = false"
      />
    </div>
  </Modal>
</template>
<script setup lang="ts">
import XlsListError from 'Components/XlsListError.vue';
import Modal from 'Components/PopupModal.vue';
import axios from 'axios';
import Toast from 'Components/ToastMessage.vue';
import { defineProps, onMounted, ref, nextTick, onUnmounted } from 'vue';
import Loader from 'Components/sections/ProgressLoader.vue';
import BtnComponent from 'Components/ButtonComponent.vue';

const selectAll = ref(false);
const sortOrder = ref('asceding');

const tableRow = ref({});
const showCriticalErrorModel = ref(false);
const showIdentifierErrorModel = ref(false);

const loader = ref(false),
  loaderText = ref('Adding activities');
const showCriticalErrorMessage = ref(false);
const showGLobalError = ref(true);
const selectedCount = ref(0);
const activitiesLength = ref(0);
const selectedActivities = ref<string[]>([]);
const tableWidth = ref({});
const toastMessage = ref('');
const toastType = ref(false);
const toastVisibility = ref(false);

const props = defineProps({
  status: {
    type: Object,
    required: true,
  },
  // Number with a default value
  importData: {
    type: Object,
    required: true,
  },
  globalError: {
    type: Object,
    required: true,
  },
  errorCount: {
    type: Object,
    required: true,
  },
});
const getDimensions = async () => {
  await nextTick();
  tableWidth.value = tableRow?.value['0']?.clientWidth;
};

const sort = () => {
  sortOrder.value === 'ascending'
    ? (sortOrder.value = 'descending')
    : (sortOrder.value = 'ascending');

  let sortedData = props.importData;
  switch (props.status['template']) {
    case 'activity':
      sortedData.sort((a, b) =>
        a.data.title &&
        a.data.title[0].narrative.toString().toLowerCase() < b.data.title &&
        b.data.title[0].narrative.toString().toLowerCase()
          ? 1
          : -1
      );
      break;

    case 'result':
      sortedData.sort((a, b) =>
        a.data.title &&
        a.data.title[0].narrative[0]['narrative'].toString().toLowerCase() <
          b.data.title &&
        b.data.title[0].narrative[0]['narrative'].toString().toLowerCase()
          ? 1
          : -1
      );

      break;
    case 'period':
      sortedData.sort((a, b) =>
        a.data.title &&
        a.data.title[0].narrative[0]['narrative'].toString().toLowerCase() <
          b.data.title &&
        b.data.title[0].narrative[0]['narrative'].toString().toLowerCase()
          ? 1
          : -1
      );

      break;
    case 'indicator':
      sortedData.sort((a, b) =>
        a.data.title &&
        a.data.title[0].narrative[0]['narrative'].toString().toLowerCase() <
          b.data.title &&
        b.data.title[0].narrative[0]['narrative'].toString().toLowerCase()
          ? 1
          : -1
      );

      break;
    default:
      break;
  }
};

onUnmounted(() => {
  window.removeEventListener('resize', getDimensions);
});

onMounted(() => {
  getDimensions();
  window.addEventListener('resize', getDimensions);
  checkCriticalError();

  if (props.globalError) {
    showIdentifierErrorModel.value = true;
  }
  activitiesLength.value = props.importData.length;
  loaderText.value = `Adding ${props.status.template}`;
});

const cancelImport = () => {
  showCriticalErrorModel.value = false;
  showGLobalError.value = false;
  axios.delete(`/import/xls`).then((res) => {
    const response = res.data;
    toastVisibility.value = true;
    setTimeout(() => (toastVisibility.value = false), 15000);
    toastMessage.value = response.message;
    toastType.value = response.success;
    setTimeout(() => {
      window.location.href = '/import/xls';
    }, 2000);
  });
};
const downloadIdentifierError = () => {
  let text;
  if (typeof props.globalError === 'object') {
    text = Object.values(props.globalError).join('\n');
  }
  let file = new File(['\ufeff' + text], 'identifier-errors.txt', {
    type: 'text/plain:charset=UTF-8',
  });
  let url = window.URL.createObjectURL(file);
  let anchorTag = document.createElement('a');
  anchorTag.href = url;
  anchorTag.download = file.name;
  anchorTag.click();
  window.URL.revokeObjectURL(url);
};

const checkCriticalError = () => {
  const criticalArry =
    props.importData &&
    props.importData.map((data, index) => {
      return errorLength('critical', index);
    });
  let totalCriricalErrorCount = 0;
  for (let i = 0; i < criticalArry.length; i++) {
    totalCriricalErrorCount += criticalArry[i];
  }
  if (totalCriricalErrorCount > 0) {
    showCriticalErrorMessage.value = true;
  }
  if (totalCriricalErrorCount > 0 || props.globalError?.length > 0) {
    showCriticalErrorModel.value = true;
  }
};
const countErrors = (activityIndex) => {
  let count = 0;
  for (const type in props.importData[activityIndex]['errors']) {
    for (const index in props.importData[activityIndex]['errors'][type]) {
      count += Object.keys(
        props.importData[activityIndex]['errors'][type][index]
      ).length;
    }
  }

  return count;
};
const addActivities = () => {
  if (selectedActivities.value.length > 0) {
    loader.value = true;

    axios
      .post(`/import/xls/activity`, { activities: selectedActivities.value })
      .then(() => {
        window.location.href = '/activities';
      });
  }
};
const errorLength = (errorType, activityIndex) => {
  let count = 0;

  for (const index in props.importData[activityIndex]['errors'][errorType]) {
    count += Object.keys(
      props.importData[activityIndex]['errors'][errorType][index]
    ).length;
  }

  return count;
};

function selectAllActivities() {
  selectAll.value = !selectAll.value;
  selectedCount.value = 0;
  selectedActivities.value.length = 0;
  Object.keys(props.importData).forEach((activity_id) => {
    let index = selectedActivities.value.indexOf(activity_id);
    if (
      Object.keys(props.importData[activity_id]['errors']).indexOf(
        'critical'
      ) === -1
    ) {
      if (selectAll.value) {
        selectedActivities.value.push(activity_id);
        selectedCount.value = selectedCount.value + 1;
      } else {
        selectedActivities.value.splice(index, 1);
      }
    }
  });
  if (!selectAll.value) {
    selectedCount.value = 0;
  }
}
</script>
<style scoped>
.xls-error {
  background-image: linear-gradient(#fff1f0 60px, #ffffff 0%);
}
</style>
