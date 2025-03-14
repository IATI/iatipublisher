<template>
  <div class="px-6 py-4 md:px-10">
    <Loader v-if="isLoaderVisible" />
    <div class="my-4 flex justify-between">
      <h4 class="mr-4 text-3xl font-bold xl:text-heading-4">
        {{ translatedData['common.common.users'] }}
      </h4>
      <div class="inline-flex flex-col items-end justify-end gap-2 md:flex-row">
        <Toast
          v-if="
            toastData.visibility &&
            toastData.message &&
            toastData.message !== ''
          "
          :message="toastData.message"
          :type="toastData.type"
        />
        <button
          v-if="usersData['total'] > 0"
          ref="dropdownBtn"
          class="button secondary-btn whitespace-nowrap font-bold"
          @click="downloadAll"
        >
          <svg-vue icon="download-file" />
          {{
            checklist.length === 0
              ? translatedData['common.common.download_all']
              : ''
          }}
        </button>
        <button
          v-if="userRole !== 'general_user'"
          class="primary-btn whitespace-nowrap"
          @click="
            () => {
              emptyFormData();
              setFormError();
              addUserForm = true;
            }
          "
        >
          <svg-vue class="text-base" icon="plus-outlined" />
          {{ getTranslatedAddNewUser(userRole, translatedData) }}
        </button>
      </div>
    </div>

    <div>
      <PopupModal
        :modal-active="addUserForm || editUserForm"
        @close="
          () => {
            addUserForm = false;
            editUserForm = false;
          }
        "
      >
        <div
          class="popup-model"
          @keyup.enter="addUserForm ? createUser() : updateUser()"
        >
          <div class="mb-5 text-2xl font-bold text-bluecoral">
            {{
              addUserForm
                ? getTranslatedAddNewUser(userRole, translatedData)
                : getTranslatedEditUser(userRole, translatedData)
            }}
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div class="col-span-2 flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.full_name'] }}
                <span class="text-crimson-50"> * </span>
              </label>
              <input
                id="full_name"
                v-model="formData.full_name"
                :class="
                  formError['full_name'] ? 'border-crimson-50' : 'border-n-30'
                "
                class="w-full rounded border p-3"
                type="text"
              />
              <span v-if="formError['full_name']" class="error">{{
                formError['full_name'][0]
              }}</span>
            </div>

            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.username'] }}
                <span class="text-crimson-50"> * </span>
              </label>
              <input
                id="username"
                v-model="formData.username"
                :class="
                  formError['username'] ? 'border-crimson-50' : 'border-n-30'
                "
                class="w-full rounded border p-3"
                type="text"
              />
              <span v-if="formError['username']" class="error">{{
                formError['username'][0]
              }}</span>
            </div>
            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.email_address'] }}
                <span class="text-crimson-50"> * </span>
              </label>
              <input
                id="email"
                v-model="formData.email"
                :class="
                  formError['email'] ? 'border-crimson-50' : 'border-n-30'
                "
                class="w-full rounded border p-3"
                type="email"
              />
              <span v-if="formError['email']" class="error">{{
                formError['email'][0]
              }}</span>
            </div>

            <div
              v-if="addUserForm"
              :class="formError['status'] && 'error__multiselect'"
              class="flex flex-col items-start gap-2"
            >
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.status'] }}
                <span class="text-crimson-50"> * </span>
              </label>
              <Multiselect
                id="status"
                v-model="formData.status"
                :options="status"
                :placeholder="translatedData['common.common.select_status']"
                :searchable="true"
              />
              <span v-if="formError['status']" class="error">{{
                formError['status'][0]
              }}</span>
            </div>
            <div
              v-if="userRole === 'admin'"
              :class="formError['role_id'] && 'error__multiselect'"
              class="flex flex-col items-start gap-2"
            >
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.role'] }}
                <span class="text-crimson-50"> * </span></label
              >
              <Multiselect
                id="role"
                v-model="formData.role_id"
                :options="roles"
                :placeholder="translatedData['common.common.select_an_option']"
                :searchable="true"
              />
              <span v-if="formError['role_id']" class="error">{{
                formError['role_id'][0]
              }}</span>
            </div>

            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.new_password'] }}
                <span v-if="!editUserForm" class="text-crimson-50"> * </span>
              </label>
              <input
                id="password"
                v-model="formData.password"
                autocomplete="one-time-code"
                :class="
                  formError['password'] ? 'border-crimson-50' : 'border-n-30'
                "
                :placeholder="
                  translatedData['common.common.enter_new_password']
                "
                class="w-full rounded border border-n-30 p-3"
                type="password"
              />
              <span v-if="formError['password']" class="error">{{
                formError['password'][0]
              }}</span>
            </div>
            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.confirm_password'] }}
                <span v-if="!editUserForm" class="text-crimson-50"> * </span>
              </label>
              <input
                id="password-confirmation"
                v-model="formData.password_confirmation"
                autocomplete="one-time-code"
                :placeholder="translatedData['common.common.confirm_password']"
                :class="
                  formError['password_confirmation']
                    ? 'border-crimson-50'
                    : 'border-n-30'
                "
                class="w-full rounded border border-n-30 p-3"
                type="password"
              />
              <span v-if="formError['password_confirmation']" class="error">{{
                formError['password_confirmation'][0]
              }}</span>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-2">
            <button
              class="secondary-btn font-bold"
              @click="
                () => {
                  addUserForm = false;
                  editUserForm = false;
                }
              "
            >
              {{ translatedData['common.common.cancel'] }}
            </button>
            <button
              class="primary-btn !px-10"
              @click="addUserForm ? createUser() : updateUser()"
            >
              {{ translatedData['common.common.save'] }}
            </button>
          </div>
        </div>
      </PopupModal>
      <PopupModal
        :modal-active="deleteModal"
        @close="
          () => {
            deleteModal = false;
          }
        "
      >
        <div class="title mb-6 flex">
          <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
          <b>
            {{ translatedData['common.common.delete_user'] }}
          </b>
        </div>
        <p
          class="rounded-lg bg-rose p-4"
          v-html="
            getTranslatedDeleteConfirmation(deleteUsername, translatedData)
          "
        ></p>
        <div class="mt-6 flex justify-end space-x-2">
          <button
            class="secondary-btn font-bold"
            @click="
              () => {
                deleteModal = false;
              }
            "
          >
            {{ translatedData['common.common.cancel'] }}
          </button>
          <button class="primary-btn !px-10" @click="deleteUser(deleteId)">
            {{ translatedData['common.common.delete'] }}
          </button>
        </div>
      </PopupModal>

      <PopupModal
        :modal-active="statusModal"
        @close="
          () => {
            statusModal = false;
          }
        "
      >
        <div class="title mb-6 flex">
          <b>
            {{ getTranslatedMakeUser(statusValue, translatedData) }}
          </b>
        </div>
        <p
          class="rounded-lg bg-rose p-4"
          v-html="
            getTranslatedMakeUserConfirmation(statusValue, translatedData)
          "
        ></p>
        <div class="mt-6 flex justify-end space-x-2">
          <button
            class="secondary-btn font-bold"
            @click="
              () => {
                statusModal = false;
              }
            "
          >
            {{ translatedData['common.common.cancel'] }}
          </button>
          <button
            class="primary-btn !px-10"
            @click="toggleUserStatus(statusId)"
          >
            {{ translatedData['common.common.yes'] }}
          </button>
        </div>
      </PopupModal>

      <div class="filters mb-4 flex flex-wrap justify-between gap-2">
        <div class="select filters inline-flex items-center space-x-2">
          <svg-vue class="w-10 text-lg" icon="funnel" />
          <span
            v-if="userRole === 'superadmin' || userRole === 'iati_admin'"
            class="multiselect-label-wrapper"
            :style="generateLabel('organisation')"
            ><Multiselect
              id="organization-filter"
              v-model="filter.organization"
              :options="organizations"
              :placeholder="
                translatedData['common.common.organisation']?.toUpperCase()
              "
              :searchable="true"
              mode="multiple"
              :taggable="true"
              :close-on-select="false"
              :clear-on-select="false"
              :hide-selected="false"
              label="name"
            />
          </span>

          <span
            class="multiselect-label-wrapper"
            :style="generateLabel('role')"
          >
            <Multiselect
              id="role-filter"
              v-model="filter.roles"
              :options="roles"
              :placeholder="translatedData['common.common.role']?.toUpperCase()"
              :searchable="true"
              mode="multiple"
              :close-on-select="false"
              :clear-on-select="false"
              :hide-selected="false" />
            <span v-if="filter.roles.length > 0" class="status"> </span
          ></span>
          <span
            class="multiselect-label-wrapper"
            :style="generateLabel('status')"
            ><Multiselect
              id="status-filter"
              v-model="filter.status"
              :options="status"
              :placeholder="
                translatedData['common.common.status']?.toUpperCase()
              "
              :searchable="true"
            />
          </span>
          <span></span>
        </div>
        <div
          class="flex h-[38px] w-full items-center justify-end gap-3 space-x-2 px-4 2xl:w-auto"
        >
          <span>
            <DateRangeWidget
              :dropdown-range="dropdownRange"
              :first-date="oldestDates"
              :clear-date="clearDate"
              :starting-date="filter.start_date"
              :date-name="dateType"
              :ending-date="filter.end_date"
              @trigger-set-date-range="setDateRangeDate"
              @trigger-set-date-type="setDateType"
              @date-cleared="clearDate = false"
            />
          </span>
          <div class="open-text h-[38px]">
            <svg-vue
              class="absolute left-2 top-1/2 w-10 -translate-y-1/2 text-base"
              icon="magnifying-glass"
            />
            <input
              v-model="filter.q"
              type="text"
              :placeholder="translatedData['common.common.search_for_users']"
            />
          </div>
        </div>
      </div>

      <div
        v-if="isFilterApplied"
        class="mb-4 flex max-w-full flex-wrap items-center gap-2"
      >
        <span class="text-sm font-bold uppercase text-n-40">
          {{ translatedData['common.common.filtered_by'] }}
        </span>

        <span
          v-if="filter.organization.length"
          class="inline-flex flex-wrap gap-2"
        >
          <span
            v-for="(item, index) in filter.organization"
            :key="index"
            class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
          >
            <span class="text-n-40">
              {{ translatedData['common.common.org'] }}:
            </span>
            <span
              class="max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
            >
              {{ textBubbledata(item, 'org') }}
            </span>
            <svg-vue
              class="mx-2 mt-1 cursor-pointer text-xs"
              icon="cross"
              @click="filter.organization.splice(index, 1)"
            />
          </span>
        </span>
        <span v-if="filter.roles.length" class="inline-flex flex-wrap gap-2">
          <span
            v-for="(item, index) in filter.roles"
            :key="index"
            class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
          >
            <span class="text-n-40">
              {{ translatedData['common.common.roles'] }}:
            </span>
            <span>{{ textBubbledata(item, 'roles') }}</span>
            <svg-vue
              class="mx-2 mt-1 cursor-pointer text-xs"
              icon="cross"
              @click="filter.roles.splice(index, 1)"
            />
          </span>
        </span>
        <span v-if="filter.status.length" class="inline-flex flex-wrap gap-2">
          <span
            v-for="(item, index) in filter.status"
            :key="index"
            class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
          >
            <span class="text-n-40">
              {{ translatedData['common.common.status'] }}:
            </span>
            <span>{{ textBubbledata(item, 'status') }}</span>
            <svg-vue
              class="mx-2 mt-1 cursor-pointer text-xs"
              icon="cross"
              @click="
                () => {
                  filter.status = '';
                }
              "
            />
          </span>
        </span>
        <span
          v-if="filter.start_date && filter.end_date"
          class="inline-flex flex-wrap gap-2"
        >
          <span
            class="flex items-center space-x-1 rounded-full border border-n-30 px-2 py-1 text-xs"
          >
            <span class="text-n-40">
              {{ translatedData['common.common.date'] }}:
            </span>
            <span>{{
              textBubbledata(
                filter.selected_date_filter,
                filter.selected_date_filter
              )
            }}</span>
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
        <button
          class="font-bold uppercase text-bluecoral"
          @click="
            () => {
              clearFilter();
            }
          "
        >
          {{ translatedData['common.common.clear_filter'] }}
        </button>
      </div>
      <p class="py-1">
        {{ getTranslatedTotalNumberOfUsers(totalUser, translatedData) }}
      </p>
      <div class="iati-list-table user-list-table text-n-40">
        <table>
          <thead>
            <tr class="bg-n-10">
              <th id="title" scope="col">
                <span class="inline-flex items-center">
                  <span
                    v-if="
                      filter.direction === 'desc' &&
                      filter.orderBy === 'username'
                    "
                  >
                    <svg-vue
                      class="mx-2 h-3 w-2 cursor-pointer"
                      icon="sort-descending"
                      @click="sort('username')"
                    />
                  </span>
                  <span v-else>
                    <svg-vue
                      class="mx-2 h-3 w-2 cursor-pointer"
                      icon="sort-ascending"
                      @click="sort('username')"
                    />
                  </span>

                  <span>{{ translatedData['common.common.users'] }}</span>
                </span>
              </th>
              <th id="measure" scope="col" style="width: 210px">
                <span>{{ translatedData['common.common.email'] }}</span>
              </th>

              <th v-if="isSuperadmin" id="title" scope="col">
                <span class="inline-flex items-center">
                  <span
                    v-if="
                      filter.direction === 'desc' &&
                      filter.orderBy === 'publisher_name'
                    "
                  >
                    <svg-vue
                      class="mx-2 h-3 w-2 cursor-pointer"
                      icon="sort-descending"
                      @click="sort('publisher_name')"
                    />
                  </span>
                  <span v-else>
                    <svg-vue
                      class="mx-2 h-3 w-2 cursor-pointer"
                      icon="sort-ascending"
                      @click="sort('publisher_name')"
                    />
                  </span>

                  <span>{{
                    translatedData['common.common.organisation_name']
                  }}</span>
                </span>
              </th>

              <th id="title" scope="col">
                <span>{{ translatedData['common.common.user_role'] }}</span>
              </th>
              <th>
                <span>{{ translatedData['common.common.status'] }}</span>
              </th>
              <th
                id="aggregation_status"
                class="flex items-center"
                scope="col"
                width="208px"
              >
                <span
                  v-if="
                    filter.direction === 'desc' &&
                    filter.orderBy === 'created_at'
                  "
                  class="inline-flex items-center"
                >
                  <svg-vue
                    class="mx-2 h-3 w-2 cursor-pointer"
                    icon="sort-descending"
                    @click="sort('created_at')"
                  />
                </span>
                <span v-else>
                  <svg-vue
                    class="mx-2 h-3 w-2 cursor-pointer"
                    icon="sort-ascending"
                    @click="sort('last_logged_in')"
                  />
                </span>
                <span class="whitespace-nowrap">
                  {{ translatedData['common.common.last_login'] }}
                </span>
              </th>
              <th
                v-if="userRole !== 'general_user'"
                id="action"
                scope="col"
                width="190px"
              >
                <span>
                  {{ translatedData['common.common.action'] }}
                </span>
              </th>
              <th id="cb" scope="col">
                <span class="cursor-pointer">
                  <svg-vue icon="checkbox" @click="toggleSelectall" />
                </span>
              </th>
            </tr>
          </thead>
          <tbody v-if="usersData?.data.length > 0 || fetchingTableData">
            <tr v-if="fetchingTableData">
              <td colspan="4">
                {{ translatedData['common.common.fetching_data'] }}
              </td>
            </tr>
            <tr v-for="(user, index) in usersData?.data" v-else :key="index">
              <td>
                <div class="ellipsis relative">
                  <p
                    class="w-32 overflow-x-hidden overflow-ellipsis whitespace-nowrap text-sm capitalize text-n-50"
                  >
                    {{ user['full_name'] }}
                  </p>
                </div>
                <div class="ellipsis relative">
                  <p
                    class="w-32 overflow-x-hidden overflow-ellipsis whitespace-nowrap"
                  >
                    {{ user['username'] }}
                  </p>
                </div>
              </td>
              <td class="flex space-x-2">
                <span class="ms-1">
                  <svg-vue
                    class="mt-1 cursor-pointer text-base"
                    :icon="
                      user['email_verified_at']
                        ? 'tick-outline'
                        : 'alert-outline'
                    "
                  />
                </span>
                <span class="... truncate">
                  {{ user['email'] }}
                </span>
              </td>
              <td v-if="isSuperadmin">
                <div class="ellipsis relative">
                  <p
                    class="w-32 overflow-x-hidden overflow-ellipsis whitespace-nowrap"
                  >
                    {{ user['name'] }}
                    {{
                      user['publisher_name'] ? user['publisher_name'] : '- -'
                    }}
                  </p>

                  <div class="w-52">
                    <span class="ellipsis__title--hover"
                      >{{
                        user['publisher_name'] ? user['publisher_name'] : '- -'
                      }}
                    </span>
                  </div>
                </div>
              </td>
              <td class="capitalize">
                {{ roles[user['role_id']] }}
              </td>
              <td :class="user['status'] ? 'text-spring-50' : 'text-n-40'">
                {{
                  user['status']
                    ? translatedData['common.common.active']
                    : translatedData['common.common.inactive']
                }}
              </td>
              <td>
                {{
                  user['last_logged_in']
                    ? formatDate(user['last_logged_in'])
                    : translatedData['common.common.not_available']
                }}
              </td>
              <td
                v-if="userRole !== 'general_user'"
                class="flex h-full items-center space-x-6"
              >
                <p v-if="currentUserId !== user['id']" @click="editUser(user)">
                  <svg-vue
                    class="cursor-pointer text-base"
                    icon="edit-action"
                  />
                </p>
                <!-- <p @click="deleteUser(user['id'])"> -->
                <p
                  v-if="currentUserId !== user['id']"
                  @click="openDeletemodel(user)"
                >
                  <svg-vue class="cursor-pointer text-base" icon="delete" />
                </p>

                <p
                  v-if="currentUserId !== user['id']"
                  @click="openStatusModel(user)"
                >
                  <span
                    :class="user['status'] ? 'bg-spring-50' : 'bg-n-40'"
                    class="relative block h-4 w-7 cursor-pointer rounded-full"
                  >
                    <span
                      :class="
                        user['status'] ? 'translate-x-0' : 'translate-x-full'
                      "
                      class="absolute left-[2px] top-1/2 block h-3 w-3 -translate-y-1/2 rounded-full bg-white duration-200"
                    />
                  </span>
                </p>
              </td>
              <td class="space-2">
                <span
                  v-if="currentUserId !== user['id']"
                  class="relative h-5 w-5"
                  ><input
                    v-model="checklist"
                    class="user-checklist"
                    :value="user['id']"
                    type="checkbox"
                  />
                  <span class="pseudo-checkbox" />
                  <svg-vue class="ticked-svg text-spring-50" icon="ticked" />
                </span>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <td v-if="loader" colspan="5" class="text-center">
              <div colspan="5" class="spin"></div>
            </td>
            <td v-else colspan="8" class="text-center">
              {{ translatedData['common.common.users_not_found'] }}
            </td>
          </tbody>
        </table>
      </div>

      <div class="mt-6">
        <Pagination
          v-if="usersData && usersData['last_page'] > 1"
          :data="usersData"
          @fetch-activities="fetchUsersList"
        />
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import {
  defineProps,
  reactive,
  ref,
  computed,
  watch,
  onMounted,
  provide,
  watchEffect,
} from 'vue';
import Loader from '../../components/Loader.vue';
import Toast from 'Components/ToastMessage.vue';
import axios from 'axios';
import PopupModal from 'Components/PopupModal.vue';
import Multiselect from '@vueform/multiselect';
import moment from 'moment';
import Pagination from 'Components/TablePagination.vue';
import { watchIgnorable } from '@vueuse/core';
import DateRangeWidget from 'Components/DateRangeWidget.vue';
import { generateUsername, kebabCaseToSnakecase } from 'Composable/utils';

const props = defineProps({
  organizations: { type: Object, required: true },
  status: { type: Object, required: true },
  roles: { type: Object, required: true },
  currentUserId: { type: Object, required: true },
  userRole: { type: String, required: true },
  oldestDates: { type: String, required: true },
  translatedData: { type: Object, required: true },
});

const toastData = reactive({
  visibility: false,
  message: '',
  type: false,
});

const filter = reactive({
  organization: [],
  roles: [],
  status: '',
  orderBy: '',
  direction: '',
  q: '',
  start_date: '',
  end_date: '',
  date_type: 'created_at',
  selected_date_filter: '',
});

const isLoaderVisible = ref(false);
const addUserForm = ref(false);
const editUserForm = ref(false);
const usersData = reactive({ data: [] });
const isEmpty = ref(true);
const allSelected = ref<boolean[]>([]);
const deleteModal = ref(false);
const deleteId = ref();
const totalUser = ref(0);
const statusId = ref();
const statusModal = ref(false);
const statusValue = ref();
const statusUsername = ref();
const deleteUsername = ref();
const loader = ref(true);
const fetchingTableData = ref(false);
const selectedIds = ref({});
const checklist = ref([]);
const currentpageData = ref([]);
const clearDate = ref(false);
const editUserId = ref('');

const dateType = ref('');

watchEffect(() => {
  dateType.value = props.translatedData?.['common.common.all_time'] || '';
});

const isSuperadmin = ref(false);
isSuperadmin.value =
  props.userRole === 'superadmin' || props.userRole === 'iati_admin';

const dropdownRange = ref({
  created_at: props.translatedData['common.common.user_created_date'],
  last_logged_in: props.translatedData['common.common.last_login_date'],
});

const formData = reactive({
  username: '',
  full_name: '',
  email: '',
  status: '1',
  role_id: '',
  password: '',
  password_confirmation: '',
});

const formError = reactive({
  username: '',
  full_name: '',
  email: '',
  status: '',
  role_id: '',
  password: '',
  password_confirmation: '',
});

const isFilterApplied = computed(() => {
  return (
    filter.organization.length + filter.roles.length != 0 ||
    filter.status != '' ||
    (filter.start_date && filter.end_date)
  );
});

const { ignoreUpdates } = watchIgnorable(toastData, () => undefined, {
  flush: 'sync',
});

watch(
  () => formData.full_name,
  (fullname) => {
    formData.username = generateUsername(fullname);
  }
);

watch(
  () => toastData.visibility,
  () => {
    setTimeout(() => {
      toastData.visibility = false;
      ignoreToastUpdate();
    }, 2000);
  }
);

const ignoreToastUpdate = () => {
  ignoreUpdates(() => {
    toastData.message = '';
  });
};

const clearDateFilter = () => {
  filter.selected_date_filter = '';
  clearDateRangeFilter();
};

onMounted(async () => {
  dropdownRange.value.created_at =
    props.translatedData['common.common.user_created_date'];
  dropdownRange.value.last_logged_in =
    props.translatedData['common.common.last_login_date'];
  dateType.value = props.translatedData['common.common.all_time'];

  let filterParams = getFilterParamsFromPreviousPage();
  if (filterParams) {
    for (let i = 0; i < filterParams.length; i++) {
      let key = kebabCaseToSnakecase(filterParams[i][0]);
      let value = filterParams[i][1];
      if (['roles', 'organization'].includes(key)) {
        filter[key].push(value);
      } else if (key === 'date_type') {
        dateType.value = value.split('-').join(' ');
      } else {
        filter[key] = value;
      }
    }
  }
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

onMounted(async () => {
  axios.get(`/users/page/1`).then((res) => {
    const response = res.data;
    for (let i = 0; i < response.data.data.length; i++) {
      response.data.data[i].role = response.data.data[i].role
        .split('_')
        .join(' ');
    }
    Object.assign(usersData, response.data);
    isEmpty.value = response.data.data.length ? false : true;
    loader.value = false;
  });

  setTimeout(() => {
    toastData.visibility = false;
  }, 5000);
});

const textBubbledata = (id, field) => {
  switch (field) {
    case 'org':
      return props.organizations[+id];
    case 'roles':
      return props.roles[+id];
    case 'status':
      return props.status[+id];
    default:
      return field;
  }
};

const clearFilter = () => {
  filter.organization = [];
  filter.roles = [];
  filter.status = '';
  filter.direction = '';
  filter.orderBy = '';
  filter.q = '';
  filter.selected_date_filter = '';
  clearDateRangeFilter();
};

const clearDateRangeFilter = () => {
  clearDate.value = true;
};

const setDateRangeDate = (startDate, endDate, selectedDate) => {
  filter.start_date = startDate;
  filter.end_date = endDate;
  filter.selected_date_filter = selectedDate;
};
const setDateType = (dateType) => {
  filter.date_type = dateType;
};

const createUser = () => {
  isLoaderVisible.value = true;
  let passwordData = {
    password: formData.password,
    password_confirmation: formData.password_confirmation,
  };

  axios
    .post('/user', { ...formData, ...passwordData })
    .then((res) => {
      toastData.visibility = true;
      toastData.message = res.data.message;
      toastData.type = res.data.success;
      setFormError();
      setFormError(res.data.errors);

      if (res.data.success) {
        clearFilter();
        fetchUsersList(usersData['current_page'], true);
        addUserForm.value = false;
        emptyFormData();
        setFormError();
      }
    })
    .catch((error) => {
      toastData.visibility = true;
      toastData.message = error.data.message;
      toastData.type = false;
      addUserForm.value = false;
    })
    .finally(() => {
      isLoaderVisible.value = false;
    });
};

const editUser = (user) => {
  formData.username = user.username;
  formData.full_name = user.full_name;
  formData.email = user.email;
  formData.role_id = user.role_id;
  editUserId.value = user.id;
  editUserForm.value = true;
};

const emptyFormData = () => {
  for (const key in formData) {
    formData[key] = key === 'status' ? 1 : '';
  }
};
const setFormError = (errors = {}) => {
  if (Object.keys(errors).length) {
    for (const key in errors) {
      formError[key] = errors[key];
    }
  } else {
    for (const key in formError) {
      formError[key] = '';
    }
  }
};

const openStatusModel = (user) => {
  statusId.value = user.id;
  statusValue.value = user.status;
  statusModal.value = true;
  statusUsername.value = user.username;
};

const updateUser = () => {
  isLoaderVisible.value = true;
  let passwordData = {
    password: formData.password,
    password_confirmation: formData.password_confirmation,
  };

  axios
    .patch(`/user/${editUserId.value}`, { ...formData, ...passwordData })
    .then((res) => {
      toastData.visibility = true;
      toastData.message = res.data.message;
      toastData.type = res.data.success;
      isLoaderVisible.value = false;
      setFormError();
      setFormError(res.data.errors);

      if (res.data.success) {
        editUserForm.value = false;
        fetchUsersList(usersData['current_page']);
        editUserId.value = '';
        emptyFormData();
        setFormError();
        window.scrollTo(0, 0);
      }
    })
    .catch((error) => {
      editUserId.value = '';
      toastData.visibility = true;
      toastData.message = error.data.message;
      toastData.type = false;
      isLoaderVisible.value = false;
    })
    .finally(() => {
      isLoaderVisible.value = false;
    });
};

watch(
  () => [
    filter.organization,
    filter.roles,
    filter.q,
    filter.status,
    filter.start_date,
    filter.end_date,
    filter.date_type,
  ],
  () => {
    fetchUsersList(usersData['current_page'], true);
  },
  { deep: true }
);

function fetchUsersList(active_page: number, filtered = false) {
  let route = `/users/page/${filtered ? '1' : active_page}`;
  fetchingTableData.value = true;
  let params = new URLSearchParams();

  for (const filter_key in filter) {
    if (filter[filter_key]) {
      if (filter[filter_key].length > 0) {
        params.append(filter_key, filter[filter_key]);
      }
    }
  }

  axios
    .get(route, { params: params })
    .then((res) => {
      const response = res.data;
      Object.assign(usersData, response.data);
      isEmpty.value = response.data ? false : true;
      totalUser.value = response.data.total;
    })
    .finally(() => {
      fetchingTableData.value = false;
    });
}

const openDeletemodel = (user) => {
  deleteModal.value = true;
  deleteId.value = user.id;
  deleteUsername.value = user.username;
};

function deleteUser(id: number) {
  deleteModal.value = false;
  window.scrollTo(0, 0);

  axios.delete(`/user/${id}`).then((res) => {
    if (res.data.message) {
      toastData.visibility = true;
      toastData.message = res.data.message;
      toastData.type = res.data.success;
    }

    if (res.data.success) {
      fetchUsersList(usersData['current_page']);
    }
  });
}

const sort = (param) => {
  filter.direction =
    filter.direction === 'asc' && filter.orderBy === param ? 'desc' : 'asc';
  filter.orderBy = param;

  fetchUsersList(1);
};

function toggleUserStatus(id: number) {
  window.scrollTo(0, 0);
  isLoaderVisible.value = true;
  statusModal.value = false;

  axios
    .patch(`/user/status/${id}`)
    .then((res) => {
      if (res.status) {
        toastData.visibility = true;
        toastData.message = res.data.message;
        toastData.type = res.data.success;

        fetchUsersList(usersData['current_page']);
      }
    })
    .finally(() => {
      isLoaderVisible.value = false;
    });
}

function formatDate(date: Date) {
  return moment(date).format('LL');
}

const toggleSelectall = () => {
  currentpageData.value = usersData.data.map((value) => {
    return value['id'];
  });
  for (let i = 0; i < usersData.data.length; i++) {
    if (!checklist.value.includes(usersData.data[i]['id']))
      checklist.value[checklist.value.length + i] = usersData.data[i]['id'];
  }
  selectedIds.value[usersData['current_page']] = checklist.value;
  if (allSelected.value[usersData['current_page']]) {
    checklist.value = checklist.value.filter(
      (n) => !Object.values(currentpageData.value).includes(n)
    );
  }
  checklist.value = checklist.value.filter(function (el) {
    return el != null;
  });
  allSelected.value[usersData['current_page']] =
    !allSelected.value[usersData['current_page']];
};
watch(
  () => checklist.value,
  () => {
    selectedIds.value[usersData['current_page']] = [];

    currentpageData.value = usersData.data.map((value) => {
      return value['id'];
    });
    for (let i = 0; i < checklist.value.length; i++) {
      if (currentpageData.value.includes(checklist.value[i])) {
        selectedIds.value[usersData['current_page']][i] = checklist.value[i];
      }
    }
    selectedIds.value[usersData['current_page']] = selectedIds.value[
      usersData['current_page']
    ].filter(function (el) {
      return el != null;
    });
  }
);

const generateLabel = (label) => {
  return { '--label': `'${label}'` };
};

const downloadAll = () => {
  let route = `/users/download/`;
  let params = new URLSearchParams();
  let allPageSelected;
  allPageSelected = Object.values(selectedIds.value).flat();

  if (checklist.value.length == 0) {
    for (const filter_key in filter) {
      if (filter[filter_key].length > 0) {
        params.append(filter_key, filter[filter_key]);
      }
    }
  } else {
    params.append('users', allPageSelected);
  }

  axios.get(route, { params: params }).then((res) => {
    const response = res.data;
    let blob = new Blob([response], {
      type: 'application/csv',
    });
    let link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = res.headers['content-disposition'].split('=')[1];
    link.click();
  });
};

/**
 * Returns the text : Add a new User  || Add a new IATI Admin
 *
 * @param userRole
 * @param transData
 *
 */
function getTranslatedAddNewUser(userRole: string, transData): string {
  const role =
    userRole === 'admin'
      ? transData['common.common.user']
      : transData['common.common.iati_admin'];

  return transData['common.common.add_a_new_user_role']?.replace(
    ':userRole',
    role
  );
}

/**
 * Returns the text : Edit User || Edit IATI Admin
 *
 * @param userRole
 * @param transData
 */
function getTranslatedEditUser(userRole: string, transData): string {
  const role =
    userRole === 'admin'
      ? transData['common.common.user']
      : transData['common.common.iati_admin'];

  return transData['common.common.edit'] + ' ' + role;
}

/**
 * Returns the text : Are you sure you want to delete xyz ?
 *
 * @param deleteUsername
 * @param transData
 */
function getTranslatedDeleteConfirmation(
  deleteUsername: string,
  transData
): string {
  return transData['common.common.are_you_sure_you_want_to_delete']?.replace(
    ':deleteUsername',
    deleteUsername
  );
}

/**
 * Returns the text : Make user active || Make user inactive
 *
 * @param statusValue
 * @param transData
 */
function getTranslatedMakeUser(statusValue: string, transData): string {
  return transData['common.common.make_user']?.replace(
    ':statusValue',
    statusValue
      ? transData['common.common.active']
      : transData['common.common.inactive']
  );
}

/**
 * Returns the text :
 * Are you sure you want to make user active ?
 * Are you sure you want to make user inactive ?
 *
 * @param statusValue
 * @param transData
 */
function getTranslatedMakeUserConfirmation(
  statusValue: string,
  transData
): string {
  return transData['common.common.make_user_confirmation']?.replace(
    ':statusValue',
    statusValue
      ? transData['common.common.active']
      : transData['common.common.inactive']
  );
}

/**
 * Returns the text :
 *
 * Total Number of Users: 1
 *
 * @param totalCount
 * @param transData
 */
function getTranslatedTotalNumberOfUsers(
  totalCount: number,
  transData
): string {
  return transData['common.common.total_number_of_users']?.replace(
    ':totalUser',
    totalCount
  );
}

provide('translatedData', props.translatedData);
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
