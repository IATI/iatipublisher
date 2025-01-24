<template>
  <div class="flex items-end">
    <span
      :class="{
        'text-[64px]': value[0].amount,
      }"
    >
      {{ value[0].amount ? Number(value[0].amount).toLocaleString() : '' }}
      <span v-if="!value[0].amount" class="text-xs italic text-light-gray"
        >N/A</span
      >
    </span>
    <span v-if="value[0].amount" class="mb-5">{{ value[0].currency }}</span>
  </div>
  <div v-if="value[0].amount" class="text-sm">
    {{
      value[0].date
        ? `${translatedData['common.common.valued_at']} ${dateFormat(
            value[0].date
          )}`
        : ''
    }}
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, toRefs } from 'vue';
import dateFormat from './../../../../composable/dateFormat';

export default defineComponent({
  name: 'TransactionValue',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    const { data } = toRefs(props);
    const translatedData = inject('translatedData') as Record<string, string>;

    interface ArrayObject {
      [index: number]: { amount: string; currency: string; date: Date };
    }
    const value = data.value as ArrayObject;
    return { value, dateFormat, translatedData };
  },
});
</script>
