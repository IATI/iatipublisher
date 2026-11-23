<template>
  <div v-for="(post, i) in data.content" :key="i" class="title-content">
    <div v-if="post.narrative" class="flex flex-col">
      <span v-if="post.language" class="language mb-1.5">
        (Language: {{ types.languages[post.language] }})
      </span>
      <span v-if="post.narrative" class="max-w-[887px] text-sm">
        {{ post.narrative }}
      </span>
    </div>
    <span v-else class="text-sm italic">Title Missing</span>
    <div v-if="Number(i) !== data.content.length - 1" class="mb-4"></div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';

export default defineComponent({
  name: 'ActivityTitle',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      languages: [];
    }
    const types = inject('types') as Types;
    return { types };
  },
});
</script>
