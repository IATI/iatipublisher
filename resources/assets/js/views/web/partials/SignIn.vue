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
        v-if="
          message !== '' &&
          !(errorData.emailOrUsername || errorData.password) &&
          intent === 'verify'
        "
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
      <div class="relative mb-4 mt-6 flex flex-col text-sm text-bluecoral">
        <label for="username">{{
          translatedData['public.login.sign_in_section.username_label']
        }}</label>
        <input
          id="username"
          v-model="formData.emailOrUsername"
          class="username input sm:h-16"
          :class="{
            error_input: errorData.emailOrUsername,
          }"
          type="text"
          :placeholder="translatedData['common.common.type_username_here']"
        />
        <svg-vue class="absolute left-5 top-12 text-xl sm:left-6" icon="user" />
        <span
          v-if="errorData.emailOrUsername !== ''"
          class="error text-xs"
          role="alert"
        >
          {{ errorData.emailOrUsername }}
        </span>
      </div>
      <div class="relative mb-4 flex flex-col text-sm text-bluecoral">
        <label for="Password">{{
          translatedData['common.common.password']
        }}</label>
        <input
          id="password"
          v-model="formData.password"
          class="password input sm:h-16"
          :class="{
            error__input: errorData.password || errorData.emailOrUsername,
          }"
          type="password"
          :placeholder="translatedData['common.common.type_password_here']"
        />
        <svg-vue
          class="absolute left-5 top-12 text-xl sm:left-6"
          icon="pw-lock"
        />
        <span v-if="errorData.password" class="error" role="alert">{{
          errorData.password
        }}</span>
      </div>
      <p class="mb-6 text-sm text-n-40">
        {{
          translatedData['public.login.sign_in_section.forgot_password_label']
        }}
        <span
          ><a
            class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
            href="/password/email"
            >{{ translatedData['public.login.sign_in_section.reset'] }}</a
          ></span
        >
      </p>
      <button id="btn" type="submit" class="btn" @click="login">
        {{ translatedData['common.common.sign_in'] }}
        <svg-vue class="" icon="right-arrow" />
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, reactive, ref } from 'vue';
import axios from 'axios';
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
    const formData = reactive({
      emailOrUsername: '',
      password: '',
    });
    const errorData = reactive({
      emailOrUsername: '',
      password: '',
    });
    const isLoaderVisible = ref(false);

    async function login() {
      isLoaderVisible.value = true;

      let form = {
        emailOrUsername: formData.emailOrUsername,

        password: formData.password,
      };

      axios
        .post('/login', form)
        .then((response) => {
          errorData.emailOrUsername = '';
          errorData.password = '';

          if (!('errors' in response)) {
            window.location.reload();
          }
        })
        .catch((error) => {
          const { errors } = error.response.data;
          errorData.emailOrUsername = errors.emailOrUsername
            ? errors.emailOrUsername[0]
            : '';
          errorData.password = errors.password ? errors.password[0] : '';
          isLoaderVisible.value = false;
        });
    }

    return {
      formData,
      errorData,
      isLoaderVisible,
      login,
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
.username {
  @apply mb-2;
}
.password {
  @apply mb-2;
}
label {
  @apply mb-2 font-bold;
}
</style>
