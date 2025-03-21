<template>
  <div class="activities__card elements__panel min-h-full lg:min-h-0">
    <div class="mb-3 grid grid-flow-col">
      <div class="relative">
        <svg-vue
          class="panel__search absolute left-2.5 top-3 text-sm text-n-30"
          icon="panel-search"
        ></svg-vue>
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
            class="w-2.5 text-xs transition duration-200 ease-linear"
            :class="{ 'rotate-180': searchBtnValue }"
            icon="dropdown-arrow"
          ></svg-vue>
        </button>
        <div
          v-show="searchBtnValue"
          ref="dropdown"
          class="button__dropdown button dropdown-btn absolute right-0 top-full z-10 w-[118px] bg-white text-left shadow-dropdown"
        >
          <ul class="w-full bg-eggshell py-2">
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('')"
            >
              <svg-vue class="mr-1 text-lg" icon="box"></svg-vue>
              <span>{{ translatedData['common.common.all_elements'] }}</span>
            </li>
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('core')"
            >
              <svg-vue class="mr-1 text-lg" icon="core"></svg-vue>
              <span>{{ translatedData['common.common.core'] }}</span>
            </li>
            <li
              class="flex px-3.5 py-1.5 hover:bg-white"
              @click="dropdownFilter('completed')"
            >
              <svg-vue class="mr-1 text-lg" icon="double-tick"></svg-vue>
              <span>{{ translatedData['common.common.completed'] }}</span>
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
    <div class="elements__listing grid grid-cols-2 gap-2">
      <a
        v-for="(post, index) in filteredElements"
        :id="'sidebar-' + index.toString()"
        :key="String(index)"
        class="elements__item relative flex cursor-pointer flex-col items-center justify-center rounded border border-dashed border-n-40 px-[3px] py-2.5 text-n-30"
        :href="
          post.has_data || userRole !== 'admin'
            ? '#' + index
            : '/organisation/' + index
        "
      >
        <div class="status_icons absolute right-0 top-0 mr-1 mt-1 inline-flex">
          <svg-vue
            v-if="
              index === 'organisation_identifier'
                ? status['identifier']
                : status[index.toString()]
            "
            class="text-base text-teal-50"
            icon="double-tick"
          ></svg-vue>
          <svg-vue
            v-if="orgMandatoryElements().includes(index.toString())"
            class="text-base text-camel-50"
            icon="core"
          ></svg-vue>
        </div>
        <template v-if="index === 'name'">
          <svg-vue
            class="text-base"
            icon="organisation-elements/building"
          ></svg-vue>
        </template>
        <template v-else>
          <svg-vue
            :icon="'organisation-elements/' + index"
            class="text-base"
          ></svg-vue>
        </template>
        <div class="title mt-1 break-all text-xs">
          {{ index.toString().replace(/_/g, '-') }}
        </div>
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, defineProps, inject, onMounted, reactive, ref } from 'vue';
import { useToggle } from '@vueuse/core';
import { orgMandatoryElements } from 'Composable/coreElements';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  status: {
    type: Object,
    required: true,
  },
  completed: {
    type: Object,
    required: true,
  },
  mandatory: {
    type: Object,
    required: true,
  },
});

const [searchBtnValue, searchBtnToggle] = useToggle();
const dropdown = ref();
const dropdownBtn = ref();
const userRole = inject('userRole');
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

const asArrayData = Object.entries(props.data);
const filteredElements = computed(() => {
  const filtered = asArrayData.filter(([key, value]) => {
    if (!elements.status) {
      return key
        .toLowerCase()
        .includes(
          elements.search.toLowerCase().replace(/_/g, ' ').replace(/-/g, '_')
        );
    } else {
      if (value[elements.status]) {
        return key
          .toLowerCase()
          .includes(
            elements.search.toLowerCase().replace(/_/g, ' ').replace(/-/g, '_')
          );
      }
    }
  });

  return Object.fromEntries(filtered);
});

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

const dropdownFilter = (s: string) => {
  elements.status = s;
  searchBtnToggle();
};
</script>
