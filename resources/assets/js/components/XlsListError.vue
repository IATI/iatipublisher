<template>
  <div>
    <div class="flex">
      <div class="group relative">
        <div
          :class="{ 'font-bold': countErrors(index) }"
          class="group max-w-[300px] overflow-hidden overflow-x-hidden text-ellipsis whitespace-nowrap text-sm uppercase text-n-50"
        >
          {{ title }}
        </div>
        <div
          class="absolute left-[80%] top-0 z-[110] hidden max-w-[500px] overflow-x-scroll whitespace-nowrap rounded bg-eggshell px-4 py-2 text-sm font-normal shadow-sm group-hover:block"
        >
          {{ title }}
        </div>
      </div>
      <div class="tect-xs mx-3 font-normal text-n-40">
        <span class="capitalize">
          ({{ status['template'] }}
          {{ getTranslatedElement(translatedData, 'identifier') }}
        </span>
        : {{ activity.identifier }})
      </div>
      <span
        v-if="countErrors(index) > 0"
        class="ml-4 inline-flex cursor-pointer items-center space-x-2 text-crimson-50"
        @click="
          () => {
            showErrors = !showErrors;
          }
        "
      >
        <span>
          {{
            translatedData['common.common.show_count_errors'].replace(
              ':count',
              String(countErrors(index))
            )
          }}
        </span>
        <svg-vue class="text-[6px]" icon="dropdown-arrow" />
      </span>
    </div>
    <div v-if="showErrors" class="mt-6 px-7 py-2">
      <div
        v-if="Object.keys(activity['errors']).indexOf('critical') !== -1"
        :style="`width: ${width - 70}px;`"
        class="critical-container mt-2 cursor-pointer"
        @click="criticalAccordionToggle"
      >
        <div
          class="flex items-center justify-between border border-none p-3 pb-0.5"
        >
          <span class="flex items-center space-x-2">
            <svg-vue class="text-crimson-40" icon="alert" />
            <span>
              {{ errorLength('critical') }}
              {{ translatedData['common.common.critical_errors'] }}
            </span>
          </span>

          <svg-vue
            icon="dropdown-arrow"
            class="ml-1 cursor-pointer text-[4px] duration-200"
            :class="{ 'rotate-180': showCritical, '': !showCritical }"
          />
        </div>
        <div class="error-help">
          (
          {{
            translatedData[
              'workflow_frontend.import.the_activity_contains_critical_errors'
            ]
          }}
          )
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
        v-if="
          activity['errors'] &&
          Object.keys(activity['errors']).indexOf('error') !== -1
        "
        :style="`width: ${width - 70}px;`"
        class="error-container mt-2 cursor-pointer"
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
        v-if="
          activity['errors'] &&
          Object.keys(activity['errors']).indexOf('warning') !== -1
        "
        :style="`width: ${width - 70}px;`"
        class="warning-container my-2 cursor-pointer border-none bg-eggshell"
        @click="warningAccordionToggle"
      >
        <div class="flex items-center justify-between bg-eggshell p-3 pb-0.5">
          <span class="flex items-center space-x-2">
            <svg-vue icon="alert" class="text-camel-40" />
            <span>
              {{ errorLength('warning') }}
              {{ translatedData['common.common.warnings'] }}
            </span>
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
</template>
<script setup lang="ts">
import { defineProps, computed, ref, toRefs } from 'vue';
import { getTranslatedElement, getTranslatedUntitled } from 'Composable/utils';
const showErrors = ref(false);
const showCritical = ref(false);
const criticalToggle = ref(false);
const showError = ref(false);
const errorToggle = ref(false);
const warningToggle = ref(false);
const showWarning = ref(false);

const props = defineProps({
  // Number with a default value
  importData: {
    type: Object,
    required: true,
  },
  width: { type: Number, required: false, default: 0 },
  activity: {
    type: Object,
    required: true,
  },
  index: {
    type: Number,
    required: true,
  },
  status: {
    type: String,
    required: true,
  },
  translatedData: {
    type: Object,
    required: true,
  },
});

const errorLength = (currentError) => {
  let count = 0;

  props.activity?.errors[currentError] &&
    Object.values(props.activity['errors'][currentError])?.map((item) => {
      count += Object.keys(item as object).length;
    });

  return count;
};

const { translatedData } = toRefs(props);

const title = computed(() => {
  switch (props.status['template']) {
    case 'activity':
      return props.activity.data.title
        ? props.activity.data.title[0].narrative ??
            getTranslatedUntitled(translatedData)
        : getTranslatedUntitled(translatedData);

    case 'result':
      return props.activity.data.title
        ? props.activity.data.title[0].narrative[0]['narrative'] ??
            getTranslatedUntitled(translatedData)
        : getTranslatedUntitled(translatedData);
    case 'period':
      return (
        (props.activity.data.period_start &&
          props.activity.data.period_start[0].date) +
        ' - ' +
        (props.activity.data.period_end &&
          props.activity.data.period_end[0].date)
      );
    case 'indicator':
      return props.activity.data.title
        ? props.activity.data.title[0].narrative[0]['narrative'] ??
            getTranslatedUntitled(translatedData)
        : getTranslatedUntitled(translatedData);
    default:
      return getTranslatedUntitled(translatedData);
  }
});

const countErrors = (activityIndex) => {
  let count = 0;
  for (const type in props.importData[activityIndex]['errors']) {
    for (const index in props.importData[activityIndex]['errors'][type]) {
      count +=
        props.importData[activityIndex] &&
        Object.keys(props.importData[activityIndex]['errors'][type][index])
          .length;
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

  if (criticalToggle.value && target != null) {
    target.style.cssText = `height: ${elHeight}px;`;
    setTimeout(function () {
      target.style.cssText = ``;
    }, 100);
    criticalToggle.value = false;
  } else if (target != null) {
    target.style.cssText = `height: ${elHeight}px;`;

    setTimeout(function () {
      target.style.cssText = `height: auto;`;
    }, 600);

    criticalToggle.value = true;
  }
};
const warningAccordionToggle = (e: Event) => {
  showWarning.value = !showWarning.value;
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.warning-dropdown-container');
  const elHeight = target?.querySelector('.warning-dropdown')?.clientHeight;
  if (warningToggle.value && target != null) {
    target.style.cssText = `height: ${elHeight}px;`;
    setTimeout(function () {
      target.style.cssText = ``;
    }, 100);
    warningToggle.value = false;
  } else if (target != null) {
    target.style.cssText = `height: ${elHeight}px;`;
    setTimeout(function () {
      target.style.cssText = `height: auto;`;
    }, 100);

    warningToggle.value = true;
  }
};
const errorAccordionToggle = (e: Event) => {
  showError.value = !showError.value;
  const currentTarget = e.currentTarget as HTMLElement;
  const target = (
    currentTarget.parentElement as HTMLElement
  ).querySelector<HTMLElement>('.error-dropdown-container');
  const elHeight = target?.querySelector('.error-dropdown')?.clientHeight;
  if (errorToggle.value && target != null) {
    target.style.cssText = `height: ${elHeight}px;`;
    setTimeout(function () {
      target.style.cssText = ``;
    }, 100);
    errorToggle.value = false;
  } else if (target != null) {
    target.style.cssText = `height: ${elHeight}px;`;

    setTimeout(function () {
      target.style.cssText = `height: auto;`;
    }, 600);

    errorToggle.value = true;
  }
};
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
  background-color: transparent;
}

.error-dropdown-container p {
  color: black;
}
</style>
