<template>
  <div class="activities__content--element px-3 py-3" :class="layout">
    <Modal :modal-active="deleteValue" width="583" @close="deleteToggle">
      <div class="mb-4">
        <div class="title mb-6 flex">
          <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
          <b>{{ translatedData['common.common.delete_element'] }}</b>
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
            @click="deleteElement(title)"
          />
        </div>
      </div>
    </Modal>

    <div class="rounded-lg bg-white p-4">
      <div class="mb-4 flex">
        <div :id="title" class="title flex grow text-n-50">
          <template v-if="title === 'name'">
            <svg-vue
              class="mr-1.5 text-xl text-bluecoral"
              icon="organisation-elements/building"
            ></svg-vue>
          </template>
          <template v-else-if="title === 'reporting_org'">
            <svg-vue
              class="mr-1.5 text-xl text-bluecoral"
              icon="organisation-elements/reporting_org"
            ></svg-vue>
          </template>
          <template v-else-if="title === 'recipient_org_budget'">
            <svg-vue
              class="mr-1.5 text-xl text-bluecoral"
              icon="organisation-elements/recipient_org_budget"
            ></svg-vue>
          </template>
          <template v-else>
            <svg-vue
              :icon="'organisation-elements/' + title"
              class="mr-1.5 text-xl text-bluecoral"
            ></svg-vue>
          </template>
          <div class="title text-sm font-bold">
            {{ getTranslatedElementName(title) }}
          </div>
          <div
            class="status ml-2.5 flex text-xs leading-5"
            :class="{
              'text-spring-50': status,
              'text-crimson-50': !status,
            }"
          >
            <b class="mr-2 text-base leading-3">.</b>
            <span v-if="status">{{
              translatedData['common.common.completed']
            }}</span>
            <span v-else>{{
              translatedData['common.common.not_completed']
            }}</span>
          </div>
        </div>
        <div class="icons flex flex-row-reverse items-center">
          <a
            v-if="userRole === 'admin'"
            class="edit-button mx-2.5 flex items-center text-xs font-bold uppercase"
            :href="'/organisation/' + title"
          >
            <svg-vue class="mr-0.5 text-base" icon="edit"></svg-vue>
            <span class="hidden text-[10px] lg:block">{{
              translatedData['common.common.edit']
            }}</span>
          </a>

          <HoverText
            v-if="tooltip"
            :name="title.toString().replace(/_/g, '-')"
            :hover-text="tooltip"
            :show-iati-reference="true"
            class="text-n-40"
          ></HoverText
          ><svg-vue
            v-if="orgMandatoryElements().includes(title)"
            class="mr-1.5"
            icon="core"
          ></svg-vue>

          <a
            v-if="userRole === 'admin' && !notDeletableElements.includes(title)"
            class="edit-button mx-2.5 flex items-center text-xs font-bold uppercase hover:cursor-pointer"
            @click="deleteValue = true"
          >
            <svg-vue class="mr-0.5 text-base" icon="delete"></svg-vue>
            <span class="hidden text-[10px] lg:block">{{
              translatedData['common.common.delete']
            }}</span>
          </a>
        </div>
      </div>

      <HelperText :helper-text="deprecationCodeUsage" />

      <div class="divider mb-4 h-px w-full bg-n-20"></div>
      <div class="text-sm text-n-50">
        <!-- iati_organizational_identifier -->
        <div v-if="title == 'organisation_identifier'">
          {{ content }}
        </div>

        <!-- name -->
        <div v-if="title == 'name'">
          <div v-for="(post, i) in data.content" :key="i" class="title-content">
            <div v-if="post.narrative" class="flex flex-col">
              <span v-if="post.language" class="language mb-1.5">
                ({{ getTranslatedLanguage(translatedData) }}:
                {{ types?.languages[post.language] }})
              </span>
              <span v-if="post.narrative" class="max-w-[887px] text-sm">
                {{ post.narrative }}
              </span>
            </div>
            <span v-else class="text-sm italic">{{
              getTranslatedMissing(translatedData, 'title')
            }}</span>
            <div v-if="i !== data.content.length - 1" class="mb-4"></div>
          </div>
        </div>
        <!-- name ends -->

        <div v-if="title == 'reporting_org'">
          <ReportingOrganisation :content="content" />
        </div>

        <div v-if="title == 'total_budget'">
          <TotalBudget :content="content" />
        </div>

        <div v-if="title == 'recipient_org_budget'">
          <ReportingOrgBudget :content="content" />
        </div>

        <div v-if="title == 'recipient_region_budget'">
          <ReportingRegionBudget :content="content" />
        </div>

        <div v-if="title == 'recipient_country_budget'">
          <RecipientCountryBudget :content="content" />
        </div>

        <div v-if="title == 'total_expenditure'">
          <TotalExpenditure :content="content" />
        </div>

        <!-- document link -->
        <div v-if="title == 'document_link'" class="document-link text-xs">
          <DocumentLink :content="content" />
        </div>
        <!-- document link ends -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { defineProps, inject, provide } from 'vue';
import HoverText from 'Components/HoverText.vue';
import { orgMandatoryElements } from 'Composable/coreElements';

import {
  DocumentLink,
  RecipientCountryBudget,
  ReportingOrganisation,
  ReportingOrgBudget,
  ReportingRegionBudget,
  TotalBudget,
  TotalExpenditure,
} from 'Organisation/elements/Index';
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';
import HelperText from 'Components/HelperText.vue';
import { getTranslatedLanguage, getTranslatedMissing } from 'Composable/utils';

const props = defineProps({
  data: {
    type: Object,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  tooltip: {
    type: String,
    required: true,
  },
  content: {
    type: Object || Array,
    required: true,
  },
  language: {
    type: String,
    required: false,
    default: 'en',
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
  status: {
    type: Boolean,
    required: true,
  },
  deprecationCodeUsage: {
    type: Object,
    required: true,
  },
});

const userRole = inject('userRole');

let layout = 'basis-6/12';
if (props.width === 'full') {
  layout = 'basis-full';
}

provide('orgTypes', props.types);

const getTranslatedElementName = (string) => {
  const translationKey = `elements.name.${string}`;

  return translatedData[translationKey];
};

let notDeletableElements = ['organisation_identifier', 'name', 'reporting_org'];
let [deleteValue, deleteToggle] = useToggle();
interface ToastDataTypeface {
  message: string;
  type: boolean;
  visibility: boolean;
}
const toastData = inject('toastData') as ToastDataTypeface;
const translatedData = inject('translatedData') as Record<string, string>;

const deleteElement = (element) => {
  deleteValue.value = false;
  window.scrollTo(0, 0);
  axios
    .delete(`/organisation/${element}`)
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
        translatedData[
          'organisationDetail.organisation_elements_detail.couldnt_delete_the_organisation_element_due_to_system_error'
        ];
      toastData.type = false;
      toastData.visibility = true;
    });
};
</script>
