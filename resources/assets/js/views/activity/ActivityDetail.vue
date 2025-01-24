<template>
  <div>
    <div
      v-if="showSidebar"
      class="fixed top-0 z-[150] h-screen w-screen bg-black/10 lg:hidden"
      @click="toggleSidebar"
    />
    <div
      v-if="showSidebar"
      class="details-sidebar-close-icon lg:hidden"
      @click="
        () => {
          showSidebar = !showSidebar;
        }
      "
    >
      <svg-vue icon="chevron" class="rotate-180 pb-2 text-3xl text-white" />
    </div>
    <div class="relative bg-paper px-5 pb-[71px] pt-4 xl:px-10">
      <!-- title section -->
      <div class="page-title mb-6">
        <div class="pb-4 text-caption-c1 text-n-40">
          <div>
            <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
              <div class="flex items-center">
                <a class="whitespace-nowrap font-bold" href="/activities">
                  {{ translatedData['common.common.your_activities'] }}
                </a>
                <span class="separator mx-4"> / </span>
                <div class="breadcrumb__title">
                  <span
                    class="breadcrumb__title last w-[200px] overflow-hidden text-ellipsis text-n-30"
                  >
                    {{ pageTitle ?? translatedData['common.common.untitled'] }}
                  </span>
                  <span class="ellipsis__title--hover">
                    {{
                      pageTitle
                        ? pageTitle
                        : getTranslatedUntitled(translatedData)
                    }}
                  </span>
                </div>
              </div>
            </nav>
          </div>
        </div>

        <div class="flex items-end gap-4">
          <div class="title max-w-[50%] basis-6/12">
            <div class="inline-flex w-full items-center">
              <div class="mr-3">
                <a href="/activities">
                  <svg-vue icon="arrow-short-left" />
                </a>
              </div>
              <div class="inline-flex min-h-[48px] grow flex-wrap items-center">
                <h4 class="ellipsis__title relative text-2xl font-bold">
                  <span class="ellipsis__title overflow-hidden">
                    {{
                      pageTitle
                        ? pageTitle
                        : getTranslatedUntitled(translatedData)
                    }}
                  </span>
                  <span class="ellipsis__title--hover">
                    {{
                      pageTitle
                        ? pageTitle
                        : getTranslatedUntitled(translatedData)
                    }}
                  </span>
                </h4>
              </div>
            </div>
          </div>
          <div class="actions flex grow flex-col items-end justify-end">
            <div class="relative inline-flex justify-end">
              <!-- toast msg for publishing -->

              <Toast
                v-if="toastData.visibility"
                :message="toastData.message"
                :type="toastData.type"
                class="mr-3 whitespace-nowrap"
              />

              <!-- refresh toast message -->
              <RefreshToastMessage
                v-if="refreshToastMsg.visibility"
                :message="refreshToastMsg.refreshMessage"
                :type="refreshToastMsg.refreshMessageType"
                class="mr-3 whitespace-nowrap"
              />
              <ErrorPopupForPublish
                v-if="errorData.visibility"
                :message="errorData.message"
                :title="
                  translatedData[
                    'common.common.activity_couldnt_be_published_because'
                  ]
                "
                @close-popup="
                  () => {
                    errorData.visibility = false;
                  }
                "
              />
              <!-- {{ typeof toastData.message }} -->
              <div class="inline-flex items-center justify-end gap-3">
                <!-- Delete Activity -->
                <DeleteButton />

                <!-- Unpublish Activity -->
                <UnPublish
                  v-if="store.state.unPublished"
                  :activity-id="activityProps.id"
                />
                <!-- Publish Activity -->
                <Publish
                  v-if="store.state.showPublished"
                  :linked-to-iati="activityProps.linked_to_iati"
                  :status="activityProps.status"
                  :core-completed="coreCompleted"
                  :activity-id="activityProps.id"
                  :deprecation-status-map="deprecationStatusMap"
                  :pa="pa"
                />
              </div>
            </div>

            <Errors
              v-if="errorsWithoutAdvisory.length > 0 || importActivityError"
              :error-data="store.state.publishErrors"
              class="absolute bottom-[calc(100%-52px)] right-0"
            />
          </div>
        </div>
      </div>
      <!-- title section ends -->
      <div class="sidebar-open-icon" @click="toggleSidebar">
        <svg-vue icon="chevron" class="pb-2 text-3xl text-white" />
      </div>
      <div class="activities">
        <aside class="activities__sidebar hidden lg:block">
          <div
            v-if="
              publishStatus.linked_to_iati && publishStatus.status === 'draft'
            "
            class="mb-2"
          >
            <PreviouslyPublished />
          </div>
          <div id="progress" class="mb-1 flex">
            <div class="activities__card progress mr-1">
              <div class="mb-2 flex items-center justify-between">
                <span class="mr-2">
                  {{ translatedData['common.common.publishing_progress'] }}
                </span>
                <HoverText
                  :hover-text="
                    translatedData[
                      'activity_detail.activity_detail.the_iati_standard_contains_a_wide_range_of_data_elements_and_your_organisation_is_encouraged'
                    ]
                  "
                  name=""
                  class="hover-text"
                  position="right"
                />
              </div>
              <ProgressBar :percent="progress" class="mb-3" />
              <span>{{
                translatedData[
                  'activity_detail.activity_detail.complete_all_core_elements_to_get_100_score'
                ]
              }}</span>
            </div>
            <div class="activities__card elements">
              <div class="mb-7 flex items-center justify-between">
                <span>
                  {{ translatedData['common.common.elements'] }}
                </span>
                <HoverText
                  :hover-text="
                    translatedData[
                      'common.common.each_element_represents_a_basic_unit_of_information_in_the_iati_standard'
                    ]
                  "
                  name=""
                  class="hover-text"
                />
              </div>
              <div class="mb-3 flex justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="core" />
                  <span>
                    {{ translatedData['common.common.core'] }}
                  </span>
                </div>
                <HoverText
                  :hover-text="
                    translatedData[
                      'activity_detail.activity_detail.core_elements_include_the_iati_standards_mandatory_and_recommended_elements'
                    ]
                  "
                  name=""
                  class="hover-text"
                />
              </div>
              <div class="flex justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue class="text-spring-50" icon="double-tick" />
                  <span>
                    {{ translatedData['common.common.completed'] }}
                  </span>
                </div>
                <HoverText
                  :hover-text="
                    translatedData[
                      'common.common.you_cannot_publish_an_activity_until_all_the_mandatory_fields_have_been_filled'
                    ]
                  "
                  name=""
                  class="hover-text"
                />
              </div>
            </div>
          </div>
          <div v-sticky-component="{ boundary: '.activities' }">
            <Elements :activity-id="activity.id" :data="elementProps" />
          </div>
        </aside>

        <div
          :class="
            showSidebar
              ? `-translate-x-[20px]  ${
                  istopVisible
                    ? 'top-[60px] h-[calc(100vh_-_60px)]'
                    : 'top-[0px] h-[100vh]'
                }`
              : ` -translate-x-[110%] ${
                  istopVisible
                    ? 'top-[60px] h-[calc(100vh_-_60px)]'
                    : 'top-[0px] h-[100vh]'
                } `
          "
          class="opacity-1 fixed z-[200] block overflow-y-auto duration-200 lg:hidden"
        >
          <aside class="!z-[200] w-[280px] bg-white pt-8">
            <div
              v-if="
                publishStatus.linked_to_iati && publishStatus.status === 'draft'
              "
              class="mb-2"
            >
              <PreviouslyPublished />
            </div>
            <div class="mb-1 flex">
              <div class="activities__card progress mr-1">
                <div class="mb-2 flex items-center justify-between">
                  <span class="mr-2">
                    {{ translatedData['common.common.publishing_progress'] }}
                  </span>
                  <HoverText
                    :hover-text="
                      translatedData[
                        'activity_detail.activity_detail.the_iati_standard_contains_a_wide_range_of_data_elements_and_your_organisation_is_encouraged'
                      ]
                    "
                    name=""
                    class="hover-text"
                    position="right"
                  />
                </div>
                <ProgressBar :percent="progress" class="mb-3" />
                <span>
                  {{
                    translatedData[
                      'activity_detail.activity_detail.complete_all_core_elements_to_get_100_score'
                    ]
                  }}
                </span>
              </div>
              <div class="activities__card elements">
                <div class="mb-7 flex items-center justify-between">
                  <span>Elements</span>
                  <HoverText
                    :hover-text="
                      translatedData[
                        'common.common.each_element_represents_a_basic_unit_of_information_in_the_iati_standard'
                      ]
                    "
                    name=""
                    class="hover-text"
                  />
                </div>
                <div class="mb-3 flex justify-between">
                  <div class="flex items-center space-x-1">
                    <svg-vue icon="core" />
                    <span>
                      {{ translatedData['common.common.core'] }}
                    </span>
                  </div>
                  <HoverText
                    :hover-text="
                      translatedData[
                        'activity_detail.activity_detail.core_elements_include_the_iati_standards_mandatory_and_recommended_elements'
                      ]
                    "
                    name=""
                    class="hover-text"
                  />
                </div>
                <div class="flex justify-between">
                  <div class="flex items-center space-x-1">
                    <svg-vue class="text-spring-50" icon="double-tick" />
                    <span>
                      {{ translatedData['common.common.completed'] }}
                    </span>
                  </div>
                  <HoverText
                    :hover-text="
                      translatedData[
                        'common.common.you_cannot_publish_an_activity_until_all_the_mandatory_fields_have_been_filled'
                      ]
                    "
                    name=""
                    class="hover-text"
                  />
                </div>
              </div>
            </div>
            <div v-sticky-component="{ boundary: '.activities' }">
              <Elements :activity-id="activity.id" :data="elementProps" />
            </div>
          </aside>
        </div>
        <div class="w-full">
          <div class="flex justify-end">
            <a
              :href="`/activity/${activityProps.id}/default_values`"
              class="mb-4 flex items-center text-xs font-bold uppercase leading-normal text-n-50"
            >
              <svg-vue class="mr-0.5 text-base" icon="setting"></svg-vue>
              <span class="whitespace-nowrap">
                {{
                  translatedData[
                    'activity_detail.activity_detail.override_this_activitys_default_values'
                  ]
                }}
              </span>
            </a>
          </div>
          <div
            class="mb-3 inline-flex max-w-[70%] flex-wrap gap-2 lg:max-w-full"
          >
            <div
              v-for="(post, key, index) in groupedData"
              :key="index"
              class="tab-btn-anchor focus:outline-0"
            >
              <button
                :disabled="post.status == 'disabled'"
                class="tab-btn !p-0"
              >
                <a :href="`#${String(key)}`" class="p-2 !pr-0">{{
                  translatedData[`activity_detail.element_group.${String(key)}`]
                }}</a>
                <span class="hover__text pr-2">
                  <HoverText
                    :name="
                      translatedData[
                        `activity_detail.element_group.${String(key)}`
                      ]
                    "
                    :hover-text="
                      translatedData[
                        'common.common.you_cannot_publish_an_activity_until_all_the_mandatory_fields_have_been_filled'
                      ]
                    "
                    icon_size="text-tiny"
                  />
                </span>
              </button>
            </div>
          </div>
          <div class="activities__content--elements -mx-3 flex flex-wrap">
            <template v-for="(post, key, index) in groupedData" :key="index">
              <div
                class="elements-title relative mx-3 mb-1 mt-3 flex w-full items-center text-sm uppercase text-n-40"
              >
                <div :id="key" class="mr-4 shrink-0">
                  {{ formatTitle(key) }}
                </div>
              </div>
              <template v-for="(element, name, i) in post.elements" :key="i">
                <template v-if="name.toString() !== 'result'">
                  <ActivityElement
                    v-if="
                      (typeof element.content === 'object'
                        ? Object.keys(element.content).length > 0
                        : element.content) ||
                      typeof element.content === 'number'
                    "
                    :id="key"
                    :data="element"
                    :types="types"
                    :title="String(name)"
                    :activity-id="activity.id"
                    :width="'full'"
                    :completed="status[name] ?? false"
                    :tooltip="element.hover_text"
                    :warning_info_text="element.warning_info_text ?? ''"
                    :has-ever-been-published="
                      publishStatus.has_ever_been_published
                    "
                    :deprecation-code-usage="
                      String(name) === 'transactions'
                        ? onlyDeprecatedStatusMap(element.content)
                        : deprecationStatusMap[name]
                    "
                    class="elements-card"
                  />
                </template>
                <template v-else>
                  <Result
                    v-if="
                      (typeof element.content === 'object'
                        ? Object.keys(element.content).length > 0
                        : element.content) ||
                      typeof element.content === 'number'
                    "
                    :id="key"
                    :data="element"
                    :types="types"
                    :default-language="
                      activityProps.default_field_values.default_language
                    "
                    :title="String(name)"
                    :activity-id="activity.id"
                    :completed="status[name] ?? false"
                    :tooltip="element.hover_text"
                  />
                </template>
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>
    <!-- TODO: Revisit after 1567 -->
    <XlsUploadIndicator />
    <PublishSelected />
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  onMounted,
  reactive,
  toRefs,
  provide,
  computed,
  onUnmounted,
  ref,
  Ref,
  watch,
} from 'vue';
import { useToggle, watchIgnorable } from '@vueuse/core';

import { useStorage } from '@vueuse/core';
import axios from 'axios';

// components
import { Result } from './elements/Index';
import HoverText from 'Components/HoverText.vue';
import ProgressBar from 'Components/RadialProgressBar.vue';
import Publish from 'Components/buttons/PublishButton.vue';
import UnPublish from 'Components/buttons/UnPublishButton.vue';
import DeleteButton from 'Components/buttons/DeleteButton.vue';
import Errors from 'Components/sections/StickyErrors.vue';
import Toast from 'Components/ToastMessage.vue';
import ErrorPopupForPublish from 'Components/ErrorPopupForPublish.vue';
import getActivityTitle from 'Composable/title';
import XlsUploadIndicator from 'Components/XlsUploadIndicator.vue';
import RefreshToastMessage from 'Activity/bulk-publish/RefreshToast.vue';
import PublishSelected from 'Activity/bulk-publish/PublishSelected.vue';

// Activity Components
import Elements from 'Activity/partials/ActivitiesElements.vue';
import ActivityElement from 'Activity/partials/ActivityElement.vue';
import PreviouslyPublished from 'Components/status/PreviouslyPublished.vue';

// Vuex Store
import { detailStore } from 'Store/activities/show';
import { useStore } from 'Store/activities';
import {
  getTranslatedUntitled,
  onlyDeprecatedStatusMap,
} from 'Composable/utils';

export default defineComponent({
  components: {
    ErrorPopupForPublish,
    HoverText,
    ProgressBar,
    Elements,
    ActivityElement,
    Result,
    Publish,
    Errors,
    UnPublish,
    DeleteButton,
    PreviouslyPublished,
    XlsUploadIndicator,
    Toast,
    RefreshToastMessage,
    PublishSelected,
  },
  props: {
    elements: {
      type: Object,
      required: true,
    },
    groups: {
      type: Object,
      required: true,
    },
    activity: {
      type: Object,
      required: true,
    },
    progress: {
      type: Number,
      required: true,
    },
    toast: {
      type: Object,
      required: true,
    },
    types: {
      type: Object,
      required: true,
    },
    status: {
      type: Object,
      required: true,
    },
    results: {
      type: Array,
      required: true,
    },
    transactions: {
      type: Array,
      required: true,
    },
    coreCompleted: {
      type: Boolean,
      required: true,
    },
    iatiValidatorResponse: {
      type: Object,
      required: true,
    },
    importActivityError: {
      type: Object,
      required: true,
    },
    deprecationStatusMap: {
      type: Object,
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
    currentLanguage: {
      type: String,
      required: true,
    },
  },
  setup(props) {
    interface paType {
      publishingActivities: {
        organization_id?: string;
        job_batch_uuid?: string;
        activities?: object;
        status?: string;
        message?: string;
      };
    }

    const refreshToastMsg = reactive({
      visibility: false,
      refreshMessageType: true,
      refreshMessage:
        props.translatedData[
          'common.common.activity_has_been_published_successfully_refresh_to_see_changes'
        ],
    });

    const pa: Ref<paType> = useStorage('vue-use-local-storage', {
      publishingActivities: localStorage.getItem('publishingActivities') ?? {},
    });

    const { types, coreCompleted } = toRefs(props);
    let removed = sessionStorage.getItem('removed');

    const store = detailStore();
    const indexStore = useStore();
    const showSidebar = ref(false);
    const positionY = ref(0);
    const screenWidth = ref(0);
    const publishingActivities = ref();

    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    const errorData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    showSidebar;
    /**
     * For modal popup completed
     */
    const [deleteValue, deleteToggle] = useToggle();
    const [downloadValue, downloadToggle] = useToggle();

    const toggleSidebar = () => {
      showSidebar.value = !showSidebar.value;
    };
    const handleScroll = () => {
      positionY.value = window.scrollY;
    };
    const istopVisible = computed(() => {
      return positionY.value === 0;
    });
    const width = computed(() => {
      return window.innerWidth;
    });

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', calcWidth);
    });
    onMounted(() => {
      window.onload = () => {
        publishingActivities.value = pa.value?.publishingActivities;

        if (removed) {
          toastData.type = true;
          toastData.visibility = true;
          toastData.message =
            props.translatedData[
              'activity_detail.activity_detail.removed_successfully'
            ];
          sessionStorage.clear();
        }
      };
      screenWidth.value = window.innerWidth;
      window.addEventListener('scroll', handleScroll);
      window.addEventListener('resize', calcWidth);
      if (props.toast.message !== '') {
        toastData.type = props.toast.type;
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }
    });
    watch(
      () => indexStore?.state?.startBulkPublish,
      async () => {
        await bulkPublishStatus();

        publishingActivities.value = pa.value?.publishingActivities;
      },
      { deep: true }
    );

    const bulkPublishStatus = async () => {
      pa.value = { publishingActivities: {} };
      let count = 0;
      const checkStatus = setInterval(() => {
        axios.get(`/activities/bulk-publish-status`).then((res) => {
          const response = res.data;
          if ('data' in response) {
            // saving in local storage
            pa.value.publishingActivities.activities = response.data.activities;
            pa.value.publishingActivities.status = response.data.status;
            pa.value.publishingActivities.message = response.data.message;

            clearInterval(checkStatus);
          }
        });
        if (count > 5) {
          clearInterval(checkStatus);
        }

        count++;
      }, 1000);
    };

    const calcWidth = (event) => {
      screenWidth.value = event.target.innerWidth;
      if (screenWidth.value > 1024) {
        document.documentElement.style.overflow = 'auto';
      } else {
        showSidebar.value &&
          (document.documentElement.style.overflow = 'hidden');
      }
    };
    watch(
      () => showSidebar.value,
      (sidebar) => {
        if (sidebar) {
          document.documentElement.style.overflow = 'hidden';
        } else document.documentElement.style.overflow = 'auto';
      }
    );
    watch(
      () => toastData.visibility,
      () => {
        setTimeout(() => {
          toastData.visibility = false;
          ignoreToastUpdate();
        }, 10000);
      }
    );

    /**
     * Grouping all the data's for scroll function
     *
     * this data is created using props.element_group and props.activity
     */

    const { groups, activity, elements, status, transactions } = toRefs(props),
      groupedData = groups.value,
      activityProps = activity.value,
      activities = groups.value,
      elementProps = elements.value,
      statusProps = status.value,
      transactionProps = transactions.value;

    const { results } = toRefs(props);
    activityProps.result = results.value;
    activityProps.transactions = transactionProps;

    // generating available elements
    Object.keys(activities).map((key) => {
      let flag = false;

      Object.keys(activities[key]['elements']).map((k) => {
        if (
          typeof activityProps[k] === 'number' ||
          (typeof activityProps[k] === 'object' &&
            activityProps[k] &&
            Object.keys(activityProps[k]).length)
        ) {
          activities[key]['elements'][k]['content'] = activityProps[k];
          activities[key]['elements'][k]['hover_text'] =
            elementProps[k]['hover_text'] ?? '';
          activities[key]['elements'][k]['warning_info_text'] =
            elementProps[k]['warning_info_text'] ?? '';
          flag = true;
        } else {
          delete activities[key][k];
        }
      });

      if (flag === false) {
        delete activities[key];
      }
    });

    // generating available categories of elements
    Object.keys(groupedData).map((key) => {
      if (Object.prototype.hasOwnProperty.call(activities, key)) {
        groupedData[key]['status'] = 'enabled';
      } else {
        groupedData[key]['status'] = 'disabled';
      }
    });

    /**
     * Grouping all elements and theirs completed status
     *
     * combining props.elements and props.status
     *
     * @returns object
     */
    Object.keys(elementProps).map((key) => {
      elementProps[key]['completed'] = statusProps[key] ?? false;
      elementProps[key]['not_completed'] = !(statusProps[key] ?? false);
      elementProps[key]['has_data'] = 0;

      if (key in activityProps) {
        if (
          typeof activityProps[key] === 'number' ||
          (typeof activityProps[key] === 'object' &&
            activityProps[key] &&
            Object.keys(activityProps[key]).length)
        ) {
          if (
            Object.keys(activityProps[key]).length > 0 ||
            activityProps[key].toString.length > 0
          ) {
            elementProps[key]['has_data'] = 1;
          }
        }
      }
    });

    /**
     * Finding current language - activity title
     */
    let pageTitle = getActivityTitle(
      activityProps.title,
      activityProps.default_field_values?.default_language
    );

    function formatTitle(title: string) {
      return (
        props.translatedData[`activity_detail.element_group.${title}`] ??
        title.replace(/_/gi, ' ')
      );
    }

    const toastMessage = reactive({
      message: '',
      type: false,
    });

    const { ignoreUpdates } = watchIgnorable(toastData, () => undefined, {
      flush: 'sync',
    });

    const ignoreToastUpdate = () => {
      ignoreUpdates(() => {
        toastData.message = '';
      });
    };

    interface PublishStatusTypeface {
      linked_to_iati: boolean;
      status: string;
    }

    const publishStatus: PublishStatusTypeface = reactive({
      linked_to_iati: activityProps.linked_to_iati,
      status: activityProps.status,
      has_ever_been_published: activityProps.has_ever_been_published,
    });

    const errorsWithoutAdvisory = computed(() => {
      return store.state.publishErrors.filter(
        (error: { severity: string }) => error.severity !== 'advisory'
      );
    });

    // vue provides
    provide('types', types.value);
    provide('coreCompleted', coreCompleted.value);
    provide('toastMessage', toastMessage);
    provide('toastData', toastData);
    provide('errorData', errorData);
    provide('importActivityError', props.importActivityError);
    provide('activityId', props.activity.id);
    provide('elements', props.elements);
    provide('activities', publishingActivities as Ref);
    provide('refreshToastMsg', refreshToastMsg);
    provide('translatedData', props.translatedData);
    provide('currentLanguage', props.currentLanguage);

    indexStore.dispatch('updateSelectedActivities', [activity.value.id]);

    /**
     * Breadcrumb data
     */
    const breadcrumbData = [
      {
        title: props.translatedData['common.common.your_activities'],
        link: '/activities',
      },
      {
        title: pageTitle,
        link: '',
      },
    ];

    /**
     *  Global State
     */
    let { iatiValidatorResponse } = toRefs(props);
    const validationResult = iatiValidatorResponse.value;

    if (validationResult && validationResult.errors.length > 0) {
      store.dispatch('updatePublishErrors', validationResult.errors);
    }

    if (publishStatus.linked_to_iati) {
      store.dispatch('updateUnPublished', true);
    } else {
      store.dispatch('updateUnPublished', false);
    }

    if (
      !(publishStatus.linked_to_iati && publishStatus.status === 'published')
    ) {
      store.dispatch('updateShowPublished', true);
    } else {
      store.dispatch('updateShowPublished', false);
    }

    return {
      groupedData,
      activities,
      deleteValue,
      XlsUploadIndicator,
      deleteToggle,
      downloadValue,
      downloadToggle,
      toastData,
      elementProps,
      props,
      formatTitle,
      pageTitle,
      toastMessage,
      publishStatus,
      breadcrumbData,
      store,
      activityProps,
      errorData,
      showSidebar,
      toggleSidebar,
      istopVisible,
      screenWidth,
      refreshToastMsg,
      publishingActivities,
      width,
      indexStore,
      pa,
      errorsWithoutAdvisory,
    };
  },
  methods: { getTranslatedUntitled, onlyDeprecatedStatusMap },
});
</script>
