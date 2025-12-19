<template>
  <div
    v-if="hasErrors"
    class="relative bg-white duration-300"
    :class="{
      'h-[55px]': !show,
      'mb-5  ': !isEmpty || !show,
      'mb-10 h-[full] pb-4 ': show,
    }"
  >
    <Loader v-if="isLoaderVisible" />
    <div
      :show="!show"
      :class="
        show
          ? 'border-l-2 border-l-salmon-50 pb-2.5 pl-4 pr-6 pt-4 text-sm leading-relaxed text-n-50 duration-300 ease-out'
          : 'alert relative border-l-2 border-l-salmon-50 duration-300 ease-out'
      "
    >
      <div class="flex items-center justify-between">
        <div class="flex h-5 items-center space-x-4">
          <div :show="show" class="flex items-center">
            <svg-vue
              icon="warning-activity"
              class="mr-2 grow-0 text-base text-salmon-50"
            ></svg-vue>
            <span class="text-sm font-bold text-n-50">
              {{ errorCount }}
              {{ translatedData['activity_index.error_message.alerts'] }}
            </span>
          </div>
          <div
            v-if="!errorData.default_setting"
            :class="
              show && !errorData.default_setting ? 'text-show' : 'text-hide'
            "
          >
            <svg-vue icon="red-dot" class="text-[6px]"></svg-vue>
            <span class="text-sm font-bold text-bluecoral">
              {{
                translatedData[
                  'activity_index.error_message.complete_your_setup'
                ]
              }}
            </span>
          </div>
          <div
            v-if="!errorData.publisher_active"
            :class="show ? 'text-show' : 'text-hide'"
          >
            <svg-vue icon="red-dot" class="text-[6px]"></svg-vue>
            <span class="text-sm font-bold text-n-50">
              {{
                translatedData[
                  'activity_index.error_message.publisher_is_inactive'
                ]
              }}
            </span>
          </div>
        </div>
        <div>
          <button
            class="text-sm leading-relaxed text-bluecoral"
            @click="show = !show"
          >
            {{
              !show
                ? translatedData['common.common.show_more']
                : translatedData['activity_index.error_message.show_less']
            }}
          </button>
        </div>
      </div>
    </div>

    <div
      :class="show ? 'border-show duration-300' : 'border-hide duration-300'"
    ></div>

    <div v-if="!errorData.default_setting" class="ml-4 mr-6">
      <TransitionRoot
        :show="show"
        as="template"
        enter="transition-all duration-300 ease-out"
        enter-from="-translate-y-32 opacity-0 w-[65%] mx-auto"
        enter-to="translate-y-0 opacity-100 w-full mx-auto"
        leave="transition-all duration-300 ease-out"
        leave-from="translate-y-0 opacity-100 w-full mx-auto"
        leave-to="-translate-y-32 opacity-0 w-[65%] mx-auto"
      >
        <div class="alert mb-2.5">
          <div class="alert__container">
            <div class="alert__content">
              <svg-vue icon="red-dot" class="text-[6px]"></svg-vue>
              <span>{{
                translatedData[
                  'activity_index.error_message.complete_your_setup'
                ]
              }}</span>
            </div>
            <div class="ml-5">
              <p
                v-html="
                  translatedData[
                    'activity_index.error_message.we_recommend_that_you_complete_default_values'
                  ]
                "
              ></p>
            </div>
          </div>
        </div>
      </TransitionRoot>
    </div>
    <div v-if="!errorData.registry_approved" class="ml-4 mr-6">
      <TransitionRoot
        :show="show"
        as="template"
        enter="transition-all duration-300 ease-out"
        enter-from="-translate-y-11 opacity-0 w-[90%] mx-auto"
        enter-to="translate-y-0 opacity-100 w-full mx-auto"
        leave="transition-all duration-300 ease-out"
        leave-from="translate-y-0 opacity-100 w-full mx-auto"
        leave-to="-translate-y-11 opacity-0 w-[90%] mx-auto"
      >
        <div class="alert mb-2.5">
          <div class="alert__container">
            <div class="alert__content">
              <svg-vue icon="red-dot" class="text-[6px]"></svg-vue>
              <span>{{
                translatedData[
                  'common.common.your_organisation_is_pending_approval_by_the_iati_team'
                ]
              }}</span>
            </div>

            <div class="ml-5 text-left">
              <p>
                <a href="https://iatistandard.org/en/guidance/get-support/"
                  >Contact Support</a
                >
                {{
                  translatedData[
                    'common.common.if_your_account_has_not_been_approved_within_two_working_days_of_registering'
                  ]
                }}
              </p>
            </div>
          </div>
        </div>
      </TransitionRoot>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, onMounted, reactive, ref } from 'vue';
import { TransitionRoot } from '@headlessui/vue';
import Loader from '../components/Loader.vue';
import axios from 'axios';
import { ToastInterface } from 'Interfaces/ToastInterface';

defineProps({
  isEmpty: {
    type: Boolean,
    required: false,
    default: true,
  },
});

const show = ref(false);
const hasErrors = ref(false);
const errorCount = ref(0);

const toastData = inject('toastData') as ToastInterface;
const errorData = reactive({
  account_verified: false,
  publisher_active: false,
  registry_approved: false,
  default_setting: false,
});
const isLoaderVisible = ref(false);

onMounted(async () => {
  axios
    .all([axios.get('/setting/status'), axios.get('/organisation/status')])
    .then(
      axios.spread(function (setting_res) {
        const response = setting_res.data;

        errorData.default_setting = response?.data?.default_status;
        errorData.publisher_active = true;
        errorData.registry_approved =
          response?.data?.registry_approved ?? false;

        let groupedError = ['default_setting', 'account_verified'];

        for (const error in errorData) {
          if (!errorData[error] && groupedError.indexOf(error) === -1) {
            errorCount.value += 1;
          }
        }

        if (!(errorData.account_verified && errorData.default_setting)) {
          errorCount.value += 1;
        }

        if (Object.values(errorData).indexOf(false) > -1) {
          hasErrors.value = true;
        }
      })
    );
});

const translatedData = inject('translatedData') as Record<string, string>;
</script>

<style lang="scss" scoped>
.alert {
  @apply rounded bg-camel-10 p-4 pr-6 text-sm leading-relaxed text-n-50;

  &__container {
    @apply flex flex-col leading-6;
  }

  &__content {
    @apply flex items-center space-x-4;

    span {
      @apply text-sm font-bold text-n-50;
    }
  }

  &__message {
    @apply flex items-center space-x-1;
  }
}

.text-show {
  @apply invisible flex items-center space-x-2 opacity-0 duration-300;
  transform: translate(-50px, 30px);
}

.text-hide {
  @apply flex -translate-y-0 items-center space-x-2 duration-300;
}

.border-hide::before {
  @apply absolute left-0 top-0 rounded bg-salmon-50 duration-300 ease-out;
  width: 2px;
  height: 100%;
  content: '';
  transform: translateY(-100%);
}

.border-show::before {
  @apply absolute left-0 top-0 rounded bg-salmon-50 duration-300 ease-out;
  width: 2px;
  height: 100%;
  content: '';
  transform: translateY(0%);
}
</style>
