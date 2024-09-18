<template>
  <form method="POST" action="" class="form">
    <div class="grid justify-items-center gap-6 tracking-normal xl:px-2">
      <div class="space-y-4 sm:space-y-6">
        <p class="text-center text-xl font-bold text-n-50 sm:text-2xl">
          {{
            translatedData[
              'public.register.not_registered_page.verification_email.please_verify_your_account'
            ]
          }}
        </p>
        <p class="text-sm text-n-40 sm:text-base">
          {{
            translatedData[
              'public.register.not_registered_page.verification_email.verification_email_sent_to'
            ]
          }}
          <span class="font-bold text-n-50"> {{ props.email }}. </span>
          {{
            translatedData[
              'public.register.not_registered_page.verification_email.please_click_link_to_verify'
            ]
          }}
        </p>
      </div>
      <svg-vue class="verification" icon="ic-verification" />
      <a href="/activities" class="btn btn-next get-started-btn">
        {{ translatedData['common.common.get_started'] }}
        <svg-vue class="text-2xl" icon="right-arrow" />
      </a>
    </div>
  </form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    email: { type: String, default: '' },
    translatedData: { type: String, required: true },
  },

  setup(props) {
    return {
      props,
    };
  },
});
</script>
