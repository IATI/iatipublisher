<template>
  <section class="flex flex-col gap-6 lg:flex-row">
    <div v-if="showStatsLoader" class="min-w-[450px] rounded bg-white p-4">
      <div v-for="n in 3" :key="n" class="my-8">
        <div class="my-5">
          <ShimmerLoading class="mx-auto !w-[200px] !rounded-sm" />
        </div>
        <ShimmerLoading class="mx-auto my-4 !w-[380px] !rounded-sm" />
      </div>
      <ShimmerLoading class="mx-auto my-4 !w-[380px] !rounded-sm" />
    </div>
    <div v-else class="min-w-[450px] rounded bg-white p-4">
      <div v-if="currentView !== 'user'">
        <div class="border-b border-n-20 pb-4">
          <div class="flex items-center justify-between">
            <span
              v-if="currentView === 'publisher'"
              class="text-xs uppercase text-n-40"
              >Total No. of Publisher Registration in IATI</span
            >
            <span v-else class="text-xs uppercase text-n-40"
              >Total No. of Activities in IATI</span
            >
          </div>
          <p class="my-1 text-2xl text-bluecoral">
            <!-- total count -->
            {{ total }}
          </p>
        </div>
        <div class="border-b border-n-20 py-4">
          <div class="flex items-center justify-between">
            <span
              v-if="currentView === 'publisher'"
              class="text-xs uppercase text-n-40"
              >Last registered publisher</span
            >
            <span v-else class="text-xs uppercase text-n-40">
              Last Publisher with Activity Update
            </span>
          </div>
          <div>
            <button
              v-if="currentView === 'publisher'"
              class="mb-1 mt-2 text-2xl text-bluecoral"
              @click="proxyUser()"
            >
              <!-- latest registered -->
              {{
                truncateText(
                  lastRegistered?.name
                    ? lastRegistered?.name[0].narrative
                    : lastRegistered?.publisher_name
                    ? lastRegistered?.publisher_name
                    : getTranslatedUntitled(translatedData),
                  30
                )
              }}
            </button>
            <button
              v-else
              class="mb-1 mt-2 text-2xl text-bluecoral"
              @click="proxyUser()"
            >
              <!-- latest registered-->

              {{
                truncateText(
                  lastUpdatedPublisher?.name
                    ? lastUpdatedPublisher?.name[0].narrative
                    : lastUpdatedPublisher?.publisher_name
                    ? lastUpdatedPublisher?.publisher_name
                    : getTranslatedUntitled(translatedData),
                  30
                )
              }}
            </button>
            <div
              v-if="currentView === 'publisher'"
              class="my-1 text-xs italic text-n-40"
            >
              <!-- latest registered date -->
              Registered On:
              {{ formatDate(lastRegistered?.created_at) }}
            </div>
            <div v-else class="my-1 text-xs italic text-n-40">
              <!-- latest registered date -->
              Last updated on:
              {{ formatDate(lastUpdatedActivity?.updated_at) }}
            </div>
          </div>
        </div>
        <div class="border-b border-n-20 py-4">
          <div class="flex items-center justify-between space-x-5">
            <span
              v-if="currentView === 'publisher'"
              class="text-xs uppercase text-n-40"
              >No. of Publishers Inactive (not logged in) since 6 Months in
              IATI</span
            >
            <span v-else class="text-xs uppercase text-n-40">
              Total No. of Publishers with No Activity in IATI
            </span>
          </div>
          <p
            v-if="currentView === 'publisher'"
            class="my-1 text-2xl text-bluecoral"
          >
            <!-- total count -->
            {{ inactivePublisher }}
          </p>
          <div v-else class="my-1 text-2xl text-bluecoral">
            <!-- total count -->
            {{ publisherWithoutActivity }}
          </div>
        </div>
      </div>
      <div v-else>
        <div class="mb-2 flex items-center space-x-2.5">
          <span class="text-sm text-bluecoral"
            >Different users in IATI Publishers</span
          >
        </div>
        <table class="w-full">
          <thead>
            <tr class="bg-n-10">
              <td class="px-6 py-4 text-xs font-bold uppercase text-n-40">
                users
              </td>
              <td class="py-4 text-xs font-bold uppercase text-n-40">active</td>
              <td class="py-4 text-xs font-bold uppercase text-n-40">
                disabled
              </td>
              <td class="py-4 text-xs font-bold uppercase text-n-40">total</td>
            </tr>
          </thead>
          <tbody v-if="showPublisherStats">
            <tr
              v-for="(value, key) in publisherStats"
              :key="key"
              class="border-b border-n-20"
            >
              <td class="px-6 py-4 text-sm text-bluecoral">
                <a :href="`/users?roles=${value.roleId}`">{{
                  value.display
                }}</a>
              </td>
              <td class="px-6 py-4 text-sm text-n-50">
                {{ value.active }}
              </td>
              <td class="px-6 py-4 text-sm text-n-50">
                {{ value.disabled }}
              </td>
              <td class="px-6 py-4 text-sm text-n-50">
                {{ value.active + value.disabled }}
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="i in 3" :key="i">
              <td class="px-6 py-2.5">
                <ShimmerLoading class="!rounded-sm" />
              </td>
              <td class="px-6 py-2.5">
                <ShimmerLoading class="!rounded-sm" />
              </td>
              <td class="px-6 py-2.5">
                <ShimmerLoading class="!rounded-sm" />
              </td>
              <td class="px-6 py-2.5">
                <ShimmerLoading class="!rounded-sm" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p class="mt-24 text-xs italic text-n-40">
        This widget is not affected by the date range
      </p>
    </div>
    <div class="flex w-full flex-col justify-between rounded bg-white p-4">
      <div v-if="currentView === 'user'">
        <div class="flex space-x-2.5 px-2 text-xs uppercase text-n-40">
          <p>Total Number of Users</p>
        </div>
        <ShimmerLoading
          v-if="showGraphLoader"
          class="mx-auto !mt-3 !h-10 !w-[100px] !rounded-sm"
        />

        <div v-else class="my-1 px-2 text-3xl text-bluecoral">
          {{ graphTotal }}
        </div>
      </div>
      <div v-else>
        <div class="flex space-x-2.5 px-2 text-xs uppercase text-n-40">
          <p v-if="currentView === 'publisher'">
            Total No. of Publisher Registration
          </p>
          <p v-else>Total No. of Activities Added</p>
        </div>
        <ShimmerLoading
          v-if="showGraphLoader"
          class="mx-auto !mt-3 !h-10 !w-[100px] !rounded-sm"
        />

        <div v-else class="my-1 px-2 text-3xl text-bluecoral">
          {{ graphTotal }}
        </div>
      </div>
      <DashboardGraph :current-view="currentView" />
    </div>
    <Loader
      v-if="loader.status"
      :text="loader.text"
      :translated-data="translatedData"
      :class="{ 'animate-loader': loader }"
    />
  </section>
</template>
<script lang="ts" setup>
import { ref, defineProps, onMounted, watch, inject, Ref } from 'vue';
import DashboardGraph from './DashboardGraph.vue';

import axios from 'axios';
import moment from 'moment';
import Loader from 'Components/sections/ProgressLoader.vue';
import { getTranslatedUntitled, truncateText } from '../../composable/utils';
import ShimmerLoading from 'Components/ShimmerLoading.vue';
interface PublisherStat {
  active: number;
  disabled: number;
  display: boolean;
  roleId: string;
}
const props = defineProps({
  currentView: {
    type: String,
    required: true,
  },
});
const total = ref();
const inactivePublisher = ref();
const publisherWithoutActivity = ref();
const lastRegistered = ref();
const lastUpdatedPublisher = ref();
const lastUpdatedActivity = ref();
const loader = ref({ status: false, text: '' });
const showStatsLoader = ref(false);
const graphTotal = inject('graphTotal') as Ref;
const publisherStats = ref<PublisherStat[]>([]);
const showPublisherStats = ref(true);
const showGraphLoader = inject('showGraphLoader') as Ref;
const userId = ref();

onMounted(() => {
  fetchStatsData();
});

const formatDate = (date) => {
  return moment(date).format('MMMM DD, YYYY');
};

const proxyUser = () => {
  loader.value.status = true;
  loader.value.text = 'Proxy Login';
  const endpoint = `/proxy-organisation/${userId.value}`;

  axios.get(endpoint).then((res) => {
    const response = res.data;

    if (response.success === true) {
      setTimeout(() => {
        window.location.replace('/activities');
      }, 1000);
    } else {
      loader.value.status = false;
    }
  });
};

watch(
  () => props.currentView,
  () => {
    fetchStatsData();
  }
);
const fetchStatsData = () => {
  showStatsLoader.value = true;
  axios
    .get(`/dashboard/${props.currentView}/stats`)
    .then((res) => {
      const response = res.data;
      total.value = response.data.totalCount;
      lastRegistered.value = response.data.lastRegisteredPublisher;
      lastUpdatedPublisher.value = response.data.lastUpdatedPublisher;
      lastUpdatedActivity.value = response.data.lastUpdatedActivity;

      if (props.currentView === 'publisher') {
        userId.value = lastRegistered.value.user_id;
        inactivePublisher.value = response.data.inActivePublisher;
      }
      if (props.currentView === 'activity') {
        userId.value = response.data.userId;
        publisherWithoutActivity.value = response.data.publisherWithoutActivity;
      }
      if (props.currentView === 'user') {
        showPublisherStats.value = true;
        publisherStats.value = response.data;
      }
    })
    .finally(() => {
      showStatsLoader.value = false;
    });
};
</script>
<style></style>
