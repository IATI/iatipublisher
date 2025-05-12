<template>
  <div>
    <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
      <div class="flex items-center">
        <template v-for="(bc, d) in breadcrumbData" :key="d">
          <template v-if="Number(d) === 0">
            <a class="whitespace-nowrap font-bold" href="/">
              {{ bc.title }}
            </a>
          </template>
          <template v-else>
            <div class="breadcrumb__title max-w-[200px]">
              <span class="breadcrumb__title overflow-hidden text-n-30">
                <a v-if="bc.link" :href="bc.link">
                  {{ bc.title }}
                </a>
                <span v-else>{{ bc.title }}</span>
              </span>
              <span class="ellipsis__title--hover w-[calc(100%_+_35px)]">
                {{ bc.title }}
              </span>
            </div>
          </template>
          <span
            v-if="Number(d) !== breadcrumbData.length - 1"
            class="separator mx-4"
          >
            /
          </span>
        </template>
      </div>
    </nav>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs } from 'vue';
import getActivityTitle from 'Composable/title';

export default defineComponent({
  name: 'BreadCrumb',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    const { data } = toRefs(props);
    const breadcrumbData = data.value;
    return { getActivityTitle, breadcrumbData };
  },
});
</script>
