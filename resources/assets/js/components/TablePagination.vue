<template>
  <nav class="pagination" aria-label="Pagination">
    <a
      class="prev-btn"
      :class="{
        'pointer-events-none': data.last_page <= 1,
      }"
      aria-disabled="true"
      @click="previousPage"
    >
      <svg-vue icon="arrow-left"></svg-vue>
      <span class="">{{ translatedData['common.common.previous'] }}</span>
    </a>

    <span v-if="data.last_page < 6" class="flex"
      ><a
        v-for="(index, i) in data.last_page"
        :key="index"
        :class="active_page === index ? 'current' : ''"
        @click="changePage(i + 1)"
      >
        {{ index }}
      </a></span
    >
    <span v-else class="flex">
      <a :class="active_page === 1 ? 'current' : ''" @click="changePage(1)">
        1
      </a>
      <span v-if="active_page < 5" class="flex">
        <a
          v-for="(index, i) in 4"
          :key="index"
          :class="active_page === index + 1 ? 'current' : ''"
          @click="changePage(i + 2)"
        >
          {{ index + 1 }}
        </a>
        <span class="pagination-dots">...</span>
      </span>
      <span v-else-if="active_page > data.last_page - 4" class="flex">
        <span class="pagination-dots">...</span>
        <a
          v-for="index in lastpages"
          :key="index"
          :class="active_page === index ? 'current' : ''"
          @click="changePage(+index)"
        >
          {{ index }}
        </a>
      </span>
      <span v-else class="flex">
        <span class="pagination-dots">...</span>
        <a
          v-for="index in midpages"
          :key="index"
          :class="active_page === index ? 'current' : ''"
          @click="changePage(+index)"
        >
          {{ index }}
        </a>
        <span class="pagination-dots">...</span>
      </span>

      <a
        :class="active_page === data.last_page ? 'current' : ''"
        @click="changePage(data.last_page)"
      >
        {{ data.last_page }}
      </a>
    </span>
    <a
      class="next-btn"
      :class="{
        'pointer-events-none': data.last_page <= 1,
      }"
      @click="nextPage"
    >
      <span class="">{{ translatedData['common.common.next'] }}</span>
      <svg-vue icon="arrow-right" />
    </a>
  </nav>
</template>

<script lang="ts">
import { defineComponent, computed, ref, watch, inject } from 'vue';

export default defineComponent({
  name: 'PaginationComponent',
  components: {},
  props: {
    data: {
      type: [Object],
      required: true,
    },
    reset: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  emits: ['fetchActivities'],
  setup(props, { emit }) {
    const active_page = ref(1);
    const last_pagelist = ref();
    const mid_pagelist = ref();
    const translatedData = inject('translatedData') as Record<string, string>;

    watch(
      () => props.reset,
      (value) => {
        if (value) {
          active_page.value = 1;
        }
      }
    );

    watch(active_page, () => {
      emit('fetchActivities', active_page.value);
    });

    const lastpages = computed(() => {
      return last_pagelist.value;
    });

    const midpages = computed(() => {
      return mid_pagelist.value;
    });

    function updateActivePage(page: number) {
      active_page.value = page;
    }

    function changePage(pageNum: number) {
      active_page.value = pageNum;
    }

    function nextPage() {
      active_page.value =
        active_page.value === props.data.last_page ? 1 : active_page.value + 1;
    }

    watch(
      () => active_page.value,
      (currentPage) => {
        last_pagelist.value = Array.from(
          Array(props.data.last_page),
          (_, index) => index + 1
        );
        last_pagelist.value = last_pagelist.value.filter((value) => {
          return (
            value > props.data.last_page - 5 && props.data.last_page != value
          );
        });

        mid_pagelist.value = Array.from(
          Array(currentPage + 2),
          (_, index) => index + 1
        );
        mid_pagelist.value = mid_pagelist.value.filter((value) => {
          return value > currentPage - 3;
        });
      }
    );

    function previousPage() {
      active_page.value =
        active_page.value === 1 ? props.data.last_page : active_page.value - 1;
    }

    return {
      props,
      active_page,
      nextPage,
      previousPage,
      changePage,
      lastpages,
      midpages,
      translatedData,
    };
  },
});
</script>
