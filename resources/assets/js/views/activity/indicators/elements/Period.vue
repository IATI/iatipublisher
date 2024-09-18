<template>
  <tr v-if="data.length === 0">
    <td></td>
    <td>
      <div>
        <NotYet
          :link="`/indicator/${id.indicator}/period/create`"
          :description="
            translatedData['common.common.you_havent_added_any_periods_yet']
          "
          btn-text="Add period"
          class="max-w-[442px]"
        />
      </div>
    </td>
  </tr>

  <tr v-else>
    <td>Periods</td>
    <td>
      <div class="inline-flex gap-4">
        <div>
          <div
            v-for="(item, key) in data"
            :key="key"
            class="flex"
            :class="{
              'mb-1': Number(key) !== data.length - 1,
            }"
          >
            <div>
              <a
                class="text-xs text-n-50"
                :href="`/indicator/${id.indicator}/period/${item.id}`"
              >
                {{
                  dateFormat(item.period.period_start[0].date, 'MMMM DD, YYYY')
                }}
                -
                {{
                  dateFormat(item.period.period_end[0].date, 'MMMM DD, YYYY')
                }}
              </a>
            </div>
            <div class="ml-2">
              <Btn
                :text="translatedData['common.common.edit']"
                icon="edit"
                :link="`/indicator/${id.indicator}/period/${item.id}/edit`"
              />
            </div>
          </div>
        </div>
        <div class="shrink-0">
          <Btn
            :text="translatedData['common.common.show_full_period_list']"
            icon=""
            design="bgText"
            :link="`/indicator/${id.indicator}/period`"
            class="-mt-1 mr-2.5"
          />
        </div>
      </div>
    </td>
  </tr>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';

import dateFormat from 'Composable/dateFormat';
import Btn from 'Components/buttons/Link.vue';

export default defineComponent({
  name: 'IndicatorPeriod',
  components: {
    Btn,
  },
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface ParentData {
      activity: string;
      result: string;
      indicator: string;
    }
    const id = inject('parentData') as ParentData;
    const translatedData = inject('translatedData') as Record<string, string>;
    return { id, dateFormat, translatedData };
  },
});
</script>
