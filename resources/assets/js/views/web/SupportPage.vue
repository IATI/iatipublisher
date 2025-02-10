<template>
  <div class="smooth -mb-7 px-5 py-7 sm:px-10 sm:py-14 md:-mb-20">
    <div class="mx-auto max-w-[700px]">
      <section class="rounded bg-white py-4 shadow-textbox sm:py-8">
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="text-2xl font-bold text-bluecoral">
            {{ translatedData['common.common.support'] }}
          </h3>

          <p class="mt-4 text-base">
            {{ translatedData['public.support.paragraph_one'] }}
            <a
              target="_blank"
              rel="noopener noreferrer"
              href="mailto:support@iatistandard.org"
              >support@iatistandard.org.</a
            >
          </p>
          <p
            class="mt-4 text-base"
            v-html="translatedData['public.support.paragraph_two']"
          ></p>
        </article>
      </section>
    </div>
  </div>
</template>
<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import LanguageService from 'Services/language';

const translatedData = ref({});

onMounted(() => {
  LanguageService.getTranslatedData('workflow_frontend,common,public')
    .then((response) => {
      translatedData.value = response.data;
      console.log(translatedData.value);
    })
    .catch((error) => console.log(error));
});
</script>
