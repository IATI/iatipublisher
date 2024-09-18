<template>
  <div class="text-sm">
    {{
      code[0].disbursement_channel_code
        ? type.disbursementChannel[code[0].disbursement_channel_code]
        : ''
    }}
    <span
      v-if="!code[0].disbursement_channel_code"
      class="text-xs italic text-light-gray"
      >N/A</span
    >
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';

export default defineComponent({
  name: 'TransactionDisbursementChannel',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    interface ArrayObject {
      [index: number]: { disbursement_channel_code: string };
    }
    const { data } = toRefs(props);

    const code = data.value as ArrayObject;

    const type = inject('types');

    return { code, type };
  },
});
</script>
