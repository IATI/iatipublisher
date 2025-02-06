<template>
  <div class="smooth -mb-7 px-5 py-7 sm:px-10 sm:py-14 md:-mb-20">
    <div class="mx-auto max-w-[700px]">
      <section class="rounded bg-white py-4 shadow-textbox sm:py-8">
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="my-2 text-2xl font-bold text-bluecoral">
            {{ translatedData['public.about.what_is_iati_publisher'] }}
          </h3>
          <p class="mt-4 text-base">
            {{ translatedData['public.about.iati_introduction'] }}
          </p>
        </article>
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="my-2 text-2xl font-bold text-bluecoral">
            {{ translatedData['public.about.use_iati.title'] }}:
          </h3>
          <ul class="ml-4 mt-4 list-disc text-base text-bluecoral">
            <li class="text-base">
              <span class="text-black">{{
                translatedData['public.about.use_iati.list_item_one']
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData['public.about.use_iati.list_item_two']
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData['public.about.use_iati.list_item_three']
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData['common.common.run_automatic_checks']
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData['public.about.use_iati.list_item_five']
              }}</span>
            </li>
          </ul>
          <p class="mt-4 text-base">
            {{ translatedData['public.about.use_iati.paragraph_one'] }}
          </p>
          <p class="mt-4 text-base">
            {{ translatedData['public.about.use_iati.paragraph_two'] }}:
            <a
              target="_blank"
              rel="noopener noreferrer"
              href="mailto: support@iatistandard.org"
            >
              support@iatistandard.org </a
            >.
          </p>
        </article>
        <article class="ounded px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="my-2 text-2xl font-bold text-bluecoral">
            {{ translatedData['public.about.development_of_iati.title'] }}
          </h3>
          <p
            class="mt-4 text-base"
            v-html="
              translatedData['public.about.development_of_iati.paragraph_one']
            "
          ></p>
        </article>
      </section>
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import LanguageService from 'Services/language';

export default defineComponent({
  setup() {
    const translatedData = ref({});

    onMounted(() => {
      LanguageService.getTranslatedData('common,public')
        .then((response) => {
          translatedData.value = response.data;
        })
        .catch((error) => console.log(error));
    });

    return {
      translatedData,
    };
  },
});
</script>
