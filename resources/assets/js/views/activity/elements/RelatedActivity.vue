<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="related-content text-sm">
      <div class="category">
        <span v-if="post.relationship_type">{{
          types.relatedActivityType[post.relationship_type]
        }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData, 'type')
        }}</span>
      </div>
      <div>
        <span v-if="post.activity_identifier">{{
          post.activity_identifier
        }}</span>
        <span v-else class="italic">{{
          getTranslatedMissing(translatedData, 'reference')
        }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import dateFormat from 'Composable/dateFormat';
import { getTranslatedMissing } from 'Composable/utils';

export default defineComponent({
  name: 'RelatedActivity',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      relatedActivityType: [];
      languages: [];
    }

    const types = inject('types') as Types;

    return { types, dateFormat };
  },
  methods: { getTranslatedMissing },
});
</script>
