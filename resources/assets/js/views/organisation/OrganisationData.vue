<template>
  <div>
    <div
      v-if="showSidebar"
      class="fixed top-0 z-[50] h-screen w-screen bg-black/10 lg:hidden"
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
      <div class="page-title mb-4 xl:mb-6">
        <div class="flex items-end gap-4">
          <div class="title grow-0">
            <div class="max-w-sm pb-4 text-caption-c1 text-n-40">
              <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
                <div class="flex">
                  <a class="whitespace-nowrap font-bold" href="/organisation">
                    {{
                      translatedData[
                        'organisationDetail.organisation_data.your_organisation'
                      ]
                    }}
                  </a>
                  <span class="separator mx-4"> / </span>
                  <div class="breadcrumb__title">
                    <span
                      class="breadcrumb__title last overflow-hidden text-n-30"
                      >{{
                        organization.name
                          ? organization.name['0'].narrative ??
                            getTranslatedUntitled(translatedData)
                          : getTranslatedUntitled(translatedData)
                      }}</span
                    >
                    <span class="ellipsis__title--hover w-[calc(100%_+_35px)]">
                      {{
                        organization.name
                          ? organization.name['0'].narrative ??
                            getTranslatedUntitled(translatedData)
                          : getTranslatedUntitled(translatedData)
                      }}
                    </span>
                  </div>
                </div>
              </nav>
            </div>
            <div class="inline-flex max-w-3xl items-center">
              <div class="mr-3">
                <a href="/activities">
                  <svg-vue icon="arrow-short-left" />
                </a>
              </div>
              <div>
                <h4 class="ellipsis__title relative mr-4 text-2xl font-bold">
                  <span
                    class="ellipsis__title !inline-block max-w-[400px] overflow-x-hidden text-ellipsis whitespace-nowrap"
                  >
                    {{
                      organization.name
                        ? organization.name['0'].narrative ??
                          getTranslatedUntitled(translatedData)
                        : getTranslatedUntitled(translatedData)
                    }}
                  </span>
                  <span class="ellipsis__title--hover w-[calc(100%_+_35px)]">
                    {{
                      organization.name
                        ? organization.name['0'].narrative ??
                          getTranslatedUntitled(translatedData)
                        : getTranslatedUntitled(translatedData)
                    }}
                  </span>
                </h4>
              </div>
            </div>
          </div>
          <div
            class="actions relative flex grow flex-col items-end justify-end gap-3 md:shrink-0 md:flex-row"
          >
            <Toast
              v-if="toastData.visibility"
              :message="toastData.message"
              :type="toastData.type"
              class="mr-4"
            />
            <ErrorPopupForPublish
              v-if="errorData.visibility"
              :message="errorData.message"
              :title="
                translatedData[
                  'organisationDetail.organisation_data.organisation_couldnt_be_published_because'
                ]
              "
              @close-popup="
                () => {
                  errorData.visibility = false;
                }
              "
            />
            <div class="inline-flex justify-end">
              <!-- Unpublish /Publish Activity -->
              <PublishUnpublish v-if="userRole === 'admin'" />
            </div>
          </div>
        </div>
      </div>
      <!-- title section ends -->
      <div class="sidebar-open-icon" @click="toggleSidebar">
        <svg-vue icon="chevron" class="pb-2 text-3xl text-white" />
      </div>
      <div class="activities">
        <aside class="activities__sidebar hidden lg:block">
          <div class="mb-1 flex">
            <div class="activities__card progress mr-1">
              <div class="mb-2 flex items-center justify-between">
                <span class="mr-2">{{
                  translatedData['common.common.publishing_progress']
                }}</span>
                <HoverText
                  :hover-text="
                    translatedData[
                      'common.common.you_cannot_publish_an_activity_until_all_the_mandatory_fields_have_been_filled'
                    ]
                  "
                  name=""
                  class="hover-text"
                  position="right"
                />
              </div>
              <RadialProgressBar
                class="mb-3 h-20 text-8xl"
                :is-percent="true"
                :percent="progress"
              ></RadialProgressBar>
              <span>{{
                translatedData[
                  'activity_detail.activity_detail.complete_all_core_elements_to_get_100_score'
                ]
              }}</span>
            </div>
            <div class="activities__card elements">
              <div class="mb-7 flex items-center justify-between">
                <span>{{ translatedData['common.common.elements'] }}</span>
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
              <div class="mb-3 flex justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="core" />
                  <span>{{ translatedData['common.common.core'] }}</span>
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
              <div class="flex justify-between">
                <div class="flex items-center space-x-1">
                  <svg-vue icon="double-tick" class="text-spring-50"></svg-vue>
                  <span>{{ translatedData['common.common.completed'] }}</span>
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
          <OrganisationElements
            :activity-id="organization.id"
            :data="elementProps"
            :status="status"
          />
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
          class="opacity-1 fixed z-[100] block duration-200 lg:hidden"
        >
          <aside class="!z-[200] h-full w-[280px] bg-white pt-8">
            <div class="mb-1 flex">
              <div class="activities__card progress mr-1">
                <div class="mb-2 flex items-center justify-between">
                  <span class="mr-2">{{
                    translatedData['common.common.publishing_progress']
                  }}</span>
                  <HoverText
                    :hover-text="
                      translatedData[
                        'common.common.you_cannot_publish_an_activity_until_all_the_mandatory_fields_have_been_filled'
                      ]
                    "
                    name=""
                    class="hover-text"
                    position="right"
                  />
                </div>
                <RadialProgressBar
                  class="mb-3 h-20 text-8xl"
                  :is-percent="true"
                  :percent="progress"
                ></RadialProgressBar>
                <span>{{
                  translatedData[
                    'activity_detail.activity_detail.complete_all_core_elements_to_get_100_score'
                  ]
                }}</span>
              </div>
              <div class="activities__card elements">
                <div class="mb-7 flex items-center justify-between">
                  <span>{{ translatedData['common.common.elements'] }}</span>
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
                <div class="mb-3 flex justify-between">
                  <div class="flex items-center space-x-1">
                    <svg-vue icon="core" />
                    <span>{{ translatedData['common.common.core'] }}</span>
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
                <div class="flex justify-between">
                  <div class="flex items-center space-x-1">
                    <svg-vue
                      icon="double-tick"
                      class="text-spring-50"
                    ></svg-vue>
                    <span>{{ translatedData['common.common.completed'] }}</span>
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
            <OrganisationElements
              :activity-id="organization.id"
              :data="elementProps"
              :status="status"
            />
          </aside>
        </div>
        <div class="activities__content">
          <div class="activities__content--elements -mx-3 grid grid-cols-2">
            <template v-for="(post, key, index) in groupedData" :key="index">
              <template v-for="(element, name, i) in post.elements" :key="i">
                <OrganisationElementsDetail
                  v-if="
                    (typeof element.content === 'object'
                      ? Object.keys(element.content).length > 0
                      : element.content) || typeof element.content === 'number'
                  "
                  :id="key"
                  :data="element"
                  :title="name.toString()"
                  :activity-id="organization.id"
                  :content="element.content"
                  :types="types"
                  :tooltip="elements[name]['hover_text']"
                  :status="
                    String(name) === 'organisation_identifier'
                      ? status['identifier']
                      : status[name]
                  "
                  class="elements-card col-span-2"
                  :class="
                    String(name) === 'organisation_identifier'
                      ? 'xl:col-span-1'
                      : ''
                  "
                  :deprecation-code-usage="
                    organizationProps['deprecation_status_map'][name.toString()]
                  "
                />
              </template>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  reactive,
  onMounted,
  toRefs,
  computed,
  onUnmounted,
  provide,
  watch,
  ref,
} from 'vue';
import HoverText from '../../components/HoverText.vue';
import RadialProgressBar from '../../components/RadialProgressBar.vue';
import OrganisationElements from './OrganisationElements.vue';
import OrganisationElementsDetail from './OrganisationElementsDetail.vue';
import Toast from 'Components/ToastMessage.vue';
import PublishUnpublish from 'Components/sections/OrganizationPublishUnpublishButton.vue';
import { useToggle } from '@vueuse/core';
import { watchIgnorable } from '@vueuse/core';
import ErrorPopupForPublish from 'Components/ErrorPopupForPublish.vue';
import { getTranslatedUntitled } from 'Composable/utils';

export default defineComponent({
  name: 'OrganisationData',
  components: {
    HoverText,
    RadialProgressBar,
    OrganisationElements,
    OrganisationElementsDetail,
    Toast,
    PublishUnpublish,
    ErrorPopupForPublish: ErrorPopupForPublish,
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
    organization: {
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
    mandatoryCompleted: {
      type: Boolean,
      required: true,
    },
    status: {
      type: Object,
      required: true,
    },
    userRole: {
      type: String,
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
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
    const showSidebar = ref(false);
    const screenWidth = ref(0);

    const [publishValue, publishToggle] = useToggle();
    const [unpublishValue, unpublishToggle] = useToggle();
    const [deleteValue, deleteToggle] = useToggle();
    const [downloadValue, downloadToggle] = useToggle();
    const positionY = ref(0);

    const toggleSidebar = () => {
      showSidebar.value = !showSidebar.value;
    };

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', calcWidth);
    });

    onMounted(() => {
      window.addEventListener('resize', calcWidth);

      window.addEventListener('scroll', handleScroll);

      if (props.toast.message !== '') {
        toastData.type = props.toast.type === 'success';
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }
    });

    const calcWidth = (event) => {
      screenWidth.value = event.target.innerWidth;

      if (screenWidth.value > 1024) {
        document.documentElement.style.overflow = 'auto';
      } else {
        showSidebar.value &&
          (document.documentElement.style.overflow = 'hidden');
      }
    };

    const handleScroll = () => {
      positionY.value = window.scrollY;
    };
    const istopVisible = computed(() => {
      return positionY.value === 0;
    });
    const { ignoreUpdates } = watchIgnorable(toastData, () => undefined, {
      flush: 'sync',
    });
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

    const ignoreToastUpdate = () => {
      ignoreUpdates(() => {
        toastData.message = '';
      });
    };

    /**
     * Grouping all the data's for scroll function
     *
     * this data is created using props.groups and props.activity
     */
    const { groups, organization, elements } = toRefs(props),
      groupedData = groups.value,
      organizationProps = organization.value,
      organizationData = groups.value,
      elementProps = elements.value;

    // generating available elements
    Object.keys(organizationData).map((key) => {
      let flag = false;

      Object.keys(organizationData[key]['elements']).map((k) => {
        if (organizationProps[k] || typeof organizationProps[k] === 'number') {
          organizationData[key]['elements'][k]['content'] =
            organizationProps[k];
          flag = true;
          elementProps[k]['has_data'] = true;
        } else {
          delete organizationData[key][k];
          elementProps[k]['has_data'] = false;
        }

        elementProps[k]['core'] =
          organizationData[key]['elements'][k]['mandatory'];
        elementProps[k]['completed'] =
          k === 'organisation_identifier'
            ? organizationProps['element_status']['identifier']
            : organizationProps['element_status'][k];
        elementProps[k]['not_completed'] = !elementProps[k]['completed'];
      });

      if (!flag) {
        delete organizationData[key];
      }
    });

    // generating available categories of elements
    Object.keys(groupedData).map((key) => {
      if (Object.prototype.hasOwnProperty.call(organizationData, key)) {
        groupedData[key]['status'] = 'enabled';
      } else {
        groupedData[key]['status'] = 'disabled';
      }
    });

    /**
     * Publish message toast after publishing
     */
    interface PublishMessage {
      message: string;
      type: boolean;
    }

    const publishMessage: PublishMessage = reactive({
      message: '',
      type: false,
    });

    interface PublishStatusTypeface {
      is_published: boolean;
      status: string;
    }

    const publishStatus: PublishStatusTypeface = reactive({
      is_published: organizationProps.is_published,
      status: organizationProps.status,
    });

    provide('publishMessage', publishMessage);
    provide('mandatoryCompleted', props.mandatoryCompleted);
    provide('toastData', toastData);
    provide('publishStatus', publishStatus);
    provide('errorData', errorData);
    provide('userRole', props.userRole);
    provide('translatedData', props.translatedData);

    return {
      groupedData,
      organizationData,
      publishValue,
      publishToggle,
      unpublishValue,
      unpublishToggle,
      deleteValue,
      deleteToggle,
      downloadValue,
      downloadToggle,
      elementProps,
      toastData,
      publishStatus,
      errorData,
      toggleSidebar,
      showSidebar,
      istopVisible,
      organizationProps,
    };
  },
  methods: { getTranslatedUntitled },
});
</script>

<style lang="scss">
.mandatory::after {
  content: '';
  width: 0.5px;
  height: 140px;
  @apply absolute -right-6 top-1 bg-n-20;
}

.element {
  @apply mb-3 ml-2;

  &__inner {
    @apply flex space-x-2.5;
  }

  &__notes {
    @apply h-4 w-8 rounded-sm border;
  }
}

.element__search {
  @apply my-4 h-10 w-full rounded border border-n-30 bg-white py-3 pl-10 pr-3 text-n-40 outline-none duration-300;

  &::placeholder {
    @apply text-sm text-n-30 duration-300;
    letter-spacing: -0.02em;
  }

  &:focus::placeholder {
    @apply text-n-40;
  }
}

.tab__links {
  @apply relative cursor-pointer px-2 pb-6;

  &::after {
    content: '';
    @apply absolute bottom-0 left-0 h-1 w-full scale-0 bg-bluecoral duration-300;
  }

  &:hover::after {
    content: '';
    @apply visible scale-100;
  }

  &--active {
    @apply font-bold text-bluecoral;
  }
}

.tab__links--active::after {
  content: '';
  @apply absolute bottom-0 left-0 h-1 w-full bg-bluecoral duration-300;
}

.separator {
  @apply mx-4;
}

.last {
  @apply text-n-30;
}
</style>
