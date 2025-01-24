<template>
  <div>
    <div class="registry__info">
      <div class="mb-4 text-sm font-bold text-n-50">
        {{ translatedData['settings.setting.iati_registry_information'] }}
      </div>
      <div class="mb-4 flex items-center text-xs text-n-50">
        <button class="text-base">
          <HoverText
            :name="translatedData['settings.setting.iati_registry_information']"
            :hover-text="
              translatedData[
                'settings.setting_publishing_form.iati_publisher_needs_to_add_your_organisations'
              ]
            "
          />
        </button>
      </div>
    </div>
    <div class="register mt-6" @keyup.enter="autoVerify">
      <div class="register__container">
        <div>
          <div class="relative">
            <div class="flex justify-between">
              <label for="publisher-id"
                >{{ translatedData['common.common.publisher_id'] }}
              </label>
              <button>
                <HoverText
                  width="w-72"
                  :name="translatedData['common.common.publisher_id']"
                  :hover-text="
                    translatedData[
                      'common.common.this_is_the_unique_id_for_your_organisation'
                    ]
                  "
                  :show-iati-reference="true"
                />
              </button>
            </div>
            <input
              id="publisher-id"
              v-model="publisherId"
              class="register__input mb-2"
              :class="{
                error__input: publishingError.publisher_id,
                'hover:cursor-not-allowed': !isSuperadmin,
              }"
              type="text"
              :placeholder="
                translatedData['common.common.type_your_publisher_id_here']
              "
              :disabled="!isSuperadmin"
              @input="updateStore('publisher_id')"
            />
          </div>
          <span v-if="publishingError.publisher_id" class="error" role="alert">
            {{ publishingError.publisher_id }}
          </span>
        </div>
        <div>
          <div class="relative">
            <div class="flex justify-between">
              <label for="api-token"
                >{{ translatedData['common.common.api_token'] }}
              </label>
              <button>
                <HoverText
                  :name="translatedData['common.common.api_token']"
                  :hover-text="
                    translatedData[
                      'common.common.the_api_token_is_a_unique_key_that_is_generated_from_your_organisation'
                    ]
                  "
                  :show-iati-reference="true"
                />
              </button>
            </div>

            <div class="relative">
              <input
                id="api-token"
                v-model="publishingForm.api_token"
                class="register__input mb-2"
                :class="{
                  error__input: publishingError.api_token,
                }"
                :disabled="userRole !== 'admin'"
                type="text"
                :placeholder="
                  translatedData['common.common.enter_api_token_here']
                "
                @input="updateStore('api_token')"
              />
              <ShimmerLoading
                v-if="!initialApiCallCompleted"
                class="!absolute top-[50%] !m-0 !ml-2 !h-8 !w-[96%] -translate-y-1/2"
              />
            </div>
            <span
              v-if="showTag && publishingInfo.isVerificationRequested"
              :class="{
                tag__correct: publishingForm.token_status === 'Correct',
                tag__pending: publishingForm.token_status === 'Pending',
                tag__incorrect: publishingForm.token_status === 'Incorrect',
              }"
            >
              {{
                translatedData[
                  publishingForm.token_status === 'Correct'
                    ? 'common.common.correct'
                    : publishingForm.token_status === 'Pending'
                    ? 'common.common.pending'
                    : 'common.common.incorrect'
                ]
              }}
            </span>
          </div>
          <span v-if="publishingError.api_token" class="error" role="alert">
            {{ publishingError.api_token }}
          </span>
        </div>
      </div>
      <button
        :class="userRole !== 'admin' && 'cursor-not-allowed'"
        class="primary-btn verify-btn"
        @click="submitPublishing"
      >
        {{ translatedData['common.common.verify'] }}
      </button>
    </div>
  </div>
</template>
<script lang="ts">
import { defineComponent, ref, computed, inject, watch } from 'vue';
import { useStore } from '../../store';
import { ActionTypes } from '../../store/setting/actions';
import HoverText from './../../components/HoverText.vue';
import ShimmerLoading from 'Components/ShimmerLoading.vue';

export default defineComponent({
  components: {
    ShimmerLoading,
    HoverText,
  },
  props: {
    organization: {
      type: Object,
      required: true,
    },
    initialApiCallCompleted: {
      type: Boolean,
      required: false,
    },
    showTag: {
      type: Boolean,
      require: false,
    },
  },
  emits: ['submitPublishing'],

  setup(props, { emit }) {
    const translatedData = inject('translatedData') as Record<string, string>;
    const tab = ref('publish');
    const store = useStore();
    const userRole = inject('userRole');
    const isSuperadmin = inject('isSuperadmin');
    const publisherId = ref(props.organization.publisher_id);

    watch(
      () => publisherId.value,
      (publisherId) => {
        store.dispatch(ActionTypes['UPDATE_PUBLISHING_FORM'], {
          key: 'publisher_id',
          value: publisherId,
        });
      }
    );

    interface ObjectType {
      [key: string]: string;
    }

    const publishingForm = computed(() => store.state.publishingForm);

    const publishingInfo = computed(() => store.state.publishingInfo);

    const publishingError = computed(
      () => store.state.publishingError as ObjectType
    );

    function submitPublishing() {
      if (userRole === 'admin') {
        emit('submitPublishing');
      }
    }

    function autoVerify() {
      emit('submitPublishing');
    }

    function updateStore(key: keyof typeof publishingForm.value) {
      store.dispatch(ActionTypes.UPDATE_PUBLISHING_FORM, {
        key: key,
        value: publishingForm.value[key],
      });
    }

    function toggleTab() {
      tab.value = tab.value === 'publish' ? 'default' : 'publish';
    }

    return {
      tab,
      publishingForm,
      publishingInfo,
      publishingError,
      store,
      props,
      userRole,
      submitPublishing,
      toggleTab,
      updateStore,
      autoVerify,
      isSuperadmin,
      publisherId,
      translatedData,
    };
  },
});
</script>
