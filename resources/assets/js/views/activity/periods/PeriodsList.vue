<template>
  <div class="relative bg-paper px-5 pb-[71px] pt-4 xl:px-10">
    <PageTitle
      :breadcrumb-data="breadcrumbData"
      title="Period List"
      :back-link="indicatorLink"
    >
      <div class="flex items-center space-x-3">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <a :href="`${periodLink}/create`">
          <Btn text="Add Period" icon="plus" type="primary" />
        </a>
      </div>
    </PageTitle>

    <div class="iati-list-table text-n-40">
      <table>
        <thead>
          <tr class="bg-n-10 text-left">
            <th id="transaction_type" scope="col">
              <span>Start Date - End Date</span>
            </th>
            <th id="code" scope="col" width="190px">
              <span>Period number</span>
            </th>
            <th id="action" scope="col" width="177px">
              <span>Action</span>
            </th>
          </tr>
        </thead>
        <tbody v-if="periodsData.data && periodsData.data.length > 0">
          <tr
            v-for="(pe, p) in periodsData.data"
            :key="p"
            class="cursor-pointer"
            @click="handleNavigate(`${periodLink}/${pe.id}`)"
          >
            <td>
              <a
                class="period-list text-sm font-bold leading-relaxed text-n-50"
                :href="`${periodLink}/${pe.id}`"
              >
                {{
                  pe.period.period_start[0].date
                    ? dateFormat(pe.period.period_start[0].date)
                    : 'Missing'
                }}
                -
                {{
                  pe.period.period_end[0].date
                    ? dateFormat(pe.period.period_end[0].date)
                    : 'Missing'
                }}
              </a>
            </td>
            <td>{{ pe.period_code }}</td>
            <td>
              <div class="flex">
                <a class="mr-6 text-n-40" :href="`${periodLink}/${pe.id}/edit`">
                  <svg-vue icon="edit" class="text-xl"></svg-vue>
                </a>
                <DeleteAction item-type="period" :item-id="pe.id" />
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else>
          <td colspan="5" class="text-center">Periods not found</td>
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      <Pagination
        v-if="periodsData && periodsData.last_page > 1"
        :data="periodsData"
        @fetch-activities="fetchListings"
      />
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  toRefs,
  onMounted,
  ref,
  reactive,
  provide,
} from 'vue';
import axios from 'axios';
// components
import Btn from 'Components/ButtonComponent.vue';
import Pagination from 'Components/TablePagination.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';
import DeleteAction from 'Components/sections/DeleteAction.vue';

// composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';

export default defineComponent({
  name: 'PeriodList',
  components: {
    Btn,
    Pagination,
    PageTitle,
    Toast,
    DeleteAction,
  },
  props: {
    activity: {
      type: Object,
      required: true,
    },
    parentData: {
      type: Object,
      required: true,
    },
    period: {
      type: Array,
      required: true,
    },
    toast: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const { activity, parentData } = toRefs(props);
    const activityId = activity.value.id,
      activityTitle = activity.value.title,
      activityLink = `/activity/${activityId}`,
      resultTitle = parentData.value.result.title,
      resultId = parentData.value.result.id,
      resultLink = `${activityLink}/result/${resultId}`,
      indicatorTitle = parentData.value.indicator.title,
      indicatorId = parentData.value.indicator.id,
      indicatorLink = `/result/${resultId}/indicator/${indicatorId}`,
      periodLink = `/indicator/${indicatorId}/period`;

    interface PeriodInterface {
      last_page: number;
      data: {
        result_id: number;
        id: number;
        period_code: string;
        period: {
          period_start: {
            date: Date;
          }[];
          period_end: {
            date: Date;
          }[];
        };
        activity_id: number;
      }[];
    }

    const periodsData = reactive({}) as PeriodInterface;
    const isEmpty = ref(false);

    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });

    /**
     * Breadcrumb data
     */
    const breadcrumbData = [
      {
        title: 'Your Activities',
        link: '/activity',
      },
      {
        title: getActivityTitle(activityTitle, 'en'),
        link: activityLink,
      },
      {
        title: 'Result List',
        link: `/activity/${activityId}/result`,
      },
      {
        title: getActivityTitle(resultTitle, 'en'),
        link: resultLink,
      },
      {
        title: 'Indicator List',
        link: `/result/${resultId}/indicator`,
      },
      {
        title: getActivityTitle(indicatorTitle, 'en'),
        link: indicatorLink,
      },
      {
        title: 'Period List',
        link: '',
      },
    ];

    onMounted(async () => {
      axios.get(`/indicator/${indicatorId}/periods/page/1`).then((res) => {
        const response = res.data;
        Object.assign(periodsData, response.data);
        isEmpty.value = response.data.data.length ? false : true;
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

    function fetchListings(active_page: number) {
      axios
        .get(`/indicator/${indicatorId}/periods/page/` + active_page)
        .then((res) => {
          const response = res.data;
          Object.assign(periodsData, response.data);
          isEmpty.value = response.data ? false : true;
        });
    }
    function handleNavigate(path) {
      window.location.href = path;
    }

    // provide
    provide('parentItemId', indicatorId);

    return {
      breadcrumbData,
      indicatorLink,
      periodLink,
      dateFormat,
      periodsData,
      getActivityTitle,
      fetchListings,
      indicatorId,
      toastData,
      handleNavigate,
    };
  },
});
</script>
