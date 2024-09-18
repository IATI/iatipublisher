<template>
  <div>
    <h4
      v-if="!isChecking"
      class="mb-4 flex items-center gap-1 border-b border-n-20 pb-2 text-sm font-bold"
    >
      <span>
        {{ translatedData['common.common.publishing_activity'] }}
      </span>
      <span
        class="inline-block rounded-full bg-lagoon-10 px-2 py-1 text-xs font-[500] text-spring-50"
      >
        {{ publishingActivityCount }}
      </span>
    </h4>
    <WizardIndex
      v-if="!isChecking"
      :completed-steps="store.state.bulkActivityPublishStatus.completedSteps"
    />

    <div v-if="store?.state?.startBulkPublish || showPublishingActivityModal">
      <PublishingActivity />
    </div>
    <div v-else>
      <div
        v-if="
          store.state.bulkActivityPublishStatus.iatiValidatorLoader ||
          store.state.startValidation ||
          showValidationPopup
        "
      >
        <IatiValidate
          :validation-stats="
            store.state.bulkActivityPublishStatus.validationStats
          "
          :activities-list="
            store.state.bulkActivityPublishStatus.importedActivitiesList
          "
          :permalink="permalink"
          :percentage-width="percentageWidth"
          :error-type="store.state.bulkActivityPublishStatus.error_type"
        />
      </div>
      <div v-else>
        <CheckingActivities
          v-if="!coreElementLoader"
          :deprecation-status-map="deprecationStatusMap"
          :core-in-completed-activities="coreInCompletedActivities"
          :core-completed-activities="coreCompletedActivities"
          :permalink="permalink"
        />
        <RollingLoader
          v-else
          :header="
            translatedData[
              'common.common.checking_your_data_before_publication'
            ]
          "
        />
      </div>
    </div>
  </div>
  <div
    class="flex gap-6 pt-2.5"
    :class="
      store.state.bulkActivityPublishStatus.publishing.response?.status ===
        'completed' &&
      store.state.bulkActivityPublishStatus.publishing.hasFailedActivities?.ids
        ?.length === 0
        ? ' justify-between '
        : 'justify-end'
    "
  >
    <div
      v-if="
        store.state.bulkActivityPublishStatus.publishing.response?.status ===
        'completed'
      "
      class="flex flex-1 items-center"
      :class="
        store.state.bulkActivityPublishStatus.publishing.response?.status ===
          'completed' &&
        store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
          ?.ids?.length === 0
          ? ' justify-between '
          : 'justify-end'
      "
    >
      <p
        v-if="
          store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
            ?.ids?.length === 0
        "
        class="flex items-center gap-3 rounded-md bg-mint p-3 text-xs"
      >
        {{
          translatedData[
            'workflow_frontend.bulk_publish.activity_has_been_published_successfully'
          ]
        }}
      </p>
      <BtnComponent
        type="primary"
        :text="translatedData['common.common.close']"
        class="bg-white px-6 uppercase"
        @click="cancelActivityPublishing(true)"
      />
    </div>
    <template v-else>
      <BtnComponent
        v-if="store?.state?.startBulkPublish || showPublishingActivityModal"
        class="space"
        type=""
        :text="translatedData['common.common.cancel']"
        @click="cancelActivityPublishing()"
      />
      <BtnComponent
        v-else
        class="space"
        type=""
        :text="translatedData['common.common.cancel']"
        @click="cancelValidation()"
      />
      <button
        v-if="
          store.state.bulkActivityPublishStatus.iatiValidatorLoader ||
          (store.state.startBulkPublish &&
            store.state.bulkActivityPublishStatus.publishing.response
              ?.status !== 'completed')
        "
        className="flex items-center gap-1.5 font-bold text-bluecoral border border-bluecoral rounded px-2.5 py-3 text-xs uppercase"
        @click="handleMinimize()"
      >
        <span>
          {{ translatedData['workflow_frontend.bulk_publish.minimize_screen'] }}
        </span>
        <svg-vue icon="open-link" class="rotate-90 text-[10px] text-n-40" />
      </button>
      <template v-if="percentageWidth !== 100">
        <template
          v-if="
            (props.coreInCompletedActivities.length > 0 ||
              props.coreCompletedActivities.length > 0) &&
            !coreElementLoader
          "
        >
          <BtnComponent
            v-if="
              !store.state.bulkActivityPublishStatus.iatiValidatorLoader &&
              !store?.state?.startBulkPublish
            "
            class="bg-white px-6 uppercase"
            type="primary"
            :text="
              translatedData[
                'workflow_frontend.bulk_publish.continue_publishing_anyway'
              ]
            "
            @click="validateActivities()"
          />
        </template>
      </template>

      <template v-else>
        <template v-if="!store?.state?.startBulkPublish">
          <BtnComponent
            class="bg-white px-6 uppercase"
            type="primary"
            :text="`${translatedData['workflow_frontend.bulk_publish.continue_publishing']} (${newSelectedActivities.length})`"
            :disabled="newSelectedActivities.length === 0"
            @click="startBulkPublish()"
          />
          <BulkPublishRevalidatePopup
            :data-changed="dataChanged"
            :show-slide-in="showSlideIn"
            @cancel="handleCancel"
            @reverify="reValidateActivities"
          />
        </template>
      </template>
    </template>
  </div>
</template>

<script setup lang="ts">
import {
  defineProps,
  defineEmits,
  computed,
  watch,
  ref,
  provide,
  watchEffect,
  onMounted,
  inject,
} from 'vue';
import WizardIndex from '../wizardSteps/WizardIndex.vue';
import BtnComponent from 'Components/ButtonComponent.vue';
import CheckingActivities from './checkingActivities/CheckingActivities.vue';
import RollingLoader from './RollingLoaderComponent.vue';
import IatiValidate from './iatiValidate/IatiValidate.vue';
import { useStore } from 'Store/activities/index';
import PublishingActivity from './publishingActivity/PublishingActivity.vue';
import { useSharedMinimize } from 'Composable/useSharedLocalStorage';
import BulkPublishRevalidatePopup from 'Components/BulkPublishRevalidatePopup.vue';
import axios from 'axios';

const store = useStore();
const props = defineProps({
  coreInCompletedActivities: {
    type: Object,
    default: () => ({}),
  },
  coreCompletedActivities: {
    type: Object,
    default: () => ({}),
  },
  deprecationStatusMap: {
    type: Object,
    default: () => ({}),
  },
  permalink: {
    type: String,
    default: () => '',
  },
  coreElementLoader: {
    type: Boolean,
    required: true,
  },

  selectedActivities: {
    type: Array,
    required: true,
  },
  showValidationPopup: {
    type: Boolean,
    required: true,
  },
  publishingActivities: {
    type: Object,
    default: () => ({}),
  },
});

const sharedMinimize = useSharedMinimize();
const newSelectedActivities = ref([] as number[]);
const isChecking = ref(true);
const dataChanged = ref(false);
const showSlideIn = ref(false);

const translatedData = inject('translatedData') as Record<string, string>;

provide('newSelectedActivities', newSelectedActivities);

const emit = defineEmits([
  'cancelValidation',
  'validateActivities',
  'startBulkPublish',
  'cancelBulkPublishing',
]);

const validateActivities = () => {
  isChecking.value = false;
  emit('validateActivities');
};

const percentageWidth = computed(() => {
  return (
    ((store.state.bulkActivityPublishStatus.validationStats.complete +
      store.state.bulkActivityPublishStatus.validationStats.failed) /
      store.state.bulkActivityPublishStatus.validationStats.total) *
    100
  );
});

watch(
  () => percentageWidth?.value,
  (value) => {
    localStorage.setItem('validationPercent', (value ?? 0).toString());
  }
);

const startBulkPublish = async () => {
  const response = await axios.get('/activities/detect-change');

  if (response.data.data.has_changed) {
    dataChanged.value = true;
    setTimeout(() => {
      showSlideIn.value = true;
    }, 300);
  } else {
    store.dispatch('updateStartValidation', false);
    // localStorage.removeItem('validatingActivities');
    store.dispatch('updateStartBulkPublish', true);
    localStorage.removeItem('activityValidating');
    store.state.bulkActivityPublishStatus.completedSteps = [1];
  }
};

const handleCancel = () => {
  if (dataChanged.value) {
    showSlideIn.value = false;
    setTimeout(() => {
      dataChanged.value = false;
    }, 300);
  } else {
    dataChanged.value = true;
  }
};

const reValidateActivities = async () => {
  showSlideIn.value = false;
  setTimeout(() => {
    dataChanged.value = false;
  }, 300);
  emit('validateActivities');
};

const handleMinimize = () => {
  sharedMinimize.value = true;
};

const showPublishingActivityModal = computed(() => {
  return (
    props.publishingActivities &&
    Object.keys(props.publishingActivities).length > 0
  );
});

const cancelActivityPublishing = (isFinalStep?: boolean) => {
  localStorage.setItem('vue-use-local-storage', 'publishingActivities:{}');
  localStorage.removeItem('validatingActivities');
  emit('cancelBulkPublishing');
  if (isFinalStep) {
    window.location.reload();
  }
};

const cancelValidation = () => {
  store.dispatch('updateStartCoreValidation', false);
  emit('cancelValidation');
};

const publishingActivityCount = computed(() => {
  const { bulkActivityPublishStatus } = store.state;
  const publishingActivities =
    bulkActivityPublishStatus?.publishing?.activities;
  const publishingStatus =
    bulkActivityPublishStatus?.publishing?.response?.status;
  const validationStatsTotal =
    bulkActivityPublishStatus?.validationStats?.total || 0;

  if (publishingActivities && Object.keys(publishingActivities).length > 0) {
    if (publishingStatus === 'completed' || publishingStatus === 'processing') {
      return Object.keys(publishingActivities).length;
    }
  }

  if (validationStatsTotal > 0) {
    return validationStatsTotal;
  }

  const coreCompletedCount = props.coreCompletedActivities?.length || 0;
  const coreInCompletedCount = props.coreInCompletedActivities?.length || 0;

  return coreCompletedCount + coreInCompletedCount;
});

watchEffect(() => {
  if (sharedMinimize.value) {
    store.state.isPublishedModalMinimized = sharedMinimize.value;
  }
});

watchEffect(() => {
  if (
    props.coreInCompletedActivities.length === 0 &&
    props.coreCompletedActivities.length > 0
  ) {
    isChecking.value = false;
  }
});

watchEffect(() => {
  const validationPercent = localStorage.getItem('validationPercent');
  const activityValidating = localStorage.getItem('activityValidating');
  const validatingActivities = store.state.validatingActivities;

  if (
    (validationPercent === '100' && activityValidating === 'false') ||
    (validatingActivities !== '' && activityValidating === 'true')
  ) {
    isChecking.value = false;
  }
});

onMounted(() => {
  const publishingStatus = localStorage.getItem('vue-use-local-storage');

  const validatingActivities = localStorage.getItem('validatingActivities');
  if (validatingActivities) {
    store.state.selectedActivities = validatingActivities
      .split(',')
      .map(Number);
  }

  if (publishingStatus) {
    const parsedStatus = JSON.parse(publishingStatus);
    const publishingActivities = parsedStatus.publishingActivities;

    if (publishingActivities && Object.keys(publishingActivities).length > 0) {
      isChecking.value = false;
    }
  }
});
</script>
