<template>
  <div class="px-10">
    <Loader v-if="isLoaderVisible" />

    <div class="my-4 flex justify-between">
      <div class="inline-flex items-center">
        <div
          class="max-w-[40vw] overflow-hidden text-ellipsis whitespace-nowrap text-[30px] font-bold md:max-w-[60vw]"
        >
          {{ userData['full_name'] }}
        </div>
      </div>
      <div class="flex flex-wrap-reverse items-end justify-end gap-2">
        <div>
          <a
            class="button primary-btn"
            href="https://account.iatistandard.org/"
          >
            <svg-vue icon="edit" class="mr-1 text-base"></svg-vue
            ><span class="text-xs uppercase">
              {{ translatedData['userProfile.user_profile.edit_your_profile'] }}
            </span>
          </a>
        </div>
      </div>
    </div>

    <div class="my-4 rounded-lg bg-white p-8">
      <div class="flex justify-between border-b border-n-30 py-6">
        <span class="inline-flex items-center space-x-2">
          <span><svg-vue icon="user-profile" class="text-base"></svg-vue></span>
          <h6 class="text-sm font-bold">
            {{ translatedData['userProfile.user_profile.your_information'] }}
          </h6></span
        >
      </div>

      <div class="flex space-x-2 border-b border-n-20 py-6">
        <div class="text-base font-bold text-n-40">
          {{ toTitleCase(translatedData['common.common.name']) }}
        </div>
        <div class="max-w-[60vw] overflow-x-hidden text-ellipsis text-base">
          {{ userData['full_name'] }}
        </div>
      </div>
      <div class="flex space-x-2 border-b border-n-20 py-6">
        <div class="text-base font-bold text-n-40">
          {{
            toTitleCase(
              translatedData['userProfile.user_profile.language_preference']
            )
          }}
        </div>
        <div class="text-base">
          {{ languagePreference[userData['language_preference']] }}
        </div>
      </div>
      <div class="flex items-baseline space-x-2 py-6">
        <div class="text-base font-bold text-n-40">
          {{ toTitleCase(translatedData['common.common.email']) }}
        </div>
        <div>
          <a>{{ userData['email'] }}</a>
          <div
            v-if="!userData['email_verified_at']"
            class="mt-1 max-w-[550px] text-n-40"
          >
            {{
              translatedData[
                'userProfile.user_profile.you_havent_verified_your_email_address_yet'
              ]
            }}
            <a
              class="cursor-pointer font-bold underline"
              @click="resendVerificationEmail()"
              >{{
                translatedData[
                  'userProfile.user_profile.resend_verification_email'
                ]
              }}</a
            >
          </div>
        </div>
      </div>
      <div
        v-if="userData['organization']"
        class="flex space-x-2 border-b border-n-20 py-6"
      >
        <div class="text-base font-bold text-n-40">
          {{ toTitleCase(translatedData['common.common.organisation']) }}
        </div>
        <div class="text-base">
          {{ userData['organization_name'] }}
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { defineProps, reactive, ref, watch, onMounted } from 'vue';
import Loader from '../../components/Loader.vue';

import { toTitleCase } from '../../composable/utils';

const props = defineProps({
  user: { type: Object, required: true },
  languagePreference: { type: Object, required: true },
  translatedData: { type: Object, required: true },
});
const isLoaderVisible = ref(false);

const userData = reactive({ user_role: '' });

onMounted(() => {
  Object.assign(userData, props.user);
  userData.user_role = userData.user_role.split('_').join(' ');
});
</script>
