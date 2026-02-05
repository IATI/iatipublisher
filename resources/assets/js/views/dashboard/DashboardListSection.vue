<template>
  <div
    class="mt-6 w-full bg-white px-6 py-6"
    :class="{ '!px-14': currentView !== 'user' }"
  >
    <div v-if="currentView === 'user'">
      <h6 class="text-xs uppercase text-n-40">users by organisation</h6>
      <div class="w-full overflow-x-scroll">
        <table class="mb-8 mt-2 w-full overflow-x-scroll text-left">
          <thead class="bg-n-10 text-xs font-bold uppercase text-n-40">
            <tr>
              <th>
                <div
                  class="flex min-w-[400px] items-center space-x-2 px-8 py-3"
                >
                  <button class="p-1" @click="toggleSort('organisation')">
                    <svg-vue
                      v-if="
                        filter.sort === 'asc' &&
                        filter.orderBy === 'organisation'
                      "
                      class="text-sm"
                      icon="ascending-arrow"
                    ></svg-vue>
                    <svg-vue
                      v-else
                      class="text-sm"
                      icon="descending-arrow"
                    ></svg-vue>
                  </button>
                  <span>Organisation</span>
                </div>
              </th>
              <th>
                <div class="flex items-center space-x-2 px-8 py-3">
                  <button class="p-1" @click="toggleSort('admin')">
                    <svg-vue
                      v-if="filter.sort === 'asc' && filter.orderBy === 'admin'"
                      class="text-sm"
                      icon="ascending-arrow"
                    ></svg-vue>
                    <svg-vue
                      v-else
                      class="text-sm"
                      icon="descending-arrow"
                    ></svg-vue>
                  </button>
                  <span>admin</span>
                </div>
              </th>
              <th>
                <div class="flex items-center space-x-2 px-8 py-3">
                  <button class="p-1" @click="toggleSort('active')">
                    <svg-vue
                      v-if="
                        filter.sort === 'asc' && filter.orderBy === 'active'
                      "
                      class="text-sm"
                      icon="ascending-arrow"
                    ></svg-vue>
                    <svg-vue
                      v-else
                      class="text-sm"
                      icon="descending-arrow"
                    ></svg-vue>
                  </button>
                  <span>active</span>
                </div>
              </th>
              <th>
                <div class="flex items-center space-x-2 px-8 py-3">
                  <button class="p-1" @click="toggleSort('deactivated')">
                    <svg-vue
                      v-if="
                        filter.sort === 'asc' &&
                        filter.orderBy === 'deactivated'
                      "
                      class="text-sm"
                      icon="ascending-arrow"
                    ></svg-vue>
                    <svg-vue
                      v-else
                      class="text-sm"
                      icon="descending-arrow"
                    ></svg-vue>
                  </button>
                  <span>deactivated</span>
                </div>
              </th>
              <th>
                <div class="flex items-center space-x-2 px-8 py-3">
                  <button class="p-1" @click="toggleSort('total')">
                    <svg-vue
                      v-if="filter.sort === 'asc' && filter.orderBy === 'total'"
                      class="text-sm"
                      icon="ascending-arrow"
                    ></svg-vue>
                    <svg-vue
                      v-else
                      class="text-sm"
                      icon="descending-arrow"
                    ></svg-vue></button
                  ><span>total </span>
                </div>
              </th>
            </tr>
          </thead>
          <!-- change this code -->
          <tbody v-if="showTableLoader">
            <TableLoaderComponent :row-count="4" :col-count="6" />
          </tbody>
          <tbody v-else-if="tableData.length === 0">
            <tr class="w-full">
              <div class="p-10 text-center text-n-50">No data found</div>
            </tr>
          </tbody>
          <tbody v-else>
            <tr
              v-for="organisation in tableData.data"
              :key="organisation?.id"
              class="border-b border-n-20 text-sm text-bluecoral"
            >
              <td>
                <a
                  class="... block cursor-pointer truncate px-8 py-3"
                  @click="
                    NavigateWithFilter(
                      'users',
                      'organization',
                      organisation.organization_id
                    )
                  "
                  >{{ truncateText(organisation.organisation, 50) }}</a
                >
              </td>
              <td>
                <p class="block px-8 py-3 text-center">
                  {{ organisation.admin_user_count }}
                </p>
              </td>

              <td>
                <p class="block px-8 py-3 text-center">
                  {{ organisation.active_user_count }}
                </p>
              </td>

              <td>
                <p class="block px-8 py-3 text-center">
                  {{ organisation.deactivated_user_count }}
                </p>
              </td>

              <td>
                <p class="block px-8 py-3 text-center">
                  {{ organisation.admin_user_count }}
                </p>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination
        v-if="tableData.last_page > 1"
        :data="tableData"
        @fetch-activities="(page) => triggerpagination(page)"
      />

      <p class="mt-10 text-xs italic text-n-40">
        This widget is not affected by the date range
      </p>
    </div>

    <div v-else>
      <div class="flex">
        <div class="border-r border-n-20">
          <h6
            v-if="currentView === 'activity'"
            class="text-xs uppercase text-n-40"
          >
            activity data
          </h6>
          <h6 v-else class="text-xs uppercase text-n-40">
            Publisher segregated by
          </h6>
          <ul class="mr-6 mt-4 min-h-[300px]">
            <li
              v-for="item in currentNavList"
              :key="item.label"
              class="w-[270px] cursor-pointer border-b border-n-30 py-2 text-sm text-n-50"
              @click="
                () => {
                  currentpage = 1;

                  fetchTableData(item);
                  currentItem = item;
                  resetpagination = true;
                }
              "
            >
              <div
                class="px-3 py-4"
                :class="activeClass === item?.label ? 'activeNav' : ''"
              >
                {{ item?.label }}
              </div>
            </li>
          </ul>
        </div>
        <div class="w-full px-4">
          <table class="w-full">
            <thead
              v-if="
                currentView === 'activity' && title === 'Activity Completion'
              "
              class="bg-n-10 text-xs font-bold uppercase text-n-40"
            >
              <tr>
                <th class="inline-flex items-center space-x-1">
                  <div class="flex space-x-1">
                    <button
                      class="p-1"
                      @click="toggleSort(sortElement.apiParams)"
                    >
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' &&
                          filter.orderBy === sortElement.apiParams
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>
                    <span class="py-3 pr-4 text-left">{{ title }}</span>
                  </div>
                </th>
                <th class="navlist-width mx-8 my-3">
                  <div class="flex space-x-1">
                    <button class="inline p-1" @click="toggleSort('published')">
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' &&
                          filter.orderBy === 'published'
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>
                    <span class="py-3 pr-4 text-right">published</span>
                  </div>
                </th>
                <td class="navlist-width mx-8 my-3">
                  <div class="flex space-x-1">
                    <button class="p-1" @click="toggleSort('draft')">
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' && filter.orderBy === 'draft'
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>
                    <div class="py-3 pr-4 text-right">draft</div>
                  </div>
                </td>
                <td class="navlist-width mx-8 my-3">
                  <div class="flex space-x-1">
                    <button class="p-1" @click="toggleSort('total')">
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' && filter.orderBy === 'total'
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>
                    <div class="py-3 pr-4 text-right">total</div>
                  </div>
                </td>
              </tr>
            </thead>
            <thead v-else class="bg-n-10 text-xs font-bold uppercase text-n-40">
              <tr>
                <th>
                  <div class="flex items-center space-x-2 px-4 py-3 text-left">
                    <button
                      v-if="
                        title !== 'Setup Completeness' &&
                        title !== 'Registration Type'
                      "
                      class="p-1"
                      @click="toggleSort(sortElement.apiParams)"
                    >
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' &&
                          filter.orderBy === sortElement.apiParams
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>

                    <span>{{ title }} </span>
                  </div>
                </th>
                <td class="navlist-width mx-8 my-3">
                  <div
                    class="flex items-center justify-end space-x-2 px-4 py-3 text-right"
                  >
                    <button
                      v-if="
                        title !== 'Setup Completeness' &&
                        title !== 'Registration Type'
                      "
                      class="p-1"
                      @click="toggleSort('count')"
                    >
                      <svg-vue
                        v-if="
                          filter.sort === 'asc' && filter.orderBy === 'count'
                        "
                        class="text-sm"
                        icon="ascending-arrow"
                      ></svg-vue>
                      <svg-vue
                        v-else
                        class="text-sm"
                        icon="descending-arrow"
                      ></svg-vue>
                    </button>
                    <span>total</span>
                  </div>
                </td>
              </tr>
            </thead>
            <tbody v-if="showTableLoader">
              <TableLoaderComponent :row-count="4" :col-count="2" />
            </tbody>
            <tbody
              v-else-if="showNoDataComponent"
              class="text-center shadow-md"
            >
              <div class="p-10">No data found</div>
            </tbody>
            <tbody
              v-else-if="
                title === 'Setup Completeness' &&
                currentView === 'publisher' &&
                Object.keys(completeNess).length
              "
            >
              <tr class="border-b border-n-20">
                <td class="text-sm text-bluecoral">
                  <a
                    class="cursor-pointer px-4 py-3 text-left"
                    @click="
                      NavigateWithFilter(
                        'list-organisations',
                        'completeness',
                        'Default_values_completed'
                      )
                    "
                  >
                    Publishers with complete default value
                  </a>
                </td>
                <td class="text-semi-dark text-sm">
                  <div class="px-4 py-3 text-right">
                    {{ completeNess?.completeSetup?.count }}
                  </div>
                </td>
              </tr>
              <tr>
                <td class="text-sm text-bluecoral">
                  <a
                    class="cursor-pointer px-4 py-3 text-left"
                    @click="
                      NavigateWithFilter(
                        'list-organisations',
                        'completeness',
                        'Default_values_not_completed'
                      )
                    "
                  >
                    Publishers with incomplete default value
                  </a>
                </td>
                <td class="text-semi-dark text-sm">
                  <div class="px-4 py-3 text-right">
                    {{ completeNess?.incompleteSetup?.count }}
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody
              v-else-if="
                title === 'Setup Completeness' &&
                currentView === 'publisher' &&
                !Object.keys(completeNess).length
              "
              class="text-center shadow-md"
            >
              <div class="p-10 text-center">No data found</div>
            </tbody>

            <tbody
              v-else-if="
                registrationType &&
                title === 'Registration Type' &&
                !registrationType.length &&
                currentView === 'publisher'
              "
              class="text-center shadow-md"
            >
              <div class="p-10 text-center">No data found</div>
            </tbody>
            <tbody
              v-else-if="
                title === 'Registration Type' &&
                registrationType.length &&
                currentView === 'publisher'
              "
            >
              <tr
                v-for="item in registrationType"
                :key="item?.id"
                class="border-b border-n-20"
              >
                <td class="text-sm text-bluecoral">
                  <a
                    class="cursor-pointer py-3 pl-8 text-left"
                    @click="
                      NavigateWithFilter(
                        'list-organisations',
                        'registration-type',
                        item?.registration_type
                      )
                    "
                  >
                    {{
                      item?.registration_type === 'new_org'
                        ? 'New Organisation'
                        : 'Existing Organisation'
                    }}
                  </a>
                </td>
                <td class="text-semi-dark text-sm">
                  <div class="px-4 py-3 text-right">
                    {{ item.count }}
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody
              v-else-if="
                title !== 'Setup Completeness' && currentView === 'publisher'
              "
            >
              <tr
                v-for="item in tableData.data"
                :key="item?.id"
                class="border-b border-n-20"
              >
                <td class="text-sm text-bluecoral">
                  <a
                    class="cursor-pointer px-4 py-3 text-left capitalize"
                    @click="
                      NavigateWithFilter(
                        'list-organisations',
                        currentItem?.apiParams,
                        item.id
                      )
                    "
                  >
                    <!-- {{ item?.label.replace(/_/g, ' ') }} -->
                    {{ item['label'] }}
                  </a>
                </td>
                <td class="text-semi-dark text-sm">
                  <div class="px-4 py-3 text-right">{{ item?.total }}</div>
                </td>
              </tr>
            </tbody>
            <tbody
              v-else-if="
                currentView === 'activity' && title !== 'Activity Completion'
              "
            >
              <tr
                v-for="(item, index) in tableData"
                :key="item?.id"
                class="border-b border-n-20"
              >
                <td class="text-sm text-bluecoral">
                  <div class="px-4 py-3 text-left">
                    {{ index }}
                  </div>
                </td>
                <td class="text-semi-dark text-center text-sm">
                  <div class="px-4 py-3">{{ item }}</div>
                </td>
              </tr>
            </tbody>
            <tbody
              v-else-if="
                currentView === 'activity' && title === 'Activity Completion'
              "
            >
              <tr
                v-for="(item, index) in tableData"
                :key="item?.id"
                class="border-b border-n-20"
              >
                <td class="text-sm text-bluecoral">
                  <div class="px-4 py-3 text-left">
                    {{ index }}
                  </div>
                </td>
                <td class="text-semi-dark text-center text-sm">
                  <div class="px-4 py-3">
                    {{ Number(item?.published ?? 0) }}
                  </div>
                </td>
                <td class="text-semi-dark text-center text-sm">
                  <div class="px-4 py-3">{{ Number(item?.draft ?? 0) }}</div>
                </td>
                <td class="text-semi-dark text-center text-sm">
                  <div class="px-4 py-3">
                    {{
                      Number(item?.published ?? 0) + Number(item?.draft ?? 0)
                    }}
                  </div>
                </td>
              </tr>
            </tbody>
            <tbody v-else class="text-center shadow-md">
              <div class="p-10">No data found</div>
            </tbody>
          </table>
          <Pagination
            v-if="
              title !== 'Setup Completeness' &&
              title !== 'Registration Type' &&
              tableData.paginatedData?.last_page > 1 &&
              currentView === 'publisher'
            "
            class="mt-4"
            :reset="resetpagination"
            :data="tableData.paginatedData"
            @fetch-activities="(page) => triggerpagination(page)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
import { ref, defineProps, watch, onMounted, inject, Ref, computed } from 'vue';
import { defineEmits } from 'vue';
import Pagination from 'Components/TablePagination.vue';
import { truncateText } from 'Composable/utils';
import TableLoaderComponent from 'Components/TableLoaderComponent.vue';

const props = defineProps({
  currentView: {
    type: String,
    required: true,
  },
  tableData: {
    type: [Object],
    required: true,
  },
  tableHeader: {
    type: String,
    required: true,
  },
  startDate: {
    type: String,
    required: true,
  },
  endDate: {
    type: String,
    required: true,
  },
  dateType: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['tableNav']);

const activityNavList = [
  { label: 'Activity Status', apiParams: 'status' },
  { label: 'Activity Added', apiParams: 'method' },
  { label: 'Activity Completion', apiParams: 'completeness' },
];
const publisherNavList = [
  { label: 'Organisation Type', apiParams: 'publisher-type' },
  { label: 'Data Licence', apiParams: 'data-license' },
  { label: 'Country', apiParams: 'country' },
  { label: 'Registration Type', apiParams: 'registration-type' },
  { label: 'Setup Completeness', apiParams: 'setup' },
];
const currentpage = ref(1);
const resetpagination = ref(false);
const filter = ref({ orderBy: '', sort: '' });
const sortElement = ref({ label: '', apiParams: '' });
const userNavlist = [{ label: 'user', apiParams: '' }];
const currentItem = ref({
  label: 'Organisation Type',
  apiParams: 'publisher-type',
});
const currentNavList = ref(publisherNavList);
const title = ref(currentNavList.value[0]?.label);
onMounted(() => {
  fetchTableData(currentNavList.value[0]);
});
const sortTable = () => {
  fetchTableData(currentItem.value, false);
};
const triggerpagination = (page) => {
  currentpage.value = page;
  resetpagination.value = false;
  fetchTableData(currentItem.value, false);
};

watch(
  () => filter.value,
  () => {
    resetpagination.value = true;
    currentpage.value = 1;
  },
  { deep: true }
);

watch(
  () => props.currentView,
  (value) => {
    currentpage.value = 1;
    if (value === 'activity') {
      currentItem.value = { label: 'Activity Status', apiParams: 'status' };

      currentNavList.value = activityNavList;
    } else if (value === 'publisher') {
      currentItem.value = {
        label: 'Organisation Type',
        apiParams: 'publisher-type',
      };

      currentNavList.value = publisherNavList;
    } else {
      currentNavList.value = userNavlist;
      currentItem.value = {
        label: 'user',
        apiParams: '',
      };
    }

    fetchTableData(currentNavList.value[0]);

    activeClass.value = currentNavList.value[0]?.label;
    title.value = currentNavList.value[0]?.label;
  }
);

const showNoDataComponent = computed(() => {
  return (
    props.tableData.length === 0 ||
    (!(
      title.value === 'Registration Type' ||
      title.value === 'Setup Completeness'
    ) &&
      props.tableData?.data?.length === 0)
  );
});

const activeClass = ref(currentNavList.value[0]?.label);

const NavigateWithFilter = (page, key, value) => {
  if (!!props.startDate && !!props.endDate) {
    window.location.href = `/${page}?${key}=${value}`;
    return;
  }
  window.location.href = `/${page}?${key}=${value}`;
};

const fetchTableData = (item, tabChange = true) => {
  activeClass.value = item?.label;
  title.value = item?.label;
  sortElement.value = item;
  emit('tableNav', item, filter, currentpage.value, tabChange);
  resetpagination.value = false;
};

const toggleSort = (order) => {
  filter.value.sort === 'asc'
    ? (filter.value.sort = 'desc')
    : (filter.value.sort = 'asc');
  filter.value.orderBy = order;
  sortTable();
};

const completeNess = inject('completeNess') as Ref;
const registrationType = inject('registrationType') as Ref;
const showTableLoader = inject('showTableLoader') as Ref;
</script>
<style lang="scss">
.activeNav {
  @apply relative  rounded bg-bluecoral text-white;
}

.navlist-width {
  width: 100px;
}

.text-semi-dark {
  color: #2a2f30 !important;
}
</style>
