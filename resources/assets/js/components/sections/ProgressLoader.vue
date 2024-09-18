<template>
  <div class="progress_bar">
    <div class="progress_bar__content">
      <div class="progress_bar__wrapper">
        <div class="progress_bar__viewer">
          <div class="progress_bar__shimmer"></div>
        </div>
      </div>
      <div
        class="progress_bar__state"
        :data-start="text"
        :data-end="translatedData['common.common.almost_there']"
      ></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, onMounted, onUnmounted } from 'vue';

defineProps({
  text: {
    type: String,
    required: true,
  },
  changeText: {
    type: Boolean,
    required: false,
    default: true,
  },
  translatedData: {
    type: Object,
    required: true,
  },
});

onMounted(() => {
  document.body.classList.add('overflow-y-hidden');
  const supportButton: HTMLElement = document.querySelector(
    '#launcher'
  ) as HTMLElement;

  if (supportButton !== null) {
    supportButton.style.display = 'none';
  }
});

onUnmounted(() => {
  document.body.classList.remove('overflow-y-hidden');
  const supportButton: HTMLElement = document.querySelector(
    '#launcher'
  ) as HTMLElement;

  if (supportButton !== null) {
    supportButton.style.display = 'block';
  }
});
</script>

<style lang="scss" scoped>
.progress_bar {
  @apply fixed  left-0 flex w-full items-center justify-center bg-white;
  top: 0 !important;
  z-index: 999999;
  height: 100vh;

  &__wrapper {
    @apply w-[250px] overflow-hidden rounded-2xl;
    background-color: #c4c4c4;
    overflow: hidden;
  }

  &__viewer {
    @apply bg-spring-50;
    border-radius: 4px;
    height: 4px;
    transform: scaleX(0);
    transform-origin: 0 0;
  }

  &.animate-loader &__viewer {
    animation: FillAnimation 6s linear forwards;
  }

  &.animate-loader &__state::before {
    content: '';
    animation: TextChange 6s linear forwards;
  }

  &__content {
    @apply flex flex-col;
  }

  &__state {
    @apply mt-6 text-center text-sm font-bold leading-normal text-blue-50;
  }
}

@keyframes FillAnimation {
  0% {
    transform: scaleX(0);
  }
  100% {
    transform: scaleX(1);
  }
}

@keyframes TextChange {
  0% {
    content: attr(data-start);
  }

  95% {
    content: attr(data-start);
  }

  100% {
    content: attr(data-end);
  }
}
</style>
