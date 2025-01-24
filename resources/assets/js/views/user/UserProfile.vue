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
        <Toast
          v-if="
            toastData.visibility &&
            toastData.message &&
            toastData.message !== ''
          "
          :message="toastData.message"
          :type="toastData.type"
        />
        <div>
          <button
            class="primary-btn"
            @click="
              () => {
                editProfileForm = true;
              }
            "
          >
            <svg-vue icon="edit" class="mr-1 text-base"></svg-vue
            ><span class="text-xs uppercase">
              {{ translatedData['userProfile.user_profile.edit_your_profile'] }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <div class="my-4 rounded-lg bg-white p-8">
      <PopupModal
        :modal-active="editPasswordForm"
        @close="
          () => {
            editPasswordForm = false;
          }
        "
      >
        <div class="popup-model h-auto" @keyup.enter="updatePassword">
          <div class="mb-4 text-2xl font-bold text-bluecoral">
            {{ translatedData['userProfile.user_profile.change_password'] }}
          </div>
          <div>
            <div class="mb-5 flex flex-col gap-2">
              <label class="text-sm text-n-50"
                >{{ translatedData['userProfile.user_profile.current_password']
                }}<span class="text-[red]"> * </span>
              </label>
              <span class="relative max-w-[calc(50%_-_12px)]">
                <svg-vue
                  icon="hide-password"
                  class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-lg"
                  @click="
                    () => {
                      showCurrentPassword = !showCurrentPassword;
                    }
                  "
                ></svg-vue>
                <input
                  v-model="passwordData.current_password"
                  :class="
                    errorPasswordData.current_password !== ''
                      ? 'border-crimson-50'
                      : 'border-n-30'
                  "
                  class="w-full rounded border border-n-30 p-3"
                  :type="showCurrentPassword ? 'text' : 'password'"
                />
              </span>
              <span
                v-if="errorPasswordData.current_password !== ''"
                class="error"
                role="alert"
              >
                {{ errorPasswordData.current_password }}
              </span>
            </div>
          </div>
          <div class="mb-5 flex space-x-6">
            <div class="flex w-full flex-col gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['userProfile.user_profile.new_password'] }}
                <span class="text-[red]"> * </span>
              </label>
              <span class="relative">
                <svg-vue
                  icon="hide-password"
                  class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-lg"
                  @click="
                    () => {
                      showNewPassword = !showNewPassword;
                    }
                  "
                ></svg-vue>
                <input
                  v-model="passwordData.password"
                  :class="
                    errorPasswordData.password !== ''
                      ? 'border-crimson-50'
                      : 'border-n-30'
                  "
                  class="w-full rounded border border-n-30 p-3"
                  :type="showNewPassword ? 'text' : 'password'"
                /> </span
              ><span
                v-if="errorPasswordData.password !== ''"
                class="error"
                role="alert"
              >
                {{ errorPasswordData.password }}
              </span>
            </div>
            <div class="flex w-full flex-col gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.confirm_password'] }}
                <span class="text-[red]"> * </span>
              </label>
              <span class="relative">
                <svg-vue
                  icon="hide-password"
                  class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-lg"
                  @click="
                    () => {
                      showConfirmPassword = !showConfirmPassword;
                    }
                  "
                ></svg-vue
                ><input
                  v-model="passwordData.password_confirmation"
                  :class="
                    errorPasswordData.password_confirmation !== ''
                      ? 'border-crimson-50'
                      : 'border-n-30'
                  "
                  class="w-full rounded border p-3"
                  :type="showConfirmPassword ? 'text' : 'password'"
                /> </span
              ><span
                v-if="errorPasswordData.password_confirmation !== ''"
                class="error"
                role="alert"
              >
                {{ errorPasswordData.password_confirmation }}
              </span>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <button
              class="secondary-btn font-bold"
              @click="
                () => {
                  editPasswordForm = false;
                }
              "
            >
              {{ translatedData['common.common.cancel'] }}
            </button>
            <button class="primary-btn !px-10" @click="updatePassword">
              {{ translatedData['common.common.save'] }}
            </button>
          </div>
        </div>
      </PopupModal>
      <!-- profile edit popup form -->
      <PopupModal
        :modal-active="editProfileForm"
        @close="
          () => {
            editProfileForm = false;
          }
        "
      >
        <div class="popup-model" @keyup.enter="updateProfile">
          <div class="mb-4 text-2xl font-bold text-bluecoral">
            {{ translatedData['userProfile.user_profile.edit_your_profile'] }}
          </div>
          <div class="grid grid-cols-2 gap-6">
            <div class="col-span-2 flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.full_name'] }}
                <span class="text-[red]"> * </span>
              </label>
              <input
                v-model="formData.full_name"
                :class="
                  errorFormData.full_name !== ''
                    ? 'border-crimson-50'
                    : 'border-n-30'
                "
                class="w-full rounded border border-n-30 p-3"
                type="text"
              />
              <span
                v-if="errorFormData.full_name !== ''"
                class="error"
                role="alert"
              >
                {{ errorFormData.full_name }}
              </span>
            </div>
            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ translatedData['common.common.username'] }}
                <span class="text-[red]"> * </span>
              </label>
              <input
                v-model="formData.username"
                :class="
                  errorFormData.username !== ''
                    ? 'border-crimson-50'
                    : 'border-n-30'
                "
                class="w-full rounded border border-n-30 p-3"
                type="text"
              />
              <span
                v-if="errorFormData.username !== ''"
                class="error"
                role="alert"
              >
                {{ errorFormData.username }}
              </span>
            </div>

            <div class="flex flex-col items-start gap-2">
              <label class="text-sm text-n-50">
                {{ toTitleCase(translatedData['common.common.email']) }}
                <span class="text-[red]"> * </span>
              </label>
              <input
                v-model="formData.email"
                :class="
                  errorFormData.email !== ''
                    ? 'border-crimson-50'
                    : 'border-n-30'
                "
                class="w-full rounded border border-n-30 p-3"
                type="email"
              />
              <span
                v-if="errorFormData.email !== ''"
                class="error"
                role="alert"
              >
                {{ errorFormData.email }}
              </span>
            </div>
            <div
              :class="
                errorFormData.language_preference !== '' && 'error__multiselect'
              "
              class="flex flex-col items-start gap-2"
            >
              <label class="text-sm text-n-50"
                >{{
                  translatedData[
                    'userProfile.user_profile.language_preference'
                  ]
                }}<span class="text-[red]">*</span></label
              >
              <Multiselect
                v-model="formData.language_preference"
                :options="languagePreference"
                :placeholder="translatedData['common.common.select_language']"
                :searchable="true"
              />
              <span
                v-if="errorFormData.language_preference !== ''"
                class="error"
                role="alert"
              >
                {{ errorFormData.language_preference }}
              </span>
            </div>
          </div>
          <div class="mt-6 flex justify-end space-x-2">
            <button
              class="secondary-btn font-bold"
              @click="
                () => {
                  editProfileForm = false;
                }
              "
            >
              Cancel
            </button>
            <button class="primary-btn !px-10" @click="updateProfile">
              Save
            </button>
          </div>
        </div>
      </PopupModal>
      <div class="flex justify-between border-b border-n-30 py-6">
        <span class="inline-flex items-center space-x-2">
          <span><svg-vue icon="user-profile" class="text-base"></svg-vue></span>
          <h6 class="text-sm font-bold">
            {{ translatedData['userProfile.user_profile.your_information'] }}
          </h6></span
        >
        <div class="inline-flex">
          <div class="inline-flex cursor-pointer space-x-1">
            <span><svg-vue icon="key" class="text-base"></svg-vue></span>
            <a
              class="text-sm font-bold text-bluecoral"
              @click="
                () => {
                  editPasswordForm = true;
                }
              "
            >
              {{
                translatedData['userProfile.user_profile.change_your_password']
              }}
            </a>
          </div>
        </div>
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
          {{ toTitleCase(translatedData['common.common.username']) }}
        </div>
        <div class="text-base">{{ userData['username'] }}</div>
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
      <div
        v-if="userData['organization']"
        class="flex space-x-2 border-b border-n-20 py-6"
      >
        <div class="text-base font-bold text-n-40">
          {{ toTitleCase(translatedData['common.common.role']) }}
        </div>
        <div class="text-base">
          {{ userData['user_role'] }}
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { defineProps, reactive, ref, watch, onMounted } from 'vue';
import Loader from '../../components/Loader.vue';
import Toast from 'Components/ToastMessage.vue';
import axios from 'axios';
import PopupModal from 'Components/PopupModal.vue';

import Multiselect from '@vueform/multiselect';
import { watchIgnorable } from '@vueuse/core';
import { toTitleCase } from '../../composable/utils';

const props = defineProps({
  user: { type: Object, required: true },
  languagePreference: { type: Object, required: true },
  translatedData: { type: Object, required: true },
});

const toastData = reactive({
  visibility: false,
  message: '',
  type: true,
});

const isLoaderVisible = ref(false);
const editProfileForm = ref(false);
const editPasswordForm = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const userData = reactive({ user_role: '' });

onMounted(() => {
  Object.assign(userData, props.user);
  userData.user_role = userData.user_role.split('_').join(' ');
});

const formData = reactive({
  username: props.user.username,
  full_name: props.user.full_name,
  email: props.user.email,
  language_preference: props.user.language_preference,
});

const errorFormData = reactive({
  username: '',
  full_name: '',
  email: '',
  language_preference: '',
});

const passwordData = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const errorPasswordData = reactive({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const { ignoreUpdates } = watchIgnorable(toastData, () => undefined, {
  flush: 'sync',
});

watch(
  () => toastData.visibility,
  () => {
    setTimeout(() => {
      toastData.visibility = false;
      ignoreToastUpdate();
    }, 10000);
  }
);

const ignoreToastUpdate = () => {
  ignoreUpdates(() => {
    toastData.message = '';
  });
};

const resendVerificationEmail = () => {
  isLoaderVisible.value = true;

  axios
    .post('/user/verification/email')
    .then((res) => {
      toastData.visibility = true;
      toastData.message = res.data.message ?? '';
      toastData.type = res.data.success;
      isLoaderVisible.value = false;
    })
    .catch((error) => {
      toastData.visibility = true;
      toastData.message = error.data.message ?? '';
      toastData.type = false;
      isLoaderVisible.value = false;
    });
};

const updatePassword = () => {
  isLoaderVisible.value = true;
  let passwordFormData = {
    current_password: passwordData.current_password,
    password: passwordData.password,
    password_confirmation: passwordData.password_confirmation,
    form_type: 'password',
  };

  axios
    .post('/update/password', passwordFormData)
    .then((res) => {
      toastData.visibility = true;
      toastData.message = res.data.message;
      toastData.type = res.data.success;
      isLoaderVisible.value = false;

      if (res.data.success) {
        editPasswordForm.value = false;
        for (const key in errorPasswordData) {
          errorPasswordData[key] = '';
        }
      } else {
        for (const key in res.data.errors) {
          errorPasswordData[key] = res.data.errors[key][0];
        }
      }
    })
    .catch((error) => {
      toastData.visibility = true;
      toastData.message = error.data.message;
      toastData.type = false;
      isLoaderVisible.value = false;
    })
    .finally(() => {
      isLoaderVisible.value = false;
    });
};

const updateProfile = () => {
  isLoaderVisible.value = true;
  axios
    .post('/update/profile', formData)
    .then((res) => {
      toastData.visibility = true;
      toastData.message = res.data.message;
      toastData.type = res.data.success;
      isLoaderVisible.value = false;

      if (res.data.success) {
        editProfileForm.value = false;
        for (const key in errorFormData) {
          errorFormData[key] = '';
        }
        for (const key in formData) {
          userData[key] = formData[key];
        }
      } else {
        for (const key in res.data.errors) {
          errorFormData[key] = res.data.errors[key][0];
        }
      }
    })
    .catch((error) => {
      toastData.visibility = true;
      toastData.message = error.data.message;
      toastData.type = false;
      isLoaderVisible.value = false;
    })
    .finally(() => {
      isLoaderVisible.value = false;
    });
};
</script>
