<template>
  <tr>
    <td>Title</td>

    <td v-if="titleData.narrative && titleData.narrative[0]?.narrative">
      <template v-for="(title, t) in titleData.narrative" :key="t">
        <div
          class="title-content"
          :class="{
            'mb-3': Number(t) !== titleData.narrative.length - 1,
          }"
        >
          <div class="language subtle-darker mb-1">
            (Language: {{ titleType[title.language] ?? 'N/A' }})
          </div>
          <div class="description text-xs">
            {{ title.narrative ?? '' }}
            <span
              v-if="!title.narrative"
              class="text-xs italic text-light-gray"
            >
              N/A
            </span>
          </div>
        </div>
      </template>
    </td>
    <td v-else>
      <span class="text-xs italic text-light-gray">N/A</span>
    </td>
  </tr>
</template>

<script lang="ts">
import { isEveryValueNull } from 'Composable/utils';
import { defineComponent, toRefs } from 'vue';

export default defineComponent({
  name: 'IndicatorTitle',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
    titleType: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);
    const titleData = data.value;
    return { titleData, isEveryValueNull };
  },
});
</script>
