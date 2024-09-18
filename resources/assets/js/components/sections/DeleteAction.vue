<template>
  <button
    class="text-n-40 hover:text-spring-50"
    @click="
      (event) => {
        deleteValue = true;
        event.stopPropagation();
      }
    "
  >
    <svg-vue icon="delete" class="text-xl"></svg-vue>
  </button>
  <Modal :modal-active="deleteValue" width="583" @close="deleteToggle">
    <div class="mb-4">
      <div class="title mb-6 flex">
        <svg-vue class="mr-1 mt-0.5 text-lg text-crimson-40" icon="delete" />
        <b v-if="props.itemType === 'result'">
          {{ getTranslatedDeleteElement(translatedData, 'result') }}
        </b>
        <b v-else-if="props.itemType === 'indicator'">
          {{ getTranslatedDeleteElement(translatedData, 'indicator') }}
        </b>
        <b v-else-if="props.itemType === 'period'">
          {{ getTranslatedDeleteElement(translatedData, 'period') }}
        </b>
        <b v-else-if="props.itemType === 'transaction'">
          {{ getTranslatedDeleteElement(translatedData, 'transaction') }}
        </b>
        <b v-else>Delete</b>
      </div>
      <div class="rounded-lg bg-rose p-4">
        <p v-if="props.itemType === 'result'">
          {{
            translatedData[
              'common.common.are_you_sure_you_want_to_delete_this_result'
            ]
          }}
        </p>
        <p v-else-if="props.itemType === 'indicator'">
          {{
            translatedData[
              'common.common.are_you_sure_you_want_to_delete_this_indicator'
            ]
          }}
        </p>
        <p v-else-if="props.itemType === 'period'">
          {{
            translatedData[
              'common.common.are_you_sure_you_want_to_delete_this_period'
            ]
          }}
        </p>
        <p v-else-if="props.itemType === 'transaction'">
          {{
            translatedData[
              'common.common.are_you_sure_you_want_to_delete_this_transaction'
            ]
          }}
        </p>
        <p v-else>Are you sure you want to delete this module ?</p>
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
          @click="deleteFunction"
        />
      </div>
    </div>
  </Modal>
</template>

<script setup lang="ts">
import { defineProps, inject } from 'vue';
import { useToggle } from '@vueuse/core';
import axios from 'axios';

//component
import BtnComponent from 'Components/ButtonComponent.vue';
import Modal from 'Components/PopupModal.vue';
import { getTranslatedDeleteElement } from 'Composable/utils';

// props
const props = defineProps({
  itemId: { type: [Number, String], required: true },
  itemType: { type: String, required: true },
});

const translatedData = inject('translatedData') as Record<string, string>;

// toggle state for modal popup
let [deleteValue, deleteToggle] = useToggle();

const deleteFunction = () => {
  if (props.itemType === 'result' || props.itemType === 'transaction') {
    axios.delete(`${props.itemType}/${props.itemId}`).then(() => {
      deleteValue.value = false;
      location.reload();
    });
  }

  if (props.itemType === 'indicator') {
    axios.delete(`${props.itemType}/${props.itemId}`).then(() => {
      deleteValue.value = false;
      location.reload();
    });
  }

  if (props.itemType === 'period') {
    axios.delete(`${props.itemType}/${props.itemId}`).then(() => {
      deleteValue.value = false;
      location.reload();
    });
  }
};
</script>
