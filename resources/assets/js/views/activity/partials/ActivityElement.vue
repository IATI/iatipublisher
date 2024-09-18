<template>
  <Modal :modal-active="deleteValue" width="583" @close="deleteToggle">
    <div class="mb-4">
      <div class="title mb-6 flex">
        <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
        <b>
          {{ translatedData['common.common.delete_element'] }}
        </b>
      </div>
      <div class="rounded-lg bg-rose p-4">
        {{
          translatedData[
            'common.common.are_you_sure_you_want_to_delete_this_element'
          ]
        }}
      </div>
    </div>
    <div class="flex justify-end">
      <div class="inline-flex">
        <BtnComponent
          class="bg-white px-6 uppercase"
          :text="translatedData['common.common.go_back']"
          type=""
          @click="deleteValue = false"
        />
        <BtnComponent
          class="space"
          :text="translatedData['common.common.delete']"
          type="primary"
          @click="deleteElement(activityId, title)"
        />
      </div>
    </div>
  </Modal>
  <div :class="layout" class="activities__content--element p-3 text-n-50">
    <div :id="title" class="rounded-lg bg-white p-4">
      <div class="mb-4 flex">
        <div class="title flex grow">
          <template
            v-if="
              title === 'reporting_org' ||
              title === 'default_tied_status' ||
              title === 'crs_add' ||
              title === 'fss'
            "
          >
            <svg-vue class="elements-svg" icon="activity-elements/building" />
          </template>

          <template v-else-if="title === 'iati_identifier'">
            <svg-vue
              class="elements-svg"
              icon="activity-elements/iati_identifier"
            />
          </template>

          <template v-else>
            <svg-vue
              :icon="'activity-elements/' + title"
              class="elements-svg"
            ></svg-vue>
          </template>

          <div class="title text-sm font-bold">
            {{ title.toString().replace(/_/g, '-') }}
          </div>

          <Status :data="completed" />
        </div>

        <div class="icons flex items-center">
          <template v-if="title == 'transactions'">
            <Btn
              :text="translatedData['common.common.add_transaction']"
              icon="add"
              :link="`/activity/${activityId}/transaction/create`"
              class="mr-2.5"
            />
            <Btn
              :text="translatedData['common.common.show_full_transaction_list']"
              icon=""
              design="bgText"
              :link="`/activity/${activityId}/transaction`"
              class="mr-2.5"
            />
          </template>
          <div v-else class="mr-2.5 flex gap-2.5">
            <Btn
              v-if="!(title === 'iati_identifier' && hasEverBeenPublished)"
              :text="translatedData['common.common.edit']"
              :link="`/activity/${activityId}/${title}`"
              class="edit-button"
            />
            <Btn
              v-if="
                title !== 'title' &&
                title !== 'iati_identifier' &&
                title !== 'reporting_org'
              "
              :text="translatedData['common.common.delete']"
              class="delete-button"
              icon="delete"
              @click="deleteActivityElement"
            />
          </div>

          <svg-vue
            v-if="activityCoreElements().includes(title)"
            class="mr-1.5"
            icon="core"
          ></svg-vue>
          <HoverText
            v-if="tooltip"
            :name="title.toString().replace(/_/g, '-')"
            :hover-text="tooltip"
            :show-iati-reference="true"
            class="text-n-40"
          />
        </div>
      </div>

      <div
        v-if="title === 'reporting_org'"
        class="my-2 flex items-center space-x-2 rounded-lg bg-eggshell p-3"
      >
        <svg-vue icon="exclamation-warning" class="h-5" />

        <!-- eslint-disable vue/no-v-html -->
        <div
          class="text-xs font-normal text-n-50"
          v-html="elements['reporting_org']['helper_text']"
        ></div>
      </div>

      <HelperText :helper-text="deprecationCodeUsage" />

      <div
        v-if="title === 'transactions' && data.warning_info_text !== ''"
        class="mb-4 flex items-center rounded-md bg-eggshell pb-2 pl-4 pr-4 pt-2 text-xs"
      >
        <svg
          class="elements-svg"
          width="18"
          height="18"
          viewBox="0 0 18 18"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M8.99935 4.79533C8.77834 4.79533 8.56638 4.88313 8.4101 5.03941C8.25382 5.19569 8.16602 5.40765 8.16602 5.62866V8.962C8.16602 9.18301 8.25382 9.39497 8.4101 9.55125C8.56638 9.70753 8.77834 9.79533 8.99935 9.79533C9.22037 9.79533 9.43233 9.70753 9.58861 9.55125C9.74489 9.39497 9.83269 9.18301 9.83269 8.962V5.62866C9.83269 5.40765 9.74489 5.19569 9.58861 5.03941C9.43233 4.88313 9.22037 4.79533 8.99935 4.79533ZM9.76602 11.9787C9.74778 11.9256 9.72256 11.8751 9.69102 11.8287L9.59102 11.7037C9.47383 11.588 9.32502 11.5097 9.16336 11.4786C9.00171 11.4474 8.83444 11.4648 8.68269 11.5287C8.5817 11.5709 8.48869 11.6301 8.40769 11.7037C8.33045 11.7815 8.26935 11.8739 8.22788 11.9754C8.18641 12.0769 8.16539 12.1857 8.16602 12.2953C8.16734 12.4042 8.18999 12.5118 8.23269 12.612C8.27011 12.7154 8.32982 12.8093 8.40759 12.8871C8.48536 12.9649 8.57927 13.0246 8.68269 13.062C8.78244 13.1061 8.89029 13.1289 8.99935 13.1289C9.10841 13.1289 9.21627 13.1061 9.31602 13.062C9.41943 13.0246 9.51335 12.9649 9.59111 12.8871C9.66888 12.8093 9.72859 12.7154 9.76602 12.612C9.80872 12.5118 9.83137 12.4042 9.83269 12.2953C9.83678 12.2398 9.83678 12.1841 9.83269 12.1287C9.81834 12.0755 9.79585 12.0249 9.76602 11.9787ZM8.99935 0.628662C7.35118 0.628662 5.74001 1.1174 4.3696 2.03308C2.99919 2.94876 1.93109 4.25025 1.30036 5.77297C0.669626 7.29568 0.504599 8.97124 0.826142 10.5877C1.14769 12.2043 1.94136 13.6891 3.1068 14.8546C4.27223 16.02 5.75709 16.8137 7.3736 17.1352C8.99011 17.4568 10.6657 17.2917 12.1884 16.661C13.7111 16.0303 15.0126 14.9622 15.9283 13.5917C16.8439 12.2213 17.3327 10.6102 17.3327 8.962C17.3327 7.86765 17.1171 6.78401 16.6983 5.77297C16.2796 4.76192 15.6657 3.84326 14.8919 3.06944C14.1181 2.29562 13.1994 1.68179 12.1884 1.263C11.1773 0.84421 10.0937 0.628662 8.99935 0.628662ZM8.99935 15.6287C7.68081 15.6287 6.39188 15.2377 5.29555 14.5051C4.19922 13.7726 3.34474 12.7314 2.84016 11.5132C2.33557 10.295 2.20355 8.9546 2.46078 7.66139C2.71802 6.36819 3.35296 5.1803 4.28531 4.24795C5.21766 3.3156 6.40554 2.68066 7.69875 2.42343C8.99196 2.16619 10.3324 2.29821 11.5506 2.8028C12.7687 3.30738 13.8099 4.16187 14.5425 5.25819C15.275 6.35452 15.666 7.64345 15.666 8.962C15.666 10.7301 14.9636 12.4258 13.7134 13.676C12.4632 14.9263 10.7675 15.6287 8.99935 15.6287Z"
            fill="#F4B784"
          />
        </svg>
        <div>{{ data.warning_info_text ?? '' }}</div>
      </div>

      <div class="divider mb-4 h-px w-full bg-n-20"></div>

      <template v-if="title === 'iati_identifier'">
        <IatiIdentifier :data="data.content.iati_identifier_text" />
      </template>

      <template v-else-if="title === 'other_identifier'">
        <OtherIdentifier :data="data" />
      </template>

      <template v-else-if="title === 'title'">
        <TitleElement :data="data" />
      </template>

      <template v-else-if="title === 'reporting_org'">
        <ReportingOrganization :data="data" />
      </template>

      <template v-else-if="title === 'description'">
        <Description :data="data.content" />
      </template>

      <template v-else-if="title === 'activity_date'">
        <ActivityDate :data="data.content" />
      </template>

      <template v-else-if="title === 'contact_info'">
        <ContactInfo :data="data.content" />
      </template>

      <template v-else-if="title === 'participating_org'">
        <ParticipatingOrg :data="data.content" />
      </template>

      <template v-else-if="title === 'recipient_country'">
        <RecipientCountry :data="data.content" />
      </template>

      <template v-else-if="title === 'recipient_region'">
        <RecipientRegion :data="data.content" />
      </template>

      <template v-else-if="title === 'location'">
        <Location :data="data.content" />
      </template>

      <template v-else-if="title === 'sector'">
        <Sector :data="data.content" />
      </template>

      <template v-else-if="title === 'policy_marker'">
        <PolicyMarker :data="data.content" />
      </template>

      <template v-else-if="title === 'tag'">
        <Tag :data="data.content" />
      </template>

      <!-- Default Aid Type -->
      <template v-else-if="title === 'default_aid_type'">
        <div
          v-for="(post, key) in data.content"
          :key="key"
          class="default_aid_type"
          :class="{ 'mb-4': key !== data.content.length - 1 }"
        >
          <div class="default_aid_type-content">
            <div class="date-type mb-2 text-sm font-bold">
              <span v-if="post.default_aid_type_vocabulary">{{
                types.aidTypeVocabulary[post.default_aid_type_vocabulary]
              }}</span>
              <span v-else class="italic">Vocabulary Missing</span>
            </div>

            <div v-if="post.default_aid_type_vocabulary == '2'" class="text-sm">
              <span v-if="post.earmarking_category">{{
                types.earmarkingCategory[post.earmarking_category]
              }}</span>
              <span v-else class="italic">Code Missing</span>
            </div>

            <div
              v-else-if="post.default_aid_type_vocabulary == '3'"
              class="text-sm"
            >
              <span v-if="post.earmarking_modality">{{
                types.earmarkingModality[post.earmarking_modality]
              }}</span>
              <span v-else class="italic">Code Missing</span>
            </div>

            <div
              v-else-if="post.default_aid_type_vocabulary == '4'"
              class="text-sm"
            >
              <span v-if="post.cash_and_voucher_modalities">{{
                types.cashandVoucherModalities[post.cash_and_voucher_modalities]
              }}</span>
              <span v-else class="italic">Code Missing</span>
            </div>

            <div v-else class="max-w-[887px] text-sm">
              <span v-if="post.default_aid_type">{{
                types.aidType[post.default_aid_type]
              }}</span>
              <span v-else class="italic">Code Missing</span>
            </div>
          </div>
        </div>
      </template>

      <!-- Country Budget Items -->
      <template v-else-if="title === 'country_budget_items'">
        <div class="category">
          <span>Vocabulary - </span>
          <span>
            <span v-if="data.content.country_budget_vocabulary">{{
              props.types.budgetIdentifierVocabulary[
                data.content.country_budget_vocabulary
              ]
            }}</span>
            <span v-else class="italic">Missing</span>
          </span>
        </div>
        <div
          v-for="(post, key) in data.content.budget_item"
          :key="key"
          class="elements-detail"
          :class="{ 'mb-4': key !== data.content.budget_item.length - 1 }"
        >
          <div
            v-if="data.content.country_budget_vocabulary === '1'"
            class="text-sm"
          >
            <div v-if="post.code" class="flex space-x-1">
              <span>
                {{ types.budgetIdentifier[post.code] }}
              </span>
              <span>({{ roundFloat(post.percentage) }}%)</span>
            </div>
            <span v-else class="italic">Missing</span>
          </div>
          <div v-else class="text-sm">
            <span v-if="post.code">{{
              types.budgetIdentifier[post.code]
            }}</span>
            <span v-else class="italic">Missing</span>
            <span v-if="post.percentage">
              ({{ roundFloat(post.percentage) }} %)</span
            >
            <span v-else class="italic">(Percentage Missing)</span>
          </div>
          <template v-for="(item, i) in post.description" :key="i">
            <div
              v-for="(narrative, k) in item.narrative"
              :key="k"
              class="elements-detail ml-5"
              :class="{ 'mb-0': k !== item.narrative - 1 }"
            >
              <table>
                <tr class="multiline">
                  <td>Description</td>
                  <td>
                    <div v-if="narrative.narrative" class="flex flex-col">
                      <span v-if="narrative.language" class="language top"
                        >(Language:
                        {{ types.languages[narrative.language] }})</span
                      >
                      <span class="description">{{ narrative.narrative }}</span>
                    </div>
                    <span v-else class="italic">Missing</span>
                  </td>
                </tr>
              </table>
            </div>
          </template>
        </div>
      </template>

      <!-- Humanitarian Scope -->
      <template v-else-if="title === 'humanitarian_scope'">
        <HumanitarianScope :data="data.content" />
      </template>

      <!-- Budget -->
      <template v-else-if="title === 'budget'">
        <div
          v-for="(post, key) in data.content"
          :key="key"
          class="elements-detail"
          :class="{ 'mb-4': key !== data.content.length - 1 }"
        >
          <div class="category">
            <span v-if="post.budget_type">{{
              types.budgetType[post.budget_type]
            }}</span>
            <span v-else class="italic">Type Missing</span>
          </div>

          <div
            v-for="(item, i) in post.budget_value"
            :key="i"
            class="elements-detail mb-1"
            :class="{ 'mb-4': i !== post.budget_value.length - 1 }"
          >
            <div class="text-sm">
              <div v-if="item.amount" class="value">
                <span>{{ Number(item.amount).toLocaleString() }}</span>
                <span>{{ item.currency }}</span>
                <span v-if="item.value_date"
                  >(Valued at {{ formatDate(item.value_date) }})</span
                >
              </div>
              <span v-else class="italic">Budget Value Missing</span>
            </div>
          </div>
          <div class="ml-5">
            <div
              v-for="(item, i) in post.period_start"
              :key="i"
              :class="{ 'mb-4': i !== post.period_start.length - 1 }"
            >
              <table>
                <tr>
                  <td>Period Start</td>
                  <td v-if="item.date">{{ formatDate(item.date) }}</td>
                  <td v-else class="italic">Missing</td>
                </tr>
              </table>
            </div>
            <div
              v-for="(item, i) in post.period_end"
              :key="i"
              :class="{ 'mb-4': i !== post.period_end.length - 1 }"
            >
              <table>
                <tr>
                  <td>Period end</td>
                  <td v-if="item.date">{{ formatDate(item.date) }}</td>
                  <td v-else class="italic">Missing</td>
                </tr>
              </table>
            </div>
            <table>
              <tr>
                <td>Status</td>
                <td>
                  <span v-if="post.budget_status">{{
                    types.budgetStatus[post.budget_status]
                  }}</span>
                  <span v-else class="italic">Missing</span>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </template>

      <!-- Planned Disbursement -->
      <template v-else-if="title === 'planned_disbursement'">
        <PlannedDisbursement :data="data.content" />
      </template>

      <!-- Document Link -->
      <template v-else-if="title === 'document_link'">
        <div
          v-for="(post, key) in data.content"
          :key="key"
          class="elements-detail"
          :class="{ 'mb-4': key !== data.content.length - 1 }"
        >
          <div>
            <div v-if="post.url" class="max-w-[887px] text-sm">
              <a :href="post.url" target="_blank">{{ post.url }}</a>
            </div>
            <span v-else class="italic">URL Missing</span>
          </div>
          <div class="ml-5">
            <div>
              <div v-for="(language, i) in post.language" :key="i">
                <table>
                  <tr>
                    <td>Language</td>
                    <td>
                      <span v-if="language.code">{{
                        types.languages[language.code]
                      }}</span>
                      <span v-else class="italic">Missing</span>
                    </td>
                  </tr>
                </table>
              </div>
              <div v-for="(document_date, i) in post.document_date" :key="i">
                <table>
                  <tr>
                    <td>Date</td>
                    <td>
                      <span v-if="document_date.date">{{
                        formatDate(document_date.date)
                      }}</span>
                      <span v-else class="italic">Missing</span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
            <div v-for="(item, i) in post.title" :key="i">
              <div
                v-for="(narrative, j) in item.narrative"
                :key="j"
                class="mb-1 flex items-center space-x-1"
              >
                <table>
                  <tr class="multiline">
                    <td>Title</td>
                    <td>
                      <span v-if="narrative.language" class="language">
                        ({{ types.languages[narrative.language] }})
                      </span>
                      <div v-if="narrative.narrative" class="flex flex-col">
                        <span>
                          {{ narrative.narrative }}
                        </span>
                      </div>
                      <span v-else class="italic">Missing</span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
            <div v-for="(category, i) in post.category" :key="i">
              <table>
                <tr>
                  <td>Category</td>
                  <td>
                    <span v-if="category.code">{{
                      types.documentCategory[category.code]
                    }}</span>
                    <span v-else class="italic">Missing</span>
                  </td>
                </tr>
              </table>
            </div>
            <table>
              <tr>
                <td>Format</td>
                <td v-if="post.format">{{ post.format }}</td>
                <td v-else class="italic">Missing</td>
              </tr>
            </table>
            <div v-for="(description, i) in post.description" :key="i">
              <div v-for="(narrative, j) in description.narrative" :key="j">
                <table>
                  <tr class="multiline">
                    <td>Description</td>
                    <td>
                      <div v-if="narrative.narrative" class="flex flex-col">
                        <span v-if="narrative.language" class="language"
                          >(Language:
                          {{ types.languages[narrative.language] }})</span
                        >
                        <span>{{ narrative.narrative }}</span>
                      </div>
                      <span v-else class="italic">Missing</span>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template v-else-if="title === 'related_activity'">
        <RelatedActivity :data="data.content" />
      </template>

      <template v-else-if="title === 'legacy_data'">
        <LegacyData :data="data.content" />
      </template>

      <template v-else-if="title === 'conditions'">
        <Conditions :data="data.content" />
      </template>

      <template v-else-if="title === 'transactions'">
        <Transactions :data="data.content" />
      </template>

      <template v-else>
        <!-- Activity Status -->
        <div class="content text-sm">
          <template v-if="title === 'activity_status'">
            <span v-if="data.content">{{
              props.types.activityStatus[data.content]
            }}</span>
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Activity Scope -->
          <template v-else-if="title === 'activity_scope'">
            <span v-if="data.content">{{
              props.types.activityScope[data.content]
            }}</span>
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Collaboration Type -->
          <template v-else-if="title === 'collaboration_type'">
            <span v-if="data.content">{{
              props.types.collaborationType[data.content]
            }}</span>
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Default Flow Type -->
          <template v-else-if="title === 'default_flow_type'">
            <span v-if="data.content">{{
              props.types.flowType[data.content]
            }}</span>
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Default Tied Status -->
          <template v-else-if="title === 'default_tied_status'">
            <span v-if="data.content">{{
              props.types.tiedStatus[data.content]
            }}</span>
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Capital Spend -->
          <template v-else-if="title === 'capital_spend'">
            <span v-if="data.content.toString()"
              >{{ data.content.toString() }}%</span
            >
            <span v-else class="italic">Missing</span>
          </template>

          <!-- Default Finance Type -->
          <template v-else-if="title === 'default_finance_type'">
            <span v-if="data.content">
              {{ props.types.financeType[data.content] }}</span
            >
            <span v-else class="italic">Missing</span>
          </template>

          <template v-else>
            <span>No content</span>
          </template>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';
import { useToggle } from '@vueuse/core';
import moment from 'moment';
import axios from 'axios';

import { activityCoreElements } from 'Composable/coreElements';

//components
import {
  ActivityDate,
  Conditions,
  ContactInfo,
  Description,
  HumanitarianScope,
  IatiIdentifier,
  LegacyData,
  Location,
  OtherIdentifier,
  ParticipatingOrg,
  PlannedDisbursement,
  PolicyMarker,
  RecipientCountry,
  RecipientRegion,
  RelatedActivity,
  ReportingOrganization,
  Sector,
  Tag,
  TitleElement,
  Transactions,
} from 'Activity/elements/Index';

import Btn from 'Components/buttons/Link.vue';
import Status from 'Components/status/ElementStatus.vue';
import HoverText from 'Components/HoverText.vue';
import Modal from 'Components/PopupModal.vue';
import BtnComponent from 'Components/ButtonComponent.vue';
import HelperText from 'Components/HelperText.vue';

// toggle state for modal popup
let [deleteValue, deleteToggle] = useToggle();

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  activityId: {
    type: Number,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  tooltip: {
    type: String,
    required: false,
    default: '',
  },
  width: {
    type: String,
    required: false,
    default: '',
  },
  types: {
    type: Object,
    required: true,
  },
  completed: {
    type: Boolean,
    required: true,
  },
  warningInfoText: {
    type: String,
    required: false,
    default: '',
  },
  hasEverBeenPublished: {
    type: Boolean,
    required: false,
    default: false,
  },
  deprecationCodeUsage: {
    type: [Boolean, Boolean],
    required: false,
    default: false,
  },
});

// call api for publishing
interface ToastDataTypeface {
  message: string;
  type: boolean;
  visibility: boolean;
}
const translatedData = inject('translatedData') as Record<string, string>;
const toastData = inject('toastData') as ToastDataTypeface;
const elements = inject('elements') as object;

let layout = 'basis-full  lg:basis-6/12';
if (props.width === 'full') {
  layout = 'basis-full';
}

function formatDate(date: Date) {
  return moment(date).format('LL');
}

function roundFloat(num: string) {
  return parseFloat(num).toFixed(2);
}
const deleteActivityElement = () => {
  deleteValue.value = true;
};

function deleteElement(id, element) {
  deleteValue.value = false;
  window.scrollTo(0, 0);
  axios
    .delete(`/api/activity/${id}/${element}`)
    .then((res) => {
      const response = res.data;

      if (response.status) {
        setTimeout(() => {
          location.reload();
        }, 300);
      }
      if (!response.status) {
        toastData.message = response.message;
        toastData.type = response.status;
        toastData.visibility = true;
      }
    })
    .catch(() => {
      toastData.message =
        "Couldn't delete the activity title due to system error.";
      toastData.type = false;
      toastData.visibility = true;
    });
}
</script>
