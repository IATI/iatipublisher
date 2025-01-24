<template>
  <div>
    <div class="registry__info">
      <div class="mb-4 text-sm font-bold text-n-50">
        {{ translatedData['common.common.default_values'] }}
      </div>
      <div class="mb-4 flex items-center text-xs text-n-50">
        <button class="text-base">
          <HoverText
            :name="translatedData['common.common.default_values']"
            :hover-text="
              translatedData[
                'settings.setting_default_form.these_values_will_be_automatically_added'
              ]
            "
          />
        </button>
      </div>
    </div>
    <span class="text-sm font-bold text-n-50">{{
      translatedData['common.common.default_for_all_data']
    }}</span>
    <div class="register mb-4 mt-4">
      <div class="register__container mb-0">
        <div>
          <div class="flex justify-between">
            <label for="default-currency">{{
              toTitleCase(translatedData['elements.label.default_currency'])
            }}</label>
            <button>
              <HoverText
                :name="
                  toTitleCase(translatedData['elements.label.default_currency'])
                "
                :hover-text="
                  translatedData[
                    'common.common.the_currency_in_which_you_report_your_financial_transactions'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>

          <Multiselect
            id="default-currency"
            v-model="defaultForm.default_currency"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.currencies"
            :searchable="true"
            @click="updateStore('default_currency')"
          />
          <span v-if="defaultError.default_currency" class="error" role="alert">
            {{ defaultError.default_currency }}
          </span>
        </div>
        <div>
          <div class="flex justify-between">
            <label for="default-language">{{
              toTitleCase(translatedData['elements.label.default_language'])
            }}</label>
            <button>
              <HoverText
                :name="
                  toTitleCase(translatedData['elements.label.default_language'])
                "
                :hover-text="
                  translatedData[
                    'common.common.the_language_in_which_you_provide_data_on_your_activities'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="default-language"
            v-model="defaultForm.default_language"
            class="vue__select"
            :class="{
              error__input: defaultError.default_language,
            }"
            :disabled="userRole !== 'admin'"
            :placeholder="translatedData['common.common.select_language']"
            :searchable="true"
            :options="props.languages"
            @click="updateStore('default_language')"
          />
          <span v-if="defaultError.default_language" class="error" role="alert">
            {{ defaultError.default_language }}
          </span>
        </div>
      </div>
    </div>

    <span class="text-sm font-bold text-n-50">{{
      translatedData[
        'settings.setting_default_form.recommended_defaults_for_activity_data'
      ]
    }}</span>
    <div class="register mb-4 mt-4">
      <div class="register__container">
        <!-- Default Hierarchy -->
        <div>
          <div class="flex justify-between">
            <label for="default-hierarchy">{{
              toTitleCase(translatedData['elements.label.default_hierarchy'])
            }}</label>
            <button>
              <HoverText
                width="w-64"
                :name="
                  toTitleCase(
                    translatedData['elements.label.default_hierarchy']
                  )
                "
                :hover-text="
                  translatedData[
                    'settings.setting_default_form.if_you_are_reporting_both_programmes_parent_activities'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <input
            id="default-hierarchy"
            v-model="defaultForm.hierarchy"
            :disabled="userRole !== 'admin'"
            class="register__input mb-2"
            type="text"
            :placeholder="
              translatedData['common.common.type_default_hierarchy_here']
            "
            @input="updateStore('hierarchy')"
          />
          <span v-if="defaultError.hierarchy" class="error" role="alert">
            {{ defaultError.hierarchy }}
          </span>
        </div>
        <!-- Default Hierarchy -->

        <!-- Humanitarian -->
        <div>
          <div class="flex justify-between">
            <label for="humanitarian">
              {{
                toTitleCase(translatedData['elements.label.humanitarian'])
              }}</label
            >
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(translatedData['elements.label.humanitarian'])
                "
                :hover-text="
                  translatedData[
                    'common.common.add_a_humanitarian_flag_to_every_activity'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="humanitarian"
            v-model="defaultForm.humanitarian"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.humanitarian }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.humanitarian"
            :searchable="true"
            @click="updateStore('humanitarian')"
          />
          <span v-if="defaultError.humanitarian" class="error" role="alert">
            {{ defaultError.humanitarian }}
          </span>
        </div>
        <!-- Humanitarian -->

        <!-- Default Flow Type -->
        <div>
          <div class="flex justify-between">
            <label for="default-collaboration-type">{{
              toTitleCase(translatedData['elements.label.default_flow_type'])
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(
                    translatedData['elements.label.default_flow_type']
                  )
                "
                :hover-text="
                  translatedData[
                    'common.common.flow_type_is_a_way_to_categorise'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="default-flow-type"
            v-model="defaultForm.default_flow_type"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.default_flow_type }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.defaultFlowType"
            :searchable="true"
            @click="updateStore('default_flow_type')"
          />
          <span
            v-if="defaultError.default_flow_type"
            class="error"
            role="alert"
          >
            {{ defaultError.default_flow_type }}
          </span>
        </div>
        <!-- Default Flow Type -->

        <!-- Default Finance Type -->
        <div>
          <div class="flex justify-between">
            <label for="default-collaboration-type">{{
              toTitleCase(translatedData['elements.label.default_finance_type'])
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(
                    translatedData['elements.label.default_finance_type']
                  )
                "
                :hover-text="
                  translatedData[
                    'common.common.the_type_of_finance_eg_grant_loan'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="default-finance-type"
            v-model="defaultForm.default_finance_type"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.default_finance_type }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.defaultFinanceType"
            :searchable="true"
            @click="updateStore('default_finance_type')"
          />
          <span
            v-if="defaultError.default_finance_type"
            class="error"
            role="alert"
          >
            {{ defaultError.default_finance_type }}
          </span>
        </div>
        <!-- Default Finance Type -->

        <!-- Default Aid Type -->
        <div>
          <div class="flex justify-between">
            <label for="default-collaboration-type">{{
              toTitleCase(translatedData['elements.label.default_aid_type'])
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(translatedData['elements.label.default_aid_type'])
                "
                :hover-text="
                  translatedData[
                    'common.common.the_type_of_aid_being_supplied_project_type_intervention'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="default-aid-type"
            v-model="defaultForm.default_aid_type"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.default_aid_type }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.defaultAidType"
            :searchable="true"
            @click="updateStore('default_aid_type')"
          />
          <span v-if="defaultError.default_aid_type" class="error" role="alert">
            {{ defaultError.default_aid_type }}
          </span>
        </div>
        <!-- Default Aid Type -->

        <!-- Default Tied Status -->
        <div>
          <div class="flex justify-between">
            <label for="default-tied-status">{{
              toTitleCase(translatedData['elements.label.default_tied_status'])
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(
                    translatedData['elements.label.default_tied_status']
                  )
                "
                :hover-text="
                  translatedData[
                    'common.common.whether_the_aid_is_untied_tied_or_partially_tied'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="default-tied-status"
            v-model="defaultForm.default_tied_status"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.default_tied_status }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.defaultTiedStatus"
            :searchable="true"
            @click="updateStore('default_tied_status')"
          />
          <span
            v-if="defaultError.default_tied_status"
            class="error"
            role="alert"
          >
            {{ defaultError.default_tied_status }}
          </span>
        </div>
        <!-- Default Tied Status -->
      </div>
    </div>

    <span class="text-sm font-bold text-n-50">{{
      translatedData[
        'settings.setting_default_form.optional_defaults_for_activity_data'
      ]
    }}</span>
    <div class="register mb-4 mt-4">
      <div class="register__container">
        <!-- Budget Not Provided -->
        <div>
          <div class="flex justify-between">
            <label for="budget-not-provided">{{
              toTitleCase(translatedData['elements.label.budget_not_provided'])
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(
                    translatedData['elements.label.budget_not_provided']
                  )
                "
                :hover-text="
                  translatedData['common.common.a_code_indicating_the_reason']
                "
              />
            </button>
          </div>
          <Multiselect
            id="budget_not_provided"
            v-model="defaultForm.budget_not_provided"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.budget_not_provided }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.budgetNotProvided"
            :searchable="true"
            @click="updateStore('budget_not_provided')"
          />
          <span
            v-if="defaultError.budget_not_provided"
            class="error"
            role="alert"
          >
            {{ defaultError.budget_not_provided }}
          </span>
        </div>
        <!-- Budget Not Provided -->

        <!-- Linked Data URI -->
        <div>
          <div class="flex justify-between">
            <label for="default-hierarchy">{{
              translatedData['elements.label.linked_data_uri_block']
            }}</label>
            <button>
              <HoverText
                width="w-64"
                :name="translatedData['elements.label.linked_data_uri_block']"
                :hover-text="
                  translatedData[
                    'settings.setting_default_form.if_a_publisher_chooses_to_publish_linked_data'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <input
            id="linked-data-uri"
            v-model="defaultForm.linked_data_uri"
            :disabled="userRole !== 'admin'"
            class="register__input mb-2"
            type="text"
            :placeholder="translatedData['common.common.enter_uri']"
            @input="updateStore('linked_data_uri')"
          />
          <span v-if="defaultError.linked_data_uri" class="error" role="alert">
            {{ defaultError.linked_data_uri }}
          </span>
        </div>
        <!-- Linked Data URI -->

        <!-- Default Collaboration Type -->
        <div>
          <div class="flex justify-between">
            <label for="default-collaboration-type">{{
              toTitleCase(
                translatedData['elements.label.default_collaboration_type']
              )
            }}</label>
            <button>
              <HoverText
                width="w-72"
                :name="
                  toTitleCase(
                    translatedData['elements.label.collaboration_type']
                  )
                "
                :hover-text="
                  translatedData[
                    'settings.setting_default_form.the_type_of_collaboration_involved_in_the_activitys_disbursements'
                  ]
                "
                :show-iati-reference="true"
              />
            </button>
          </div>
          <Multiselect
            id="collaboration-type"
            v-model="defaultForm.default_collaboration_type"
            :disabled="userRole !== 'admin'"
            class="vue__select"
            :class="{ error__input: defaultError.default_collaboration_type }"
            :placeholder="translatedData['common.common.select_an_option']"
            :options="props.defaultCollaborationType"
            :searchable="true"
            @click="updateStore('default_collaboration_type')"
          />
          <span
            v-if="defaultError.default_collaboration_type"
            class="error"
            role="alert"
          >
            {{ defaultError.default_collaboration_type }}
          </span>
        </div>
        <!-- Default Collaboration Type -->
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, computed, inject } from 'vue';
import Multiselect from '@vueform/multiselect';
import { useStore } from '../../store';
import { ActionTypes } from 'Store/setting/actions';
import HoverText from './../../components/HoverText.vue';
import { toTitleCase } from 'Composable/utils';

export default defineComponent({
  methods: { toTitleCase },
  components: {
    Multiselect,
    HoverText,
  },
  props: {
    currencies: {
      type: [String, Object],
      required: true,
    },
    languages: {
      type: [String, Object],
      required: true,
    },
    humanitarian: {
      type: [String, Object],
      required: true,
    },
    budgetNotProvided: {
      type: [String, Object],
      required: true,
    },
    defaultCollaborationType: {
      type: [String, Object],
      required: true,
    },
    defaultFlowType: {
      type: [String, Object],
      required: true,
    },
    defaultFinanceType: {
      type: [String, Object],
      required: true,
    },
    defaultAidType: {
      type: [String, Object],
      required: true,
    },
    defaultTiedStatus: {
      type: [String, Object],
      required: true,
    },
  },

  setup(props) {
    const translatedData = inject('translatedData') as Record<string, string>;
    const userRole = inject('userRole');
    const store = useStore();

    const defaultForm = computed(() => {
      return store.state.defaultForm;
    });

    const defaultError = computed(() => {
      return store.state.defaultError;
    });

    function updateStore(key: keyof typeof defaultForm.value) {
      store.dispatch(ActionTypes.UPDATE_DEFAULT_VALUES, {
        key: key,
        value: defaultForm.value[key],
      });
    }

    return {
      props,
      userRole,
      defaultForm,
      defaultError,
      updateStore,
      translatedData,
    };
  },
});
</script>
