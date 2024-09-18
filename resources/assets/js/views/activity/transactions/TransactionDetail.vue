<template>
  <div class="bg-paper px-4 pb-[71px] pt-4 xl:px-10">
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
      :title="`${
        transactionData.reference && transactionData.reference !== ''
          ? transactionData.reference
          : getTranslatedUntitled(translatedData)
      } - ${translatedData['common.common.transaction_detail']}`"
      :back-link="`${activityLink}/transaction`"
    >
      <div class="flex items-center space-x-3">
        <Toast
          v-if="toastData.visibility"
          :message="toastData.message"
          :type="toastData.type"
          class="mr-3"
        />
        <Btn
          :text="translatedData['common.common.edit_transaction']"
          :link="`${activityLink}/transaction/${transaction.id}/edit`"
          icon="edit"
        />
      </div>
    </PageTitle>
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
      <div class="indicator rounded-lg bg-eggshell px-6 py-4 text-n-50">
        <ul class="text-sm font-bold leading-relaxed">
          <li v-for="(rData, r, ri) in transactionData" :key="ri">
            <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
              <span>{{ r }}</span>
              <span v-if="isMandatoryIcon(r)" class="required-icon px-1"
                >*</span
              >
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
            <li v-for="(rData, r, ri) in transactionData" :key="ri">
              <a v-smooth-scroll :href="`#${String(r)}`" :class="linkClasses">
                <span>{{ toKebabCase(r) }}</span>
                <span v-if="isMandatoryIcon(r)" class="required-icon px-1"
                  >*</span
                >
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
          <template v-for="(post, key) in transactionData" :key="key">
            <TransactionElement
              v-if="key.toString() !== 'deprecation_status_map'"
              :data="post"
              :element-name="key.toString()"
              :edit-url="`/activity/${transaction.activity_id}/transaction/${transaction.id}`"
              :width="
                key.toString() === 'value' ||
                key.toString() === 'transaction_type' ||
                key.toString() === 'transaction_date' ||
                key.toString() === 'reference' ||
                key.toString() === 'disbursement_channel' ||
                key.toString() === 'humanitarian'
                  ? ''
                  : 'full'
              "
              :hover-text="
                element['attributes'][key]
                  ? element['attributes'][key]['hover_text'] ?? ''
                  : element['sub_elements'][key]['hover_text'] ?? ''
              "
              :types="types"
              :deprecation-status-map="
                transaction['deprecation_status_map'][key.toString()]
              "
            />
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {
  computed,
  defineComponent,
  onMounted,
  onUnmounted,
  provide,
  reactive,
  ref,
  toRefs,
  watch,
  watchEffect,
} from 'vue';
//components
import Btn from 'Components/buttons/Link.vue';
import PageTitle from 'Components/sections/PageTitle.vue';
import Toast from 'Components/ToastMessage.vue';
//composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';
import TransactionElement from './TransactionElement.vue';
import {
  getTranslatedElement,
  getTranslatedUntitled,
  toKebabCase,
  toTitleCase,
} from 'Composable/utils';

export default defineComponent({
  name: 'TransactionDetail',
  components: {
    TransactionElement,
    Btn,
    PageTitle,
    Toast,
  },
  props: {
    activity: {
      type: Object,
      required: true,
    },
    transaction: {
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
    const { activity, transaction } = toRefs(props);

    const linkClasses =
      'flex items-center w-full bg-white rounded p-2 text-sm text-n-50 font-bold leading-relaxed mb-2 shadow-default';
    const showSidebar = ref(false);
    const positionY = ref(0);
    const screenWidth = ref(0);

    const toastData = reactive({
      visibility: false,
      message: '',
      type: true,
    });
    const handleScroll = () => {
      positionY.value = window.scrollY;
    };
    const istopVisible = computed(() => {
      return positionY.value === 0;
    });

    // titles
    const transactionData = transaction.value.transaction;
    const calcWidth = (event) => {
      screenWidth.value = event.target.innerWidth;
      if (screenWidth.value > 1024) {
        document.documentElement.style.overflow = 'auto';
      } else {
        showSidebar.value &&
          (document.documentElement.style.overflow = 'hidden');
      }
    };

    const activityId = activity.value.id,
      activityTitle = getActivityTitle(activity.value.title, 'en'),
      activityLink = `/activity/${activityId}`,
      transactionLink = `${activityLink}/transaction/${transaction.value.id}`;

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('resize', calcWidth);
    });

    watch(
      () => showSidebar.value,
      (sidebar) => {
        if (sidebar) {
          document.documentElement.style.overflow = 'hidden';
        } else {
          document.documentElement.style.overflow = 'auto';
        }
      }
    );
    /**
     * Breadcrumb data
     */
    const breadcrumbData = [
      {
        title: props.translatedData['common.common.your_activities'],
        link: '/activity',
      },
      {
        title: activityTitle,
        link: activityLink,
      },
      {
        title: props.translatedData['common.common.transaction_list'],
        link: `/activity/${activityId}/transaction`,
      },
      {
        title: props.translatedData['elements.label.transaction'],
        link: '',
      },
    ];

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

    const isMandatoryIcon = (r) => {
      return (
        r.toString() === 'value' ||
        r.toString() === 'transaction_type' ||
        r.toString() === 'transaction_date'
      );
    };

    provide('translatedData', props.translatedData);

    return {
      activityTitle,
      dateFormat,
      transactionData,
      linkClasses,
      breadcrumbData,
      activityLink,
      transactionLink,
      toastData,
      isMandatoryIcon,
      showSidebar,
      istopVisible,
    };
  },
  methods: { toKebabCase, getTranslatedElement, getTranslatedUntitled },
});
</script>
