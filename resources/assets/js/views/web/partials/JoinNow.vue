<template>
  <div
    class="right join-now m-auto flex basis-2/4 items-center rounded-l-lg rounded-r-lg bg-white px-5 py-5 sm:px-3 md:my-0 md:rounded-l-none xl:px-14"
  >
    <div class="right__container flex flex-col">
      <h2 class="mb-2 hidden sm:block">
        {{ translatedData['common.common.join_now'] }}
      </h2>
      <span class="mb-8 text-n-40 xl:pr-6">
        {{ translatedData['public.login.join_now_section.to_begin_text'] }}
      </span>
      <a href="/iati/register" class="right__content mb-6">
        <div class="right__icon">
          <svg-vue class="text-6xl" icon="default-1" />
        </div>
        <div class="details mx-4 xl:px-1">
          <span class="text-sm font-bold text-bluecoral">{{
            translatedData['public.login.join_now_section.new_to_iati_label']
          }}</span>
          <p
            class="text-xs leading-5 text-n-40"
            v-html="
              translatedData['public.login.join_now_section.new_to_iati_text']
            "
          ></p>
        </div>
        <div>
          <svg-vue class="right__arrow text-2xl" icon="right-arrow" />
        </div>
      </a>
      <a href="/register" class="right__content mb-4">
        <div class="right__icon">
          <svg-vue class="text-6xl" icon="default-2" />
        </div>
        <div class="details mx-4 xl:px-1">
          <span class="text-sm font-bold text-bluecoral">{{
            translatedData[
              'public.login.join_now_section.my_organisation_has_registered_label'
            ]
          }}</span>
          <p
            class="text-xs leading-5 text-n-40"
            v-html="
              translatedData[
                'public.login.join_now_section.my_organisation_has_registered_text'
              ]
            "
          ></p>
        </div>
        <div>
          <svg-vue class="right__arrow text-2xl" icon="right-arrow" />
        </div>
      </a>
      <span class="text-sm text-n-40">
        {{ translatedData['public.login.join_now_section.information_text'] }}
        <a
          class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
          href="mailto:support@iatistandard.org"
          target="_blank"
          >{{ translatedData['common.common.contact_support'] }}</a
        ></span
      >
    </div>
  </div>
</template>
<script setup lang="ts">
import { defineProps } from 'vue';

defineProps({
  translatedData: {
    type: Object,
    required: true,
  },
});
</script>
