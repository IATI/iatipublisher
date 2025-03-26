<template>
  <div class="relative bg-paper px-5 pb-[71px] pt-4 xl:px-10">
    <PageTitle
      :breadcrumb-data="breadcrumbData"
      :title="translatedData['common.common.result_list']"
      :back-link="activityLink"
    >
      <div class="flex items-center space-x-3">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <Transition>
          <ButtonComponent
            v-if="store.state.selectedResults.length > 0"
            type="secondary"
            :text="`${translatedData['common.common.delete_selected']} (${store.state.selectedResults.length})`"
            icon="delete"
            @click="initiateDelete('bulk')"
          />
        </Transition>
        <div class="open-text h-[42px]">
          <svg-vue
            class="absolute left-2 top-1/2 w-10 -translate-y-1/2 text-base"
            icon="magnifying-glass"
          />
          <input
            v-model="searchValue"
            type="text"
            :placeholder="translatedData['common.common.search_result']"
            @change="getResults('search')"
          />
        </div>
        <a :href="`${activityLink}/result/create`">
          <Btn
            :text="translatedData['common.common.add_result']"
            icon="plus"
            type="primary"
          />
        </a>
      </div>
    </PageTitle>

    <FilteringPills :pills="titles" @filter-by="handleFilter" />

    <!-- page content -->
    <div class="iati-list-table exception text-n-40">
      <table>
        <thead>
          <tr class="bg-n-10 text-left">
            <th
              id="transaction_type"
              scope="col"
              class="w-[650px] 2xl:w-[1000px]"
            >
              <span>{{ getTranslatedElement(translatedData, 'title') }}</span>
            </th>
            <th id="transaction_type" scope="col">
              <span>{{ translatedData['common.common.result_number'] }}</span>
            </th>
            <th id="transaction_value" scope="col">
              <span>Result Type</span>
            </th>
            <th id="transaction_date" scope="col">
              <span>{{
                getTranslatedElement(translatedData, 'aggregation_status')
              }}</span>
            </th>
            <th id="action" scope="col">
              <span>{{ translatedData['common.common.action'] }}</span>
            </th>
            <th id="select_all" scope="col">
              <span>
                <span
                  class="cursor-pointer"
                  @click="toggleSelectAll(resultsData.data)"
                >
                  <svg-vue
                    icon="checkbox"
                    :class="isAllValueSelected ? '!text-spring-50' : ''"
                  />
                </span>
              </span>
            </th>
          </tr>
        </thead>
        <tbody v-if="resultsData.data && resultsData.data.length > 0">
          <tr v-for="(result, t, index) in resultsData.data" :key="index">
            <td
              class="exception cursor-pointer"
              @click="handleNavigate(`${activityLink}/result/${result.id}`)"
            >
              <div class="ellipsis relative">
                <a
                  :href="`${activityLink}/result/${result.id}`"
                  class="ellipsis exception text-n-50"
                >
                  {{ getActivityTitle(result.result.title[0].narrative, 'en') }}
                </a>
                <div class="w-52">
                  <span class="ellipsis__title--hover">{{
                    getActivityTitle(result.result.title[0].narrative, 'en')
                  }}</span>
                </div>
              </div>
            </td>
            <td>{{ result.result_code }}</td>
            <td
              class="cursor-pointer"
              @click="handleNavigate(`${activityLink}/result/${result.id}`)"
            >
              {{
                types.resultType[result.result.type] ??
                getTranslatedMissing(translatedData)
              }}
            </td>
            <td
              class="cursor-pointer capitalize"
              @click="handleNavigate(`${activityLink}/result/${result.id}`)"
            >
              {{
                parseInt(result.result.aggregation_status)
                  ? translatedData['common.common.true']
                  : result.result.aggregation_status
                  ? translatedData['common.common.false']
                  : getTranslatedMissing(translatedData)
              }}
            </td>
            <td>
              <div class="flex">
                <a
                  class="mr-6 text-n-40"
                  :href="`/activity/${result.activity_id}/result/${result.id}/edit`"
                >
                  <svg-vue icon="edit" class="text-xl"></svg-vue>
                </a>
                <span
                  class="cursor-pointer"
                  @click="initiateDelete('single', result.id)"
                >
                  <svg-vue icon="delete" class="text-xl"></svg-vue>
                </span>
                <!-- <DeleteAction :item-id="result.id" item-type="result" /> -->
              </div>
            </td>

            <td
              class="check-column"
              @click="(event: Event) => event.stopPropagation()"
            >
              <label class="sr-only" for="">
                {{ translatedData['common.common.select_results'] }}
              </label>
              <label class="checkbox">
                <input
                  v-model="store.state.selectedResults"
                  :value="result.id"
                  type="checkbox"
                  class="cursor-pointer"
                />
                <span class="checkmark" />
              </label>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <td colspan="5" class="text-center">
            {{ translatedData['common.common.no_data_found'] }}
          </td>
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      <Pagination
        v-if="resultsData && resultsData.last_page > 1"
        :data="resultsData"
        :reset="isPaginationReset"
        @fetch-activities="fetchListings"
      />
    </div>

    <PopupModal
      :modal-active="deleteModalShow"
      width="583"
      @close="deleteToggle"
    >
      <div class="mb-4">
        <div class="title mb-6 flex">
          <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
          <b>{{ translatedData['common.common.delete_results'] }}</b>
        </div>
        <div class="rounded-lg bg-rose p-4">
          <p>
            {{
              deleteResultsList.type === 'single'
                ? translatedData[
                    'common.common.are_you_sure_you_want_to_delete_this_result'
                  ]
                : translatedData[
                    'common.common.are_you_sure_you_want_to_delete_these_results'
                  ]
            }}
          </p>
        </div>
      </div>
      <div class="flex justify-end">
        <div class="inline-flex">
          <ButtonComponent
            class="bg-white px-6 uppercase"
            :text="translatedData['common.common.go_back']"
            type=""
            @click="deleteModalShow = false"
          />
          <ButtonComponent
            class="space"
            :text="translatedData['common.common.delete']"
            type="primary"
            @click="confirmDelete"
          />
        </div>
      </div>
    </PopupModal>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  ref,
  toRefs,
  onMounted,
  reactive,
  provide,
  computed,
  watchEffect,
} from 'vue';
import axios from 'axios';

// components
import Btn from 'Components/ButtonComponent.vue';
import Pagination from 'Components/TablePagination.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';

// composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';
import FilteringPills from 'Components/FilteringPills.vue';
import { useStore } from 'Store/activities';
import ButtonComponent from 'Components/ButtonComponent.vue';
import PopupModal from 'Components/PopupModal.vue';
import { useToggle } from '@vueuse/core';
import { getTranslatedElement, getTranslatedMissing } from 'Composable/utils';

export default defineComponent({
  name: 'ResultsList',
  components: {
    Btn,
    Pagination,
    PageTitle,
    Toast,
    FilteringPills,
    ButtonComponent,
    PopupModal,
  },
  props: {
    activity: {
      type: Object,
      required: true,
    },
    results: {
      type: Object,
      required: true,
    },
    types: {
      type: Object,
      required: true,
    },
    toast: {
      type: Object,
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const { activity } = toRefs(props);

    const activityId = activity.value.id,
      activityTitle = activity.value.title,
      activityLink = `/activity/${activityId}`;
    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });

    const searchValue = ref('');
    const activePage = ref(1);
    const filterValue = ref('all');
    const resetPills = ref(false);
    const store = useStore();
    const deleteModalShow = ref(false);
    const deleteResultsList = ref({
      type: '',
      id: 0,
    });

    // toggle state for modal popup
    let [deleteValue, deleteToggle] = useToggle();

    interface ResultsInterface {
      last_page: number;

      data: {
        result_code: string;
        id: number;
        result: {
          title: {
            narrative: [];
          }[];
          type: string;
          aggregation_status: string;
        };
        activity_id: number;
      }[];
    }

    const resultsData = reactive({}) as ResultsInterface;
    const isEmpty = ref(false);
    const isPaginationReset = ref(false);
    const isAllValueSelected = ref(false);

    const countData = ref({
      all: 0,
      output: 0,
      outcome: 0,
      impact: 0,
      other: 0,
    });

    /**
     * Breadcrumb data
     */
    const breadcrumbData = [
      {
        title: props.translatedData['common.common.your_activities'],
        link: '/activities',
      },
      {
        title: getActivityTitle(activityTitle, 'en'),
        link: activityLink,
      },
      {
        title: props.translatedData['common.common.result_list'],
        link: '',
      },
    ];

    const titles = computed(() => [
      { title: 'All', searchTerm: 'all', count: countData.value.all },
      {
        title: 'Output',
        searchTerm: 'output',
        count: countData.value.output,
      },
      {
        title: 'Outcome',
        searchTerm: 'outcome',
        count: countData.value.outcome,
      },
      {
        title: 'Impact',
        searchTerm: 'impact',
        count: countData.value.impact,
      },
      {
        title: 'Other',
        searchTerm: 'other',
        count: countData.value.other,
      },
    ]);

    function handleNavigate(path) {
      window.location.href = path;
    }

    /**
     * Reset the filtering pills to their default state.
     * This is done by setting the reactive boolean `resetPills`
     * to true, and then setting it back to false after a short
     * delay. This is done to ensure that the pills are reset
     * after the user has finished interacting with the
     * filtering elements.
     */
    const resetPill = () => {
      resetPills.value = true;
      setTimeout(() => {
        resetPills.value = false;
      }, 100);
    };

    /**
     * Reset the pagination to its default state.
     * This is done by setting the reactive boolean `isPaginationReset`
     * to true, and then setting it back to false after a short
     * delay. This is done to ensure that the pagination is reset
     * after the user has finished interacting with the
     * pagination elements.
     */
    const resetPagination = () => {
      isPaginationReset.value = true;
      setTimeout(() => {
        isPaginationReset.value = false;
      }, 100);
    };

    /**
     * Toggles the selection of all the results in the list.
     * @param {object[]} data - List of results with an id property.
     * If all results are selected, it removes them from the selected
     * results list. If not all are selected, it adds them to the selected
     * results list.
     * @returns {void}
     */
    const toggleSelectAll = (data: { id: number }[]) => {
      const allSelected = data.every((item) =>
        store.state.selectedResults.includes(item.id)
      );

      isAllValueSelected.value = !allSelected;

      if (allSelected) {
        store.state.selectedResults = store.state.selectedResults.filter(
          (id) => !data.some((item) => item.id === id)
        );
      } else {
        const newIds = data.map((item) => item.id);
        store.state.selectedResults = [
          ...new Set([...store.state.selectedResults, ...newIds]),
        ];
      }
    };

    /**
     * Gets the results for the current page based on the search value and
     * filter value.
     * @returns {Promise<void>}
     */
    const getResults = async (value?: string): Promise<void> => {
      if (value === 'search') {
        activePage.value = 1;
        resetPagination();
      }

      const params = new URLSearchParams({
        filterBy: filterValue.value,
      });
      await axios
        .get(
          `/activity/${activityId}/results/page/${activePage.value}?q=${
            searchValue.value
          }&${params.toString()}`
        )
        .then((res) => {
          const response = res.data;
          Object.assign(resultsData, response.data.results);
          countData.value = response.data.stats;
          isEmpty.value = response?.data?.data?.results?.length ? false : true;
        });
    };

    /**
     * Resets the search value and pagination and fetches the results for the
     * given filter value.
     * @param {string} value - The filter value.
     * @returns {Promise<void>}
     */
    const handleFilter = async (value: string): Promise<void> => {
      filterValue.value = value;
      activePage.value = 1;
      searchValue.value = '';
      resetPill();
      resetPagination();
      await axios
        .get(
          `/activity/${activityId}/results/page/${activePage.value}?filterBy=${value}`
        )
        .then((res) => {
          const response = res.data;
          Object.assign(resultsData, response.data.results);
          isEmpty.value = response.data.results.length ? false : true;
        });
    };

    /**
     * Shows a toast message of the given type with the given message.
     * Automatically hides the toast after 5 seconds.
     * @param {boolean} type - The type of the toast message.
     * @param {string} message - The message to be displayed in the toast.
     */
    const showToast = (type: boolean, message: string) => {
      toastData.type = type;
      toastData.visibility = true;
      toastData.message = message;
      setTimeout(() => {
        toastData.visibility = false;
      }, 5000);
    };

    /**
     * Sets the delete modal data with the given type and id and shows the
     * delete modal.
     * @param {string} type - The type of the item to be deleted (e.g. "single", "bulk").
     * @param {number} [id] - The id of the item to be deleted.
     */
    const initiateDelete = (type: string, id?: number) => {
      deleteResultsList.value = {
        type,
        id: id ?? 0,
      };
      deleteModalShow.value = true;
    };

    /**
     * Handles API error responses by displaying an appropriate toast message.
     * If the error response has a status of 422, it shows a specific error message
     * related to result IDs. Otherwise, it displays a generic error message.
     *
     * @param {Object} error - The error object from the API response.
     */
    const handleApiError = (error) => {
      if (error?.response?.status === 422) {
        showToast(false, error?.response?.data?.errors?.result_ids[0]);
      } else {
        showToast(false, 'Failed to delete results. Something went wrong.');
      }
    };

    /**
     * Deletes results with the given URL and optional data.
     * @param {string} url - The URL to delete the results from.
     * @param {Object} [data] - The data to be passed with the request.
     * @param {number[]} [data.result_ids] - The IDs of the results to be deleted.
     *
     * If the request is successful, it shows a toast message with the response
     * message and resets the pagination and the pill.
     *
     * If the request fails, it calls the handleApiError function to handle the
     * error response.
     */
    const deleteResult = async (
      url: string,
      data?: { result_ids?: number[] }
    ) => {
      try {
        const response = await axios.delete(url, data ? { data } : undefined);
        if (response.data.status) {
          showToast(true, response.data.msg);
          getResults();
          resetPill();
          resetPagination();
        }
      } catch (error) {
        handleApiError(error);
      }
    };

    /**
     * Deletes a single result with the specified ID.
     *
     * @param {number} id - The ID of the result to be deleted.
     *
     * This function sends a request to delete a specific result identified by the given ID.
     * Upon successful deletion, it triggers necessary updates in the system.
     */
    const singleDeleteResult = async (id: number) => {
      await deleteResult(`/activity/${activityId}/result/${id}`);
    };

    /**
     * Deletes multiple results with the given IDs.
     *
     * This function uses the deleteResult function to delete the results.
     * Upon successful deletion, it resets the pagination and triggers a new
     * fetch of the results.
     */
    const bulkDeleteResults = async () => {
      const { selectedResults } = store.state;

      if (selectedResults.length > 0) {
        await deleteResult(`/activity/${activityId}/results`, {
          result_ids: selectedResults,
        });

        store.state.selectedResults = [];
        isAllValueSelected.value = false;

        isPaginationReset.value = true;
        setTimeout(() => {
          isPaginationReset.value = false;
        }, 100);

        getResults();
      }
    };

    /**
     * Handles the deletion of results after the user confirms the deletion in the
     * delete modal.
     *
     * If the deletion type is 'single', it calls the singleDeleteResult function
     * to delete the result with the given ID.
     *
     * If the deletion type is 'bulk', it calls the bulkDeleteResults function to
     * delete the selected results.
     *
     * In either case, it sets the delete modal to be hidden and resets the
     * selected results state.
     */
    const confirmDelete = () => {
      deleteModalShow.value = false;
      if (
        deleteResultsList.value.type === 'single' &&
        deleteResultsList.value.id > 0
      ) {
        singleDeleteResult(deleteResultsList.value.id);
      } else {
        bulkDeleteResults();
      }
    };

    /**
     * Fetches the results for the given page and filter value.
     *
     * @param {number} active_page - The page number to fetch.
     *
     * This function sets the active page number and fetches the results for the
     * given page and filter value. It then updates the resultsData state with the
     * response and sets the isEmpty state to true or false based on whether the
     * response has data.
     */
    function fetchListings(active_page: number) {
      activePage.value = active_page;
      const params = new URLSearchParams({
        q: searchValue.value,
        filterBy: filterValue.value,
      });
      axios
        .get(
          `/activity/${activityId}/results/page/` +
            active_page +
            '?' +
            params.toString()
        )
        .then((res) => {
          const response = res.data;
          Object.assign(resultsData, response.data.results);
          isEmpty.value = response.data.results ? false : true;
        });
    }

    onMounted(async () => {
      axios.get(`/activity/${activityId}/results/page/1`).then((res) => {
        const response = res.data;
        countData.value = response.data.stats;
        Object.assign(resultsData, response.data.results);
        isEmpty.value = response.data.results.length ? false : true;
      });

      if (props.toast.message !== '') {
        toastData.type = props.toast.type;
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }

      setTimeout(() => {
        toastData.visibility = false;
      }, 5000);
    });

    // Provide
    provide('parentItemId', activityId);
    provide('translatedData', props.translatedData);

    return {
      breadcrumbData,
      activityLink,
      toastData,
      dateFormat,
      resultsData,
      getActivityTitle,
      fetchListings,
      handleNavigate,
      searchValue,
      getResults,
      titles,
      handleFilter,
      isPaginationReset,
      store,
      toggleSelectAll,
      isAllValueSelected,
      initiateDelete,
      deleteModalShow,
      deleteResultsList,
      deleteToggle,
      deleteValue,
      confirmDelete,
    };
  },
  methods: { getTranslatedMissing, getTranslatedElement },
});
</script>
<style scoped>
.v-enter-active,
.v-leave-active {
  transition: all 0.5s ease;
}

.v-enter-from,
.v-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>
