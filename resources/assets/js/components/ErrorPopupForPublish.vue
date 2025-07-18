<template>
  <div>
    <div class="fixed inset-0 z-40 bg-black/20"></div>
    <div
      class="fixed left-1/2 top-[50vh] z-50 w-[550px] max-w-[90%] -translate-x-1/2 -translate-y-1/2 rounded-lg bg-white p-6"
    >
      <h3 class="mb-4 text-lg font-medium">
        <svg-vue icon="alert" class="mr-2 inline text-crimson-40"></svg-vue
        ><span class="font-bold">{{ props.title }}</span>
      </h3>
      <p
        v-if="typeof props.message === 'string'"
        class="list-disc rounded-md bg-salmon-10 p-3 font-medium"
      >
        <span
          v-if="
            props.extraInfo &&
            props.extraInfo.error_type === 'max_size_exception'
          "
        >
          <span class="text-md">
            {{
              translatedData[
                'activity_index.error_popup_for_publish.the_organisation_has_surpassed_the_tools_maximum_allowed_file_size'
              ]
            }}
            <span
              class="cursor-pointer font-bold text-bluecoral"
              @click="
                () => {
                  close();
                  openZendeskLauncher();
                }
              "
            >
              {{
                translatedData[
                  'activity_index.error_popup_for_publish.contact_info'
                ]
              }}
            </span>
          </span>
        </span>
        <span
          v-else-if="
            props.extraInfo &&
            props.extraInfo.error_type === 'batch_size_exception'
          "
        >
          <span class="text-md">
            {{
              translatedData[
                'activity_index.error_popup_for_publish.the_selected_items_exceed_the_allowed_size_for_publishing_at_once'
              ]
            }}
          </span>
        </span>
        <span v-else>
          {{ props.message }}
        </span>
      </p>
      <ul v-else class="list-disc rounded-md bg-salmon-10 p-3 font-medium">
        <li
          v-for="(item, index) in props.message"
          :key="index"
          class="my-3 ml-6"
        >
          <span>
            {{ item }}
          </span>
          <template
            v-if="
              item ===
              translatedData[
                'activity_index.error_popup_for_publish.your_organisation_data_not_published'
              ]
            "
          >
            <a class="text-base font-semibold" href="/organisation">
              {{
                translatedData[
                  'activity_index.error_popup_for_publish.go_to_organisation'
                ]
              }}
            </a>
          </template>
          <template
            v-if="
              item === 'Your organisation is pending approval by the IATI team.'
            "
          >
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
          </template>
        </li>
      </ul>
      <div class="mt-4 flex flex-row-reverse">
        <button
          class="rounded bg-bluecoral px-5 py-2 font-semibold text-white"
          @click="close"
        >
          {{ translatedData['common.common.close'] }}
        </button>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import {
  defineProps,
  PropType,
  defineEmits,
  onMounted,
  onUnmounted,
  inject,
} from 'vue';

const emit = defineEmits(['close-popup']);
const props = defineProps({
  message: {
    required: true,
    type: (Array as PropType<Array<string>>) || String,
  },
  title: { type: String, required: true },
  extraInfo: {
    type: Object as PropType<{ error_type?: string }>,
    default: null,
  },
});

const translatedData = inject('translatedData') as Record<string, string>;
const close = () => {
  emit('close-popup', 'closed');
};
onMounted(() => {
  document.documentElement.style.overflow = 'hidden';
});
onUnmounted(() => {
  document.documentElement.style.overflow = 'auto';
});

function openZendeskLauncher() {
  if (window.zE && window.zE.activate) {
    window.zE.activate();
  }
}

declare global {
  interface Window {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    //@ts-ignore
    zE: any;
  }
}
</script>
