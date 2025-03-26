<template>
  <div class="relative bg-paper px-5 pb-[71px] pt-4 xl:px-10">
    <PageTitle
      :breadcrumb-data="breadcrumbData"
      :title="translatedData['common.common.transaction_list']"
      :back-link="activityLink"
    >
      <div class="flex items-center space-x-3">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <ButtonComponent
          v-if="store.state.selectedTransactions.length > 0"
          type="secondary"
          :text="`${translatedData['common.common.delete_selected']} (${store.state.selectedTransactions.length})`"
          icon="delete"
          @click="initiateDelete('bulk')"
        />
        <a :href="`${activityLink}/transaction/create`">
          <Btn
            :text="translatedData['common.common.add_transaction']"
            icon="plus"
            type="primary"
          />
        </a>
      </div>
    </PageTitle>

    <FilteringPills
      :pills="titles"
      :reset="resetPill"
      @filter-by="handleFilter"
    />

    <!-- page content -->
    <div class="iati-list-table text-n-40">
      <table>
        <thead>
          <tr class="bg-n-10">
            <th id="internal_ref" scope="col">
              <span>{{
                getTranslatedElement(translatedData, 'internal_ref')
              }}</span>
            </th>
            <th
              id="transaction_type"
              scope="col"
              class="cursor-pointer transition-all duration-300 hover:text-spring-50"
              :class="{ 'text-spring-50': currentlySortedBy === 'type' }"
              @click="sortByOrder('type', true)"
            >
              <div class="flex items-center">
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      columnDirections.type === 'asc'
                        ? 'ascending'
                        : 'descending'
                    }-arrow`"
                  />
                </span>
                <span>{{
                  getTranslatedElement(translatedData, 'transaction_type')
                }}</span>
              </div>
            </th>
            <th
              id="transaction_value"
              scope="col"
              class="cursor-pointer transition-all duration-300 hover:text-spring-50"
              :class="{ 'text-spring-50': currentlySortedBy === 'value' }"
              @click="sortByOrder('value', true)"
            >
              <div class="flex items-center">
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      columnDirections.value === 'asc'
                        ? 'ascending'
                        : 'descending'
                    }-arrow`"
                  />
                </span>
                <span>{{
                  getTranslatedElement(translatedData, 'transaction_value')
                }}</span>
              </div>
            </th>
            <th
              id="transaction_date"
              scope="col"
              class="cursor-pointer transition-all duration-300 hover:text-spring-50"
              :class="{ 'text-spring-50': currentlySortedBy === 'date' }"
              @click="sortByOrder('date', true)"
            >
              <div class="flex items-center">
                <span class="sorting-indicator">
                  <svg-vue
                    :icon="`${
                      columnDirections.date === 'asc'
                        ? 'ascending'
                        : 'descending'
                    }-arrow`"
                  />
                </span>
                <span>{{
                  getTranslatedElement(translatedData, 'transaction_date')
                }}</span>
              </div>
            </th>
            <th id="action" scope="col">
              <span>{{ translatedData['common.common.action'] }}</span>
            </th>
            <th id="select_all" scope="col">
              <span>
                <span
                  class="cursor-pointer"
                  @click="toggleSelectAll(transactionsData.data)"
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
        <tbody v-if="transactionsData.data && transactionsData.data.length > 0">
          <tr v-for="(trans, t, index) in transactionsData.data" :key="index">
            <td
              class="cursor-pointer"
              @click="handleNavigate(`${activityLink}/transaction/${trans.id}`)"
            >
              <div class="ellipsis relative">
                <a :href="`${activityLink}/transaction/${trans.id}`">
                  <span>{{
                    trans.transaction.reference &&
                    trans.transaction.reference !== ''
                      ? trans.transaction.reference
                      : '- - -'
                  }}</span>
                </a>
                <div class="w-52">
                  <span class="ellipsis__title--hover">{{
                    trans.transaction.reference &&
                    trans.transaction.reference !== ''
                      ? trans.transaction.reference
                      : '- - -'
                  }}</span>
                </div>
              </div>
            </td>
            <td
              class="cursor-pointer"
              @click="handleNavigate(`${activityLink}/transaction/${trans.id}`)"
            >
              {{
                types.transactionType[
                  trans.transaction.transaction_type[0].transaction_type_code
                ] ?? '- - -'
              }}
            </td>
            <td
              class="cursor-pointer truncate"
              @click="handleNavigate(`${activityLink}/transaction/${trans.id}`)"
            >
              {{
                trans.transaction.value[0].amount
                  ? Number(trans.transaction.value[0].amount).toLocaleString()
                  : '- - -'
              }}
            </td>
            <td
              class="cursor-pointer"
              @click="handleNavigate(`${activityLink}/transaction/${trans.id}`)"
            >
              <span>
                {{
                  trans.transaction.transaction_date[0].date
                    ? moment(trans.transaction.transaction_date[0].date).format(
                        'YYYY-MM-DD'
                      )
                    : '- - -'
                }}
              </span>
            </td>
            <td>
              <div class="flex text-n-40">
                <a
                  class="mr-6"
                  :href="`${activityLink}/transaction/${trans.id}/edit`"
                >
                  <svg-vue icon="edit" class="text-xl"></svg-vue>
                </a>
                <span
                  class="cursor-pointer"
                  @click="initiateDelete('single', trans.id)"
                >
                  <svg-vue icon="delete" class="text-xl"></svg-vue>
                </span>
              </div>
            </td>

            <td
              class="check-column"
              @click="(event: Event) => event.stopPropagation()"
            >
              <label class="sr-only" for=""> Select transaction </label>
              <label class="checkbox">
                <input
                  v-model="store.state.selectedTransactions"
                  :value="trans.id"
                  type="checkbox"
                  class="cursor-pointer"
                />
                <span class="checkmark" />
              </label>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <td colspan="5" class="text-center">Transactions not found</td>
        </tbody>
      </table>
    </div>
    <div class="mt-6">
      <Pagination
        v-if="transactionsData && transactionsData.last_page > 1"
        :data="transactionsData"
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
          <b>{{
            toTitleCase(translatedData['common.common.delete_transaction'])
          }}</b>
        </div>
        <div class="rounded-lg bg-rose p-4">
          <p>
            {{
              deleteTransactionList.type === 'single'
                ? translatedData[
                    'common.common.are_you_sure_you_want_to_delete_this_transaction'
                  ]
                : translatedData[
                    'common.common.are_you_sure_you_want_to_delete_these_transactions'
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
  toRefs,
  reactive,
  onMounted,
  provide,
  computed,
  ref,
  watchEffect,
} from 'vue';
import axios from 'axios';

//components
import Btn from 'Components/ButtonComponent.vue';
import Pagination from 'Components/TablePagination.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';
import FilteringPills from 'Components/FilteringPills.vue';

//composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';
import { useToggle } from '@vueuse/core';
import moment from 'moment';
import { useStore } from 'Store/activities';
import ButtonComponent from 'Components/ButtonComponent.vue';
import PopupModal from 'Components/PopupModal.vue';
import { getTranslatedElement, toTitleCase } from 'Composable/utils';

// toggle state for modal popup
let [deleteValue, deleteToggle] = useToggle();

export default defineComponent({
  name: 'TransactionList',
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
    transactions: {
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
      activityTitle = getActivityTitle(activity.value.title, 'en'),
      activityLink = `/activity/${activityId}`;
    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    const searchTerm = ref('all');
    const currentlySortedBy = ref('');
    const columnDirections = ref({
      type: 'ascending',
      value: 'ascending',
      date: 'ascending',
    });
    const showPills = ref(false);
    const isPaginationReset = ref(false);
    const store = useStore();
    const isAllValueSelected = ref(false);
    const activePage = ref(1);
    const deleteModalShow = ref(false);
    const deleteTransactionList = ref({
      type: '',
      id: 0,
    });
    const resetPill = ref(false);

    interface TransactionInterface {
      last_page: number;
      data: {
        id: number;
        transaction: {
          reference: string;
          transaction_type: {
            transaction_type_code: string;
          }[];
          value: {
            amount: string;
          }[];
          transaction_date: {
            date: Date;
          }[];
        };
        activity_id: number;
      }[];
    }

    interface TitleInterface {
      title: string;
      searchTerm: string;
      count: number;
    }

    const transactionsData = reactive({}) as TransactionInterface;

    const countData = ref({
      all: 0,
      incoming_funds: 0,
      outgoing_commitment: 0,
      disbursement: 0,
      expenditure: 0,
      others: 0,
    });

    const titles = computed<TitleInterface[]>(() => [
      { title: 'All', searchTerm: 'all', count: countData.value.all },
      {
        title: 'Incoming Funds',
        searchTerm: 'incoming_funds',
        count: countData.value.incoming_funds,
      },
      {
        title: 'Outgoing Commitment',
        searchTerm: 'outgoing_commitment',
        count: countData.value.outgoing_commitment,
      },
      {
        title: 'Disbursement',
        searchTerm: 'disbursement',
        count: countData.value.disbursement,
      },
      {
        title: 'Expenditure',
        searchTerm: 'expenditure',
        count: countData.value.expenditure,
      },
      { title: 'Others', searchTerm: 'others', count: countData.value.others },
    ]);

    const fetchTransactions = async (
      activityId: string,
      searchTerm: string,
      order: string | undefined,
      direction: string
    ) => {
      try {
        const params = new URLSearchParams({
          filterBy: searchTerm,
          direction,
          ...(order && { orderBy: order }),
        });
        const response = await axios.get(
          `/activity/${activityId}/transactions/page/${
            activePage.value
          }?${params.toString()}`
        );
        return response.data.data;
      } catch (error) {
        console.error('Error fetching transactions:', error);
        throw error;
      }
    };

    const sortingDirection = (type: string) => {
      columnDirections.value[type] =
        columnDirections.value[type] === 'asc' ? 'desc' : 'asc';
    };

    const sortByOrder = async (order: string, sort?: boolean) => {
      currentlySortedBy.value = order;

      if (sort) {
        sortingDirection(order);
      }

      try {
        const direction = order ? columnDirections.value[order] : 'asc';
        const data = await fetchTransactions(
          activityId,
          searchTerm.value,
          order,
          direction
        );
        Object.assign(transactionsData, data.transactions);
      } catch (error) {
        console.log('Error', error);
      }
    };

    const handleFilter = (filterType: string) => {
      searchTerm.value = filterType;
      activePage.value = 1;
      isPaginationReset.value = true;
      setTimeout(() => {
        isPaginationReset.value = false;
      }, 100);
      sortByOrder(currentlySortedBy.value);
    };

    const toggleSelectAll = (data: { id: number }[]) => {
      const allSelected = data.every((item) =>
        store.state.selectedTransactions.includes(item.id)
      );

      isAllValueSelected.value = !allSelected;

      if (allSelected) {
        store.state.selectedTransactions =
          store.state.selectedTransactions.filter(
            (id) => !data.some((item) => item.id === id)
          );
      } else {
        const newIds = data.map((item) => item.id);
        store.state.selectedTransactions = [
          ...new Set([...store.state.selectedTransactions, ...newIds]),
        ];
      }
    };

    const showToast = (type: boolean, message: string) => {
      toastData.type = type;
      toastData.visibility = true;
      toastData.message = message;
      setTimeout(() => {
        toastData.visibility = false;
      }, 5000);
    };

    const handleApiError = (error) => {
      if (error?.response?.status === 422) {
        showToast(false, error?.response?.data?.errors?.transaction_ids[0]);
      } else {
        showToast(
          false,
          'Failed to delete transactions. Something went wrong.'
        );
      }
    };

    // Final Delete Transaction Logic
    const deleteTransaction = async (
      url: string,
      data?: { transaction_ids?: number[] }
    ) => {
      try {
        const response = await axios.delete(url, data ? { data } : undefined);
        if (response.data.status) {
          showToast(true, response.data.msg);
          getTransactions();
          resetPill.value = true;
          isPaginationReset.value = true;

          setTimeout(() => {
            isPaginationReset.value = false;
            resetPill.value = false;
          }, 100);
        }
      } catch (error) {
        handleApiError(error);
      }
    };

    // Single Transaction Delete Need ID
    const singleDeleteTransaction = async (id: number) => {
      await deleteTransaction(`/activity/${activityId}/transaction/${id}`);
    };

    // Bulk Transaction Delete, Retrieves Selected From Store
    const bulkDeleteTransactions = async () => {
      const { selectedTransactions } = store.state;

      if (selectedTransactions.length > 0) {
        await deleteTransaction(`/activity/${activityId}/transactions`, {
          transaction_ids: selectedTransactions,
        });

        store.state.selectedTransactions = [];
        isAllValueSelected.value = false;

        isPaginationReset.value = true;
        setTimeout(() => {
          isPaginationReset.value = false;
        }, 100);

        getTransactions().then();
      }
    };

    // Initial Delete Function Called To Show Popup
    const initiateDelete = (type: string, id?: number) => {
      deleteTransactionList.value = {
        type,
        id: id ?? 0,
      };
      deleteModalShow.value = true;
    };

    // Delete Confirmation From Popup
    const confirmDelete = () => {
      deleteModalShow.value = false;
      if (
        deleteTransactionList.value.type === 'single' &&
        deleteTransactionList.value.id > 0
      ) {
        singleDeleteTransaction(deleteTransactionList.value.id);
      } else {
        bulkDeleteTransactions();
      }
    };

    const getTransactions = async () => {
      await axios
        .get(`/activity/${activityId}/transactions/page/${activePage.value}`)
        .then((res) => {
          const response = res.data;
          countData.value = response.data.stats;
          Object.assign(transactionsData, response.data.transactions);
        });
    };

    onMounted(async () => {
      getTransactions().then();

      if (props.toast.message !== '') {
        toastData.type = props.toast.type;
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }

      setTimeout(() => {
        toastData.visibility = false;
      }, 5000);
    });

    function handleNavigate(path) {
      window.location.href = path;
    }

    function fetchListings(active_page: number) {
      activePage.value = active_page;
      const params = new URLSearchParams({
        filterBy: searchTerm.value,
        direction: currentlySortedBy.value
          ? columnDirections.value[currentlySortedBy.value]
          : 'asc',
        ...(currentlySortedBy.value && { orderBy: currentlySortedBy.value }),
      });

      axios
        .get(
          `/activity/${activityId}/transactions/page/${active_page}?${params.toString()}`
        )
        .then((res) => {
          const response = res.data;
          Object.assign(transactionsData, response.data.transactions);
        })
        .catch((error) => {
          console.error('Error fetching paginated data:', error);
        });
    }

    /**
     * Breadcrumb data
     */
    const breadcrumbData = [
      {
        title: props.translatedData['common.common.your_activities'],
        link: '/activities',
      },
      {
        title: activityTitle,
        link: activityLink,
      },
      {
        title: props.translatedData['common.common.transaction_list'],
        link: '',
      },
    ];

    provide('parentItemId', activityId);
    provide('translatedData', props.translatedData);

    return {
      breadcrumbData,
      activityLink,
      dateFormat,
      transactionsData,
      getActivityTitle,
      fetchListings,
      toastData,
      deleteValue,
      deleteToggle,
      handleNavigate,
      sortingDirection,
      sortByOrder,
      titles,
      handleFilter,
      currentlySortedBy,
      columnDirections,
      showPills,
      isPaginationReset,
      store,
      toggleSelectAll,
      isAllValueSelected,
      bulkDeleteTransactions,
      singleDeleteTransaction,
      deleteModalShow,
      confirmDelete,
      initiateDelete,
      deleteTransactionList,
      resetPill,
    };
  },
  computed: {
    moment() {
      return moment;
    },
  },
  methods: { toTitleCase, getTranslatedElement },
});
</script>
