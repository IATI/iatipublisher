<template>
  <div
    :id="elementName"
    class="activities__content--element px-3 py-3 text-n-50"
    :class="{
      'basis-full': width === 'full',
      'basis-6/12': width === '',
    }"
  >
    <div class="rounded-lg bg-white p-4">
      <div class="mb-4 flex">
        <div class="title flex grow">
          <div class="title text-sm font-bold">
            {{ elementName.toString().replace(/_/g, '-') }}
          </div>
        </div>
        <div class="icons flex items-center">
          <!-- <svg-vue class="mr-1.5" icon="core"></svg-vue> -->
          <HoverText :hover-text="hoverText" class="text-n-40"></HoverText>
        </div>
      </div>

      <div>
        <HelperText :helper-text="deprecationStatusMap" />
      </div>

      <div class="divider mb-4 h-px w-full bg-n-20"></div>
      <div>
        <template v-if="elementName === 'description'">
          <Description :data="elementData" />
        </template>

        <template v-else-if="elementName === 'aid_type'">
          <AidType :data="elementData" />
        </template>

        <template v-else-if="elementName === 'transaction_type'">
          <div class="text-sm"><TransactionType :data="elementData" /></div>
        </template>

        <template v-else-if="elementName === 'transaction_date'">
          <div class="text-sm"><TransactionDate :data="elementData" /></div>
        </template>

        <template v-else-if="elementName === 'value'">
          <Value :data="elementData" />
        </template>

        <template v-else-if="elementName === 'humanitarian'">
          <div class="text-sm">
            {{ data === '0' ? 'False' : data === '1' ? 'True' : '' }}
            <span v-if="!data" class="text-xs italic text-light-gray">N/A</span>
          </div>
        </template>

        <template v-else-if="elementName === 'provider_organization'">
          <ProviderOrganization :data="elementData" />
        </template>

        <template v-else-if="elementName === 'receiver_organization'">
          <ReceiverOrganization :data="elementData" />
        </template>

        <template v-else-if="elementName === 'disbursement_channel'">
          <DisbursementChannel :data="elementData" />
        </template>

        <template v-else-if="elementName === 'sector'">
          <Sector :data="elementData" />
        </template>

        <template v-else-if="elementName === 'recipient_country'">
          <RecipientCountry :data="elementData" />
        </template>

        <template v-else-if="elementName === 'recipient_region'">
          <RecipientRegion :data="elementData" />
        </template>

        <template v-else-if="elementName === 'flow_type'">
          <FlowType :data="elementData" />
        </template>

        <template v-else-if="elementName === 'finance_type'">
          <FinanceType :data="elementData" />
        </template>

        <template v-else-if="elementName === 'tied_status'">
          <TiedStatus :data="elementData" />
        </template>
        <template v-else>
          <div class="text-sm">
            {{ data ?? '' }}
            <span v-if="!data" class="text-xs italic text-light-gray">N/A</span>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, provide } from 'vue';
import HoverText from './../../../components/HoverText.vue';
import dateFormat from './../../../composable/dateFormat';
import {
  Description,
  AidType,
  TransactionType,
  TransactionDate,
  Value,
  ProviderOrganization,
  ReceiverOrganization,
  DisbursementChannel,
  Sector,
  RecipientCountry,
  RecipientRegion,
  FlowType,
  FinanceType,
  TiedStatus,
} from './elements/Index';
import HelperText from 'Components/HelperText.vue';

export default defineComponent({
  name: 'ActivityElement',
  components: {
    HelperText,
    HoverText,
    Description,
    AidType,
    TransactionType,
    TransactionDate,
    Value,
    ProviderOrganization,
    ReceiverOrganization,
    DisbursementChannel,
    Sector,
    RecipientCountry,
    RecipientRegion,
    FlowType,
    FinanceType,
    TiedStatus,
  },
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
    elementName: {
      type: String,
      required: true,
    },
    editUrl: {
      type: String,
      required: true,
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
    hoverText: {
      type: String,
      required: false,
      default: '',
    },
    deprecationStatusMap: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data, types } = toRefs(props),
      elementData = data.value;

    provide('types', types);

    return {
      elementData,
      dateFormat,
    };
  },
});
</script>
