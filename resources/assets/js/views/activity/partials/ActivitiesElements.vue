<template>
  <div class="activities__card elements__panel mt-6">
    <div class="grid grid-flow-col">
      <div class="relative">
        <svg-vue
          class="panel__search absolute left-2.5 top-3 text-sm text-n-30"
          icon="panel-search"
        />
        <input
          v-model="elements.search"
          class="panel__input"
          :placeholder="
            translatedData[
              'organisationDetail.organisation_elements.search_elements_to_add_edit'
            ]
          "
          type="text"
        />
      </div>
      <div class="relative grid justify-items-end">
        <button
          ref="dropdownBtn"
          class="button panel-btn dropdown-btn"
          @click="searchBtnToggle()"
        >
          <svg-vue
            :icon="elementIcons[elements.status] ?? 'box'"
            class="text-lg"
          />
          <svg-vue
            :class="{
              'rotate-180': searchBtnValue,
            }"
            class="w-2.5 text-xs transition duration-200 ease-linear"
            icon="dropdown-arrow"
          />
        </button>
        <div
          v-show="searchBtnValue"
          ref="dropdown"
          class="button__dropdown button dropdown-btn"
        >
          <ul class="w-full bg-eggshell py-2">
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('')"
            >
              <svg-vue class="mr-1 text-lg" icon="box" />
              <span>{{ translatedData['common.common.all_elements'] }}</span>
            </li>
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('core')"
            >
              <svg-vue class="mr-1 text-lg" icon="core" />
              <span>{{ translatedData['common.common.core'] }}</span>
            </li>
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('completed')"
            >
              <svg-vue class="mr-1 text-lg" icon="double-tick" />
              <span>
                {{ translatedData['common.common.completed'] }}
              </span>
            </li>
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('not_completed')"
            >
              <svg-vue class="!mr-1.5 ml-1" icon="red-cross"></svg-vue>
              <span>{{ translatedData['common.common.not_completed'] }}</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="elements__listing mt-3 grid grid-cols-2 gap-2">
      <template v-for="(post, index) in filteredElements" :key="index">
        <a
          v-if="
            !(index.toString() === 'indicator' || index.toString() === 'period')
          "
          :id="'sidebar-' + index.toString()"
          class="elements__item relative flex cursor-pointer flex-col items-center justify-center rounded border border-dashed border-n-40 py-2.5 text-n-30"
          :href="getLink(post.has_data, index.toString())"
        >
          <div
            class="status_icons absolute right-0 top-0 mr-1 mt-1 inline-flex"
          >
            <svg-vue
              v-if="post.completed"
              class="text-base text-spring-50"
              icon="double-tick"
            ></svg-vue>
            <svg-vue
              v-if="activityCoreElements().includes(index.toString())"
              class="text-base text-camel-50"
              icon="core"
            ></svg-vue>
          </div>
          <template
            v-if="
              index === 'reporting_org' ||
              index === 'default_tied_status' ||
              index === 'crs_add' ||
              index === 'fss'
            "
          >
            <svg-vue
              class="text-base"
              icon="activity-elements/building"
            ></svg-vue>
          </template>
          <template v-else>
            <svg-vue
              :icon="'activity-elements/' + index"
              class="text-base"
            ></svg-vue>
          </template>
          <div class="title mt-1 text-xs">
            {{ index.toString().replace(/_/g, '-') }}
          </div>
        </a>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import {
  computed,
  defineProps,
  reactive,
  onMounted,
  ref,
  toRefs,
  inject,
} from 'vue';
import { useToggle } from '@vueuse/core';

import { activityCoreElements } from 'Composable/coreElements';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  activityId: {
    type: Number,
    required: true,
  },
});

const { data } = toRefs(props);
const dropdown = ref();
const dropdownBtn = ref();
const [searchBtnValue, searchBtnToggle] = useToggle();
const translatedData = inject('translatedData') as Record<string, string>;
/**
 * Search functionality
 */
const elements = reactive({
  search: '',
  status: '',
});

const elementIcons = {
  completed: 'double-tick',
  not_completed: 'red-cross',
  core: 'core',
};

const asArrayData = Object.entries(data.value);
const filteredElements = computed(() => {
  const filtered = asArrayData.filter(([key, value]) => {
    if (!elements.status) {
      return key
        .toLowerCase()
        .includes(
          elements.search.toLowerCase().replace('_', '').replace('-', '_')
        );
    } else {
      if (value[elements.status]) {
        return key
          .toLowerCase()
          .includes(
            elements.search.toLowerCase().replace('_', '').replace('-', '_')
          );
      }
    }
  });

  const justStrings = Object.fromEntries(filtered);
  return justStrings;
});

/**
 * Adding core data
 */
Object.keys(data.value).map((key) => {
  if (activityCoreElements().includes(key.toString())) {
    data.value[key]['core'] = true;
  }
});

const dropdownFilter = (s: string) => {
  elements.status = s;
  searchBtnToggle();
};

onMounted(() => {
  window.addEventListener('click', (e) => {
    if (
      !dropdownBtn.value.contains(e.target) &&
      !dropdown.value.contains(e.target) &&
      searchBtnValue.value
    ) {
      searchBtnToggle();
    }
  });
});

function getLink(has_data: number, index: string) {
  if (has_data) {
    return `#${index}`;
  } else if (index == 'result' || index == 'transactions') {
    let element = index == 'result' ? 'result' : 'transaction';
    return `/activity/${props.activityId}/${element}/create`;
  }

  return `/activity/${props.activityId}/${index}`;
}
</script>

<style lang="scss">
.activities {
  position: relative;
  .elements {
    border-radius: 0px 8px 8px 0px;
    width: 125px;
    min-height: 174px;
  }

  .elements__panel {
    @apply rounded-lg p-4 shadow-dropdown;

    .panel__input:focus,
    .panel__search:focus {
      @apply text-n-40;
    }
  }

  .hover__text {
    @apply ml-1;
  }
  .button__dropdown {
    @apply absolute right-0 top-full z-10 text-left shadow-dropdown;
    min-width: 150px;

    li {
      @apply flex px-3.5 py-1.5 hover:bg-white;

      svg {
        @apply mr-1;
      }
    }
  }
}
</style>
