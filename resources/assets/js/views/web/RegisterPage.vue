<template>
  <section
    class="section mx-3 mb-7 sm:mx-10 sm:mb-10 md:mb-14 xl:mx-24 xl:px-1"
  >
    <Loader v-if="isLoaderVisible" />
    <div class="section__container">
      <div class="section__title">
        <h2>
          {{ translatedData['public.register.registered_page.heading'] }}
        </h2>
        <p>
          {{ translatedData['public.register.registered_page.start_journey'] }}
        </p>
      </div>
      <div class="section__wrapper flex justify-center">
        <EmailVerification
          v-if="checkStep('3')"
          :email="formData['email']"
          :translated-data="translatedData"
        />
        <div v-else class="form input__field" @keyup.enter="goToNextForm">
          <aside class="mb-4 block border-b border-b-n-10 pb-4 xl:hidden">
            <span class="text-base font-bold">
              {{ translatedStepXOutOf3 }}
            </span>
            <ul class="relative mt-3 text-sm text-n-40">
              <li
                v-for="(form, key, i) in registerForm"
                :key="i"
                :class="{
                  'relative font-bold text-n-50': checkStep(key),
                  'mb-6 hidden': !checkStep(key),
                }"
              >
                <span v-if="checkStep(key)" class="list__active" />
                <div class="flex items-center">
                  <span v-if="!form['is_complete']" class="mr-3">
                    {{ i + 1 }}
                  </span>
                  <span
                    class="font-bold"
                    :class="{
                      'text-n-50': checkStep(key),
                      'text-bluecoral': !checkStep(key) && form.is_complete,
                      'text-n-40': !checkStep(key) && !form.is_complete,
                    }"
                  >
                    {{ form['title'] }}
                  </span>
                </div>
                <p v-if="checkStep(key)" class="detail mt-2 font-normal">
                  {{ form['description'] }}
                </p>
              </li>
            </ul>
          </aside>
          <div class="form__container">
            <div class="flex justify-between">
              <div class="flex items-center space-x-1">
                <HoverText
                  v-if="registerForm[getCurrentStep()]['hover_text']"
                  :hover-text="registerForm[getCurrentStep()]['hover_text']"
                  :name="registerForm[getCurrentStep()].title"
                  position="right"
                />
                <span class="text-xl font-bold text-n-50 sm:text-2xl">
                  {{ registerForm[getCurrentStep()].title }}
                </span>
              </div>
              <div class="flex items-center">
                <small class="label">
                  <span class="required-icon px-1">*</span>
                  <span>{{
                    translatedData['common.common.mandatory_fields']
                  }}</span>
                </small>
              </div>
            </div>
            <div
              v-if="!publisherExists"
              class="feedback mt-6 border-l-2 border-crimson-50 bg-crimson-10 p-4 text-sm text-n-50 xl:h-32"
            >
              <p class="mb-2 flex font-bold">
                <svg-vue class="mr-2 text-xl" icon="warning" />
                {{
                  translatedData[
                    'public.register.registered_page.register_section.info_doesnt_match_iati_registry'
                  ]
                }}
              </p>
              <p
                class="ml-8 xl:mr-1"
                v-html="
                  translatedData[
                    'public.register.registered_page.register_section.make_sure_account_holder'
                  ]
                "
              ></p>
            </div>
            <div class="form__content">
              <div
                v-for="(field, index, key) in registerForm[getCurrentStep()][
                  'fields'
                ]"
                :key="key"
                :class="field.class"
              >
                <div class="mb-2 flex items-center justify-between">
                  <label :for="field.id" class="label"
                    >{{ field['label'] }}
                    <span v-if="field.required" class="required-icon"> *</span>
                  </label>
                  <HoverText
                    v-if="'hover_text' in field && field.hover_text !== ''"
                    :hover-text="field.hover_text"
                    :name="field.label"
                  />
                </div>
                <input
                  v-if="isTextField(field.type, field.name)"
                  :id="field.id"
                  v-model="formData[field.name]"
                  :class="{
                    'error_input form__input': errorData[field.name],
                    form__input: !errorData[field.name],
                  }"
                  :placeholder="field.placeholder"
                  :type="field.type"
                />

                <input
                  v-if="field.name === 'identifier'"
                  v-model="formData[field.name]"
                  :class="{
                    'error_input form__input': errorData[field.name],
                    form__input: !errorData[field.name],
                  }"
                  :placeholder="field.placeholder"
                  :type="field.type"
                  disabled="true"
                />

                <Multiselect
                  v-if="field.type === 'select'"
                  v-model="formData[field.name]"
                  :class="{
                    'error_input vue__select': errorData[field.name],
                    vue__select: !errorData[field.name],
                  }"
                  :options="field.options"
                  :placeholder="field.placeholder"
                  :searchable="true"
                />
                <span
                  v-if="field.help_text && errorData[field.name] === ''"
                  class="text-xs font-normal text-n-40"
                  >{{ field.help_text }}
                </span>

                <span
                  v-if="errorData[field.name] !== ''"
                  class="error"
                  role="alert"
                >
                  {{ errorData[field.name] }}
                </span>
              </div>
            </div>
          </div>
          <div class="flex flex-wrap items-center justify-between">
            <button
              v-if="!checkStep(1)"
              class="btn-back"
              @click="goToPreviousForm()"
            >
              <svg-vue class="mr-3 cursor-pointer" icon="left-arrow" />
              {{ translatedData['common.common.go_back'] }}
            </button>
            <span
              v-if="checkStep(1)"
              class="pb-4 text-sm font-normal text-n-40 sm:pb-0"
            >
              {{ translatedData['common.common.already_have_an_account'] }}
              <a
                class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
                href="/"
              >
                {{ translatedData['common.common.sign_in'] }} .</a
              ></span
            >
            <button
              v-if="!checkStep(3)"
              class="btn btn-next"
              @click="goToNextForm()"
            >
              {{ translatedData['public.register.commons.next_step'] }}
              <svg-vue class="text-2xl" icon="right-arrow" />
            </button>
          </div>
          <div v-if="checkStep(2)" class="mt-6 text-center">
            <span class="text-sm font-normal text-n-40">
              {{ translatedData['common.common.already_have_an_account'] }}
              <a
                class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
                href="/"
              >
                {{ translatedData['common.common.sign_in'] }} .</a
              ></span
            >
          </div>
        </div>

        <aside class="register__sidebar hidden xl:block">
          <span class="text-base font-bold">
            {{ translatedStepXOutOf3 }}
          </span>
          <ul class="relative mt-6 text-sm text-n-40">
            <li
              v-for="(form, key, i) in registerForm"
              :key="i"
              :class="{
                'relative font-bold text-n-50': checkStep(key),
                'mb-6 flex items-center': !checkStep(key),
              }"
            >
              <span v-if="checkStep(key)" class="list__active" />
              <div class="flex items-center">
                <span v-if="!form['is_complete']" class="ml-6 mr-3">
                  {{ i + 1 }}
                </span>
                <span v-if="form['is_complete']" class="ml-6 mr-3">
                  <svg-vue class="text-xs" icon="checked"> </svg-vue>
                </span>
                <span
                  class="font-bold"
                  :class="{
                    'text-n-50': checkStep(key),
                    'text-bluecoral': !checkStep(key) && form.is_complete,
                    'text-n-40': !checkStep(key) && !form.is_complete,
                  }"
                >
                  {{ form['title'] }}
                </span>
              </div>
              <p
                v-if="checkStep(key)"
                class="detail mb-6 mt-2 font-normal xl:pr-2"
              >
                {{ form['description'] }}
              </p>
            </li>
          </ul>
        </aside>
      </div>
    </div>
  </section>
</template>

<script lang="ts">
import { computed, defineComponent, reactive, ref, toRefs, watch } from 'vue';
import axios from 'axios';
import EmailVerification from './EmailVerification.vue';
import HoverText from './../../components/HoverText.vue';
import Multiselect from '@vueform/multiselect';
import Loader from '../../components/Loader.vue';

import { generateUsername, toTitleCase } from 'Composable/utils';

export default defineComponent({
  methods: { toTitleCase },
  components: {
    EmailVerification,
    HoverText,
    Multiselect,
    Loader,
  },

  props: {
    country: {
      type: [String, Object],
      required: true,
    },
    agency: {
      type: [String, Object],
      required: true,
    },
    uncategorizedOrganisationRegistrationAgency: {
      type: [Object],
      required: true,
    },
    languages: {
      type: [Object],
      required: true,
    },
    translatedData: {
      type: Object,
      required: true,
    },
  },

  setup(props) {
    const step = ref(1);
    const publisherExists = ref(true);
    const isLoaderVisible = ref(false);

    const translatedStepXOutOf3 = ref('');

    watch(
      [() => props.translatedData, getCurrentStep],
      () => {
        translatedStepXOutOf3.value = props.translatedData[
          'public.register.registered_page.step_count_out_of_3'
        ].replace(':count', getCurrentStep());
      },
      { immediate: true }
    );

    let { agency } = toRefs(props);

    interface ObjectType {
      [key: string]: string;
    }

    const errorData: ObjectType = reactive({
      publisher_name: '',
      publisher_id: '',
      country: '',
      registration_agency: '',
      registration_number: '',
      identifier: '',
      username: '',
      full_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      default_language: '',
    });

    const formData: ObjectType = reactive({
      publisher_name: '',
      publisher_id: '',
      country: '',
      registration_agency: '',
      registration_number: '',
      identifier: '',
      username: '',
      full_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      default_language: '',
      source: '',
    });

    watch(
      () => formData.country,
      () => {
        formData.registration_agency = '';
      }
    );

    watch(
      () => [formData.registration_agency, formData.registration_number],
      () => {
        formData.identifier = formData.registration_agency
          ? formData.registration_agency + '-' + formData.registration_number
          : formData.registration_number;
      },
      { deep: true }
    );

    watch(
      () => formData.full_name,
      () => {
        formData.username = generateUsername(formData.full_name);
      }
    );

    const registration_agency = computed(() => {
      const agencies = agency.value;

      if (formData.country) {
        const uncategorized = props.uncategorizedOrganisationRegistrationAgency;

        return Object.fromEntries(
          Object.entries(agencies).filter(
            ([key]) =>
              key.startsWith(formData.country) ||
              uncategorized.some((k) => key.startsWith(k))
          )
        );
      } else {
        return agencies;
      }
    });

    const isTextField = computed(() => {
      return (fieldType: string, fieldName: string) => {
        return (
          (fieldType === 'text' ||
            fieldType === 'password' ||
            fieldType === 'email') &&
          fieldName != 'identifier'
        );
      };
    });

    const checkStep = computed(() => {
      return (formStep: string | number) => {
        return parseInt(formStep.toString()) === step.value;
      };
    });

    const registerForm = reactive({
      1: {
        title: props.translatedData['common.common.publisher_information'],
        is_complete: false,
        description:
          props.translatedData[
            'public.register.registered_page.register_section.publisher_information_description'
          ],
        hover_text:
          props.translatedData[
            'public.register.registered_page.register_section.publisher_information_hover_text'
          ],
        fields: {
          publisher_name: {
            label: props.translatedData['common.common.publisher_name'],
            name: 'publisher_name',
            placeholder:
              props.translatedData[
                'common.common.enter_your_organisation_name'
              ],
            id: 'publisher-name',
            required: true,
            hover_text:
              props.translatedData[
                'common.common.the_name_of_your_organisation_publishing_the_data'
              ],
            type: 'text',
            class: 'col-span-2 mb-4 lg:mb-2',
            help_text: '',
          },
          publisher_id: {
            label: props.translatedData['common.common.publisher_id'],
            name: 'publisher_id',
            placeholder:
              props.translatedData['common.common.type_your_publisher_id_here'],
            id: 'publisher-id',
            required: true,
            hover_text:
              props.translatedData[
                'common.common.this_is_the_unique_id_for_your_organisation'
              ],
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: '',
          },
          country: {
            label: props.translatedData['common.common.country'],
            name: 'country',
            placeholder: props.translatedData['common.common.select_an_option'],
            id: 'country_select',
            required: false,
            type: 'select',
            hover_text:
              props.translatedData[
                'common.common.add_the_location_of_your_organisation'
              ],
            options: props.country,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          organization_registration_agency: {
            label:
              props.translatedData[
                'common.common.organisation_registration_agency'
              ],
            name: 'registration_agency',
            placeholder: props.translatedData['common.common.select_an_option'],
            id: 'registration-agency',
            required: true,
            hover_text:
              props.translatedData[
                'public.register.registered_page.register_section.organisation_registration_agency_hover_text'
              ],
            type: 'select',
            options: registration_agency,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          organization_registration_no: {
            label:
              props.translatedData[
                'public.register.registered_page.register_section.organisation_registration_number'
              ],
            name: 'registration_number',
            placeholder:
              props.translatedData[
                'common.common.type_your_registration_number_here'
              ],
            id: 'registration-number',
            required: true,
            hover_text:
              props.translatedData[
                'common.common.provide_your_organisations_registration_number'
              ],
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: props.translatedData['common.common.for_example_123456'],
          },
          iati_organizational_identifier: {
            label:
              props.translatedData[
                'common.common.iati_organisation_identifier'
              ],
            name: 'identifier',
            placeholder: '',
            id: 'identifier',
            required: true,
            hover_text:
              props.translatedData[
                'common.common.this_is_a_unique_code_for_your_organisation'
              ],
            type: 'text',
            class: 'mb-4 lg:mb-6',
            help_text:
              props.translatedData['common.common.this_is_autogenerated'],
          },
        },
      },
      2: {
        title: props.translatedData['common.common.administrator_information'],
        is_complete: false,
        description:
          props.translatedData[
            'common.common.this_will_create_an_admin_account_for_you_as_an_individual'
          ],
        hover_text:
          props.translatedData[
            'public.register.registered_page.register_section.administrator_hover_text'
          ],
        fields: {
          full_name: {
            label: props.translatedData['common.common.full_name'],
            name: 'full_name',
            placeholder:
              props.translatedData['common.common.type_your_full_name_here'],
            id: 'full-name',
            hover_text: '',
            required: true,
            type: 'text',
            class: 'mb-4 lg:mb-2',
          },
          email: {
            label: props.translatedData['common.common.email_address'],
            name: 'email',
            placeholder:
              props.translatedData['common.common.type_valid_email_here'],
            id: 'email',
            required: true,
            hover_text: '',
            type: 'email',
            class: 'mb-4 lg:mb-2',
          },
          username: {
            label: props.translatedData['common.common.username'],
            name: 'username',
            placeholder:
              props.translatedData['common.common.type_username_here'],
            id: 'username',
            required: true,
            hover_text:
              props.translatedData[
                'common.common.you_will_need_this_later_to_login'
              ],
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: '',
          },
          default_language: {
            label: props.translatedData['elements.label.default_language'],
            name: 'default_language',
            placeholder:
              props.translatedData[
                'common.common.select_your_default_language'
              ],
            id: 'default-language',
            required: true,
            type: 'select',
            options: props.languages,
            class: 'mb-4 lg:mb-2',
          },
          password: {
            label: props.translatedData['common.common.password'],
            name: 'password',
            placeholder:
              props.translatedData['common.common.type_password_here'],
            id: 'password',
            required: true,
            hover_text: '',
            type: 'password',
            class: 'mb-4 lg:mb-2',
          },
          confirm_password: {
            label: props.translatedData['common.common.confirm_password'],
            name: 'password_confirmation',
            placeholder:
              props.translatedData['common.common.type_password_here'],
            id: 'password-confirmation',
            required: true,
            hover_text: '',
            type: 'password',
            class: 'mb-4 lg:mb-6',
          },
        },
      },
      3: {
        title: props.translatedData['common.common.email_verification'],
        is_complete: false,
        description:
          props.translatedData[
            'common.common.please_verify_and_activate_your_iati_publisher_account'
          ],
      },
    });

    const updateFormErrors = (errors = []) => {
      const errorLength = Object.keys(errors).length;

      if (errorLength === 0) {
        for (const errKey in errorData) {
          errorData[errKey] = '';
        }
      }

      if (errorLength > 0) {
        for (const errKey in errorData) {
          errorData[errKey] = errKey in errors ? errors[errKey][0] : '';
        }
      }
    };

    function verifyPublisher() {
      isLoaderVisible.value = true;

      formData.identifier = `${formData.registration_agency}-${formData.registration_number}`;

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/verifyPublisher', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          publisherExists.value = true;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];

          updateFormErrors(errors);

          if ('publisher_error' in response) {
            publisherExists.value = false;
          }

          if (response.success) {
            registerForm['1'].is_complete = true;
            step.value += 1;
          }

          formData.source = response.data.publisher_source_type;
          isLoaderVisible.value = false;
        })
        .catch(() => {
          isLoaderVisible.value = false;
        });
    }

    function submitForm() {
      isLoaderVisible.value = true;

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/register', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];
          updateFormErrors(errors);
          errorData.password_confirmation = errors.password_confirmation
            ? errors.password_confirmation[0]
            : errors.password
            ? errors.password[0]
            : '';
          isLoaderVisible.value = false;

          if (response.success) {
            registerForm['2'].is_complete = true;
            step.value += 1;
          }
        })
        .catch((error) => {
          const { errors } = error.response.data;
          isLoaderVisible.value = false;
          updateFormErrors(errors);
          errorData.password_confirmation = errors.password_confirmation
            ? errors.password_confirmation[0]
            : errors.password
            ? errors.password[0]
            : '';
        });
    }

    function getCurrentStep() {
      return step.value.toString();
    }

    function goToNextForm() {
      if (step.value === 1) verifyPublisher();
      if (step.value === 2) submitForm();
    }

    function goToPreviousForm() {
      step.value -= 1;
    }

    return {
      registerForm,
      formData,
      errorData,
      publisherExists,
      isLoaderVisible,
      goToNextForm,
      goToPreviousForm,
      getCurrentStep,
      checkStep,
      isTextField,
      props,
      translatedStepXOutOf3,
    };
  },
});
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss">
.label {
  text-transform: capitalize;
  @apply text-sm font-normal text-n-50;
}

.section {
  &__container {
    @media screen and (min-width: 1280px) {
      max-width: 1206px;
    }
    max-width: 865px;
    margin: auto;

    .feedback {
      @media screen and (min-width: 1280px) {
        width: 702px;
      }

      p {
        line-height: 22px;
      }
    }

    .section__wrapper {
      box-shadow: 0px 20px 40px 20px rgba(0, 0, 0, 0.05);

      .verification {
        font-size: 190px;
      }
    }

    .section__title {
      @media screen and (min-width: 440px) {
        @apply leading-9;
      }

      @apply mx-3 my-7 text-center leading-7 sm:leading-10 lg:mb-10 lg:mt-14;

      p {
        font-weight: normal;
        font-style: normal;
        @apply text-sm text-n-40 sm:text-base;
      }
    }

    .register__sidebar {
      @apply bg-eggshell;
      padding: 96px 32px 40px;
      width: 344px;

      ul {
        width: 253px;
      }

      ul::before {
        content: '';
        width: 4px;
        height: 175px;
        @apply bg-n-20;
        border-radius: 2px;
        position: absolute;
        left: 0px;
        top: 0px;
      }

      .detail {
        margin-left: 45px;
      }

      .list__active::after {
        position: absolute;
        top: 0;
        left: -1px;
        width: 6px;
        height: 85px;
        @apply bg-turquoise;
        content: '';
        border-radius: 2px;
        z-index: 5;
      }
    }
  }
}

.form {
  @apply bg-white p-5 sm:px-10 sm:py-10 lg:px-20;
  border-top-left-radius: 8px;
  border-bottom-left-radius: 8px;
  width: 862px;

  &__container {
    @apply border-b-2 border-b-n-10;
    margin-bottom: 24px;

    .error__input {
      @apply border border-crimson-50;
    }
  }

  &__content {
    margin-top: 24px;
  }
}

@media screen and (min-width: 1024px) {
  .form__content {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
  }
}
</style>
