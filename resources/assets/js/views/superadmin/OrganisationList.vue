<template>
  <div class="bg-paper px-10 pb-[71px] pt-4">
    <div class="my-4 flex justify-between">
      <h4 class="mr-4 text-3xl font-bold xl:text-heading-4">Organisations</h4>
      <div class="inline-flex items-center">
        <Toast
          v-if="toastMessage.visibility"
          class="mr-3.5"
          :message="toastMessage.message"
          :type="toastMessage.type"
        />
      </div>
    </div>
    <div class="organization-list overflow-hidden">
      <TableList
        :countries="props.countries"
        :setup-completeness="props.setupCompleteness"
        :registration-types="props.registrationTypes"
        :publisher-types="props.publisherTypes"
        :data-licenses="props.dataLicenses"
        :oldest-dates="props.oldestDates"
      />
    </div>
    <Loader
      v-if="loader.status"
      :text="loader.text"
      :translated-data="translatedData"
      :class="{ 'animate-loader': loader.status }"
    />
  </div>
</template>

<script setup lang="ts">
import { reactive, provide, defineProps } from 'vue';

// Components
import Loader from 'Components/sections/ProgressLoader.vue';
import Toast from 'Components/ToastMessage.vue';
import TableList from './components/TableList.vue';
import transactionDate from 'Activity/transactions/elements/TransactionDate.vue';

const props = defineProps({
  countries: { type: Object, required: true },
  setupCompleteness: { type: Object, required: true },
  registrationTypes: { type: Object, required: true },
  publisherTypes: { type: Object, required: true },
  dataLicenses: { type: Object, required: true },
  oldestDates: {
    type: String,
    required: true,
  },
  translatedData: {
    type: Object,
    required: true,
  },
});

const loader = reactive({
  status: false,
  text: 'Please Wait',
});

const toastMessage = reactive({
  visibility: false,
  message: '',
  type: true,
});

// provide
provide('loader', loader);
provide('toastData', toastMessage);
provide('translatedData', props.translatedData);
</script>
