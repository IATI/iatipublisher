<template>
  <div class="flex space-x-2">
    <div
      class="validation validation__errorHead"
      :class="{
        'invisible opacity-0': errorValue,
        'opacity-1 visible': !errorValue,
      }"
    >
      <div class="validation__heading flex items-center justify-between">
        <div class="icon flex grow items-center text-sm leading-relaxed">
          <svg-vue
            class="mr-1 text-base text-crimson-50"
            icon="warning-fill"
          ></svg-vue>
          <div class="font-bold">
            {{ errorCount(errorData) + importErrorlength }}
            {{ translatedData['common.common.issues_found'] }}
          </div>
        </div>
        <button class="validation__toggle" @click="errorToggle()">
          {{ translatedData['common.common.show'] }}
        </button>
      </div>
    </div>
    <div
      class="validation validation__errors"
      :class="{
        'opacity-1 visible': errorValue,
        'invisible opacity-0': !errorValue,
      }"
    >
      <div class="flex justify-between px-5 py-4">
        <div class="flex space-x-8">
          <div
            v-if="errorData.length"
            class="relative cursor-pointer"
            :class="
              issueType === 'validator'
                ? 'active text-sm font-bold text-n-50'
                : 'text-sm font-bold text-n-30'
            "
            @click="issueType = 'validator'"
          >
            {{ translatedData['common.common.iati_validator_issues'] }}
          </div>
          <div
            v-if="importErrors"
            class="relative cursor-pointer"
            :class="
              issueType === 'upload'
                ? 'active text-sm font-bold text-n-50'
                : 'text-sm font-bold text-n-30'
            "
            @click="issueType = 'upload'"
          >
            {{ translatedData['common.common.uploaded_file_issues'] }}
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button
            v-if="issueType == 'upload'"
            class="flex items-center"
            @click="deleteErrors"
          >
            <svg-vue class="text-sm text-bluecoral" icon="delete"></svg-vue>
            <span class="ml-0.5 mt-1 text-bluecoral">
              {{ translatedData['common.common.remove'] }}
            </span>
          </button>
          <button
            class="validation__toggle text-bluecoral"
            @click="errorToggle()"
          >
            <svg-vue class="mr-1 mt-2.5 text-lg" icon="cross"></svg-vue>
          </button>
        </div>
      </div>
      <div class="validation__errors-list">
        <div v-if="issueType === 'validator'">
          <div
            v-for="(error, e) in tempData"
            :key="e"
            :class="{ 'mb-4': Number(e) != Object.keys(tempData).length - 1 }"
          >
            <ErrorLists v-if="error.length > 0" :type="e" :errors="error" />
          </div>
        </div>
        <div v-if="issueType === 'upload'">
          <div v-for="(item, index) in importErrorTypes" :key="index">
            <UploadedErrors
              v-if="Object.keys(importErrors).indexOf(item) !== -1"
              :item="importErrors[item]"
              :index="item"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  toRefs,
  reactive,
  watch,
  defineProps,
  inject,
  ref,
  computed,
  onMounted,
} from 'vue';
import { useToggle } from '@vueuse/core';

// components
import ErrorLists from 'Components/sections/ErrorLists.vue';
import UploadedErrors from 'Components/sections/UploadedErrors.vue';
import axios from 'axios';

const props = defineProps({
  errorData: { type: Array, required: true },
});

// toggle issues
const [errorValue, errorToggle] = useToggle();
const translatedData = inject('translatedData') as Record<string, string>;
const importErrors = inject('importActivityError') as object;
const activityId = inject('activityId');
const issueType = ref();

/**
 * list of errors
 **/
interface ErrorInterface {
  category: string;
  response: [];
  id: string;
  identifier: string;
  message: string;
  severity: string;
  title: string;
}
const { errorData } = toRefs(props);
const importErrorTypes = ['error', 'warning'];

interface TempData {
  errors: object[];
  critical: object[];
  warnings: object[];
}
onMounted(() => {
  if (errorData.value.length) {
    issueType.value = 'validator';
    return;
  }
  issueType.value = 'upload';
});

const tempData: TempData = reactive({
  errors: [],
  critical: [],
  warnings: [],
});

const updateTempMessage = () => {
  const errorDataProps = errorData.value as ErrorInterface[];

  for (const data in tempData) {
    tempData[data] = [];
  }

  for (const data of errorDataProps) {
    const severity = data.severity;
    switch (severity) {
      case 'critical':
        tempData.critical.push(data);
        break;
      case 'error':
        tempData.errors.push(data);
        break;
      case 'warning':
        tempData.warnings.push(data);
        break;
    }
  }
};

updateTempMessage();
const importErrorlength = computed(() => {
  let count = 0;

  for (const type in importErrors) {
    for (const index in importErrors[type]) {
      count += Object.keys(importErrors[type][index]).length;
    }
  }

  return count;
});

watch(
  () => errorData.value,
  () => {
    updateTempMessage();
  }
);

const deleteErrors = () => {
  axios.delete(`/import/errors/${activityId}`).then((res) => {
    if (res.status) {
      sessionStorage.setItem('removed', 'true');
      location.reload();
    }
  });
};

const errorCount = (errorData) => {
  return errorData.filter(
    (error: { severity: string }) => error.severity !== 'advisory'
  )?.length;
};
</script>

<style lang="scss" scoped>
.validation {
  @apply rounded-bl-lg rounded-tl-lg border transition-all duration-500;
  box-shadow: 0px 4px 40px rgba(0, 0, 0, 0.1);

  &__errorHead {
    @apply w-[212px] border-crimson-20 bg-crimson-10;
    box-shadow: 0px 4px 40px rgba(0, 0, 0, 0.1);
  }

  &__errors {
    @apply absolute right-0 top-0 z-10 flex w-[595px] flex-col overflow-hidden border-white bg-white;
    max-height: calc(100vh - 60px);
  }

  &__heading {
    @apply px-4 py-3;
  }

  &__errors-list {
    @apply grow overflow-y-auto px-4 py-3;
  }

  &__toggle {
    @apply text-xs uppercase leading-normal text-blue-50;
  }
}
.active {
  &::after {
    content: '';
    position: absolute;
    height: 2px;
    border-radius: 2px;
    background-color: #06dbe4;
    width: 100%;
    top: calc(100% + 3px);
    left: 0;
  }
}
</style>
