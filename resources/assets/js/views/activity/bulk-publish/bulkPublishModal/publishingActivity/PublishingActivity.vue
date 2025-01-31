<template>
  <template
    v-if="
      store.state.bulkActivityPublishStatus.publishing.response?.status ===
      'completed'
    "
  >
    <h3 class="mt-6 pb-2 text-sm font-bold text-bluecoral">
      {{ translatedData['publish.bulk_publish.publishing_completed'] }}
    </h3>
    <div class="rounded-lg border border-n-20">
      <div
        class="rounded-t-lg bg-n-10 px-6 py-4 font-bold leading-[18px] tracking-normal text-n-50"
      >
        {{ translatedData['common.common.activity'] }}
      </div>
      <ul
        class="max-h-[50vh] space-y-4 divide-y divide-n-20 overflow-auto px-6 pb-4 text-sm leading-[22px] tracking-normal text-n-50"
      >
        <li
          v-for="(value, name, index) in store.state.bulkActivityPublishStatus
            .publishing.activities"
          :key="index"
          class="item flex pt-4"
        >
          <div
            class="activity-title grow pr-2 text-sm leading-normal text-n-50"
          >
            {{ value['activity_title'] }}
          </div>
          <div class="shrink-0 text-xl">
            <svg-vue
              v-if="value['status'] === 'completed'"
              class="text-spring-50"
              icon="tick"
            />
            <svg-vue
              v-else-if="value['status'] === 'failed'"
              class="text-crimson-50"
              icon="times-circle"
            />
          </div>
        </li>
      </ul>
    </div>
    <div>
      <div
        v-if="
          store.state.bulkActivityPublishStatus.publishing.hasFailedActivities
            ?.ids?.length > 0
        "
        class="flex items-center justify-between py-2"
      >
        <div class="text-sm font-medium text-crimson-50">
          Some activities have failed to publish.
          {{
            translatedData[
              'publish.bulk_publish.some_activities_have_failed_to_publish'
            ]
          }}
        </div>
        <div
          class="retry flex cursor-pointer items-center text-crimson-50"
          @click="retryPublishing"
        >
          <svg-vue class="mr-1" icon="redo" />
          <span class="text-xs uppercase">{{
            translatedData['common.common.Retry']
          }}</span>
        </div>
      </div>
    </div>
  </template>
  <div v-else>
    <RollingLoader header="Publishing Activities" />
    <p
      class="mt-2.5 rounded-lg bg-paper p-4 text-sm leading-[22px] tracking-normal text-n-50"
    >
      {{
        translatedData['publish.bulk_publish.this_process_may_take_some_time']
      }}
    </p>
  </div>
</template>

<script setup lang="ts">
import RollingLoader from '../RollingLoaderComponent.vue';
import { useStore } from 'Store/activities';

const store = useStore();

const retryPublishing = () => {
  store.dispatch('updatePublishRetry', !store.state.startPublishingRetry);
};
</script>

<style scoped></style>
