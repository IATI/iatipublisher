<template>
  <div>
    <div class="flex items-center gap-1">
      <svg-vue icon="warning-activity" />
      <p class="text-sm font-bold text-n-50">
        {{
          translatedData[
            'workflow_frontend.bulk_publish.core_elements_not_complete_in_count_activities'
          ]?.replace(':count', coreInCompletedActivities.length)
        }}
      </p>
    </div>
    <div class="mt-[10px] rounded-lg bg-paper p-4">
      <p
        class="text-sm text-n-50"
        v-html="
          translatedData[
            'workflow_frontend.bulk_publish.review_the_core_completeness_percentage'
          ]
        "
      ></p>
      <p>
        <svg-vue class="h-full w-full" icon="core-completeness" />
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';

defineProps({
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
});

const translatedData = inject('translatedData') as Record<string, string>;
</script>
