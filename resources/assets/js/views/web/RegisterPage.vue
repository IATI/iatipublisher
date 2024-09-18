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
        <EmailVerification v-if="checkStep('3')" :email="formData['email']" />
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
                    <span v-if="field.required" class="text-salmon-40"> *</span>
                  </label>
                  <HoverText
                    v-if="field.hover_text !== ''"
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
              {{ translatedData['common.common.already_have_an_acoount'] }}
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
              {{ translatedData['common.common.already_have_an_acoount'] }}
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
import {
  computed,
  defineComponent,
  onMounted,
  reactive,
  ref,
  toRefs,
  watch,
  watchEffect,
} from 'vue';
import axios from 'axios';
import EmailVerification from './EmailVerification.vue';
import HoverText from './../../components/HoverText.vue';
import Multiselect from '@vueform/multiselect';
import Loader from '../../components/Loader.vue';

import { generateUsername } from 'Composable/utils';
import LanguageService from 'Services/language';

export default defineComponent({
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
  },

  setup(props) {
    const translatedData = ref({});
    const step = ref(1);
    const publisherExists = ref(true);
    const isLoaderVisible = ref(false);
    const translatedStepXOutOf3 = ref('');

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

    watchEffect(() => {
      if (translatedData.value && registerForm) {
        // Form 1
        registerForm[1].title =
          translatedData.value['common.common.publisher_information'];
        registerForm[1].description =
          translatedData.value[
            'public.register.registered_page.register_section.publisher_information_description'
          ];
        registerForm[1].hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.publisher_information_hover_text'
          ];
        registerForm[1].fields.publisher_name.label =
          translatedData.value['common.common.publisher_name'];
        registerForm[1].fields.publisher_name.placeholder =
          translatedData.value[
            'public.register.registered_page.register_section.publisher_name_placeholder'
          ];
        registerForm[1].fields.publisher_name.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.publisher_name_hover_text'
          ];
        registerForm[1].fields.publisher_id.label =
          translatedData.value['common.common.publisher_id'];
        registerForm[1].fields.publisher_id.placeholder =
          translatedData.value['common.common.type_your_publisher_id_here'];
        registerForm[1].fields.publisher_id.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.publisher_id_hover_text'
          ];
        registerForm[1].fields.country.label =
          translatedData.value['common.common.country'];
        registerForm[1].fields.country.placeholder =
          translatedData.value['common.common.select_a_country'];
        registerForm[1].fields.country.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.country_hover_tezt'
          ];
        registerForm[1].fields.organization_registration_agency.label =
          translatedData.value[
            'common.common.organisation_registration_agency'
          ];
        registerForm[1].fields.organization_registration_agency.placeholder =
          translatedData.value[
            'common.common.select_an_organisation_registration_agency'
          ];
        registerForm[1].fields.organization_registration_agency.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.organisation_registration_agency_hover_text'
          ];
        registerForm[1].fields.organization_registration_no.label =
          translatedData.value[
            'public.register.registered_page.register_section.organisation_registration_number'
          ];
        registerForm[1].fields.organization_registration_no.placeholder =
          translatedData.value[
            'common.common.type_your_registration_number_here'
          ];
        registerForm[1].fields.organization_registration_no.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.organisation_registration_number_hover_text'
          ];
        registerForm[1].fields.organization_registration_no.help_text =
          translatedData.value['common.common.for_example_123456'];
        registerForm[1].fields.iati_organizational_identifier.label =
          translatedData.value['common.common.iati_organisation_identifier'];
        registerForm[1].fields.iati_organizational_identifier.hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.iati_organisation_identifier_hover_text'
          ];
        registerForm[1].fields.iati_organizational_identifier.help_text =
          translatedData.value['common.common.this_is_autogenerated'];
        // Form 2
        registerForm[2].title =
          translatedData.value['common.common.administrator_information'];
        registerForm[2].description =
          translatedData.value[
            'public.register.registered_page.register_section.administrator_description'
          ];
        registerForm[2].hover_text =
          translatedData.value[
            'public.register.registered_page.register_section.administrator_hover_text'
          ];
        registerForm[2].fields.full_name.label =
          translatedData.value['common.common.full_name'];
        registerForm[2].fields.full_name.placeholder =
          translatedData.value['common.common.type_your_full_name_here'];
        registerForm[2].fields.email.label =
          translatedData.value['common.common.email_address'];
        registerForm[2].fields.email.placeholder =
          translatedData.value['common.common.type_valid_email_here'];

        registerForm[2].fields.username.label =
          translatedData.value['common.common.username'];
        registerForm[2].fields.username.placeholder =
          translatedData.value['common.common.type_username_here'];
        registerForm[2].fields.username.hover_text =
          translatedData.value[
            'common.common.you_will_need_this_later_to_login'
          ];
        registerForm[2].fields.default_language.label =
          translatedData.value['elements.label.default_language'];
        registerForm[2].fields.default_language.placeholder =
          translatedData.value['common.common.select_your_default_language'];
        registerForm[2].fields.password.label =
          translatedData.value['common.common.password'];
        registerForm[2].fields.password.placeholder =
          translatedData.value['common.common.type_password_here'];
        registerForm[2].fields.confirm_password.label =
          translatedData.value['common.common.confirm_password'];
        registerForm[2].fields.confirm_password.placeholder =
          translatedData.value['common.common.type_password_here'];
        // Form 3
        registerForm[3].title =
          translatedData.value[
            'public.register.registered_page.register_section.heading_three'
          ];
        registerForm[3].description =
          translatedData.value[
            'common.common.please_verify_and_activate_your_iati_publisher_account'
          ];

        console.log(
          translatedData.value[
            'public.register.registered_page.step_count_out_of_3'
          ]
        );
        translatedStepXOutOf3.value = translatedData.value[
          'public.register.registered_page.step_count_out_of_3'
        ].replace(':count', getCurrentStep());
        console.log(translatedStepXOutOf3.value);
      }
    });

    const registerForm = reactive({
      1: {
        title: 'Publisher Information',
        is_complete: false,
        description:
          'This information will be used to register your organisation',
        hover_text:
          'Provide information about your organisation. You will need to provide the same information that you used to create your Publisher Account on the IATI Registry (iatiregistry.org).',
        fields: {
          publisher_name: {
            label: 'Publisher Name',
            name: 'publisher_name',
            placeholder: 'Type your Publisher Name here',
            id: 'publisher-name',
            required: true,
            hover_text: 'Provide the name of your organisation.',
            type: 'text',
            class: 'col-span-2 mb-4 lg:mb-2',
            help_text: '',
          },
          publisher_id: {
            label: 'Publisher ID',
            name: 'publisher_id',
            placeholder: 'Type your publisher ID here',
            id: 'publisher-id',
            required: true,
            hover_text:
              "This is the unique ID for your organisation that you created when you set up your IATI Registry Publisher Account. It should be a shorter version of your organisation's name, which will include lowercase letters and may include numbers, - (dash) or _ (underscore). For example nef_mali' for Near East Foundation Mali.",
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: '',
          },
          country: {
            label: 'Country',
            name: 'country',
            placeholder: 'Select a Country',
            id: 'country_select',
            required: false,
            type: 'select',
            hover_text: 'Add the location of your organisation.',
            options: props.country,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          organization_registration_agency: {
            label: 'Organisation Registration Agency',
            name: 'registration_agency',
            placeholder: 'Select an Organisation Registration Agency',
            id: 'registration-agency',
            required: true,
            hover_text:
              'Provide the name of the agency in your country where you organisation is registered. If you do not know this information please email support@iatistandard.org.',
            type: 'select',
            options: registration_agency,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          organization_registration_no: {
            label: 'Organisation Registration Number',
            name: 'registration_number',
            placeholder: 'Type your Registration Number here',
            id: 'registration-number',
            required: true,
            hover_text:
              'Add the registration number for your organisation that has been provided by the registration agency named above.',
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: 'E.g. 123456',
          },
          iati_organizational_identifier: {
            label: 'IATI Organisation Identifier',
            name: 'identifier',
            placeholder: '',
            id: 'identifier',
            required: true,
            hover_text:
              'The Organisation Identifier is a unique code for your organisation. This is generated from the Organisation Registration Agency and Registration Number. For more information read: <a href="http://iatistandard.org/en/guidance/preparing-organisation/organisation-account/how-to-create-your-iati-organisation-identifier/" target="_blank">How to create your IATI organisation identifier.</a>',
            type: 'text',
            class: 'mb-4 lg:mb-6',
            help_text:
              'This is autogenerated, please make sure to fill the above fields correctly.',
          },
        },
      },
      2: {
        title: 'Administrator Information',
        is_complete: false,
        description:
          'This information will be used to create an admin account in IATI Publisher',
        hover_text:
          'Provide your information to create an admin account here on IATI Publisher.',
        fields: {
          full_name: {
            label: 'Full Name',
            name: 'full_name',
            placeholder: 'Type your full name here',
            id: 'full-name',
            hover_text: '',
            required: true,
            type: 'text',
            class: 'mb-4 lg:mb-2',
          },
          email: {
            label: 'Email Address',
            name: 'email',
            placeholder: 'Type valid email here',
            id: 'email',
            required: true,
            hover_text: '',
            type: 'email',
            class: 'mb-4 lg:mb-2',
          },
          username: {
            label: 'Username',
            name: 'username',
            placeholder: 'Type username here',
            id: 'username',
            required: true,
            hover_text:
              'You will need this later to login into IATI Publisher.',
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: '',
          },
          default_language: {
            label: 'Default language',
            name: 'default_language',
            placeholder: 'Select your default language',
            id: 'default-language',
            required: true,
            type: 'select',
            options: props.languages,
            class: 'mb-4 lg:mb-2',
          },
          password: {
            label: 'Password',
            name: 'password',
            placeholder: 'Type password here',
            id: 'password',
            required: true,
            hover_text: '',
            type: 'password',
            class: 'mb-4 lg:mb-2',
          },
          confirm_password: {
            label: 'Confirm Password',
            name: 'password_confirmation',
            placeholder: 'Type password here',
            id: 'password-confirmation',
            required: true,
            hover_text: '',
            type: 'password',
            class: 'mb-4 lg:mb-6',
          },
        },
      },
      3: {
        title: 'Email Verification',
        is_complete: false,
        description:
          'Please verify and activate your IATI Publisher account through your provided email',
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

    onMounted(() => {
      LanguageService.getTranslatedData('common,public')
        .then((response) => {
          translatedData.value = response.data;
        })
        .catch((error) => console.log(error));
    });

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
      translatedData,
      translatedStepXOutOf3,
    };
  },
});
</script>

<style src="@vueform/multiselect/themes/default.css"></style>

<style lang="scss">
.label {
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
