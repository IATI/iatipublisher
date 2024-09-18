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
      :title="`${indicatorTitle} - ${translatedData['common.common.indicator_detail']}`"
      :back-link="`${indicatorLink}`"
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
          :text="translatedData['common.common.add_indicator']"
          icon="add"
          :link="`${indicatorLink}/create`"
          class="mr-2.5"
        />
        <Btn
          :text="translatedData['common.common.add_period']"
          icon="add"
          :link="`/indicator/${indicator.id}/period/create`"
          class="mr-2.5"
        />
        <Btn
          :text="translatedData['common.common.edit_indicator']"
          :link="`${indicatorLink}/${indicator.id}/edit`"
        />
      </div>
    </PageTitle>
    <div class="-mt-6 mb-8 ml-[26px] text-n-40">
      {{ translatedData['common.common.indicator_number'] }} :
      {{ indicator.indicator_code }}
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
      class="activities__sidebar fixed left-0 z-[100] block h-screen overflow-y-auto duration-200 lg:hidden"
    >
      <div
        class="indicator sticky top-0 h-full bg-eggshell px-6 py-4 text-n-50"
      >
        <ul class="text-sm font-bold leading-relaxed">
          <li v-for="(rData, r, ri) in indicatorData" :key="ri">
            <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
              <!-- <svg-vue icon="core" class="mr-2 text-base"></svg-vue> -->
              {{ toKebabCase(r) }}
            </a>
          </li>

          <li v-if="periodData.length === 0">
            <a
              :href="`/indicator/${indicator.id}/period/create`"
              :class="linkClasses"
              class="border border-dashed border-n-40"
            >
              <svg-vue icon="add" class="mr-2 text-n-40"></svg-vue>
              add period
            </a>
          </li>
          <li v-else>
            <a v-smooth-scroll href="#period" :class="linkClasses"> period </a>
          </li>
        </ul>
      </div>
    </aside>

    <div class="activities">
      <aside class="activities__sidebar hidden lg:block">
        <div
          class="indicator sticky top-0 rounded-lg bg-eggshell px-6 py-4 text-n-50"
        >
          <ul class="text-sm font-bold leading-relaxed">
            <li v-for="(rData, r, ri) in indicatorData" :key="ri">
              <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
                <!-- <svg-vue icon="core" class="mr-2 text-base"></svg-vue> -->
                {{ toKebabCase(r) }}
                <span
                  v-if="isMandatoryForIndicator(r)"
                  class="required-icon px-1"
                >
                  *
                </span>
              </a>
            </li>

            <li v-if="periodData.length === 0">
              <a
                :href="`/indicator/${indicator.id}/period/create`"
                :class="linkClasses"
                class="border border-dashed border-n-40"
              >
                <svg-vue icon="add" class="mr-2 text-n-40"></svg-vue>
                add period
              </a>
            </li>
            <li v-else>
              <a v-smooth-scroll href="#period" :class="linkClasses">
                period
              </a>
            </li>
          </ul>
        </div>
      </aside>
      <div class="activities__content">
        <div></div>
        <div class="bg-white px-4 py-5">
          <div
            class="elements-detail wider"
            :class="{
              'mb-10': countDocumentLink(indicatorData.document_link) > 0,
            }"
          >
            <div class="category flex">
              {{ indicatorTitle }}
            </div>

            <div class="ml-4">
              <div class="indicators elements-detail">
                <table>
                  <tbody>
                    <template
                      v-if="indicatorData.title[0].narrative.length > 0"
                    >
                      <TitleElement
                        id="title"
                        :data="indicatorData.title[0]"
                        :title-type="types.language"
                      />
                    </template>
                    <Ascending id="ascending" :data="indicatorData.ascending" />

                    <Measure
                      id="measure"
                      :data="indicatorData.measure"
                      :measure-type="types.indicatorMeasure"
                    />

                    <AggregationStatus
                      id="aggregation_status"
                      :data="indicatorData.aggregation_status"
                    />

                    <template
                      v-if="indicatorData.description[0].narrative.length > 0"
                    >
                      <Description
                        id="description"
                        :data="indicatorData.description[0]"
                        :desc-type="types.language"
                      />
                    </template>

                    <template v-if="indicatorData.reference.length > 0">
                      <Reference
                        id="reference"
                        :data="indicatorData.reference"
                        :ref-type="types"
                      />
                    </template>

                    <template v-if="indicatorData.baseline.length > 0">
                      <Baseline
                        id="baseline"
                        :data="indicatorData.baseline"
                        :base-type="types"
                      />
                    </template>

                    <Period id="period" :data="periodData" />
                  </tbody>
                </table>
              </div>
            </div>
            <div
              v-if="!isEveryValueNull(indicatorData.document_link)"
              id="document_link"
            >
              <div class="title mb-4">
                <div class="item elements-detail wider">
                  <table class="mb-2">
                    <tr>
                      <td class="pl-4">Document Link:</td>
                    </tr>
                  </table>
                </div>
                <div
                  class="divider mb-4 h-px w-full border-b border-n-20"
                ></div>
              </div>
              <div class="ml-4">
                <DocumentLink
                  :data="indicatorData.document_link"
                  :type="types"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  toRefs,
  onMounted,
  reactive,
  provide,
  ref,
  watch,
  computed,
  onUnmounted,
  watchEffect,
} from 'vue';

//component
import Btn from 'Components/buttons/Link.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';

//helper
import {
  countDocumentLink,
  isEveryValueNull,
  toKebabCase,
} from 'Composable/utils';

import {
  TitleElement,
  Measure,
  Ascending,
  AggregationStatus,
  Description,
  Reference,
  Baseline,
  DocumentLink,
  Period,
} from './elements/Index';

//composable
import getActivityTitle from 'Composable/title';

export default defineComponent({
  name: 'IndicatorDetail',
  components: {
    TitleElement,
    Measure,
    Ascending,
    AggregationStatus,
    Description,
    Reference,
    Baseline,
    DocumentLink,
    Period,
    Btn,
    PageTitle,
    Toast,
  },
  props: {
    activity: {
      type: Object,
      required: true,
    },
    resultTitle: {
      type: Object,
      required: true,
    },
    indicator: {
      type: Object,
      required: true,
    },
    period: {
      type: Array,
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
    const linkClasses =
      'flex items-center w-full bg-white rounded p-2 text-sm text-n-50 font-bold leading-normal mb-2 shadow-default';

    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    let { indicator, activity, period, resultTitle } = toRefs(props);
    const showSidebar = ref(false);
    const positionY = ref(0);
    const screenWidth = ref(0);

    //indicator
    const indicatorData = indicator.value.indicator;
    const periodData = period.value;

    // vue provides
    const parentData = {
      activity: activity.value.id,
      result: indicator.value.result_id,
      indicator: indicator.value.id,
    };

    provide('parentData', parentData);

    const activityId = activity.value.id,
      activityLink = `/activity/${activityId}`,
      resultId = indicator.value.result_id,
      defaultLanguage = activity.value.default_field_values?.language,
      activityTitle = getActivityTitle(activity.value.title, defaultLanguage),
      resultTitled = getActivityTitle(
        resultTitle.value[0].narrative,
        defaultLanguage
      ),
      resultLink = `${activityLink}/result/${resultId}`,
      indicatorLink = `/result/${resultId}/indicator`,
      indicatorTitle = getActivityTitle(
        indicatorData.title[0].narrative,
        defaultLanguage
      );

    const calcWidth = (event) => {
      screenWidth.value = event.target.innerWidth;
      if (screenWidth.value > 1024) {
        document.documentElement.style.overflow = 'auto';
      } else {
        showSidebar.value &&
          (document.documentElement.style.overflow = 'hidden');
      }
    };

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
        title: resultTitled,
        link: resultLink,
      },
      {
        title: props.translatedData['common.common.indicator_list'],
        link: `/result/${resultId}/indicator`,
      },
      {
        title: indicatorTitle,
        link: '',
      },
    ];

    const handleScroll = () => {
      positionY.value = window.scrollY;
    };

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

    const istopVisible = computed(() => {
      return positionY.value === 0;
    });

    const isMandatoryForIndicator = (elementOrAttribute: string) => {
      const mandatoryElementOrAttribute = ['measure', 'title'];

      return mandatoryElementOrAttribute.includes(elementOrAttribute);
    };
    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', calcWidth);
    });

    watch(
      () => showSidebar.value,
      (sidebar) => {
        if (sidebar) {
          document.documentElement.style.overflow = 'hidden';
        } else document.documentElement.style.overflow = 'auto';
      }
    );

    provide('translatedData', props.translatedData);

    return {
      linkClasses,
      indicatorTitle,
      indicatorData,
      activityLink,
      resultLink,
      indicatorLink,
      breadcrumbData,
      toastData,
      periodData,
      showSidebar,
      istopVisible,
      countDocumentLink,
      isMandatoryForIndicator,
      isEveryValueNull,
    };
  },
  methods: { toKebabCase },
});
</script>
