<template>
  <div class="text-sm">
    {{
      tsData[0].tied_status_code
        ? type.tiedStatusType[tsData[0].tied_status_code]
        : ''
    }}
    <span
      v-if="!tsData[0].tied_status_code"
      class="text-xs italic text-light-gray"
      >N/A</span
    >
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';

export default defineComponent({
  name: 'TransactionTiedStatus',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    const { data } = toRefs(props);

    interface ArrayObject {
      [index: number]: { tied_status_code: string };
    }
    const tsData = data.value as ArrayObject;
    const type = inject('types');
    return { tsData, type };
  },
});
</script>
