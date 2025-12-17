<template>
  <div
    id="right"
    class="right m-auto flex basis-2/4 items-center rounded-l-lg rounded-r-lg bg-white px-5 py-5 sm:px-10 sm:py-10 md:my-0 md:rounded-l-none lg:px-14 lg:py-28 xl:px-24"
  >
    <Loader v-if="isLoaderVisible"></Loader>
    <div class="right__container flex w-full flex-col" @keyup.enter="login">
      <h2 class="mb-2 hidden sm:block">
        {{ translatedData['common.common.sign_in'] }}
      </h2>
      <span class="text-n-40">{{
        translatedData['public.login.sign_in_section.welcome_back_label']
      }}</span>
      <div
        v-if="message !== '' && intent === 'verify'"
        class="error mt-2 text-xs"
        role="alert"
      >
        {{ message }}
      </div>
      <div
        v-if="intent === 'password_changed'"
        class="w-full border-l-2 border-spring-50 bg-[#EEF9F5] px-4 py-3"
      >
        <div class="flex space-x-2">
          <svg-vue class="text-spring-50" icon="tick" />
          <span class="flex flex-col space-y-2">
            <span class="text-sm font-bold text-n-50">{{
              translatedData[
                'public.login.password_changed_section.password_updated'
              ]
            }}</span>
            <span class="text-sm text-n-50">{{
              translatedData[
                'public.login.password_changed_section.use_new_password'
              ]
            }}</span>
          </span>
        </div>
      </div>
      <button id="btn" type="button" class="btn mt-4" @click="loginWithIati">
        {{
          translatedData['common.common.log_in_with_iati'] || 'Log in with iati'
        }}
        <svg-vue class="" icon="right-arrow" />
      </button>
      <div class="mt-6 block leading-6">
        <span class="flex flex-wrap">
          {{
            translatedData[
              'public.login.iati_publishing_tool_section.havent_registered_label'
            ]
          }}
          <a
            href="https://account.iatistandard.org/"
            class="ml-1 border-b-2 border-b-transparent text-base text-turquoise hover:border-b-2 hover:border-b-turquoise"
          >
            {{ translatedData['common.common.join_now'] }}
          </a>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import Loader from 'Components/Loader.vue';

export default defineComponent({
  components: {
    Loader,
  },
  props: {
    message: {
      type: String,
      required: false,
      default: '',
    },
    intent: {
      type: String,
      required: false,
      default: '',
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const isLoaderVisible = ref(false);

    async function loginWithIati() {
      isLoaderVisible.value = true;
      try {
        // Redirect to Asgardeo SSO login page
        window.location.href = '/login/iati'; // Assuming this is the endpoint for SSO login
      } catch (error) {
        console.error('Error during SSO login:', error);
        isLoaderVisible.value = false;
      }
    }

    return {
      isLoaderVisible,
      loginWithIati,
    };
  },
});
</script>

<style lang="scss" scoped>
#btn {
  padding: 13px 0;
  svg {
    @apply absolute right-7 text-2xl;
    transition: 0.4s;
  }
}
@media screen and (min-width: 640px) {
  #btn {
    padding: 18px 0;
  }
}
</style>
