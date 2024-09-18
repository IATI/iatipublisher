<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="mb-1 text-sm">
      <div v-if="post.legacy_name">{{ post.legacy_name }}</div>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData, 'name')
      }}</span>
    </div>
    <div class="ml-5">
      <table>
        <tr>
          <td>Value</td>
          <td v-if="post.value">
            <span class="description">{{ post.value }}</span>
          </td>
          <td v-else class="italic">
            {{ getTranslatedMissing(translatedData) }}
          </td>
        </tr>
      </table>
      <table>
        <tr>
          <td>Iati-Equivalent</td>
          <td v-if="post.iati_equivalent">
            <span class="description">{{ post.iati_equivalent }}</span>
          </td>
          <td v-else class="italic">
            {{ getTranslatedMissing(translatedData) }}
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import { getTranslatedMissing } from 'Composable/utils';

export default defineComponent({
  name: 'ActivitySector',
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const translatedData = inject('translatedData') as Record<string, string>;

    return { translatedData };
  },
  methods: { getTranslatedMissing },
});
</script>
