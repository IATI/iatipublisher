<template>
  <section class="main mt-10 sm:mx-10 xl:mx-24 xl:px-1">
    <div
      class="main__container rounded-lg bg-bluecoral pb-8 sm:mb-10 md:mb-20 md:flex md:bg-none md:pb-0"
    >
      <div
        class="left flex flex-col items-center justify-center bg-bluecoral px-3 pb-72 pt-5 text-white sm:rounded-l-lg sm:rounded-r-lg sm:px-5 sm:pt-10 md:basis-2/4 md:rounded-r-none md:pb-16 lg:pb-44 lg:pt-44 xl:px-24"
      >
        <div class="left__container rounded-lg p-5 sm:p-10">
          <span class="left__title font-bold">IATI Publisher - reflection</span>
          <p class="pt-2 sm:pb-8 sm:pt-6">
            Welcome to IATI Publisher. This lets you publish IATI data on your
            organisation's development and humanitarian activities.
            <br />
            <span v-if="pageContent !== 'Join Now'">
              Use this page to create a new account for your organisation.
            </span>
          </p>
          <div class="block">
            <span class="flex flex-wrap">
              {{
                pageContent === 'Join Now'
                  ? "Haven't registered yet?"
                  : 'Already have an account?'
              }}
              <button
                class="ml-1 border-b-2 border-b-transparent text-base text-turquoise hover:border-b-2 hover:border-b-turquoise"
                @click="togglePage"
              >
                {{ pageContent }}
              </button>
            </span>
          </div>
        </div>
      </div>

      <SignIn
        v-if="pageContent === 'Join Now'"
        :message="message"
        :intent="intent"
      />
      <JoinNow v-else />
    </div>
  </section>
</template>

<script>
import { defineComponent, ref } from 'vue';
import SignIn from './partials/SignIn.vue';
import JoinNow from './partials/JoinNow.vue';

export default defineComponent({
  components: {
    JoinNow,
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
  },
  setup(props) {
    const pageContent = ref(props.page === 'signin' ? 'Join Now' : 'Sign In');

    function togglePage() {
      pageContent.value =
        pageContent.value === 'Join Now' ? 'Sign In' : 'Join Now';
    }

    return {
      pageContent,
      togglePage,
    };
  },
});
</script>
