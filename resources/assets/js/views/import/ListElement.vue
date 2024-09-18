<template>
  <td class="title">
    <span class="flex">
      <span
        class="ellipsis !inline-block w-[400px] overflow-x-hidden text-ellipsis whitespace-nowrap font-normal"
        >{{
          activity['data']['title'][0]['narrative']
            ? activity['data']['title'][0]['narrative']
            : getTranslatedMissing(translatedData)
        }}</span
      >

      <span
        v-if="Object.keys(activity['errors']).length > 0"
        class="mb-4 ml-4 inline-flex cursor-pointer items-center text-sm font-medium text-crimson-50"
        @click="toggleError"
      >
        <span class="flex items-center space-x-2">
          <svg-vue class="text-crimson-40" icon="alert" />
          <span>
            {{
              translatedData['common.common.show_count_issues'].replace(
                ':count',
                String(countErrors())
              )
            }}</span
          >
        </span>

        <svg-vue
          icon="dropdown-arrow"
          class="ml-1 text-[4px] duration-200"
          :class="{ 'rotate-180': active, '': !active }"
        /> </span
    ></span>

    <div
      :style="`width: ${width - 40}px;`"
      class="upload-error-content h-[auto] !max-h-[auto] duration-200"
      :class="{ closed: !active }"
    >
      <div class="py-4">
        <div
          v-if="Object.keys(activity['errors']).indexOf('critical') !== -1"
          class="critical-container mt-2 cursor-pointer"
          :style="`width: ${width - 40}px;`"
          @click="criticalAccordionToggle"
        >
          <div
            class="flex items-center justify-between border border-none p-3 pb-0.5"
          >
            <span class="flex items-center space-x-2">
              <svg-vue class="text-crimson-40" icon="alert" />
              <span>
                {{ errorLength('critical') }}
                {{ translatedData['common.common.critical_errors'] }}</span
              >
            </span>

            <svg-vue
              icon="dropdown-arrow"
              class="ml-1 cursor-pointer text-[4px] duration-200"
              :class="{ 'rotate-180': showCritical, '': !showCritical }"
            />
          </div>
          <div class="error-help">
            ({{
              translatedData[
                'workflow_frontend.import.the_activity_contains_critical_errors'
              ]
            }})
          </div>
          <div class="critical-dropdown-container">
            <div class="critical-dropdown">
              <div
                v-for="(ele_err, i) in activity['errors']['critical']"
                :key="i"
                class="p-4"
              >
                <p class="mb-2 font-semibold capitalize">
                  {{ i }}
                </p>

                <p
                  v-for="item in Object.keys(ele_err)"
                  :key="(item as string)"
                  class="error-list mb-2 text-sm font-medium"
                >
                  {{ item.toString().replace(/_/g, ' ').replace(/\./g, ' > ') }}
                  <br />
                  {{ ele_err[item] }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="Object.keys(activity['errors']).indexOf('error') !== -1"
          class="error-container mt-2 cursor-pointer"
          :style="`width: ${width - 40}px;`"
          @click="errorAccordionToggle"
        >
          <div
            class="flex items-center justify-between border border-none bg-rose p-3 pb-0.5"
          >
            <span class="flex items-center space-x-2">
              <svg-vue class="text-crimson-40" icon="alert" />
              <span
                >{{ errorLength('error') }}
                {{ translatedData['common.common.errors'] }}</span
              >
            </span>
            <svg-vue
              icon="dropdown-arrow"
              class="ml-1 cursor-pointer text-[4px] duration-200"
              :class="{ 'rotate-180': showError, '': !showError }"
            />
          </div>
          <div class="error-help">
            ({{
              translatedData[
                'workflow_frontend.import.the_activity_with_the_errors_will_be_uploaded_to_our_system'
              ]
            }})
          </div>
          <div class="error-dropdown-container">
            <div class="error-dropdown">
              <div
                v-for="(ele_err, i) in activity['errors']['error']"
                :key="i"
                class="text-primary-black bg-rose p-4"
              >
                <p class="mb-2 font-semibold capitalize">
                  {{ i }}
                </p>

                <p
                  v-for="item in Object.keys(ele_err)"
                  :key="(item as string)"
                  class="error-list mb-2 text-sm font-medium"
                >
                  {{ item.toString().replace(/_/g, ' ').replace(/\./g, ' > ') }}
                  <br />
                  {{ ele_err[item] }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div
          v-if="Object.keys(activity['errors']).indexOf('warning') !== -1"
          class="warning-container my-2 cursor-pointer border-none bg-eggshell"
          :style="`width: ${width - 40}px;`"
          @click="warningAccordionToggle"
        >
          <div class="flex items-center justify-between bg-eggshell p-3 pb-0.5">
            <span class="flex items-center space-x-2">
              <svg-vue icon="alert" class="text-camel-40" /><span>
                {{ errorLength('warning') }}
                {{ translatedData['common.common.warnings'] }}</span
              >
            </span>
            <svg-vue
              icon="dropdown-arrow"
              class="ml-1 cursor-pointer text-[4px] duration-200"
              :class="{ 'rotate-180': showWarning, '': !showWarning }"
            />
          </div>
          <div class="error-help bg-eggshell">
            ({{
              translatedData[
                'workflow_frontend.import.the_field_with_warnings_will_be_uploaded_to_our_system'
              ]
            }})
          </div>
          <div class="warning-dropdown-container">
            <div class="warning-dropdown">
              <div
                v-for="(ele_err, i) in activity['errors']['warning']"
                :key="i"
                class="bg-eggshell p-4"
              >
                <p class="mb-2 font-semibold capitalize">
                  {{ i }}
                </p>

                <p
                  v-for="item in Object.keys(ele_err)"
                  :key="(item as string)"
                  class="error-list mb-2 text-sm font-medium"
                >
                  {{ item.toString().replace(/_/g, ' ').replace(/\./g, ' > ') }}
                  <br />
                  {{ ele_err[item] }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </td>

  <td>
    <span class="text-sm leading-relaxed">{{
      !activity['existence']
        ? translatedData['common.common.new']
        : translatedData['common.common.existing']
    }}</span>
  </td>

  <td class="check-column" @click="(event: Event) => event.stopPropagation()">
    <label class="sr-only" for=""> Select </label>
    <label
      v-if="Object.keys(activity['errors']).indexOf('critical') === -1"
      class="checkbox"
    >
      <input
        v-model="activities"
        type="checkbox"
        :value="index"
        @click="selectElement(index)"
      />
      <span class="checkmark" />
    </label>
    <label v-else class="checkbox">
      <span class="checkmark" />
    </label>
  </td>
</template>

<script setup lang="ts">
import { defineProps, defineEmits, ref, watch, reactive, inject } from 'vue';
import { getTranslatedMissing } from 'Composable/utils';

const props = defineProps({
  activity: {
    type: Object,
    required: true,
  },
  index: {
    type: String,
    required: true,
  },
  width: { type: Number, required: false, default: 0 },
  selectedActivities: {
    type: String,
    required: true,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;
const emit = defineEmits(['selectElement']);

const active = ref(false);
const showCritical = ref(false);
const showError = ref(false);
const showWarning = ref(false);
let activities = reactive([]);
const criticalToggle = ref(false);
const errorToggle = ref(false);
const warningToggle = ref(false);

function toggleError() {
  active.value = !active.value;
}

const selectElement = (index) => {
  emit('selectElement', index);
};

const countErrors = () => {
  let count = 0;

  for (const type in props.activity['errors']) {
    for (const index in props.activity['errors'][type]) {
      count += Object.keys(props.activity['errors'][type][index]).length;
    }
  }

  return count;
};

const criticalAccordionToggle = (e: Event) => {
  showCritical.value = !showCritical.value;
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.critical-dropdown-container');
  const elHeight = target?.querySelector('.critical-dropdown')?.clientHeight;

  if (criticalToggle.value) {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;
      setTimeout(function () {
        target.style.cssText = ``;
      }, 100);
      criticalToggle.value = false;
    }
  } else {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;

      setTimeout(function () {
        target.style.cssText = `height: auto;`;
      }, 600);

      criticalToggle.value = true;
    }
  }
};
const errorAccordionToggle = (e: Event) => {
  showError.value = !showError.value;
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.error-dropdown-container');
  const elHeight = target?.querySelector('.error-dropdown')?.clientHeight;
  if (errorToggle.value) {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;
      setTimeout(function () {
        target.style.cssText = ``;
      }, 100);
      errorToggle.value = false;
    }
  } else {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;

      setTimeout(function () {
        target.style.cssText = `height: auto;`;
      }, 600);

      errorToggle.value = true;
    }
  }
};
const errorLength = (currentError) => {
  let count = 0;

  // if (Object.keys(props.activity).indexOf('errors') !== -1) {
  Object.values(props.activity['errors'][currentError]).map((item) => {
    count += Object.keys(item as object).length;
  });
  // }

  return count;
};
const warningAccordionToggle = (e: Event) => {
  showWarning.value = !showWarning.value;
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.warning-dropdown-container');
  const elHeight = target?.querySelector('.warning-dropdown')?.clientHeight;
  if (warningToggle.value) {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;
      setTimeout(function () {
        target.style.cssText = ``;
      }, 100);
      warningToggle.value = false;
    }
  } else {
    if (target != null) {
      target.style.cssText = `height: ${elHeight}px;`;
      setTimeout(function () {
        target.style.cssText = `height: auto;`;
      }, 100);

      warningToggle.value = true;
    }
  }
};
watch(
  () => props.selectedActivities,
  () => {
    let selectedData = JSON.parse(props.selectedActivities);
    if (selectedData.length) {
      Object.assign(activities, selectedData);
    } else {
      activities.length = 0;
    }
  }
);
</script>
<style scoped>
.critical-container {
  position: relative;
  background-color: #f6f0ff;
  z-index: 1;
}

.critical-container::after {
  position: absolute;
  content: ' ';
  z-index: 10;
  background-color: #a66ee9;
  height: 100%;
  width: 2px;
  left: 0;
  top: 0;
}

.error-dropdown-container,
.warning-dropdown-container,
.critical-dropdown-container {
  @apply h-0 overflow-hidden transition-all duration-500;
}

.warning-container {
  position: relative;
  z-index: 1;
}

.error-container::after {
  position: absolute;
  content: ' ';
  z-index: 10;
  @apply bg-crimson-40;
  height: 100%;
  width: 2px;
  left: 0;
  top: 0;
}

.error-container {
  position: relative;
  z-index: 1;

  @apply bg-rose;
}

.warning-container::after {
  position: absolute;
  content: ' ';
  z-index: 10;
  @apply bg-camel-40;
  height: 100%;
  width: 2px;
  left: 0;
  top: 0;
}

.error-help {
  font-size: 12px;
  padding-left: 30px;
  font-style: italic;
  font-weight: 400;
  margin-bottom: 18px;
  background-color: none;
}
.error-dropdown-container p {
  color: black;
}
</style>
