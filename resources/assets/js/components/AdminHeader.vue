<template>
  <header
    :class="isLoading && 'hidden'"
    class="activity__header flex min-h-[60px] max-w-full justify-between gap-5 bg-bluecoral px-5 text-xs leading-normal text-white sm:gap-10 xl:px-10"
  >
    <Toast
      v-if="toastVisibility"
      class="toast -bottom-24"
      :message="toastMessage"
      :type="toastType"
    />
    <Toast
      v-if="errorToastVisibility"
      class="toast-error"
      :message="errorToastMessage"
      :type="errorToastType"
    />
    <div class="flex items-center gap-5">
      <div
        class="hamburger-menu"
        @click="
          () => {
            showSidebar = !showSidebar;
          }
        "
      >
        <div id="hamburger" class="hamburger scale-75">
          <span class="bg-n-20" />
          <span class="bg-n-20" />
          <span class="bg-n-20" />
        </div>
      </div>
      <figure class="flex grow-0 items-center">
        <a :href="superAdmin ? '/list-organisations' : '/activities'">
          <svg-vue icon="logo" class="text-4xl" />
        </a>
      </figure>
      <div
        id="activity-menu-overlay"
        @click="
          () => {
            showSidebar = !showSidebar;
          }
        "
      ></div>
    </div>
    <div id="nav-list" class="activity-nav-menu flex w-full justify-between">
      <!-- commented to temporarily hide language buttons , remove width later -->
      <nav class="justify-end">
        <ul class="flex">
          <li
            v-for="(language, index) in data.languages"
            :key="index"
            :class="data.languageNavLiClasses"
          >
            <button
              type="button"
              :class="[
                language.language.toLowerCase() == currentLanguage.toLowerCase()
                  ? 'nav__pointer'
                  : '',
                data.languageNavAnchorClasses,
              ]"
              @click="changeLanguage(language.language.toLowerCase())"
            >
              <span>{{ language.language }}</span>
            </button>
          </li>
        </ul>
      </nav>
      <nav>
        <ul class="activity-nav-list -mx-4">
          <li
            v-for="(menu, index) in data[
              superAdmin ? 'superadmin_menus' : 'org_menus'
            ]"
            :key="index"
            :class="data.menuNavLiClasses"
          >
            <a
              v-if="menu.identifier !== 'add-import-activity'"
              :class="[
                { nav__pointer: menu.active },
                data.menuNavAnchorClasses,
              ]"
              :href="menu.permalink"
            >
              <span class="">{{ menu.name }}</span>
            </a>
            <span
              v-if="menu.identifier === 'add-import-activity'"
              :class="[
                { nav__pointer: menu.active },
                data.menuNavAnchorClasses,
              ]"
            >
              <span class="add-import"
                >{{ menu.name }}
                <div
                  v-if="menu.identifier === 'add-import-activity'"
                  style="visibility: hidden"
                  class="button__dropdown add-import-dropdown absolute top-full z-50 w-56 -translate-y-3 bg-white p-2 text-left shadow-dropdown duration-300"
                >
                  <ul class="flex-col">
                    <li>
                      <a
                        id="header-add-activity-manually"
                        class="cursor-pointer"
                        :class="liClass"
                        @click="modalValue = true"
                        >{{
                          translatedData['common.common.add_activity_manually']
                        }}</a
                      >
                    </li>
                    <li>
                      <a
                        id="header-import-activity"
                        href="/import"
                        :class="liClass"
                        >{{
                          translatedData[
                            'common.common.import_activities_from_csv_xml'
                          ]
                        }}</a
                      >
                    </li>
                    <li>
                      <a
                        id="header-import-xls"
                        href="/import/xls"
                        :class="liClass"
                        >{{
                          translatedData[
                            'common.common.import_activities_from_xls'
                          ]
                        }}</a
                      >
                    </li>
                  </ul>
                </div>
              </span>
            </span>

            <div
              v-if="menu.name === 'Add / Import Activities'"
              class="button__dropdown invisible absolute left-4 top-full z-10 w-56 -translate-y-3 bg-white p-2 text-left opacity-0 shadow-dropdown outline transition-all duration-300 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100"
            >
              <ul class="flex-col">
                <li>
                  <a :class="liClass" @click="modalValue = true">{{
                    translatedData['common.common.add_activity_manually']
                  }}</a>
                </li>
                <li>
                  <a href="/import" :class="liClass">{{
                    translatedData[
                      'common.common.import_activities_from_csv_xml'
                    ]
                  }}</a>
                </li>
                <li>
                  <a id="header-import-xls" href="/import/xls" :class="liClass">
                    {{
                      translatedData['common.common.import_activities_from_xls']
                    }}
                  </a>
                </li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>
    </div>
    <div
      class="user-nav"
      :class="{ 'grow-0': !superAdmin, 'grow justify-end': superAdmin }"
    >
      <div class="user-nav">
        <div class="search">
          <input
            v-if="!superAdmin"
            v-model="searchValue"
            class="search__input mr-3.5"
            type="text"
            :placeholder="
              translatedData['workflow_frontend.import.search_activity']
            "
            @keyup.enter="searchFunction('/activities')"
          />
          <input
            v-else
            v-model="searchValue"
            class="search__input mr-3.5"
            type="text"
            placeholder="Search organisation..."
            @keyup.enter="searchFunction('/list-organisations')"
          />
          <svg-vue icon="search" />
          <span v-if="spinner" class="spinner" />
        </div>
        <button
          :class="
            isTouchDevice
              ? 'button secondary-btn--touch'
              : 'button secondary-btn dropdown-btn'
          "
          @click="showUserDropdown = !showUserDropdown"
        >
          <svg-vue icon="user-profile" />
          <svg-vue class="dropdown__arrow" icon="dropdown-arrow" />
          <div
            v-if="!isTouchDevice"
            :class="{ 'mt-14': hasAdminBar == 1 }"
            class="profile__dropdown"
          >
            <ul>
              <li class="border-b border-b-n-20">
                <div>
                  <svg-vue class="user-profile" icon="user-profile" />
                </div>
                <div class="flex flex-col break-all capitalize leading-4">
                  <span class="text-n-50">
                    {{ user.full_name }}
                  </span>
                  <span class="outine text-tiny text-n-40">
                    {{ organization?.publisher_name }}
                  </span>
                </div>
              </li>
              <li
                class="dropdown__list border-b border-b-n-20"
                @click="redirectProfile"
              >
                <a class="flex w-full space-x-4" href="/profile"
                  ><svg-vue class="mx-1 text-base" icon="user" />
                  <span>{{
                    translatedData['adminHeader.admin_header.your_profile']
                  }}</span></a
                >
              </li>
              <li
                v-if="!superAdmin"
                class="dropdown__list border-b border-b-n-20"
                @click="getStarted"
              >
                <a class="flex w-full space-x-4">
                  <svg-vue icon="rocket-icon" class="mx-1 mt-0.5 scale-[1.2]" />
                  <span>{{
                    translatedData['common.common.get_started']
                  }}</span></a
                >
              </li>
              <li class="dropdown__list flex" @click="logout">
                <svg-vue class="ml-1 mr-3" icon="logout"></svg-vue>
                <button class="text-sm">
                  {{ translatedData['adminHeader.admin_header.logout'] }}
                </button>
              </li>
            </ul>
          </div>
          <div
            v-else
            :class="
              !showUserDropdown
                ? 'invisible -translate-y-2 opacity-0'
                : 'opacity-1 visible translate-y-0'
            "
            class="profile__dropdown--touch"
          >
            <ul>
              <li class="border-b border-b-n-20">
                <div>
                  <svg-vue class="user-profile" icon="user-profile" />
                </div>
                <div class="flex flex-col break-all capitalize leading-4">
                  <span class="text-n-50">
                    {{ user.full_name }}
                  </span>
                  <span class="text-tiny text-n-40">
                    {{ organization?.publisher_name }}
                  </span>
                </div>
              </li>
              <li
                class="dropdown__list border-b border-b-n-20"
                @click="redirectProfile"
              >
                <a class="flex w-full space-x-4" href="/profile"
                  ><svg-vue class="mx-1 text-base" icon="user" />
                  <span>{{
                    translatedData['adminHeader.admin_header.your_profile']
                  }}</span></a
                >
              </li>
              <li
                v-if="!superAdmin"
                class="dropdown__list border-b border-b-n-20"
                @click="getStarted"
              >
                <a class="flex w-full space-x-4">
                  <svg-vue icon="rocket-icon" class="mx-1 mt-0.5 scale-[1.2]" />
                  <span>{{
                    translatedData['common.common.get_started']
                  }}</span></a
                >
              </li>
              <li class="dropdown__list flex" @click="logout">
                <button class="text-sm">
                  {{ translatedData['adminHeader.admin_header.logout'] }}
                </button>
              </li>
            </ul>
          </div>
        </button>
      </div>
    </div>

    <CreateModal
      v-if="!superAdmin"
      :modal-active="modalValue"
      @close="ToggleModel"
      @close-modal="ToggleModel"
      @toast="toast"
    />
  </header>
</template>

<script setup lang="ts">
import {
  defineProps,
  ref,
  watch,
  reactive,
  onMounted,
  computed,
  onUnmounted,
  provide,
  Ref,
} from 'vue';
import { detailStore } from 'Store/activities/show';
import axios from 'axios';
import { useToggle, useStorage } from '@vueuse/core';
import CreateModal from '../views/activity/CreateModal.vue';
import Toast from './ToastMessage.vue';
import LanguageService from 'Services/language';
import Multiselect from '@vueform/multiselect';

const store = detailStore();

const props = defineProps({
  user: { type: Object, required: true },
  onboarding: { type: Object, required: true },
  organization: {
    type: Object,
    validator: (v: unknown) =>
      typeof v === 'object' || typeof v === 'string' || v === null,
    required: false,
    default() {
      return {};
    },
  },
  superAdmin: { type: Boolean, required: true },
  hasAdminBar: { type: Number || Boolean, default: false },
  defaultLanguage: { type: String, default: '' },
  translatedData: { type: Object, required: true },
  currentLanguage: { type: String, required: true },
});

const showUserDropdown = ref(false);

const toastVisibility = ref(false);
const isLoading = ref(false);

const showSidebar = ref(false);
const toastMessage = ref('');
const toastType = ref(false);

const errorToastVisibility = ref(false);
const errorToastMessage = ref('');
const errorToastType = ref(false);
// const translatedData = ref({});

const data = reactive({
  languageNavLiClasses: 'flex',
  languageNavAnchorClasses:
    'flex text-white items-center uppercase nav__pointer-hover px-1.5',
  menuNavLiClasses: 'flex px-4 relative',
  menuNavAnchorClasses:
    'flex text-white items-center uppercase nav__pointer-hover',
  languages: [
    {
      language: 'EN',
      permalink: '#',
      active: props.currentLanguage == 'en',
    },
    {
      language: 'FR',
      permalink: '#',
      active: props.currentLanguage == 'fr',
    },
    {
      language: 'ES',
      permalink: '#',
      active: props.currentLanguage == 'es',
    },
  ],

  org_menus: [
    {
      name: props.translatedData['adminHeader.admin_header.activity_data'],
      identifier: 'activity-data',
      permalink: '/activities',
      active: true,
    },
    {
      name: props.translatedData['adminHeader.admin_header.organisation_data'],
      identifier: 'organisation-data',
      permalink: '/organisation',
      active: false,
    },
    {
      name: props.translatedData['common.common.settings'],
      identifier: 'settings',
      permalink: '/setting',
      active: false,
    },
    {
      name: props.translatedData[
        'adminHeader.admin_header.add_import_activity'
      ],
      identifier: 'add-import-activity',
      permalink: '#',
      active: false,
    },
    {
      name: props.translatedData['common.common.users'],
      identifier: 'users',
      permalink: '/users',
      active: false,
    },
  ],
  superadmin_menus: [
    {
      name: 'Dashboard',
      identifier: 'dashboard',
      permalink: '/dashboard',
      active: false,
    },
    {
      name: 'Organisation List',
      identifier: 'organisation-list',
      permalink: '/list-organisations',
      active: false,
    },

    {
      name: props.translatedData['common.common.users'],
      identifier: 'users',
      permalink: '/users',
      active: false,
    },
  ],
});

const changeLanguage = (lang: string) => {
  LanguageService.changeLanguage(lang)
    .then(() => {
      window.location.reload();
    })
    .catch((error) => {
      console.log(error);
    });
};

watch(
  () => store.state.isLoading,
  (value) => {
    isLoading.value = value;
  }
);

const liClass =
  'block p-2.5 text-n-40 text-tiny uppercase leading-[1.5] font-bold hover:!text-n-50 hover:bg-n-10';
const [modalValue, modalToggle] = useToggle();
function toast(message: string, type: boolean) {
  toastVisibility.value = true;
  setTimeout(() => (toastVisibility.value = false), 15000);
  toastMessage.value = message;
  toastType.value = type;
}
const isTouchDevice = computed(() => {
  return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
});

function ToggleModel() {
  modalToggle();
  window.localStorage.removeItem('openAddModel');
}

watch(
  () => showSidebar.value,
  (sidebar) => {
    if (sidebar) {
      document.documentElement.style.overflow = 'hidden';
    } else document.documentElement.style.overflow = 'auto';
  }
);

function changeActiveMenu() {
  const path = window.location.pathname;
  data.org_menus.forEach((menu, key) => {
    data.org_menus[key]['active'] = menu.permalink === path ? true : false;
  });
  if (
    path.includes('activity') ||
    path.includes('result') ||
    path.includes('indicator')
  ) {
    data.org_menus[0]['active'] = true;
  }
  if (path.includes('organisation')) {
    data.org_menus[1]['active'] = true;
  }
  if (path.includes('import')) {
    data.org_menus[3]['active'] = true;
  }
  if (path.includes('dashboard')) {
    data.superadmin_menus[0]['active'] = true;
  }
  if (path.includes('users')) {
    data.org_menus[4]['active'] = true;
    data.superadmin_menus[2]['active'] = true;
  }
  if (path.includes('list-organisations')) {
    data.superadmin_menus[1]['active'] = true;
  }
  if (
    path.includes('system-version') ||
    path.includes('log-viewer') ||
    path.includes('link3') ||
    path.includes('link4')
  ) {
    data.superadmin_menus[2]['active'] = true;
  }
}

// local storage for publishing
const pa = useStorage('vue-use-local-storage', {
  publishingActivities: localStorage.getItem('publishingActivities') ?? {},
});

async function logout() {
  pa.value.publishingActivities = {};
  await axios.post('/logout').then((res) => {
    if (res.status) {
      sessionStorage.removeItem('isModelCloseClicked');
      window.location.href = '/';
    }
  });
}
/**
 * Search functionality
 *
 */
const searchValue: Ref<string | null> = ref('');
const currentURL = window.location.href;

if (currentURL.includes('?')) {
  const queryString = window.location.search,
    urlParams = new URLSearchParams(queryString),
    search = urlParams.get('q');
  searchValue.value = search;
}

const spinner = ref(false);

const searchFunction = (url: string) => {
  spinner.value = true;
  const param = searchValue.value?.replace('#', '');
  let sortingParam = '';
  if (currentURL.includes('?') && currentURL.includes('&')) {
    const queryString = window.location.search;
    let queryStringArr = queryString.split('&') as [];
    sortingParam = '&' + queryStringArr.slice(1).join('&');
  }
  let href = param
    ? `${url}?q=${param}${sortingParam}`
    : props.superAdmin
    ? '/list-organisations'
    : '/activities/';
  window.location.href = href;
};

const getStarted = async () => {
  const isModelCloseClicked = useStorage(
    'isModelCloseClicked',
    false,
    sessionStorage
  );

  try {
    await axios.post('/organisation-onboarding/toggle-dont-show/', {
      value: false,
    });
    isModelCloseClicked.value = false;
    sessionStorage.setItem('isForceOpenModal', 'true');
    window.location.href = '/activities';
  } catch {
    errorToastVisibility.value = true;
    setTimeout(() => (errorToastVisibility.value = false), 5000);
    errorToastMessage.value = 'Something went wrong. Please try again later.';
    errorToastType.value = false;
  }
};

onMounted(() => {
  changeActiveMenu();
  if (
    localStorage.getItem('openAddModel') === 'true' &&
    window.location.pathname === '/activities'
  ) {
    modalValue.value = true;
  }
});
const redirectProfile = () => {
  window.location.href = '/profile';
};

onUnmounted(() => {
  localStorage.removeItem('openAddModel');
});

provide('defaultLanguage', props.defaultLanguage);
provide('translatedData', props.translatedData);
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss" scoped>
.activity__header {
  top: 0px;
  z-index: 100;

  nav {
    display: flex;

    a:hover {
      @apply text-white;
    }
    ul {
      @apply flex;
    }
  }
  .add-btn {
    @media screen and (max-width: 375px) {
      display: none;
    }
  }
  .user-nav {
    @apply flex items-center;
  }
}
.toast {
  @apply absolute  left-2/4 z-50;
  transform: translate(-50%, -50%);
}

.toast-error {
  @apply absolute right-5 top-5 z-50;
}

.profile__dropdown {
  @apply invisible absolute right-3 z-20 bg-white text-left text-sm text-bluecoral opacity-0 shadow-dropdown duration-300 sm:right-10;
  top: 50px;
  width: 265px;
  box-shadow: 4px 4px 40px rgba(0, 50, 76, 0.2);

  @media screen and (max-width: 640px) {
    width: 220px;
  }

  li {
    @apply flex items-center space-x-3 p-3 sm:p-4;
    a:hover {
      @apply text-bluecoral;
    }
    .user-profile {
      font-size: 26px;
    }
  }
  .dropdown__list {
    @apply bg-n-10 hover:bg-n-20 hover:text-bluecoral;
    a {
      @apply capitalize;
    }
  }
}
.profile__dropdown--touch {
  @apply absolute  right-10 z-20 bg-white text-left text-sm text-bluecoral shadow-dropdown  duration-300;
  top: 50px;
  width: 265px;
  box-shadow: 4px 4px 40px rgba(0, 50, 76, 0.2);

  @media screen and (max-width: 640px) {
    width: 220px;
  }

  li {
    @apply flex items-center space-x-3 p-3 sm:p-4;
    a:hover {
      @apply text-bluecoral;
    }
    .user-profile {
      font-size: 26px;
    }
  }
  .dropdown__list {
    @apply bg-n-10 hover:bg-n-20 hover:text-bluecoral;
    a {
      @apply capitalize;
    }
  }
}
.dropdown-btn:hover,
.dropdown-btn:active {
  .profile__dropdown {
    @apply visible opacity-100;
    transform: translateY(10px);
  }
  .dropdown__arrow {
    transform: rotate(180deg);
  }
}
.add-import {
  cursor: pointer;
}
.add-import-dropdown {
  visibility: hidden;
  opacity: 0;
}
.add-import:hover .add-import-dropdown {
  visibility: visible !important;
  opacity: 1 !important;
  transform: translateY(0);
}

.spinner {
  @apply absolute right-7 top-3 inline-block animate-spin rounded-full border-2 border-n-10 border-opacity-5;
  width: 15px;
  height: 15px;
  border-top-color: white;
}
</style>
