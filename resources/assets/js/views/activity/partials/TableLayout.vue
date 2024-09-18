<template>
  <div class="iati-list-table mb-10">
    <table>
      <thead>
        <tr class="bg-n-10">
          <th id="sn" scope="col">
            <span>{{ translatedData['common.common.s_n'] }}</span>
          </th>
          <th id="title" scope="col">
            <span>{{ translatedData['common.common.activity_title'] }}</span>
          </th>
          <th id="publishing-progress" scope="col" class="progress-bar-header">
            <a
              class="flex justify-end text-n-50 transition duration-500 hover:text-spring-50"
              :href="sortByPublishingProgress()"
            >
              <span class="sorting-indicator" :class="sortingDirection()">
                <svg-vue :icon="`${sortingDirection()}-arrow`" />
              </span>
              <span class="">{{
                translatedData['common.common.publishing_progress']
              }}</span>
            </a>
          </th>
          <th id="date" scope="col">
            <a
              class="text-n-50 transition duration-500 hover:text-spring-50"
              :href="sortByDateUrl()"
            >
              <span class="sorting-indicator" :class="sortingDirection()">
                <svg-vue :icon="`${sortingDirection()}-arrow`" />
              </span>
              <span>{{ translatedData['common.common.updated_on'] }}</span>
            </a>
          </th>
          <th id="status" scope="col">
            <span class="hidden">{{
              translatedData['common.common.status']
            }}</span>
          </th>
          <th id="publish" scope="col">
            <span class="hidden">{{
              translatedData['common.common.publish']
            }}</span>
          </th>
          <th id="cb" scope="col">
            <span>
              <span class="cursor-pointer" @click="toggleSelectAll(data.data)">
                <svg-vue
                  icon="checkbox"
                  :class="isAllValueSelected ? '!text-spring-50' : ''"
                />
              </span>
            </span>
          </th>
        </tr>
      </thead>
      <tbody v-if="data.total > 0">
        <tr v-if="loader">
          <td colspan="5" class="text-center">
            <div colspan="5" class="spin"></div>
          </td>
        </tr>
        <tr
          v-for="(datum, index) in data.data"
          v-else
          :key="datum['id']"
          :class="{
            'already-published':
              datum['linked_to_iati'] && datum['status'] === 'draft',
          }"
        >
          <td class="relative">
            <PreviouslyPublished
              v-if="datum['linked_to_iati'] && datum['status'] === 'draft'"
              class="absolute left-0 top-0 inline-block whitespace-nowrap"
            />
            {{ (currentPage - 1) * 25 + Number(index) + 1 }}
          </td>
          <td class="title max-w-[450px]">
            <div
              class="flex items-start transition duration-500 hover:text-spring-50"
            >
              <div class="ellipsis relative w-full">
                <a
                  :href="'/activity/' + datum['id']"
                  class="ellipsis w-full !max-w-full overflow-hidden text-n-50"
                  >{{
                    datum['default_title_narrative'] &&
                    datum['default_title_narrative'] !== ''
                      ? datum['default_title_narrative']
                      : translatedData['common.common.untitled']
                  }}</a
                >
                <div class="w-52">
                  <span class="ellipsis__title--hover">{{
                    datum['default_title_narrative'] &&
                    datum['default_title_narrative'] !== ''
                      ? datum['default_title_narrative']
                      : translatedData['common.common.untitled']
                  }}</span>
                </div>
              </div>
            </div>
          </td>

          <td class="flex items-center justify-end gap-2 text-n-40">
            <div class="progress-bar-parent">
              <div class="progress-bar-wrapper">
                <div class="progress-bar-container bg-spring-10">
                  <div
                    class="progress-bar-fill bg-spring-50"
                    :style="{ width: datum['complete_percentage'] + '%' }"
                  ></div>
                </div>
              </div>
              <div class="progress-bar-number">
                <span class="text-xs font-semibold text-spring-50"
                  >{{ datum['complete_percentage'] }}%</span
                >
              </div>
            </div>
          </td>

          <td class="text-n-40">
            {{ formatDate(datum.updated_at, currentLanguage) }}
          </td>

          <td>
            <button
              class="inline-flex items-center transition duration-500 hover:text-spring-50"
              :class="{
                'text-n-40': datum['status'] === 'draft',
                'text-spring-50': datum['status'] === 'published',
              }"
            >
              <span class="mr-1 text-base">
                <svg-vue
                  :icon="
                    datum['status'] === 'draft' ? 'document-write' : 'tick'
                  "
                />
              </span>
              <span class="text-sm leading-relaxed">{{
                datum['status'] === 'draft'
                  ? translatedData['common.common.status']
                  : translatedData['common.common.published']
              }}</span>
            </button>
          </td>

          <td>
            <div class="flex flex-wrap gap-2">
              <UnPublish
                v-if="datum.linked_to_iati"
                type="outline"
                :activity-id="datum['id']"
              />

              <!--TODO: Review after 1567-->
              <Publish
                v-if="datum['status'] !== 'published'"
                :linked-to-iati="datum.linked_to_iati"
                :status="datum.status"
                :core-completed="datum.coreCompleted"
                type="outline"
                :activity-id="datum['id']"
                :publish="false"
                :deprecation-status-map="datum['deprecation_status_map']"
              />
            </div>
          </td>

          <th
            class="check-column"
            @click="(event: Event) => event.stopPropagation()"
          >
            <label class="sr-only" for="">
              Select "{{ datum['default_title_narrative'] }}"
            </label>
            <label class="checkbox">
              <input
                v-model="store.state.selectedActivities"
                :value="datum.id"
                type="checkbox"
                class="cursor-pointer"
                @change="(e) => handleCheckboxChange(e, datum.status, datum.id)"
              />
              <span class="checkmark" />
            </label>
          </th>
        </tr>
      </tbody>
      <tbody v-else>
        <td v-if="loader" colspan="5" class="text-center">
          <div colspan="5" class="spin"></div>
        </td>
        <td v-else colspan="5" class="text-center">
          {{ translatedData['common.common.activities_not_found'] }}
        </td>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, ref, watch } from 'vue';
import moment from 'moment';

// Vuex Store
import { useStore } from 'Store/activities';

import PreviouslyPublished from 'Components/status/PreviouslyPublished.vue';
import Publish from 'Components/buttons/PublishButton.vue';
import UnPublish from 'Components/buttons/UnPublishButton.vue';

const props = defineProps({
  data: { type: Object, required: true },
  loader: { type: Boolean, required: false },
  onlyLoader: { type: Boolean, required: false, default: false },
  currentPage: { type: Number, required: true, default: 1 },
});

const translatedData = inject('translatedData') as Record<string, string>;
const currentLanguage = inject('currentLanguage') as string;

const isAllValueSelected = ref(false);
const store = useStore();

function formatDate(date: Date, currentLocale: string) {
  return moment(date).locale(currentLocale).fromNow();
}

function toggleSelectAll(activities: {
  [key: string]: { id: number; status: string };
}) {
  try {
    const selectedIds = Object.values(activities).map((item) => item.id);
    const newSet = [...store.state.selectedActivities, ...selectedIds];
    const selectedStatus = Object.values(activities).map((item) => ({
      activity_id: item.id,
      status: item.status,
    }));

    if (newSet.length > 0) {
      const filteredSet = [...new Set(newSet)];

      if (isAllValueSelected.value) {
        const filterAllCurrentPage = store.state.selectedActivities.filter(
          (item) => !selectedIds.includes(item)
        );

        store.state.selectedActivityStatus =
          store.state.selectedActivityStatus.filter(
            (item) => !selectedIds.includes(item.activity_id)
          );

        store.dispatch('updateSelectedActivities', filterAllCurrentPage);
        isAllValueSelected.value = false;
        return;
      }
      store.dispatch('updateSelectedActivities', filteredSet);
    }

    store.state.selectedActivityStatus = [
      ...store.state.selectedActivityStatus.filter(
        (item) => !selectedIds.includes(item.activity_id)
      ),
      ...selectedStatus,
    ];
  } catch (error) {
    console.error('An error occurred while toggling select all:', error);
  }
}

//Sorting by update_at
let direction = 'asc';

const sortingDirection = () => {
  return direction === 'asc' ? 'descending' : 'ascending';
};

const sortByPublishingProgress = () => {
  let queryString = window.location.search;
  let params = new URLSearchParams(queryString);
  let query = params.get('q') ?? '';
  let direction = params.get('direction') === 'desc' ? 'asc' : 'desc';

  params.set('q', query);
  params.set('orderBy', 'complete_percentage');
  params.set('direction', direction);

  return `?${params.toString()}`;
};

const sortByDateUrl = () => {
  let queryString = window.location.search;
  let params = new URLSearchParams(queryString);
  let query = params.get('q') ?? '';
  let direction = params.get('direction') === 'desc' ? 'asc' : 'desc';

  params.set('q', query);
  params.set('orderBy', 'updated_at');
  params.set('direction', direction);

  return `?${params.toString()}`;
};

/**
 * Handles a checkbox change event for the activity status checkboxes.
 *
 * If the checkbox is checked, adds the activity status to the
 * store's selectedActivityStatus array. If the checkbox is unchecked,
 * removes the activity status from the store's selectedActivityStatus array.
 *
 * @param {Event} e - The checkbox change event.
 * @param {string} value - The value of the checkbox (the activity status).
 * @param {number} id - The ID of the activity.
 */
const handleCheckboxChange = (e: Event, value: string, id: number): void => {
  if (e.target) {
    const isChecked = (e.target as HTMLInputElement).checked;

    if (isChecked) {
      store.state.selectedActivityStatus = [
        ...store.state.selectedActivityStatus,
        { activity_id: id, status: value },
      ];
    } else {
      store.state.selectedActivityStatus =
        store.state.selectedActivityStatus.filter(
          (item) => item.activity_id !== id
        );
    }
  }
};

function containsAllValues(): boolean {
  const selectedIds = Object.values(props.data.data).map(
    (item: any) => item.id
  );

  return selectedIds.every((item) =>
    store.state.selectedActivities.includes(item)
  );
}

watch(
  () => props.data.data,
  () => {
    isAllValueSelected.value = containsAllValues();
  },
  { deep: true }
);

watch(
  () => store.state.selectedActivities,
  () => {
    isAllValueSelected.value = containsAllValues();
  },
  { deep: true }
);
</script>
<style scoped>
@keyframes spinner {
  0% {
    transform: translate3d(-50%, -50%, 0) rotate(0deg);
  }
  100% {
    transform: translate3d(-50%, -50%, 0) rotate(360deg);
  }
}

.spin::before {
  animation: 1.5s linear infinite spinner;
  animation-play-state: inherit;
  border: solid 3px #cfd0d1;
  border-bottom-color: grey;
  border-radius: 50%;
  content: '';
  height: 20px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate3d(-50%, -50%, 0);
  width: 20px;
  will-change: transform;
}

.spin {
  height: 40px;
  position: relative;
  width: 100%;
  margin: auto;
}
</style>
