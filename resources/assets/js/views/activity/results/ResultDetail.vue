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
      :title="translatedData['common.common.result_detail']"
      :back-link="`${activityLink}/result`"
    >
      <div class="flex items-center space-x-3">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <a :href="`${activityLink}/result/create`">
          <Btn
            :text="translatedData['common.common.edit_result']"
            :link="`${resultLink}/edit`"
            icon="edit"
          />
        </a>
      </div>
    </PageTitle>
    <div class="-mt-6 mb-8 ml-[26px] text-n-40">
      {{ translatedData['common.common.result_number'] }} :
      {{ result.result_code }}
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
          ? `  ${
              istopVisible
                ? 'top-[60px] h-[calc(100vh_-_60px)]'
                : 'top-[0px] h-[100vh]'
            } translate-x-[0px]`
          : `${
              istopVisible
                ? 'top-[60px] h-[calc(100vh_-_60px)]'
                : 'top-[0px] h-[100vh]'
            } -translate-x-[150%]`
      "
      class="activities__sidebar fixed left-0 z-[100] block overflow-y-auto bg-eggshell duration-200 lg:hidden"
    >
      <div
        class="indicator sticky top-0 h-full rounded-lg bg-eggshell px-6 py-4 text-n-50"
      >
        <ul class="text-sm font-bold leading-relaxed">
          <li v-for="(rData, r, ri) in resultsData" :key="ri">
            <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
              <!-- <svg-vue icon="moon" class="mr-2 text-base"></svg-vue> -->
              {{ toKebabCase(r) }}
            </a>
          </li>
          <li v-if="hasIndicators">
            <a v-smooth-scroll href="#indicator" :class="linkClasses">
              <!-- <svg-vue icon="moon" class="mr-2 text-base"></svg-vue> -->
              {{ getTranslatedElement(translatedData, 'indicator') }}
              <span class="required-icon px-1"> * </span>
            </a>
          </li>
          <li v-if="!hasIndicators">
            <a
              :href="`/result/${result.id}/indicator/create`"
              :class="linkClasses"
              class="border border-dashed border-n-40"
            >
              <svg-vue icon="add" class="mr-2 text-n-40"></svg-vue>
              {{ translatedData['common.common.add_indicator'] }}
              <span class="required-icon px-1"> * </span>
            </a>
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
            <li v-for="(rData, r, ri) in resultsData" :key="ri">
              <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
                <!-- <svg-vue icon="moon" class="mr-2 text-base"></svg-vue> -->
                {{ toKebabCase(r) }}
                <span v-if="isMandatoryForResult(r)" class="required-icon px-1"
                  >*</span
                >
              </a>
            </li>
            <li v-if="hasIndicators">
              <a v-smooth-scroll href="#indicator" :class="linkClasses">
                <!-- <svg-vue icon="moon" class="mr-2 text-base"></svg-vue> -->
                {{
                  getTranslatedElement(
                    translatedData,
                    'indicator'
                  ).toLowerCase()
                }}
                <span class="required-icon px-1">*</span>
              </a>
            </li>
            <li v-if="!hasIndicators">
              <a
                :href="`/result/${result.id}/indicator/create`"
                :class="linkClasses"
                class="border border-dashed border-n-40"
              >
                <svg-vue icon="add" class="mr-2 text-n-40"></svg-vue>
                {{ translatedData['common.common.add_indicator'] }}
                <span class="required-icon px-1">*</span>
              </a>
            </li>
          </ul>
        </div>
      </aside>
      <div class="activities__content">
        <div></div>

        <div
          class="activities__content--elements -mx-3 -mt-3 flex-wrap xl:flex"
        >
          <template v-for="(post, key) in result.result" :key="key">
            <ResultElement
              :data="post"
              :element-name="key.toString()"
              :edit-url="`/activity/${result.activity_id}/result/${result.id}`"
              :width="
                key.toString() === 'title' ||
                key.toString() === 'description' ||
                key.toString() === 'document_link' ||
                key.toString() === 'reference'
                  ? 'full'
                  : ''
              "
              :types="types"
              :hover-text="
                element['attributes'][key]
                  ? element['attributes'][key]['hover_text'] ?? ''
                  : element['sub_elements'][key]['hover_text'] ?? ''
              "
            />
          </template>

          <!-- Indicator -->
          <template v-if="hasIndicators">
            <Indicator :result="result" :type="types" tool-tip="Indicator" />
          </template>
        </div>

        <!-- indicator button -->
        <a
          v-if="!hasIndicators"
          :href="`/result/${result.id}/indicator/create`"
          class="add_indicator flex w-full rounded border border-dashed border-n-40 bg-white px-4 py-3 text-xs leading-normal"
        >
          <div class="grow text-left italic">
            {{
              translatedData['common.common.you_havent_added_any_indicator_yet']
            }}
          </div>
          <div
            class="flex shrink-0 items-center font-bold uppercase text-bluecoral"
          >
            <svg-vue icon="add" class="mr-1 shrink-0 text-base"></svg-vue>
            <span class="grow text-[10px]">{{
              translatedData['common.common.add_new_indicator']
            }}</span>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  defineComponent,
  toRefs,
  onMounted,
  ref,
  computed,
  watch,
  onUnmounted,
  reactive,
  watchEffect,
  provide,
} from 'vue';

//component
import ResultElement from './ResultElement.vue';
import Indicator from 'Activity/results/elements/Indicator.vue';
import Btn from 'Components/buttons/Link.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';

//composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';
import { getTranslatedElement, toKebabCase } from 'Composable/utils';
import transactionElement from '../transactions/TransactionElement.vue';

export default defineComponent({
  name: 'ResultDetail',
  components: {
    ResultElement,
    Indicator,
    Btn,
    PageTitle,

    Toast,
  },
  props: {
    activity: {
      type: Object,
      required: true,
    },
    result: {
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
    const linkClasses =
      'flex items-center w-full bg-white rounded p-2 text-sm text-n-50 font-bold leading-normal mb-2 shadow-default';
    const positionY = ref(0);
    const showSidebar = ref(false);
    const screenWidth = ref(0);

    let { result, activity } = toRefs(props);
    const hasIndicators = result.value.indicators.length > 0 ? true : false;
    const resultsData = result.value.result;
    // const deprecationStatusMap = resultsData.deprecation_status_map;
    delete resultsData.deprecation_status_map;

    const activityId = activity.value.id,
      activityTitle = activity.value.title,
      activityLink = `/activity/${activityId}`,
      resultTitle = getActivityTitle(resultsData.title[0].narrative, 'en'),
      resultLink = `${activityLink}/result/${result.value.id}`,
      defaultLanguage = activity.value.default_field_values?.language;
    const handleScroll = () => {
      positionY.value = window.scrollY;
    };
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
        title: props.translatedData['common.common.your_activities'],
        link: '/activities',
      },
      {
        title: getActivityTitle(activityTitle, defaultLanguage),
        link: activityLink,
      },
      {
        title: resultTitle,
        link: '',
      },
    ];

    /**
     * Using Translated Breadcrumb titles
     */
    watchEffect(() => {
      if (props.translatedData) {
        breadcrumbData[0].title =
          props.translatedData['common.common.your_activities'];
      }
    });

    const istopVisible = computed(() => {
      return positionY.value === 0;
    });

    onMounted(() => {
      window.addEventListener('scroll', handleScroll);
      window.addEventListener('resize', calcWidth);

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

    const calcWidth = (event) => {
      screenWidth.value = event.target.innerWidth;
      if (screenWidth.value > 1024) {
        document.documentElement.style.overflow = 'auto';
      } else {
        showSidebar.value &&
          (document.documentElement.style.overflow = 'hidden');
      }
    };

    const isMandatoryForResult = (elementOrAttribute: string) => {
      const mandatoryElementOrAttribute = ['type', 'title', 'indicator'];

      return mandatoryElementOrAttribute.includes(elementOrAttribute);
    };

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
      activityLink,
      resultTitle,
      resultLink,
      linkClasses,
      dateFormat,
      hasIndicators,
      resultsData,
      breadcrumbData,
      toastData,
      showSidebar,
      istopVisible,
      isMandatoryForResult,
    };
  },
  computed: {
    transactionElement() {
      return transactionElement;
    },
  },
  methods: { toKebabCase, getTranslatedElement },
});
</script>
