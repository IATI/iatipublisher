<template>
  <div class="mt-14">
    <div class="reset text-center">
      <h2>
        {{ translatedData['common.common.password_recovery'] }}
      </h2>
      <p class="mb-5">
        {{ translatedData['public.forgot_password.confirm_page.subheading'] }}
      </p>
      <svg-vue class="verification m-auto" icon="ic-verification" />
    </div>
  </div>
</template>
<script lang="ts" setup>
import { defineProps } from 'vue';

defineProps({
  translatedData: {
    type: Object,
    required: true,
  },
});
</script>
