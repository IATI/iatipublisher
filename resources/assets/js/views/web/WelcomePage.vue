<template>
  <section class="main mt-10 sm:mx-10 xl:mx-24 xl:px-1">
    <div
      class="main__container rounded-lg bg-bluecoral pb-8 sm:mb-10 md:mb-20 md:flex md:bg-none md:pb-0"
    >
      <div
        class="left flex flex-col items-center justify-center bg-bluecoral px-3 pb-72 pt-5 text-white sm:rounded-l-lg sm:rounded-r-lg sm:px-5 sm:pt-10 md:basis-2/4 md:rounded-r-none md:pb-16 lg:pb-44 lg:pt-44 xl:px-24"
      >
        <div class="left__container rounded-lg p-5 sm:p-10">
          <span class="left__title font-bold">IATI Publisher</span>
          <p class="pt-2 sm:pb-8 sm:pt-6">
            {{
              translatedData[
                'public.login.iati_publishing_tool_section.welcome_text'
              ]
            }}
          </p>
        </div>
      </div>

      <SignIn
        :message="message"
        :intent="intent"
        :translated-data="translatedData"
      />
    </div>
  </section>
</template>

<script>
import { defineComponent, ref } from 'vue';
import SignIn from './partials/SignIn.vue';

export default defineComponent({
  components: {
    SignIn,
  },
  props: {
    page: {
      type: String,
      required: false,
      default: 'signin',
    },
    message: {
      type: String,
      required: true,
    },
    intent: {
      type: String,
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const pageContent = ref('Sign In');

    return {
      pageContent,
    };
  },
});
</script>
