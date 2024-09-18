<template>
  <div class="text-sm">
    {{ flowData[0].flow_type ? type.flowType[flowData[0].flow_type] : '' }}
    <span v-if="!flowData[0].flow_type" class="text-xs italic text-light-gray"
      >N/A</span
    >
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';

export default defineComponent({
  name: 'TransactionFlowType',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    interface ArrayObject {
      [index: number]: { flow_type: string };
    }

    const { data } = toRefs(props);

    const flowData = data.value as ArrayObject;

    const type = inject('types');

    return { flowData, type };
  },
});
</script>
