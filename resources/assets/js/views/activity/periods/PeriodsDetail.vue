<template>
  <div class="bg-paper px-5 pb-[71px] pt-4 xl:px-10">
    <div
      v-if="showSidebar"
      class="fixed left-0 top-0 z-[50] h-screen w-screen bg-black/10 lg:hidden"
      @click="
        () => {
          showSidebar = !showSidebar;
        }
      "
    />
    <div
      v-if="showSidebar"
      class="sidebar-close-icon lg:hidden"
      @click="
        () => {
          showSidebar = !showSidebar;
        }
      "
    >
      <svg-vue icon="chevron" class="rotate-180 pb-2 text-3xl text-white" />
    </div>
    <PageTitle
      :breadcrumb-data="breadcrumbData"
      :title="translatedData['common.common.period_detail']"
      :back-link="`${periodLink}`"
    >
      <div class="flex justify-end">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <!-- <Status class="mr-2.5" :data="false" /> -->
        <Btn
          :text="translatedData['common.common.add_period']"
          icon="add"
          :link="`${periodLink}/create`"
          class="mr-2.5"
        />
        <Btn
          :text="translatedData['common.common.edit_period']"
          :link="`${periodLink}/${period.id}/edit`"
        />
      </div>
    </PageTitle>
    <div class="-mt-6 mb-8 ml-[26px] text-n-40">
      {{ translatedData['common.common.period_number'] }} :
      {{ period.period_code }}
    </div>
    <div
      class="sidebar-open-icon"
      @click="
        () => {
          showSidebar = !showSidebar;
        }
      "
    >
      <svg-vue icon="chevron" class="pb-2 text-3xl text-white" />
    </div>
    <aside
      :class="
        showSidebar
          ? ` ${
              istopVisible
                ? 'top-[60px] h-[calc(100vh_-_50px)]'
                : 'top-0 h-screen'
            }  translate-x-[0px]`
          : `  ${
              istopVisible
                ? 'top-[60px] h-[calc(100vh_-_50px)]'
                : 'top-0 h-screen'
            } -translate-x-[150%]`
      "
      class="activities__sidebar fixed left-0 z-[100] block overflow-y-auto bg-eggshell duration-200 lg:hidden"
    >
      <div v-sticky-component>
        <div class="indicator rounded-lg bg-eggshell px-6 py-4 text-n-50">
          <ul class="text-sm font-bold leading-relaxed">
            <li>
              <a v-smooth-scroll href="#target" :class="linkClasses">
                {{ getTranslatedElement(translatedData, 'target') }}
              </a>
            </li>
            <li>
              <a v-smooth-scroll href="#actual" :class="linkClasses">
                {{ getTranslatedElement(translatedData, 'actual') }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </aside>

    <div class="activities">
      <aside class="activities__sidebar hidden lg:block">
        <div v-sticky-component>
          <div class="indicator rounded-lg bg-eggshell px-6 py-4 text-n-50">
            <ul class="text-sm font-bold leading-relaxed">
              <li>
                <a v-smooth-scroll href="#target" :class="linkClasses">
                  {{ getTranslatedElement(translatedData, 'target') }}
                </a>
              </li>
              <li>
                <a v-smooth-scroll href="#actual" :class="linkClasses">
                  {{ getTranslatedElement(translatedData, 'actual') }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </aside>
      <div class="activities__content">
        <div></div>
        <div class="bg-white px-4 py-5">
          <div class="elements-detail wider">
            <div
              v-if="
                periodData.period_start[0].date || periodData.period_end[0].date
              "
              class="category flex"
            >
              {{ dateFormat(periodData.period_start[0].date) }}
              <span
                v-if="!periodData.period_start[0].date"
                class="text-xs italic text-light-gray"
                >N/A</span
              >
              <span class="mx-1">-</span>
              {{ dateFormat(periodData.period_end[0].date) }}
              <span
                v-if="!periodData.period_end[0].date"
                class="text-xs italic text-light-gray"
                >N/A</span
              >
            </div>
            <div v-else>
              <span class="text-xs italic text-light-gray">N/A</span>
            </div>
            <TargetValue id="target" :data="periodData.target" />
            <div class="divider my-10 h-px w-full border-b border-n-20"></div>
            <ActualValue id="actual" :data="periodData.actual" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  computed,
  toRefs,
  ref,
  provide,
  onMounted,
  onUnmounted,
  watch,
  reactive,
  watchEffect,
} from 'vue';

//component
import Btn from 'Components/buttons/Link.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';

import { TargetValue, ActualValue } from './elements/Index';

//composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';
import { getTranslatedElement, toTitleCase } from '../../../composable/utils';

export default defineComponent({
  name: 'PeriodDetail',
  components: {
    TargetValue,
    ActualValue,
    Btn,
    PageTitle,
    Toast,
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
    element: {
      type: Object,
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const positionY = ref(0);
    const screenWidth = ref(0);

    const linkClasses =
      'flex items-center w-full bg-white rounded p-2 text-sm text-n-50 font-bold leading-normal mb-2 shadow-default';
    let { period, activity, parentData, types } = toRefs(props);
    const handleScroll = () => {
      positionY.value = window.scrollY;
    };

    const istopVisible = computed(() => {
      return positionY.value === 0;
    });
    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    const showSidebar = ref(false);

    // vue provide
    provide('types', types.value);

    //indicator
    const periodData = period.value.period;

    //titles
    const activityId = activity.value.id,
      defaultLanguage = activity.value.default_field_values?.default_language,
      activityTitle = getActivityTitle(activity.value.title, defaultLanguage),
      activityLink = `/activity/${activityId}`,
      resultId = parentData.value.result.id,
      resultTitle = getActivityTitle(
        parentData.value.result.title,
        defaultLanguage
      ),
      resultLink = `${activityLink}/result/${resultId}`,
      indicatorId = parentData.value.indicator.id,
      indicatorTitle = getActivityTitle(
        parentData.value.indicator.title,
        defaultLanguage
      ),
      indicatorLink = `/result/${resultId}/indicator/${indicatorId}`,
      periodLink = `/indicator/${indicatorId}/period`;

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
        title: props.translatedData['common.common.result_list'],
        link: `/activity/${activityId}/result`,
      },
      {
        title: resultTitle,
        link: resultLink,
      },
      {
        title: props.translatedData['common.common.indicator_list'],
        link: `/result/${resultId}/indicator`,
      },
      {
        title: indicatorTitle,
        link: indicatorLink,
      },
      {
        title: props.translatedData['common.common.period_list'],
        link: `/indicator/${indicatorId}/period`,
      },
      {
        title: props.translatedData['elements.label.period'],
        link: '',
      },
    ];

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

    onMounted(() => {
      window.addEventListener('resize', calcWidth);
      window.addEventListener('scroll', handleScroll);

      if (props.toast.message !== '') {
        toastData.type = props.toast.type;
        toastData.visibility = true;
        toastData.message = props.toast.message;
      }

      setTimeout(() => {
        toastData.visibility = false;
      }, 5000);
    });
    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', calcWidth);
    });

    provide('translatedData', props.translatedData);

    return {
      linkClasses,
      periodData,
      dateFormat,
      breadcrumbData,
      activityLink,
      resultLink,
      indicatorLink,
      periodLink,
      toastData,
      showSidebar,
      istopVisible,
    };
  },
  methods: { getTranslatedElement },
});
</script>
