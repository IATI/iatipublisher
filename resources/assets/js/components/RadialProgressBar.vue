<template>
  <div class="rpb">
    <radial-progress-bar
      :diameter="80"
      :completed-steps="completedPercent"
      :total-steps="totalPercent"
      :is-clockwise="false"
      inner-stroke-color="#17997b26"
      start-color="#17997B"
      stop-color="#17997B"
      :stroke-width="6"
      :inner-stroke-width="6"
    >
      <!-- Your inner content here -->
      <p class="z-10 text-base text-spring-50">{{ completedPercent }} %</p>
    </radial-progress-bar>
  </div>
</template>

<script lang="ts">
import { ref, defineComponent } from 'vue';
import RadialProgressBar from 'vue3-radial-progress';

export default defineComponent({
  components: {
    RadialProgressBar,
  },
  props: {
    percent: {
      type: Number,
      required: false,
      default: 0,
    },
  },
  setup(props) {
    const completedPercent = ref(props.percent);
    const totalPercent = ref(100);

    return {
      completedPercent,
      totalPercent,
    };
  },
});
</script>
