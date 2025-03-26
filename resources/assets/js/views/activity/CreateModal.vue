<template>
  <!--====================
        Add Activity Modal
    ========================-->
  <Modal :modal-active="props.modalValue">
    <Loader v-if="loaderVisibility" />

    <h5 class="title mb-5 flex text-xl font-bold text-bluecoral sm:text-2xl">
      {{
        translatedData[
          'activity_index.create_modal.add_a_title_and_identifier_for_the_activity'
        ]
      }}
    </h5>
    <div class="manual-import overflow-hidden">
      <div class="input__field">
        <div class="mb-5">
          <div class="form-group-title-container">
            <HoverText
              :name="translatedData['elements.label.title']"
              :hover-text="
                translatedData['elements.element_json_schema.title_hover_text']
              "
              position="right"
              :show-iati-reference="true"
            />
            <p class="form-group-title">
              {{ translatedData['elements.label.title'] }}
            </p>
          </div>
          <div class="form-group">
            <div class="form__content gap-6">
              <div>
                <div class="label-field">
                  <label class="label" for="narrative"
                    >{{ translatedData['elements.label.narrative'] }}
                    <span class="required-icon"> *</span>
                  </label>
                  <HoverText
                    :name="translatedData['elements.label.narrative']"
                    :hover-text="
                      translatedData[
                        'elements.element_json_schema.title_sub_elements_narrative_hover_text'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </div>
                <input
                  v-model="formData.narrative"
                  class="form__input"
                  :class="{
                    error__input: errorData.narrative != '',
                  }"
                  type="text"
                  :placeholder="translatedData['common.common.enter_narrative']"
                />
                <span
                  v-if="errorData.narrative != ''"
                  class="error"
                  role="alert"
                >
                  {{ errorData.narrative }}
                </span>
              </div>
              <div>
                <div class="label-field">
                  <label class="label" for="">
                    {{ translatedData['elements.label.language'] }}
                    <span class="required-icon"> *</span>
                  </label>
                  <HoverText
                    :name="translatedData['elements.label.language']"
                    :hover-text="
                      translatedData[
                        'activity_index.create_modal.a_code_specifying_the_language_of_text_in_this_element'
                      ]
                    "
                    :show-iati-reference="true"
                  />
                </div>

                <Multiselect
                  v-model="formData.language"
                  class="vue__select"
                  :class="{
                    error__input: errorData.language != '',
                    'default-value-indicator': defaultLanguage,
                  }"
                  :searchable="true"
                  :options="languages"
                  :placeholder="
                    defaultLanguage ??
                    translatedData['common.common.select_language']
                  "
                />

                <span
                  v-if="errorData.language != ''"
                  class="error"
                  role="alert"
                >
                  {{ errorData.language }}
                </span>

                <span v-else class="text-xs font-normal text-n-40"
                  >{{
                    translatedData[
                      'common.common.if_no_language_is_selected_your_default_language_is_assumed'
                    ]
                  }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div>
          <div class="form-group-title-container">
            <HoverText
              :name="translatedData['elements.name.iati_identifier']"
              position="right"
              :hover-text="
                translatedData[
                  'activity_index.create_modal.a_globally_unique_identifier_for_the_activity'
                ]
              "
              :show-iati-reference="true"
            />
            <p class="form-group-title">
              {{ translatedData['elements.name.iati_identifier'] }}
            </p>
          </div>
          <div class="form-group">
            <div class="form__content">
              <div>
                <div class="label-field">
                  <label class="label" for=""
                    >{{ translatedData['elements.label.activity_identifier'] }}
                    <span class="required-icon"> *</span>
                  </label>
                </div>
                <input
                  v-model="formData.activity_identifier"
                  class="form__input"
                  :class="{
                    error__input: errorData.activity_identifier != '',
                  }"
                  type="text"
                  :placeholder="
                    translatedData[
                      'activity_index.create_modal.type_activity_identifier_here'
                    ]
                  "
                />
                <span
                  v-if="errorData.activity_identifier != ''"
                  class="error"
                  role="alert"
                >
                  {{ errorData.activity_identifier }}
                </span>
                <span v-else class="text-xs font-normal text-n-40">
                  {{
                    translatedData[
                      'activity_index.create_modal.enter_your_own_unique_activity_identifier_such_as_abbreviation_or_simply_a_number'
                    ]
                  }}
                </span>
              </div>
              <div>
                <div class="label-field">
                  <label class="label" for=""
                    >{{
                      translatedData['elements.label.iati_identifier_block']
                    }}
                    <span class="required-icon"> *</span>
                  </label>
                </div>
                <input
                  class="form__input"
                  type="text"
                  placeholder=""
                  :value="iatiIdentifierText"
                  disabled="true"
                />

                <span
                  v-if="errorData.iati_identifier_text != ''"
                  class="error"
                  role="alert"
                >
                  {{ errorData.iati_identifier_text }}
                </span>

                <span v-else class="text-xs font-normal text-n-40"
                  >{{
                    translatedData[
                      'activity_index.create_modal.this_is_autogenerated'
                    ]
                  }}
                </span>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-8 flex justify-end">
          <div class="inline-flex">
            <BtnComponent
              class="mx-3 bg-white px-3 uppercase"
              type=""
              :text="translatedData['common.common.cancel']"
              @click="closeModal"
            />
            <BtnComponent
              class="space"
              type="primary"
              :text="translatedData['common.common.save']"
              @click="storeActivity()"
            />
          </div>
        </div>
      </div>
    </div>
  </Modal>
</template>

<script lang="ts">
import {
  defineComponent,
  inject,
  onMounted,
  reactive,
  ref,
  computed,
} from 'vue';
import Modal from '../../components/PopupModal.vue';
import BtnComponent from '../../components/ButtonComponent.vue';
import Multiselect from '@vueform/multiselect';
import HoverText from '../../components/HoverText.vue';
import Loader from '../../components/Loader.vue';
import axios from 'axios';

export default defineComponent({
  components: {
    Modal,
    BtnComponent,
    HoverText,
    Multiselect,
    Loader,
  },
  props: {
    modalValue: {
      type: Boolean,
      required: false,
    },
  },
  emits: ['closeModal', 'toast'],

  setup(props, { emit }) {
    interface ObjectType {
      [key: string]: string;
    }

    const formData: ObjectType = reactive({
      narrative: '',
      language: '',
      activity_identifier: '',
      iati_identifier_text: '',
    });

    const errorData: ObjectType = reactive({
      narrative: '',
      language: '',
      activity_identifier: '',
      iati_identifier_text: '',
    });

    const iatiIdentifierText = computed(() => {
      return formData.activity_identifier
        ? organization.identifier + '-' + formData.activity_identifier
        : '';
    });

    const loaderVisibility = ref(false);

    const languages = reactive({});
    const organization: ObjectType = reactive({});
    const translatedData = inject('translatedData') as Record<string, string>;
    const defaultLanguage = inject('defaultLanguage');

    onMounted(async () => {
      axios.get('/activities/codelists').then((res) => {
        const response = res.data;
        Object.assign(languages, response.data.languages);
        Object.assign(organization, response.data.organization);
      });
    });

    function closeModal() {
      emit('closeModal');
    }

    function storeActivity() {
      loaderVisibility.value = true;
      formData.iati_identifier_text = iatiIdentifierText.value;

      axios
        .post('/activity', formData)
        .then((res) => {
          const response = res.data;
          loaderVisibility.value = false;

          if (response.success) {
            emit('closeModal');
            window.location.href = `/activity/${response.data.id}`;
          }
        })
        .catch((error) => {
          const { errors } = error.response.data;

          errorData.narrative = errors.narrative ? errors.narrative[0] : '';
          errorData.language = errors.language ? errors.language[0] : '';
          errorData.activity_identifier = errors.activity_identifier
            ? errors.activity_identifier[0]
            : '';

          loaderVisibility.value = false;
        });
    }

    return {
      props,
      formData,
      errorData,
      loaderVisibility,
      languages,
      organization,
      closeModal,
      storeActivity,
      defaultLanguage,
      translatedData,
      iatiIdentifierText,
    };
  },
});
</script>

<style lang="scss" scoped>
.form-group {
  @apply rounded-lg border border-n-20 p-5;

  &:last-child {
    margin-bottom: 0;
  }

  .form__content {
    margin-top: 0;
    gap: 25px;

    .label-field {
      @apply mb-2 flex items-center justify-between;
    }
  }
}

.form-group-title-container {
  @apply mb-1.5 flex space-x-1;
}

.form-group-title {
  @apply text-xs font-bold text-bluecoral;
}
</style>
