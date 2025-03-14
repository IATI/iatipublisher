<template>
  <div class="page-title mb-4">
    <div class="flex gap-4 md:items-end">
      <div class="title shrink-0 grow-0">
        <div class="mb-2 text-caption-c1 text-n-40 xl:mb-4">
          <nav aria-label="breadcrumbs" class="breadcrumb">
            <p>
              <span class="last font-bold">{{
                translatedData['common.common.your_activities']
              }}</span>
            </p>
          </nav>
        </div>
        <div class="inline-flex flex-col space-y-2 md:flex-row md:items-center">
          <h4 class="mr-4 text-3xl font-bold xl:text-heading-4">
            {{ translatedData['common.common.your_activities'] }}
          </h4>
          <div class="tooltip-btn">
            <button class="">
              <svg-vue icon="question-mark" />
              <span>{{
                translatedData['common.common.what_is_an_activity']
              }}</span>
            </button>
            <div class="tooltip-btn__content z-[1]">
              <div class="content">
                <div class="mb-1.5 text-caption-c1 font-bold text-bluecoral">
                  {{ translatedData['common.common.what_is_an_activity'] }}
                </div>
                <p
                  v-html="
                    translatedData[
                      'common.common.what_is_an_activity_description'
                    ]
                  "
                ></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        class="actions relative inline-flex grow flex-col items-end justify-end space-y-2 xl:flex-row"
      >
        <Toast
          v-if="toastMessage.visibility"
          class="whitespace-nowrap lg:mr-3.5"
          :message="toastMessage.message"
          :type="toastMessage.type"
        />

        <ErrorPopupForPublish
          v-if="errorData.visibility"
          :message="errorData.message"
          :extra-info="
            errorData.extra_info !== null ? errorData.extra_info : undefined
          "
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
        <div class="inline-flex justify-end">
          <div
            class="inline-flex shrink-0 flex-col items-end justify-end gap-3 lg:flex-row"
          >
            <RefreshToastMessage
              v-if="refreshToastMsg.visibility"
              :message="refreshToastMsg.refreshMessage"
              :type="refreshToastMsg.refreshMessageType"
            />
            <div class="flex flex-col items-end gap-2 lg:flex-row">
              <div class="flex gap-2">
                <DownloadActivityButton />
                <BtnComponent
                  v-if="store.state.selectedActivities.length > 0"
                  type="secondary"
                  :text="publishedSelectedMessage"
                  icon="approved-cloud"
                  :disabled="
                    store.state.selectedActivities.length === 0 ||
                    store.state.selectedActivities.length > 100 ||
                    isDisabledPublish
                  "
                  :tooltip-text="
                    store.state.selectedActivities.length > 100
                      ? getYouCanOnlyPublish100ActivitiesAtATimeMessage(
                          store.state.selectedActivities.length
                        )
                      : ''
                  "
                  @click="checkPublish"
                />
                <!--TODO: Baki-->
                <PublishSelected ref="publishRef" />
              </div>
              <div class="flex gap-2">
                <DeleteButton
                  v-if="store.state.selectedActivities.length === 1"
                />
                <AddActivityButton />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { inject, ref, Ref, defineProps, computed } from 'vue';
import DownloadActivityButton from './DownloadActivityButton.vue';
import AddActivityButton from './AddActivityButton.vue';
import Toast from 'Components/ToastMessage.vue';
import RefreshToastMessage from 'Activity/bulk-publish/RefreshToast.vue';
import PublishSelected from 'Activity/bulk-publish/PublishSelected.vue';
import DeleteButton from 'Components/buttons/DeleteButton.vue';
import BtnComponent from 'Components/ButtonComponent.vue';
// Vuex Store
import { useStore } from 'Store/activities/index';
import ErrorPopupForPublish from 'Components/ErrorPopupForPublish.vue';
import { ToastInterface } from 'Interfaces/ToastInterface';
import { ErrorInterface } from 'Interfaces/ErrorInterface';

interface RefreshToastMsgTypeface {
  visibility: boolean;
  refreshMessageType: boolean;
  refreshMessage: string;
}

const translatedData = inject('translatedData') as Record<string, string>;
const refreshToastMsg = inject('refreshToastMsg') as RefreshToastMsgTypeface;
const toastMessage = inject('toastData') as ToastInterface;
const errorData = inject('errorData') as ErrorInterface;
const store = useStore();
const publishRef: Ref<typeof PublishSelected | null> = ref(null);

defineProps({
  isDisabledPublish: {
    type: Boolean,
    required: true,
  },
});

const checkPublish = () => {
  if (publishRef.value) {
    publishRef.value.checkPublish();
  }
};

// Computed property for the published selected message
const publishedSelectedMessage = computed(() => {
  const length = store.state.selectedActivities.length;
  let baseMessage =
    translatedData['activity_index.page_title.publish_selected'];
  baseMessage = replaceCountPlaceHolderWithCount(baseMessage, length);

  return baseMessage;
});

function replaceCountPlaceHolderWithCount(text: string, count: number) {
  return text?.replace(':count', count.toString());
}

function getYouCanOnlyPublish100ActivitiesAtATimeMessage(length: number) {
  const baseMessage =
    translatedData[
      'activity_index.page_title.you_can_only_publish_up_to_100_activities_at_a_time'
    ];
  let dynamicMessage =
    translatedData[
      'activity_index.page_title.please_remove_activities_from_selection_to_publish'
    ];

  if (length > 1) {
    dynamicMessage =
      translatedData[
        'activity_index.page_title.please_remove_activity_from_selection_to_publish'
      ];
  }

  dynamicMessage = replaceCountPlaceHolderWithCount(dynamicMessage, length);

  return `${baseMessage} ${dynamicMessage}`;
}
</script>
