<template>
  <section
    class="section register-page mx-3 mb-7 sm:mx-10 sm:mb-10 md:mb-14 xl:mx-24 xl:px-1"
  >
    <Loader v-if="isLoaderVisible" />
    <div class="section__container">
      <div class="section__title">
        <h2 class="text-2xl font-bold md:text-4xl">
          {{ translatedData['public.register.not_registered_page.heading'] }}
        </h2>

        <p>
          {{
            translatedData['public.register.not_registered_page.start_journey']
          }}
        </p>
      </div>
      <div class="section__wrapper flex justify-center">
        <EmailVerification v-if="checkStep('5')" :email="formData['email']" />
        <div v-else class="form input__field" @keyup.enter="goToNextForm">
          <aside class="mb-4 block border-b border-b-n-10 pb-4 xl:hidden">
            <span class="text-base font-bold">
              {{ translatedStepXOutOf5 }}
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
              v-if="Object.keys(iatiError).length > 0"
              class="feedback mt-6 border-l-2 border-crimson-50 bg-crimson-10 p-4 text-sm text-n-50"
            >
              <p class="mb-2 flex font-bold">
                <svg-vue class="mr-2 text-xl" icon="warning" />
                Error:
              </p>
              <div class="ml-8 xl:mr-1">
                <ul class="list-disc">
                  {{
                    iatiError
                  }}
                  <li v-for="(error, error_key) in iatiError" :key="error_key">
                    <span v-if="typeof error === 'object'">{{ error[0] }}</span>
                    <span v-else>{{ error }}</span>
                  </li>
                </ul>
              </div>
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
                <textarea
                  v-if="field.type === 'textarea'"
                  ref="textarea"
                  v-model="formData[field.name]"
                  :placeholder="field.placeholder"
                  :class="{
                    'error_input form__input ': errorData[field.name],
                    'form__input ': !errorData[field.name],
                  }"
                  @focus="resize($event)"
                  @keyup="resize($event)"
                  @keyup.enter.stop
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
                >{{ translatedData['common.common.sign_in'] }} .</a
              ></span
            >
            <button
              v-if="!checkStep(5)"
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
                >{{ translatedData['common.common.sign_in'] }} .</a
              ></span
            >
          </div>
        </div>

        <aside class="register__sidebar hidden xl:block">
          <span class="text-base font-bold">
            {{ translatedStepXOutOf5 }}
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
                  :class="{
                    'font-bold text-n-50 ': checkStep(key),
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
    types: {
      type: Object,
      required: true,
    },
  },

  setup(props) {
    const translatedData = ref({});
    const step = ref(1);
    const publisherExists = ref(true);
    const isLoaderVisible = ref(false);
    const textarea = ref(null);
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
      publisher_type: '',
      license_id: '',
      image_url: '',
      description: '',
      contact_email: '',
      website: '',
      address: '',
      source: '',
      record_exclusions: '',
      username: '',
      full_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      default_language: '',
    });

    const iatiError: ObjectType = reactive({});

    const formData: ObjectType = reactive({
      publisher_name: '',
      publisher_id: '',
      country: '',
      registration_agency: '',
      registration_number: '',
      identifier: '',
      publisher_type: '',
      license_id: '',
      image_url: '',
      description: '',
      contact_email: '',
      website: '',
      address: '',
      source: '',
      record_exclusions: '',
      username: '',
      full_name: '',
      email: '',
      password: '',
      password_confirmation: '',
      default_language: '',
      step: '1',
    });

    watch(
      () => formData.country,
      () => {
        formData.registration_agency = '';
      }
    );
    function resize(event) {
      event.target.style.height = 'auto';
      event.target.style.height = `${event.target.scrollHeight}px`;
    }

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
      const agencies = props.types.registrationAgency;

      if (formData.country) {
        const uncategorized = props.types.uncategorizedRegistrationAgencyPrefix;

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

    /**
     * For translated : Step 1 out of 5 and stuff.
     */
    const translatedStepXOutOf5 = ref('');

    /**
     * For translation
     * Since actual texts are inside a reactive objects, we need a watch effect.
     * */
    watchEffect(() => {
      if (translatedData.value && registerForm) {
        // Form 1
        registerForm[1].title =
          translatedData.value['common.common.publisher_information'];
        registerForm[1].description =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_information_description'
          ];
        registerForm[1].hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_information_hover_text'
          ];
        registerForm[1].fields.publisher_name.label =
          translatedData.value['common.common.publisher_name'];
        registerForm[1].fields.publisher_name.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_name_placeholder'
          ];
        registerForm[1].fields.publisher_name.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_name_hover_text'
          ];
        registerForm[1].fields.publisher_id.label =
          translatedData.value['common.common.publisher_id'];
        registerForm[1].fields.publisher_id.placeholder =
          translatedData.value['common.common.type_your_publisher_id_here'];
        registerForm[1].fields.publisher_id.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_id_hover_text'
          ];
        registerForm[1].fields.country.label =
          translatedData.value['common.common.country'];
        registerForm[1].fields.country.placeholder =
          translatedData.value['common.common.select_a_country'];
        registerForm[1].fields.country.hover_text =
          translatedData.value[
            'common.common.add_the_location_of_your_organisation'
          ];
        registerForm[1].fields.registration_agency.label =
          translatedData.value[
            'elements.label.organisation_registration_agency'
          ];
        registerForm[1].fields.registration_agency.placeholder =
          translatedData.value[
            'common.common.select_an_organisation_registration_agency'
          ];
        registerForm[1].fields.registration_agency.hover_text =
          translatedData.value['common/common.registration_number'];
        registerForm[1].fields.registration_number.label =
          translatedData.value['common/common.registration_number'];
        registerForm[1].fields.registration_number.placeholder =
          translatedData.value[
            'common.common.type_your_registration_number_here'
          ];
        registerForm[1].fields.registration_number.hover_text =
          translatedData.value['common.common.registration_number'];
        registerForm[1].fields.registration_number.help_text =
          translatedData.value['common.common.for_example_123456'];
        registerForm[1].fields.identifier.label =
          translatedData.value['common.common.iati_organisation_identifier'];
        registerForm[1].fields.identifier.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.identifier_placeholder'
          ];
        registerForm[1].fields.identifier.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.identifier_hover_text'
          ];
        registerForm[1].fields.identifier.help_text =
          translatedData.value['common.common.this_is_autogenerated'];
        registerForm[1].fields.publisher_type.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_type'
          ];
        registerForm[1].fields.publisher_type.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_type_placeholder'
          ];
        registerForm[1].fields.publisher_type.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_type_hover_text'
          ];
        registerForm[1].fields.license_id.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.data_licence'
          ];
        registerForm[1].fields.license_id.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.data_licence_placeholder'
          ];
        registerForm[1].fields.license_id.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.data_licence_hover_text'
          ];
        registerForm[1].fields.image_url.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_logo_url'
          ];
        registerForm[1].fields.image_url.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_logo_url_placeholder'
          ];
        registerForm[1].fields.image_url.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.publisher_logo_url_hover_text'
          ];
        registerForm[1].fields.description.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_description'
          ];
        registerForm[1].fields.description.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_description_placeholder'
          ];
        registerForm[1].fields.description.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.organisation_description_hover_text'
          ];
        // Form 2
        registerForm[2].title =
          translatedData.value[
            'public.register.not_registered_page.register_section.heading_two'
          ];
        registerForm[2].description =
          translatedData.value[
            'public.register.not_registered_page.register_section.contact_information_description'
          ];
        registerForm[2].fields.contact_email.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.contact_email'
          ];
        registerForm[2].fields.contact_email.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.contact_email_hover_text'
          ];

        registerForm[2].fields.website.label =
          translatedData.value['elements.label.website'];

        registerForm[2].fields.website.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.website_hover_text'
          ];
        registerForm[2].fields.website.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.website_placeholder'
          ];
        registerForm[2].fields.address.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.address'
          ];
        registerForm[2].fields.address.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.address_hover_text'
          ];
        registerForm[2].fields.address.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.address_placeholder'
          ];
        // Form 3
        registerForm[3].title =
          translatedData.value['common.common.email_verification'];
        registerForm[3].description =
          translatedData.value[
            'public.register.not_registered_page.register_section.publishing_additional_information_description'
          ];
        registerForm[3].fields.source.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.source'
          ];
        registerForm[3].fields.source.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.source_hover_text'
          ];
        registerForm[3].fields.source.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.source_placeholder'
          ];
        registerForm[3].fields.default_language.label =
          translatedData.value['elements.label.default_language'];
        registerForm[3].fields.default_language.placeholder =
          translatedData.value['common.common.select_your_default_language'];

        registerForm[3].fields.record_exclusions.label =
          translatedData.value[
            'public.register.not_registered_page.register_section.record_exclusions'
          ];
        registerForm[3].fields.record_exclusions.hover_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.record_exclusions_hover_text'
          ];
        registerForm[3].fields.record_exclusions.placeholder =
          translatedData.value[
            'public.register.not_registered_page.register_section.record_exclusions_placeholder'
          ];
        // Form 4
        registerForm[4].title =
          translatedData.value['common.common.administrator_information'];
        registerForm[4].description =
          translatedData.value[
            'public.register.not_registered_page.register_section.administrator_information_description'
          ];
        registerForm[4].fields.full_name.label =
          translatedData.value['common.common.full_name'];
        registerForm[4].fields.full_name.placeholder =
          translatedData.value['common.common.type_your_full_name_here'];

        registerForm[4].fields.email.label =
          translatedData.value['common.common.email_address'];
        registerForm[4].fields.email.placeholder =
          translatedData.value['common.common.type_valid_email_here'];

        registerForm[4].fields.username.label =
          translatedData.value['common.common.username'];
        registerForm[4].fields.username.placeholder =
          translatedData.value['common.common.type_username_here'];
        registerForm[4].fields.username.hover_text =
          translatedData.value[
            'common.common.you_will_need_this_later_to_login'
          ];
        registerForm[4].fields.password.label =
          translatedData.value['common.common.password'];
        registerForm[4].fields.password.placeholder =
          translatedData.value['common.common.type_password_here'];

        registerForm[4].fields.password.help_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.password_help_text'
          ];
        registerForm[4].fields.password_confirmation.label =
          translatedData.value['common.common.confirm_password'];
        registerForm[4].fields.password_confirmation.placeholder =
          translatedData.value['common.common.type_password_here'];
        registerForm[4].fields.password_confirmation.help_text =
          translatedData.value[
            'public.register.not_registered_page.register_section.confirm_password_help_text'
          ];
        // Form 5
        registerForm[5].title =
          translatedData.value['common.common.email_verification'];
        registerForm[5].description =
          translatedData.value[
            'common.common.please_verify_and_activate_your_iati_publisher_account'
          ];

        translatedStepXOutOf5.value = translatedData.value[
          'public.register.not_registered_page.step_count_out_of_5'
        ].replace(':count', getCurrentStep());
      }
    });

    /**
     * object with multi-step form information
     */
    const registerForm = reactive({
      1: {
        title: 'Publisher Information',
        is_complete: false,
        description:
          'This information will be used to register your organisation as an IATI publisher',
        hover_text:
          "We refer to organisations who publish IATI data as 'Publishers'. Before publishing data, all organisations need their own 'Publisher Account' on the IATI Registry (iatiregistry.org). Enter your organisation's data here and we'll create your organisation's Publisher Account for you. These details will also be saved here in IATI Publisher. ",
        fields: {
          publisher_name: {
            label: 'Publisher Name',
            name: 'publisher_name',
            placeholder: 'Type your organisation name here',
            id: 'publisher-name',
            required: true,
            hover_text: 'The name of your organisation publishing the data.',
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
              "Provide a unique ID for your organisation. It must be at least two characters long and use lower case letters. You can include letters, numbers and also - (dash) and _ (underscore). Where possible use a short abbreviation of your organisation's name, for example: 'nef_mali' for Near East Foundation Mali.",
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
            options: props.types.country,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          registration_agency: {
            label: 'Organisation Registration Agency',
            name: 'registration_agency',
            placeholder: 'Select an Organisation Registration Agency',
            id: 'registration-agency',
            required: true,
            hover_text:
              "Select the agency in your country where your organisation is registered. If you do not know this information please email <a href='mailto:support@iatistandard.org' target='_blank'>support@iatistandard.org</a>",
            type: 'select',
            options: registration_agency,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          registration_number: {
            label: 'Registration Number',
            name: 'registration_number',
            placeholder: 'Type your Registration Number here',
            id: 'registration-number',
            required: true,
            hover_text:
              "Provide the registration number for your organisation that has been provided by organisation registration agency. If you do not know this please email <a href='mailto:support@iatistandard.org' target='_blank'>support@iatistandard.org</a>.",
            type: 'text',
            class: 'mb-4 lg:mb-2',
            help_text: 'E.g. 123456',
          },
          identifier: {
            label: 'IATI Organisation Identifier',
            name: 'identifier',
            placeholder: '',
            id: 'identifier',
            required: true,
            hover_text:
              'The Organisation Identifier is a unique code for your organisation. This is genereated from the Organisation Registration Agency and Registration Number. For more information read:  <a href="http://iatistandard.org/en/guidance/preparing-organisation/organisation-account/how-to-create-your-iati-organisation-identifier/" target="_blank">How to create your IATI organisation identifier.</a>',
            type: 'text',
            class: 'mb-4 lg:mb-6',
            help_text:
              'This is autogenerated, please make sure to fill the above fields correctly.',
          },
          publisher_type: {
            label: 'Organisation Type',
            name: 'publisher_type',
            placeholder: 'Select an organisation type',
            id: 'publisher-type',
            required: true,
            hover_text:
              'Select the type that best describes your organisation.  <a href="https://iatistandard.org/en/iati-standard/203/codelists/organisationtype/" target="_blank"> Read more on Organisation types.</a>',
            type: 'select',
            options: props.types.publisherType,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          license_id: {
            label: 'Data Licence',
            name: 'license_id',
            placeholder: 'Select a Data Licence',
            id: 'data-license',
            required: true,
            hover_text:
              " Select the License under which your data is being published. IATI is an open data standard and requires you to make your data available under an open licence so it can be freely used. One of the most frequently used licenses is Creative Commons Attribution. <a href='https://iatistandard.org/en/guidance/standard-overview/preparing-your-organisation-data-publication/how-to-license-your-data/' target='_blank' > For more information read: How to license your data.</a>",
            type: 'select',
            options: props.types.dataLicense,
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          image_url: {
            label: 'Publisher Logo Url',
            name: 'image_url',
            placeholder: 'E.g. http://mylogo.com ',
            id: 'publisher-logo-url',
            required: false,
            hover_text:
              " Provide a link to an image to your organisation's logo (Optimum size: 200 x 120 px)",
            type: 'text',
            class: 'mb-4 lg:mb-2 relative',
            help_text: '',
          },
          description: {
            label: 'Organisation Description',
            name: 'description',
            placeholder: 'Type Description here',
            id: 'organization-description',
            required: false,
            hover_text: ' Provide a short description about your organisation.',
            type: 'textarea',
            class: 'mb-4 col-span-2 lg:mb-2 relative',
            help_text: '',
          },
        },
      },
      2: {
        title: 'Contact Information',
        is_complete: false,
        description: "This is your organisation's contact information",
        fields: {
          contact_email: {
            label: 'Contact Email',
            name: 'contact_email',
            placeholder: '',
            id: 'contact-email',
            required: true,
            hover_text:
              'Please add a contact email address for your organisation. Please note that IATI is an open data standard and the email provided here will be visible to others on the IATI Registry.',
            type: 'text',
            class: 'mb-4  lg:mb-6',
          },
          website: {
            label: 'Website',
            name: 'website',
            placeholder: 'E.g. http://mywebsite.com',
            id: 'website',
            required: false,
            hover_text: "Add the URL to your organisation's website.",
            type: 'text',
            class: 'mb-4 lg:mb-6',
          },
          address: {
            label: 'Address',
            name: 'address',
            placeholder: 'Type address here',
            id: 'address',
            required: false,
            hover_text: 'Provide a contact address for your organisation.',
            type: 'textarea',
            class: 'mb-4 col-span-2 lg:mb-6',
          },
        },
      },
      3: {
        title: 'Publishing Additional Information',
        is_complete: false,
        description: 'This is about how your organisation will publish data',
        fields: {
          source: {
            label: 'Source',
            name: 'source',
            placeholder: 'Select a Source',
            id: 'contact-email',
            required: true,
            hover_text:
              "Select an option:<br>Primary - your organisation is publishing its own or (associated organisations') data <br>Secondary - your organisation is reproducing data on the activities of another organisation",
            type: 'select',
            options: props.types.source,
            class: 'mb-4 lg:mb-6',
          },
          default_language: {
            label: 'Default language',
            name: 'default_language',
            placeholder: 'Select your default language',
            id: 'default-language',
            required: true,
            type: 'select',
            options: props.types.languages,
            class: 'mb-4 lg:mb-6',
          },
          record_exclusions: {
            label: 'Record Exclusions',
            name: 'record_exclusions',
            placeholder: 'Type Record Exclusions here',
            id: 'record-exclusions',
            required: false,
            hover_text:
              "Does your organisation have an exclusion policy that provide details on what data that it cannot publish? For example an organisation may not be able to publish data because of political sensitivity issues or if information is commercially restricted. Please provide details here about what data your organisation needs to exclude (if any), and a URL to your organisation's exclusion policy (if it has one).<a href='https://iatistandard.org/en/guidance/standard-overview/preparing-your-organisation-data-publication/information-and-data-you-cant-publish-exclusions/' target='_blank'> For more information read: Information and data you can't publish (exclusions)</a>",
            type: 'textarea',
            class: 'mb-4  col-span-2 lg:mb-6',
          },
        },
      },
      4: {
        title: 'Administrator Information',
        is_complete: false,
        description:
          'This will create an admin account for you as an individual',
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
            class: 'col-start-1 mb-4 lg:mb-2',
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
          password: {
            label: 'Password',
            name: 'password',
            placeholder: 'Type password here',
            id: 'password',
            required: true,
            help_text: 'Minimum length: 8 characters',
            type: 'password',
            class: 'mb-4 lg:mb-2',
          },
          password_confirmation: {
            label: 'Confirm Password',
            name: 'password_confirmation',
            placeholder: 'Type password here',
            id: 'password-confirmation',
            required: true,
            help_text: 'This should match the password on the left',
            type: 'password',
            class: 'mb-4 lg:mb-6',
          },
        },
      },
      5: {
        title: 'Email Verification',
        is_complete: false,
        description:
          'Please verify and activate your IATI Publisher account through your provided email',
      },
    });

    /**
     * Update Validation errors from api into errorData array
     */
    function updateValidationErrors(errorResponse) {
      cleanValidationErrors();
      for (const field in errorData) {
        errorData[field] = errorResponse[field] ? errorResponse[field][0] : '';
      }
    }

    /**
     * Update Validation errors from api into errorData array
     */
    function cleanValidationErrors() {
      for (const field in errorData) {
        errorData[field] = '';
      }
    }

    /**
     * Update IATI and system Error
     */
    function updateErrors(errorResponse) {
      if (
        Object.values(errorData).every((value) => value === '') ||
        step.value === 4
      ) {
        Object.assign(
          iatiError,
          typeof errorResponse === 'string'
            ? { error: errorResponse }
            : errorResponse
        );

        setTimeout(() => {
          cleanIatiErrors();
        }, 35000);
      }
    }

    function cleanIatiErrors() {
      for (const err in iatiError) {
        delete iatiError[err];
      }
    }

    /**
     * Verifies publisher
     */
    function verifyPublisher() {
      isLoaderVisible.value = true;

      formData.identifier = `${formData.registration_agency}-${formData.registration_number}`;
      formData.step = '1';

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/iati/register/publisher', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          publisherExists.value = true;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];
          registerForm['1'].is_complete = false;

          if ('publisher_error' in response) {
            publisherExists.value = false;
          }

          if (response.success) {
            cleanValidationErrors();
            registerForm['1'].is_complete = true;

            updateStep(1);
          } else {
            updateValidationErrors(errors);
            updateErrors(errors);
          }

          isLoaderVisible.value = false;
        })
        .catch((err) => {
          updateErrors(err);
          isLoaderVisible.value = false;
        });
    }

    /**
     * Submits registration Form
     */
    function verifyContactInformation() {
      isLoaderVisible.value = true;
      formData.step = '2';

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/iati/register/contact', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];

          updateValidationErrors(errors);
          isLoaderVisible.value = false;
          registerForm['2'].is_complete = false;

          if (response.success) {
            cleanValidationErrors();
            registerForm['2'].is_complete = true;
            updateStep(2);
          } else {
            updateErrors(errors);
          }
        })
        .catch((error) => {
          updateErrors(error);
          isLoaderVisible.value = false;
        });
    }

    /**
     * Submits registration Form
     */
    function verifyAdditionalInformation() {
      isLoaderVisible.value = true;
      formData.step = '3';

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/iati/register/additional', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];
          updateValidationErrors(errors);
          isLoaderVisible.value = false;
          registerForm['3'].is_complete = false;

          if (response.success) {
            cleanValidationErrors();
            registerForm['3'].is_complete = true;
            updateStep(3);
          } else {
            updateErrors(errors);
          }
        })
        .catch((error) => {
          updateErrors(error);
          isLoaderVisible.value = false;
        });
    }

    /**
     * Submits registration Form
     */
    function submitForm() {
      isLoaderVisible.value = true;
      formData.step = '4';

      let form = {
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      };

      axios
        .post('/iati/register', { ...formData, ...form })
        .then((res) => {
          if (res.request.responseURL.includes('activities')) {
            window.location.href = '/activities';
          }

          const response = res.data;
          const errors =
            !response.success || 'errors' in response ? response.errors : [];
          updateValidationErrors(errors);
          cleanIatiErrors();
          Object.assign(iatiError, errors);
          isLoaderVisible.value = false;
          registerForm['4'].is_complete = false;

          if (response.success) {
            cleanValidationErrors();
            registerForm['4'].is_complete = true;
            updateStep(4);
          }
        })
        .catch((error) => {
          updateErrors(error);

          isLoaderVisible.value = false;
        });
    }

    function getCurrentStep() {
      return step.value.toString();
    }

    function updateStep(current_step) {
      if (current_step === step.value) {
        step.value += 1;
      }
    }

    /**
     * calls submit function based on current step value
     */
    function goToNextForm() {
      switch (step.value) {
        case 1:
          verifyPublisher();
          break;
        case 2:
          verifyContactInformation();
          break;
        case 3:
          verifyAdditionalInformation();
          break;
        case 4:
          submitForm();
          break;
      }
    }

    function goToPreviousForm() {
      cleanIatiErrors();
      step.value -= 1;
    }

    onMounted(() => {
      LanguageService.getTranslatedData('workflow_frontend,common,public')
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
      iatiError,
      isTextField,
      props,
      step,
      resize,
      textarea,
      translatedData,
      translatedStepXOutOf5,
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
        height: 100%;
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
