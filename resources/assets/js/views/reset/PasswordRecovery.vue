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
import { onMounted, ref } from 'vue';
import LanguageService from 'Services/language';

const translatedData = ref({});

onMounted(() => {
  LanguageService.getTranslatedData('common,public')
    .then((response) => {
      translatedData.value = response.data;
    })
    .catch((error) => console.log(error));
});
</script>
