<template>
  <div
    v-if="
      percentageWidth !== 100 &&
      store.state.bulkActivityPublishStatus.iatiValidatorLoader
    "
  >
    <RollingLoader
      :header="
        translatedData['common.common.checking_your_data_before_publication']
      "
    />
    <p
      class="mt-2.5 rounded-lg bg-paper p-4 text-sm leading-[22px] tracking-normal text-n-50"
    >
      {{
        translatedData[
          'workflow_frontend.bulk_publish.this_process_may_take_some_time'
        ]
      }}
    </p>
  </div>

  <div v-else class="relative pt-2.5">
    <div v-if="hasError">
      <div class="flex items-center gap-1">
        <svg-vue class="text-xl text-crimson-50" icon="warninig-activity-red" />
        <h3 class="text-sm font-bold uppercase text-bluecoral">
          {{
            translatedData[
              'workflow_frontend.bulk_publish.validation_incomplete'
            ]
          }}
        </h3>
      </div>
      <h6
        v-if="errorType === 'generic'"
        class="my-2 text-sm"
        v-html="
          getTranslatedValidationIncompleteMessage(
            Object.keys(validActivities).length,
            Object.keys(activitiesList).length
          )
        "
      ></h6>

      <h6 v-if="errorType === 'max_merge_size_exception'" class="my-2 text-sm">
        <b class="text-[16px] text-crimson-50">
          {{
            translatedData[
              'workflow_frontend.bulk_publish.exceeded_max_publish_size'
            ]
          }}
          <a
            class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
            href="mailto:support@iatistandard.org"
            target="_blank"
          >
            {{ translatedData['common.common.contact_support_label'] }}
          </a>
        </b>
      </h6>
    </div>
    <div v-else>
      <h6 class="text-sm font-bold text-bluecoral">
        {{
          translatedData[
            'workflow_frontend.bulk_publish.data_checking_complete_click_continue_to_publish'
          ]
        }}
      </h6>
    </div>
    <div class="mt-2 rounded-md border border-n-20">
      <div
        class="flex items-center gap-1.5 rounded-t-lg bg-n-10 px-6 py-[14px] uppercase text-n-50"
      >
        <svg-vue class="text-xl" icon="warning-activity" />
        <span class="text-xs font-bold">
          {{
            translatedData[
              'workflow_frontend.bulk_publish.activities_marked_with_this_symbol_have_data_quality_issues'
            ]
          }}
        </span>
      </div>
      <ul class="max-h-[50vh] divide-y divide-n-20 overflow-auto duration-200">
        <template v-if="Object.keys(validActivities).length > 0">
          <li
            v-for="(value, key) in validActivities"
            :key="Number(key)"
            class="px-4 pt-4 pb-4 text-sm leading-[22px] tracking-normal text-n-50"
            :class="{ 'bg-[#f6f0ff]': value.top_level_error === 'critical' }"
          >
            <div class="flex items-center justify-between">
              <div>
                <label
                  class="checkbox_container"
                  :class="{ disabled: value.top_level_error === 'critical' }"
                >
                  <input
                    v-model="newSelectedActivities"
                    type="checkbox"
                    :value="key"
                    :disabled="value.top_level_error === 'critical'"
                  />
                  <span class="checkmark"></span>
                </label>
                <div
                  class="bulk_publish_activity_title max-w-[60ch] pl-6"
                  :title="value?.title"
                >
                  {{
                    value?.title?.length > 100
                      ? value?.title.slice(0, 100) + '...'
                      : value?.title ?? ''
                  }}
                </div>

                <div v-if="value.top_level_error === 'critical'">
                  <span class="text-xs italic text-crimson-50">
                    ({{
                      translatedData[
                        'workflow_frontend.bulk_publish.the_activity_contains_critical_errors_and_cannot_be_published'
                      ]
                    }})
                  </span>
                </div>
              </div>
              <div class="flex shrink-0 items-center gap-6">
                <svg-vue
                  v-if="
                    value?.is_valid === false ||
                    value?.top_level_error === 'error'
                  "
                  class="text-xl"
                  icon="warning-activity"
                />
                <a
                  v-if="
                    value?.top_level_error &&
                    (value?.top_level_error === 'error' ||
                      value?.top_level_error === 'critical')
                  "
                  :href="`${permalink}${key}`"
                  target="_blank"
                  class="flex items-center gap-[2px]"
                >
                  {{
                    translatedData[
                      'workflow_frontend.bulk_publish.open_in_new_tab'
                    ]
                  }}
                  <svg-vue class="text-base" icon="open-link-small" />
                </a>
              </div>
            </div>
          </li>
        </template>
        <template v-else>
          <li class="pt-4 text-sm leading-[22px] tracking-normal text-n-50">
            {{
              translatedData[
                'workflow_frontend.bulk_publish.no_activities_are_ready_to_publish'
              ]
            }}
          </li>
        </template>
      </ul>
    </div>
    <div
      v-if="activeTab === 1 && Object.keys(validActivities).length > 0"
      class="w-[100px] pt-3"
    >
      <label
        for="selectAll"
        class="checkbox_container !flex"
        :class="{ disabled: isAllCriticalErrors }"
      >
        <span
          class="inline-block pl-3 pt-1 text-xs font-bold uppercase leading-[18px]"
        >
          {{ translatedData['workflow_frontend.bulk_publish.select_all'] }}
        </span>
        <input
          id="selectAll"
          type="checkbox"
          :checked="
            newSelectedActivities.length === Object.keys(validActivities).length
          "
          :disabled="isAllCriticalErrors"
          @change="(e) => selectAllActivities(e)"
        />
        <span class="checkmark"></span>
      </label>
    </div>
  </div>
</template>
<script setup lang="ts">
import {
  watch,
  defineProps,
  ref,
  onMounted,
  inject,
  Ref,
  computed,
  watchEffect,
} from 'vue';

import { useStore } from 'Store/activities';
import RollingLoader from '../RollingLoaderComponent.vue';

const store = useStore();
const props = defineProps({
  validationStats: {
    type: Object,
    required: true,
  },

  activitiesList: {
    type: Object,
    required: false,
    default: () => ({}),
  },
  permalink: {
    type: String,
    required: true,
  },
  percentageWidth: {
    type: Number,
    required: true,
  },
  errorType: {
    type: String,
    required: true,
  },
});

const newSelectedActivities = inject('newSelectedActivities') as Ref<number[]>;
const translatedData = inject('translatedData') as Record<string, string>;

const activeTab = ref(1);

const isAllCriticalErrors = computed(() => {
  return (
    Object.keys(validActivities.value)
      .map(Number)
      .filter((id) => {
        return validActivities.value[id].top_level_error === 'critical';
      }).length === Object.keys(validActivities.value).length
  );
});

//setting data from local storage to vuex ,to preserve state when window is reloaded
onMounted(() => {
  //to check if validation need to be show of not when navigated or refreshed
  let showPopup = Boolean(localStorage.getItem('activityValidating'));
  if (showPopup) {
    store.dispatch('updateStartValidation', true);
  }
  removeCheckFromCritical();
});

const hasError = computed(() => {
  return store.state.bulkActivityPublishStatus.showValidationError;
});

const selectAllActivities = (event) => {
  if (event.target.checked) {
    newSelectedActivities.value = Object.keys(validActivities.value)
      .map(Number)
      .filter((id) => props.activitiesList[id].top_level_error !== 'critical');
  } else {
    newSelectedActivities.value = [];
  }
};

watch(
  () => newSelectedActivities.value,
  (value) => {
    if (
      store.state.bulkActivityPublishStatus.validationStats.total ==
      store.state.bulkActivityPublishStatus.validationStats.complete +
        store.state.bulkActivityPublishStatus.validationStats.failed
    ) {
      store.dispatch('updateValidatingActivities', value.join(','));
    }
  },
  { deep: true }
);

const removeCheckFromCritical = () => {
  newSelectedActivities.value = Object.keys(
    store.state.bulkActivityPublishStatus.importedActivitiesList
  )
    .map(Number)
    .filter(
      (id) =>
        store.state.bulkActivityPublishStatus.importedActivitiesList[id]
          .top_level_error !== 'critical'
    );
};

const validActivities = computed(() => {
  return Object.fromEntries(
    Object.entries(props.activitiesList).filter(
      ([, value]) => value.status !== 'failed'
    )
  );
});

function getTranslatedValidationIncompleteMessage(
  count: number,
  totalCount: number
): string {
  return translatedData[
    'workflow_frontend.bulk_publish.activities_could_only_be_validated_due_to_server_error'
  ]
    .replace(':count', String(count))
    .replace(':totalCount', String(totalCount));
}

watchEffect(() => {
  if (validActivities.value) {
    removeCheckFromCritical();
  }
});

watch(
  () =>
    Object.fromEntries(
      Object.entries(props.activitiesList).filter(
        ([, value]) => value.status !== 'failed'
      )
    ),
  (value) => {
    const ids = Object.keys(value);
    newSelectedActivities.value = ids.map((key) => parseInt(key));
  },
  {
    deep: true,
    immediate: true,
  }
);
</script>

<style scoped lang="scss">
.checkbox_container {
  display: block;
  position: relative;
  padding-left: 10px;
  cursor: pointer;
  font-size: 22px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}

/* Hide the browser's default checkbox */
.checkbox_container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Create a custom checkbox */
.checkmark {
  position: absolute;
  top: 3px;
  left: 0;
  height: 17px;
  width: 17px;
  border-radius: 2px;
  @apply border-2 border-n-20;
}

.checkbox_container.disabled input ~ .checkmark {
  cursor: not-allowed;
  @apply bg-n-20;
}

.checkbox_container:hover.disabled input ~ .checkmark {
  @apply border-n-20;
}

/* On mouse-over, add a grey background color */
.checkbox_container:hover input ~ .checkmark {
  @apply border-spring-50;
}

/* When the checkbox is checked, add a blue background */
.checkbox_container input:checked ~ .checkmark {
  @apply border-spring-50 bg-spring-50;
}

/* Create the checkmark/indicator (hidden when not checked) */
.checkmark:after {
  content: '';
  position: absolute;
  display: none;
}

/* Show the checkmark when checked */
.checkbox_container input:checked ~ .checkmark:after {
  display: block;
}

/* Style the checkmark/indicator */
.checkbox_container .checkmark:after {
  left: 4px;
  top: 1px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  -webkit-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  transform: rotate(45deg);
}
</style>
