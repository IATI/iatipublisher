<template>
  <div class="mx-auto w-screen max-w-[1400px] px-6 py-8">
    <div class="mb-3 flex flex-wrap justify-between border-b border-n-20 pb-3">
      <div class="flex gap-x-2">
        <button
          :class="
            currentView === 'publisher'
              ? ' !border-turquoise bg-n-10 text-bluecoral'
              : ''
          "
          class="flex w-[140px] justify-center rounded border border-n-20 py-2 text-sm text-n-40"
          @click="currentView = 'publisher'"
        >
          <span> Publisher</span>
        </button>
        <button
          :class="
            currentView === 'activity'
              ? ' !border-turquoise bg-n-10 text-bluecoral'
              : ''
          "
          class="flex w-[140px] justify-center rounded border border-n-20 py-2 text-sm text-n-40"
          @click="currentView = 'activity'"
        >
          <span>Activity</span>
        </button>
        <button
          :class="
            currentView === 'user'
              ? ' !border-turquoise bg-n-10 text-bluecoral'
              : ''
          "
          class="flex w-[140px] justify-center rounded border border-n-20 py-2 text-sm text-n-40"
          @click="currentView = 'user'"
        >
          <span>Users</span>
        </button>
      </div>
      <div class="flex w-full items-center justify-end space-x-2 xl:w-auto">
        <DateRangeWidget
          :date-label="DateLabel"
          :first-date="oldestDates[currentView]"
          :current-view="currentView"
          :date-name="'All time'"
          @trigger-set-date-range="setDateRangeDate"
        />
        <ButtonComponent
          text="Download report"
          type="secondary"
          icon="download-file"
          @click="downloadReport"
        />
      </div>
    </div>

    <DashboardStatsSection :current-view="currentView" />
    <DashboardListSection
      :current-view="currentView"
      :table-data="tableData"
      :table-header="currentNav['label']"
      :start-date="startDate"
      :end-date="endDate"
      :date-type="dateType"
      @table-nav="
        (n, filter, page, tabChange) =>
          handleChangeTableNav(n, filter, page, tabChange)
      "
    />
  </div>
</template>

<script setup lang="ts">
import DashboardStatsSection from './DashboardStatsSection.vue';
import DashboardListSection from './DashboardListSection.vue';
import DateRangeWidget from 'Components/DateRangeWidget.vue';
import { ref, onMounted, provide, watch, defineProps } from 'vue';
import ButtonComponent from 'Components/ButtonComponent.vue';
import axios from 'axios';
import moment from 'moment';
import { kebabCaseToSnakecase } from 'Composable/utils';
interface tableDaypeteType {
  data?: object;
  codeList?: object;
  paginatedData?: object;
}

const currentNav = ref({
  label: 'Organisation Type',
  apiParams: 'publisher-type',
});
const tableData = ref<tableDaypeteType>({});
const DateLabel = ref('Registered date:');
const startDate = ref('');
const endDate = ref('');
const graphAmount = ref<object[]>([]);
const graphTotal = ref(0);
const showTableLoader = ref(false);
const showGraphLoader = ref(false);
const dateType = ref('');

const dateLabel = {
  publisher: 'Registered date:',
  activity: 'Activity Added on:',
  user: 'User Created Date:',
};

const currentView = ref('publisher');
const completeNess = ref();
const registrationType = ref();

const handleChangeTableNav = (item, filter, page, tabChange = true) => {
  if (tabChange) {
    filter.value.orderBy = '';
    filter.value.sort = '';
  }

  currentNav.value = item;
  fetchTableData(filter.value, page);
};

onMounted(() => {
  setDateRangeDate('', '');
  fetchTableData();
  fetchGraphData();
});

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = defineProps({
  oldestDates: {
    type: Object,
    required: true,
  },
  translatedData: {
    type: Object,
    required: true,
  },
});

const downloadReport = () => {
  let params = new URLSearchParams();
  if (startDate.value && endDate.value) {
    params.append('start_date', startDate.value);
    params.append('end_date', endDate.value);
  }

  axios
    .get(`/dashboard/${currentView.value}/download`, { params: params })
    .then((res) => {
      const response = res.data;
      let blob = new Blob([response], {
        type: 'application/csv',
      });
      let link = document.createElement('a');
      link.href = window.URL.createObjectURL(blob);
      link.download = `${currentView.value}-report.csv`;
      link.click();
    });
};

const graphDataFormatter = (graphData) => {
  for (let date in graphData) {
    const data = {
      x: moment(date).format('MMM DD YYYY'),
      y: graphData[date],
    };
    graphAmount.value.push(data);
  }
};

const fetchGraphData = () => {
  showGraphLoader.value = true;
  let params = new URLSearchParams();
  params.append('start_date', startDate.value);
  params.append('end_date', endDate.value);

  axios
    .get(`/dashboard/${currentView.value}/count/`, { params: params })
    .then((res) => {
      graphAmount.value.length = 0;
      graphTotal.value = res.data.data['count'];
      graphDataFormatter(res.data.data['graph']);
    })
    .finally(() => {
      showGraphLoader.value = false;
    });
};

const setDateRangeDate = (start, end, type = '') => {
  startDate.value = '';
  dateType.value = type;

  if (start != '1990-12-31') {
    startDate.value = start;
  }

  endDate.value = end;
  if (currentView.value !== 'user') {
    fetchTableData();
  }
  fetchGraphData();
};

watch(
  () => currentView.value,
  () => {
    DateLabel.value = dateLabel[currentView.value] ?? currentView.value;
    startDate.value = '';
    endDate.value = '';
    fetchGraphData();
  }
);

const fetchTableData = (filter = { orderBy: '', sort: '' }, page = '1') => {
  showTableLoader.value = true;
  let params = new URLSearchParams();
  const activeTab = currentNav.value['apiParams'];

  if (filter.orderBy) {
    params.append('orderBy', kebabCaseToSnakecase(filter.orderBy));
  }
  params.append('page', page);
  if (filter.sort) {
    params.append('direction', filter.sort);
  }

  if (startDate.value && endDate.value && currentNav.value.label !== 'user') {
    params.append('start_date', startDate.value);
    params.append('end_date', endDate.value);
  }

  const apiUrl = `/dashboard/${currentView.value}/${activeTab}`;

  axios
    .get(apiUrl, { params: params })
    .then((res) => {
      let response = res.data;

      if (currentView.value === 'publisher') {
        if (activeTab !== 'setup' && activeTab !== 'registration-type') {
          tableData.value = {};
          let tempData: Array<object> = [];
          const codeList = response.data?.codeList;
          const objectLength = response.data?.paginatedData.data.length ?? 0;

          for (let i = 0; i < objectLength; i++) {
            const itemInPaginatedData = response.data?.paginatedData.data[i];
            const publisherTypeKey =
              itemInPaginatedData[kebabCaseToSnakecase(activeTab)];
            tempData.push({
              label: codeList[publisherTypeKey],
              id: publisherTypeKey,
              total: itemInPaginatedData.count,
            });
          }

          tableData.value = response.data;
          tableData.value.data = tempData;
        } else if (activeTab === 'registration-type') {
          registrationType.value = response.data.data;
        } else {
          completeNess.value = response.data;
        }
      }

      if (currentView.value === 'user' || currentView.value === 'activity') {
        tableData.value = response.data;
      }
    })
    .finally(() => {
      showTableLoader.value = false;
    });
};
provide('completeNess', completeNess);
provide('registrationType', registrationType);
provide('graphAmount', graphAmount);
provide('graphTotal', graphTotal);
provide('showTableLoader', showTableLoader);
provide('showGraphLoader', showGraphLoader);
provide('currentView', currentView);
provide('translatedData', props.translatedData);
</script>
