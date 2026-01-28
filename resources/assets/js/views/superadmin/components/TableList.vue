<template>
  <div class="filters mb-4 flex flex-wrap justify-between gap-2">
    <!--Filter options start-->
    <div class="select filters inline-flex items-center space-x-2">
      <svg-vue class="w-10 text-lg" icon="funnel" />
      <span class="multiselect-label-wrapper" :style="generateLabel('country')">
        <Multiselect
          id="country-filter"
          v-model="filter.country"
          placeholder="COUNTRY"
          mode="multiple"
          :searchable="true"
          :options="countriesWithPrefix"
          :taggable="true"
          :close-on-select="false"
          :clear-on-select="false"
          :hide-selected="false"
          :can-clear="false"
          label="country"
        />
      </span>
      <span
        class="multiselect-label-wrapper"
        :style="generateLabel('setup completeness')"
      >
        <Multiselect
          id="setup-completeness"
          v-model="filter.completeness"
          placeholder="SETUP COMPLETENESS"
          :options="setupCompleteness"
          :taggable="true"
          :close-on-select="true"
          :clear-on-select="false"
          :hide-selected="false"
          :can-clear="false"
          label="setupCompleteness"
        />
      </span>
      <span
        class="multiselect-label-wrapper whitespace-nowrap"
        :style="generateLabel('registration type')"
      >
        <Multiselect
          id="registration-type"
          v-model="filter.registration_type"
          placeholder="REGISTRATION TYPE"
          :options="registrationTypes"
          :taggable="true"
          :close-on-select="true"
          :clear-on-select="false"
          :hide-selected="false"
          :can-clear="false"
          label="registrationType"
        />
      </span>

      <span
        class="multiselect-label-wrapper"
        :style="generateLabel('Organisation Type')"
      >
        <Multiselect
          id="publisher-type"
          v-model="filter.publisher_type"
          :options="publisherTypes"
          placeholder="ORGANISATION TYPE"
          mode="multiple"
          :taggable="true"
          :close-on-select="true"
          :clear-on-select="false"
          :hide-selected="false"
          :can-clear="false"
          label="publisherType"
        />
      </span>
      <span
        class="multiselect-label-wrapper"
        :style="generateLabel('data license')"
      >
        <Multiselect
          id="data-license"
          v-model="filter.data_license"
          :options="dataLicenses"
          placeholder="DATA LICENSE"
          mode="multiple"
          :taggable="true"
          :close-on-select="true"
          :clear-on-select="false"
          :hide-selected="false"
          :can-clear="false"
          label="dataLicense"
        />
      </span>
    </div>
    <!--Filter options end-->

    <!--Date range start-->
    <div class="flex h-[38px] w-full items-center justify-end px-4 2xl:w-auto">
      <DateRangeWidget
        :dropdown-range="dropdownRange"
        :first-date="oldestDates"
        :clear-date="clearDate"
        :starting-date="filter.start_date"
        :ending-date="filter.end_date"
        :date-name="dateType"
        @trigger-set-date-range="setDateRangeDate"
        @trigger-set-date-type="setDateType"
        @date-cleared="clearDate = false"
      />
    </div>
    <!--Date range start-->
  </div>

  <!--Filter tag pills start-->
  <div
    v-if="isFilterApplied"
    class="mb-4 flex max-w-full flex-wrap items-center space-x-2"
  >
    <span class="text-sm font-bold uppercase text-n-40">Filtered by: </span>

    <span v-show="filter.country" class="inline-flex flex-wrap gap-2">
      <span
        v-for="(item, index) in filter.country"
        :key="index"
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span class="text-n-40">Country:</span>
        <span
          class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
          >{{ item }}</span
        >
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="filter.country.splice(index, 1)"
        />
      </span>
    </span>

    <span v-show="filter.completeness" class="inline-flex flex-wrap gap-2">
      <span
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span class="text-n-40">Setup Completeness:</span>
        <span
          class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
          >{{ snakeCaseToSentenceCase(filter.completeness) }}</span
        >
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="filter.completeness = ''"
        />
      </span>
    </span>

    <span v-show="filter.registration_type" class="inline-flex flex-wrap gap-2">
      <span
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span class="text-n-40">Registration Type:</span>
        <span
          class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
          >{{ snakeCaseToSentenceCase(filter.registration_type) }}</span
        >
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="filter.registration_type = ''"
        />
      </span>
    </span>

    <span
      v-show="filter.publisher_type.length"
      class="inline-flex flex-wrap gap-2"
    >
      <span
        v-for="(item, index) in filter.publisher_type"
        :key="index"
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span class="text-n-40">Organisation Type:</span>
        <span
          class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
          >{{ publisherTypes[item] }}
        </span>
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="filter.publisher_type.splice(index, 1)"
        />
      </span>
    </span>

    <span
      v-show="filter.data_license.length"
      class="inline-flex flex-wrap gap-2"
    >
      <span
        v-for="(item, index) in filter.data_license"
        :key="index"
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span class="text-n-40">Data License:</span>
        <span
          class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
          >{{ item }}</span
        >
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="filter.data_license.splice(index, 1)"
        />
      </span>
    </span>
    <span
      v-show="filter.start_date && filter.end_date"
      class="inline-flex flex-wrap gap-2"
    >
      <span
        class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
      >
        <span>
          <span class="text-n-40"> Date range: </span>
          {{ filter.selected_date_filter }}
        </span>
        <svg-vue
          class="mx-2 mt-1 cursor-pointer text-xs"
          icon="cross"
          @click="
            () => {
              clearDateFilter();
            }
          "
        />
      </span>
    </span>

    <button class="font-bold uppercase text-bluecoral" @click="resetAllFilters">
      Clear Filter
    </button>
  </div>
  <!--Filter tag pills end-->

  <div>
    <p class="py-1">Total Number of Organisation: {{ totalOrganisation }}</p>
    <div class="iati-list-table">
      <table>
        <thead>
          <tr class="bg-n-10">
            <th id="organisation_name" scope="col">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'name'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('name')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'name'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Organisation</span>
              </a>
            </th>
            <th id="country" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'country'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('country')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'country'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Country</span>
              </a>
            </th>
            <th id="registered_on" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'registered_on'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('registered_on')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'registered_on'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Registered On</span>
              </a>
            </th>
            <th id="last_login" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'last_logged_in'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('last_logged_in')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'last_logged_in'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Last Login</span>
              </a>
            </th>
            <th id="activities" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'all_activities_count'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('all_activities_count')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'all_activities_count'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Activities</span>
              </a>
            </th>
            <th id="publisher_type" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'publisher_type'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('publisher_type')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'publisher_type'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Organisation Type</span>
              </a>
            </th>
            <th id="data_licence" scope="col" style="width: 173px">
              <a
                class="cursor-pointer text-n-50 transition duration-500 hover:text-spring-50"
                :class="
                  sortParams.orderBy === 'data_license'
                    ? sortingDirection()
                    : defaultSortDirection
                "
                @click="sortBy('data_license')"
              >
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      sortParams.orderBy === 'data_license'
                        ? sortingDirection()
                        : defaultSortDirection
                    }-arrow`"
                  />
                </span>
                <span>Data licence </span>
              </a>
            </th>
            <th id="proxy" scope="col" style="width: 158px">
              <span></span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="organisationData.status === 'fetching'">
            <td colspan="4">Fetching Data...</td>
          </tr>
          <tr v-else-if="organisationData.status === 'failed to retrieve data'">
            <td colspan="4">Failed to retrieve data...</td>
          </tr>
          <tr v-else-if="organisationData.status === 'empty'">
            <td colspan="4">No Data Available</td>
          </tr>
          <tr v-for="data in organisationData.data.data" v-else :key="data.id">
            <td>
              <div>
                <div v-if="data.name" class="ellipsis relative">
                  <span class="ellipsis overflow-hidden">
                    {{ data?.name[0]?.narrative ?? 'Name Missing' }}
                  </span>
                </div>

                <div v-else>Name Missing</div>
                <div class="group relative">
                  <div
                    class="w-full overflow-x-hidden text-ellipsis text-blue-40"
                  >
                    {{ data?.user?.email }}
                  </div>
                  <div
                    class="absolute left-0 top-full hidden rounded bg-eggshell p-2 shadow-sm group-hover:block"
                  >
                    {{ data?.user?.email }}
                  </div>
                </div>
                <div class="flex">
                  <span
                    class="flex w-fit"
                    :class="
                      defaultValueStatus[data.id]
                        ? 'text-spring-50'
                        : 'text-crimson-50'
                    "
                  >
                    <svg-vue
                      class="text-md mt-1 cursor-pointer"
                      :icon="
                        defaultValueStatus[data.id]
                          ? 'tick'
                          : 'circle-red-cross'
                      "
                    ></svg-vue>
                    <span class="px-1"> Default Values </span>
                  </span>
                </div>
              </div>
            </td>
            <td class="text-n-40">
              <div>
                {{ countriesWithPrefix[data['country']] }}
              </div>
            </td>
            <td class="text-n-40">
              <div>
                <div class="pb-1">
                  {{ dateFormat(data['created_at'], 'MMMM, DD, YYYY') }}
                </div>
                <div class="text-xs">
                  Previously
                  {{
                    data['registration_type'] !== 'existing_org' ? 'not' : ''
                  }}
                  registered in IATI platform
                </div>
              </div>
            </td>
            <td class="text-n-40">
              <div>
                {{
                  data.last_logged_in
                    ? dateFormat(data.last_logged_in, 'MMMM, DD,YYYY')
                    : 'Not Available'
                }}
              </div>
            </td>
            <td class="text-n-40">
              <div>
                <div class="px-1">
                  {{ data.all_activities_count }} activities
                </div>
                <div class="text-xs">
                  {{
                    data['latest_updated_activity']
                      ? 'Last updated on:' +
                        dateFormat(
                          data['latest_updated_activity'].updated_at,
                          'MMMM, DD, YYYY'
                        )
                      : 'Not available'
                  }}
                </div>
              </div>
            </td>
            <td class="text-n-40">
              <div>
                {{ showMappedData('publisher_type', data, publisherTypes) }}
              </div>
            </td>
            <td class="text-n-40">
              <div>
                {{ showMappedData('data_license', data, dataLicenses) }}
              </div>
            </td>
            <td class="border-this flex">
              <div>
                <BtnComponent
                  text="proxy"
                  type="outline"
                  icon="smile"
                  @click="proxyUser(<number>data?.user?.id)"
                />
              </div>
              <button @click="openDeleteModal(data)">
                <svg-vue class="w-10 text-lg text-n-40" icon="delete" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mt-6">
      <Pagination
        v-if="organisationData.data && organisationData.data.last_page > 1"
        :data="organisationData.data"
        :reset="resetPagination"
        @fetch-activities="(n) => fetchOrganisation(n)"
      />
    </div>
    <div></div>
  </div>
  <PopupModal :modal-active="deleteModal" @close="toggleDeleteConfirmation">
    <div class="title mb-6 flex">
      <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
      <b>Delete organisation</b>
    </div>
    <p class="rounded-lg bg-rose p-4">
      Are you sure you want to delete <b> {{ deleteOrgName }}</b> ?
    </p>
    <p class="flex items-center gap-2 px-2">
      <label class="checkbox">
        <input id="markAsSpam" v-model="markAsSpam" type="checkbox" />
        <span class="checkmark" />
      </label>
      <label for="markAsSpam"> Mark associated emails as spam.</label>
    </p>
    <div class="mt-6 flex justify-end space-x-2">
      <button class="secondary-btn font-bold" @click="toggleDeleteConfirmation">
        Cancel
      </button>
      <button class="primary-btn !px-10" @click="deleteOrg(deleteId)">
        Delete <SpinnerLoader v-if="showLoader"></SpinnerLoader>
      </button>
    </div>
  </PopupModal>
</template>
<script lang="ts">
import {
  reactive,
  onMounted,
  inject,
  ref,
  watch,
  computed,
  defineComponent,
} from 'vue';
import axios from 'axios';
import MultiSelectWithSearch from 'Components/MultiSelectWithSearch.vue';

import dateFormat from 'Composable/dateFormat';
import {
  kebabCaseToSnakecase,
  snakeCaseToSentenceCase,
} from 'Composable/utils';

import BtnComponent from 'Components/ButtonComponent.vue';
import Pagination from 'Components/TablePagination.vue';
import Multiselect from '@vueform/multiselect';
import { watchIgnorable } from '@vueuse/core';
import DateRangeWidget from 'Components/DateRangeWidget.vue';
import { useStore } from 'Store/activities/index';
import PopupModal from 'Components/PopupModal.vue';
import SpinnerLoader from 'Components/spinnerLoader.vue';
import { ToastInterface } from 'Interfaces/ToastInterface';

export default defineComponent({
  name: 'TableList',
  components: {
    SpinnerLoader,
    PopupModal,
    BtnComponent: BtnComponent,
    Pagination: Pagination,
    Multiselect: Multiselect,
    DateRangeWidget: DateRangeWidget,
  },
  props: {
    countries: { type: Object, required: true },
    setupCompleteness: { type: Object, required: true },
    registrationTypes: { type: Object, required: true },
    publisherTypes: { type: Object, required: true },
    dataLicenses: { type: Object, required: true },
    oldestDates: {
      type: String,
      required: true,
    },
  },

  setup(props) {
    const toastMessage = inject('toastData') as ToastInterface;

    interface LoaderInterface {
      status: boolean;
      text: string;
    }

    const loader = inject('loader') as LoaderInterface;
    const dateDropdown = ref();
    const clearDate = ref(false);
    const dateType = ref('All Time');

    const store = useStore();

    const deleteId = ref('');
    const deleteOrgName = ref('');
    const deleteModal = ref(false);
    const markAsSpam = ref(false);
    const showLoader = ref(false);

    function toggleDeleteConfirmation() {
      deleteModal.value = !deleteModal.value;
      markAsSpam.value = false;
      showLoader.value = false;
    }

    const openDeleteModal = (organization) => {
      deleteModal.value = true;
      deleteId.value = organization.id;
      deleteOrgName.value = organization.name[0]?.narrative;
    };

    const deleteOrg = async (orgId) => {
      showLoader.value = true;

      const response = await axios.delete(
        `/organization/${orgId}` + (markAsSpam.value ? '?markAsSpam=true' : '')
      );

      showLoader.value = false;
      deleteModal.value = false;
      markAsSpam.value = false;
      toastMessage.message = response.data.message;
      toastMessage.type = response.data.success;
      toastMessage.visibility = true;

      if (response.data.success) {
        fetchOrganisation();
      }

      setTimeout(() => {
        toastMessage.visibility = false;
        toastMessage.message = '';
      }, 3000);
    };

    //typeface
    interface organizationInterface {
      data: oDataInterface;
      status: string;
    }

    interface oDataInterface {
      data: {
        total: number;

        id: number;
        name: {
          narrative: string;
        }[];
        organization_url: string;
        user: {
          email: string;
          id: number;
        };
        updated_at: Date;
        country: string;
        created_at: Date;
        last_logged_in: Date;
        all_activities_count: number;
        publisher_type: string | number;
        data_license: string;
      }[];
      last_page: number;
      current_page: number;
    }

    const getCountriesWithPrefix = () => {
      const returnValueForCountries = {};
      const countryCodes = Object.keys(props.countries);
      for (let i = 0; i < countryCodes.length; i++) {
        returnValueForCountries[countryCodes[i]] = `${countryCodes[i]} - ${
          props.countries[countryCodes[i]]
        }`;
      }
      return returnValueForCountries;
    };

    const countriesWithPrefix = getCountriesWithPrefix();

    // reactivity
    let organisationData: organizationInterface = reactive({
      data: {} as oDataInterface,
      status: 'fetching',
    });

    let multiselectStyle = ref({});

    let filter = reactive({
      publisher_type: [],
      data_license: [],
      country: [],
      completeness: '',
      registration_type: '',
      start_date: '',
      end_date: '',
      date_type: 'created_at',
      selected_date_filter: '',
    });
    const resetPagination = ref(false);
    const totalOrganisation = ref(0);
    let registryApiKeyStatus: boolean[] = reactive([]);
    let defaultValueStatus: boolean[] = reactive([]);
    const showMultiSelectWithSearch = ref(false);
    let dropdownRange = {
      created_at: 'Registered date range',
      last_logged_in: 'Last login date range',
    };
    const sortParams = ref({ orderBy: '', direction: '' });

    const { ignoreUpdates } = watchIgnorable(filter, () => undefined);
    watch(
      () => showMultiSelectWithSearch.value,
      (value) => {
        if (value) {
          rotateClass.value = 'rotate-180';
        } else {
          rotateClass.value = 'rotate-0';
        }
        if (value) {
          document.addEventListener('click', closePublisherModel);
        } else {
          document.removeEventListener('click', closePublisherModel);
        }
      }
    );

    const clearDateFilter = () => {
      filter.start_date = '';
      filter.end_date = '';
      filter.selected_date_filter = '';
      clearDate.value = true;
    };

    const closePublisherModel = () => {
      showMultiSelectWithSearch.value = false;
    };
    const generateLabel = (label) => {
      return { '--label': `'${label}'` };
    };

    //lifecycle
    onMounted(() => {
      let filterParams = getFilterParamsFromPreviousPage();

      if (filterParams) {
        for (let i = 0; i < filterParams.length; i++) {
          let key = kebabCaseToSnakecase(filterParams[i][0]);
          let value = filterParams[i][1];

          if (['publisher_type', 'data_license', 'country'].includes(key)) {
            filter[key].push(value);
          } else if (key === 'date_type') {
            dateType.value = value.split('-').join(' ');
          } else {
            filter[key] = value;
          }
        }
      }

      fetchOrganisation(1);
    });

    const getFilterParamsFromPreviousPage = () => {
      let queryString = window.location.href?.toString();

      if (queryString) {
        queryString = queryString.split('?')[1];

        let queryParamsInKeyVal: object[] = [];
        const queryParams = queryString?.split('&');

        if (queryParams) {
          for (let i = 0; i < queryParams.length; i++) {
            let [key, value] = queryParams[i].split('=');
            if (key) {
              queryParamsInKeyVal.push([key, value ?? '']);
            }
          }
        }

        return queryParamsInKeyVal;
      }

      return false;
    };

    /**
     * Fetching organization list
     *
     */
    const currentURL = window.location.href;
    const fetchOrganisation = (active_page = 1) => {
      organisationData.status = 'fetching';
      let queryString = '';
      if (currentURL.includes('?')) {
        queryString = window.location.search;
      }

      active_page = active_page ?? 1;
      let endpoint = `/list-organisations/page/${active_page}${queryString}`;

      if (sortParams.value.orderBy) {
        urlParams.append('orderBy', sortParams.value.orderBy);
        urlParams.append('direction', sortParams.value.direction);
      }

      if (
        isFilterApplied.value ||
        Boolean(sortParams.value.orderBy && sortParams.value.direction)
      ) {
        queryString = queryString ?? '&q=';
        endpoint = queryString !== '' ? endpoint : `${endpoint}`;
        for (const filterKey in filter) {
          if (filter[filterKey] && filter[filterKey].length > 0) {
            urlParams.append(filterKey, filter[filterKey]);
          }
        }
      }

      axios
        .get(endpoint, {
          params:
            isFilterApplied.value ||
            Boolean(sortParams.value.orderBy && sortParams.value.direction)
              ? urlParams
              : '',
        })
        .then((res) => {
          const response = res.data;
          totalOrganisation.value = response.data?.total;

          if (response.success) {
            if (response.data.data.length === 0) {
              organisationData.status = 'empty';
            } else {
              organisationData.status = 'success';
              organisationData.data = response.data;

              refreshStatusArrays(organisationData.data);
            }
          } else {
            organisationData.status = 'failed to retrieve data';
          }
        });
      urlParams = new URLSearchParams(queryString);
    };

    /**
     * Proxy User
     */
    // display/hide validator loader
    const proxyUser = (id: number) => {
      loader.status = true;
      loader.text = 'Proxy Login';
      const endpoint = `/proxy-organisation/${id}`;

      axios.get(endpoint).then((res) => {
        const response = res.data;

        if (response.success) {
          localStorage.removeItem('validatingActivitiesNames');
          // localStorage.removeItem('validatingActivities');
          localStorage.removeItem('activityValidating');
          store.dispatch('updateStartValidation', false);

          setTimeout(() => {
            window.location.replace('/activities');
          }, 1000);
        } else {
          loader.status = false;
          toastMessage.message = response.message;
          toastMessage.type = response.success;
        }
      });
    };

    /**
     * Sorting By update on
     */
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    let query = '',
      defaultSortDirection = 'descending',
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      sortDirection = 'desc';

    const queryString = window.location.search;

    let urlParams = new URLSearchParams(queryString);
    let orderType = ref('');
    orderType.value = urlParams.get('orderBy') ?? '';
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    let range = '';

    const sortingDirection = () => {
      return sortParams.value.direction === 'asc' ? 'descending' : 'ascending';
    };

    const sortBy = async (order) => {
      resetPagination.value = true;
      sortParams.value.orderBy = order;
      sortParams.value.direction =
        sortParams.value.direction === 'desc' ? 'asc' : 'desc';

      if (currentURL.includes('?')) {
        query = urlParams.get('q') ?? '';
        sortDirection = urlParams.get('direction') === 'desc' ? 'asc' : 'desc';

        let startDate = urlParams.get('start_date') ?? false;
        let endDate = urlParams.get('end_date') ?? false;

        if (startDate && endDate) {
          range = `&start_date=${startDate}&end_date=${endDate}`;
        }
      }

      await fetchOrganisation(1);
      resetPagination.value = false;
    };

    watch(
      () => [
        filter.country,
        filter.completeness,
        filter.registration_type,
        filter.publisher_type,
        filter.data_license,
        filter.start_date,
        filter.end_date,
        filter.date_type,
      ],
      () => {
        fetchOrganisation();
      },
      { deep: true }
    );

    const resetAllFilters = () => {
      ignoreUpdates(() => {
        filter.country = [];
        filter.publisher_type = [];
        filter.data_license = [];
        filter.completeness = '';
        filter.registration_type = '';
        filter.start_date = '';
        filter.end_date = '';
        filter.date_type = 'created_at';
        filter.selected_date_filter = '';
        clearDate.value = true;
      });
    };

    const isFilterApplied = computed(() => {
      return (
        filter.country.length +
          filter.publisher_type.length +
          filter.data_license.length !=
          0 ||
        filter.completeness !== '' ||
        filter.registration_type !== '' ||
        (filter.start_date !== '' && filter.end_date !== '')
      );
    });

    const refreshStatusArrays = (orgData) => {
      for (let orgDatum of orgData.data) {
        registryApiKeyStatus[orgDatum.id] =
          orgDatum?.settings?.publishing_info?.token_verification ?? false;
        defaultValueStatus[orgDatum.id] = checkIfDefaultValuesAreValid(
          orgDatum ? orgDatum.settings : false
        );
      }
    };

    const checkIfDefaultValuesAreValid = (settings) => {
      if (settings) {
        let defaultValues = settings.default_values;
        let activityDefaultValues = settings.activity_default_values;
        return !!(
          (defaultValues?.default_currency ?? false) &&
          (defaultValues?.default_language ?? false) &&
          (activityDefaultValues?.hierarchy ?? false) &&
          (activityDefaultValues?.budget_not_provided ?? false) &&
          (activityDefaultValues?.humanitarian != null ||
            activityDefaultValues?.humanitarian != '' ||
            activityDefaultValues?.humanitarian != false)
        );
      }

      return false;
    };

    const setDateRangeDate = (startDate, endDate, selectedDateFilter = '') => {
      filter.start_date = startDate;
      filter.end_date = endDate;
      filter.selected_date_filter = selectedDateFilter;
    };

    const setDateType = (dateType) => {
      filter.date_type = dateType;
    };

    const rotateClass = ref('');

    const toggleShowMultiSelect = (event) => {
      event.stopPropagation();
      const rect = event.target.getBoundingClientRect();
      multiselectStyle.value = {
        top: Number(rect.top) < 100 ? 210 + 'px' : Number(rect.top) + 50 + 'px',
        left: Number(rect.left) + 'px',
      };
      showMultiSelectWithSearch.value = !showMultiSelectWithSearch.value;
    };

    const setSelectedPublisher = (publisherTypes) => {
      filter.publisher_type = publisherTypes;
    };

    /*
     * For mapping country, publisher_type and data license
     */
    const showMappedData = (key, data, map) => {
      if (data) {
        if (key == 'data_license') {
          let license = data[key];
          license = license?.trim();
          return license ? map[license] : 'Not available';
        }

        return data[key] ? map[data[key]] : 'Not available';
      }
      return 'Not available';
    };

    return {
      BtnComponent,
      Multiselect,
      DateRangeWidget,
      MultiSelectWithSearch,
      organisationData,
      dropdownRange,
      setSelectedPublisher,
      toggleShowMultiSelect,
      setDateType,
      setDateRangeDate,
      sortBy,
      resetAllFilters,
      sortingDirection,
      defaultSortDirection,
      proxyUser,
      dateFormat,
      fetchOrganisation,
      defaultValueStatus,
      registryApiKeyStatus,
      orderType,
      clearDate,
      filter,
      snakeCaseToSentenceCase,
      isFilterApplied,
      props,
      showMultiSelectWithSearch,
      dateType,
      rotateClass,
      multiselectStyle,
      clearDateFilter,
      dateDropdown,
      sortParams,
      resetPagination,
      showMappedData,
      totalOrganisation,
      countriesWithPrefix,
      deleteModal,
      deleteOrgName,
      deleteId,
      generateLabel,
      toggleDeleteConfirmation,
      openDeleteModal,
      deleteOrg,
      markAsSpam,
      showLoader,
    };
  },
});
</script>

<style>
.multiselect-lookalike-wrapper {
  position: absolute;
  z-index: 5;
  width: 424px;
  height: fit-content;
  background: white;
  overflow-y: auto;
}
.multiselect-lookalike {
  position: relative;
  align-items: center;
  background-color: #fff !important;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  width: 160px !important;
  height: 40px;
  padding: 12px;
  cursor: pointer;
}
</style>
