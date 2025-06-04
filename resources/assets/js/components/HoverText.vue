<template>
  <div class="help">
    <button>
      <svg-vue
        class="text-n-40"
        :class="{
          'text-tiny': iconSize,
          iconSize: !iconSize,
        }"
        icon="help"
      />
    </button>
    <div
      :class="[
        position === 'right'
          ? 'help__text left-0 ' + width
          : position === 'top-left'
          ? 'help__text !top-auto bottom-full right-0 ' + width
          : 'help__text right-0 ' + width,
      ]"
    >
      <span class="close-help absolute top-4 right-2 z-[50] cursor-pointer"
        ><svg
          class="h-6 w-6"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 12 14"
          fill="none"
        >
          <path
            fill="#2A2F30"
            d="M4.588 3.5 7.212.88a.418.418 0 0 0-.591-.592L4 2.913 1.38.288a.418.418 0 1 0-.593.591L3.413 3.5.787 6.12a.417.417 0 0 0 .136.684.417.417 0 0 0 .456-.091L4 4.088l2.62 2.625a.417.417 0 0 0 .684-.136.417.417 0 0 0-.092-.456L4.588 3.5Z"
          /></svg
      ></span>
      <span class="font-bold text-bluecoral">{{ name }}</span>
      <!-- eslint-disable vue/no-v-html -->
      <p class="!text-black" v-html="hoverText" />
      <!--eslint-enable-->
      <a v-if="link" :href="link" class="inline-block font-bold text-bluecoral">
        {{ translatedData['common.common.learn_more'] }}
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';

export default defineComponent({
  props: {
    name: {
      type: String,
      required: false,
      default: '',
    },
    hoverText: {
      type: String,
      required: true,
    },
    width: {
      type: String,
      required: false,
      default: 'w-60',
    },
    position: {
      type: String,
      required: false,
      default: '',
    },
    link: {
      type: String,
      required: false,
      default: '',
    },
    iconSize: {
      type: String,
      required: false,
      default: '',
    },
    showIatiReference: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  setup() {
    const translatedData = inject('translatedData') as Record<string, string>;

    return {
      translatedData,
    };
  },
});
</script>

<style lang="scss">
.help {
  @apply relative;

  &__text {
    @apply invisible absolute top-4 z-20 space-y-1.5 rounded bg-eggshell p-4 text-left text-xs text-n-40 opacity-0 duration-200;
    // Changed ease-out to linear
    transition: all 0.3s linear;
    box-shadow: 0px 4px 40px rgb(0 0 0 / 10%);

    p a {
      font-weight: 700;
    }
  }
}
</style>
