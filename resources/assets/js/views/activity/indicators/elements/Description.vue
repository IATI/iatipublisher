<template>
  <tr>
    <td>Description</td>
    <td v-if="!isEveryValueNull(descriptionData) && !descriptionData.narrative">
      <template v-for="(description, t) in descriptionData.narrative" :key="t">
        <div
          class="description-content"
          :class="{
            'mb-3': Number(t) !== descriptionData.narrative.length - 1,
          }"
        >
          <div class="language subtle-darker">
            (Language:
            {{ description.language ? descType[description.language] : ''
            }}<span
              v-if="!description.language"
              class="text-xs italic text-light-gray"
              >N/A</span
            >)
          </div>
          <div class="description text-xs">
            {{ description.narrative }}
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
  name: 'IndicatorDescription',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
    descType: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);
    const descriptionData = data.value;
    return { descriptionData, isEveryValueNull };
  },
});
</script>
