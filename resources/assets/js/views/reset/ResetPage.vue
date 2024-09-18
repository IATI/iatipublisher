<template>
  <div class="mt-7 sm:mt-14">
    <Loader v-if="loaderVisibility" />
    <div class="reset" @keyup.enter="reset">
      <div class="mb-4 flex flex-col sm:mb-8">
        <h2>
          {{ translatedData['common.common.password_recovery'] }}
        </h2>
        <p>
          {{ translatedData['public.forgot_password.reset_page.subheading'] }}
        </p>
      </div>

      <div class="reset__content">
        <label class="text-sm font-bold text-bluecoral" for="email">{{
          toTitleCase(translatedData['common.common.email'])
        }}</label>
        <input
          id="email"
          v-model="formData.email"
          type="email"
          :placeholder="translatedData['common.common.type_valid_email_here']"
          class="input"
          :class="{
            error__input: emailError != '',
          }"
        />
        <svg-vue class="mail-icon" icon="mail" />
        <span v-if="emailError" class="error" role="alert"
          >{{ emailError }}
        </span>
      </div>
      <button type="submit" class="btn reset-btn" @click="reset()">
        {{ translatedData['public.forgot_password.reset_page.button'] }}
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive } from 'vue';
import Loader from '../../components/Loader.vue';
import axios from 'axios';
import { toTitleCase } from 'Composable/utils';

export default defineComponent({
  components: {
    Loader,
  },
  props: {
    translatedData: {
      type: Object,
      required: true,
    },
  },
  setup() {
    const formData = reactive({
      email: '',
    });
    const emailError = ref('');
    const loaderVisibility = ref(false);

    function reset() {
      loaderVisibility.value = true;

      axios
        .post('/password/email', formData)
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];

          emailError.value = errors.email ? errors.email[0] : '';

          if (response.success) {
            window.location.href = '/password/confirm';
          }

          loaderVisibility.value = false;
        })
        .catch((error) => {
          const { errors } = error.response.data;
          emailError.value = errors.email ? errors.email[0] : '';

          loaderVisibility.value = false;
        });
    }

    return {
      formData,
      loaderVisibility,
      emailError,
      reset,
    };
  },
  methods: { toTitleCase },
});
</script>

<style lang="scss">
.reset {
  @media screen and (min-width: 440px) {
    @apply p-10;
  }

  @media screen and (min-width: 640px) {
    width: 583px;
    margin: auto;

    @apply p-24;
  }
  box-shadow: 0px 20px 40px 20px rgba(0, 0, 0, 0.05);
  @apply mx-3 rounded-lg bg-white p-5;

  &__content {
    @apply relative flex flex-col;

    .input {
      @apply my-2 py-5;
    }

    .lock-icon {
      @apply absolute left-6 text-lg;
      top: 47px;

      @media screen and (min-width: 640px) {
        top: 50px;
        font-size: 20px;
      }
    }

    .mail-icon {
      @apply absolute left-6;
      top: 47px;

      @media screen and (min-width: 640px) {
        top: 51px;
      }
    }
  }
  h2 {
    @media screen and (min-width: 640px) {
      @apply text-heading-3;
      line-height: 60px;
    }

    @media screen and (min-width: 440px) {
      @apply text-heading-4;
      line-height: 50px;
    }
    @apply text-heading-5 font-bold text-n-50 sm:mb-2;
  }
  p {
    @apply text-sm text-n-40 sm:text-base;
  }
  .reset-btn {
    @apply mt-3 w-full text-xs;
    padding: 14px;

    @media screen and (min-width: 640px) {
      padding: 18px 94px;
      font-size: 14px;
    }
  }
  .verification {
    font-size: 150px;

    @media screen and (min-width: 640px) {
      font-size: 190px;
    }
  }
}
.reset__password {
  height: 610px;
}
</style>
