<template>
  <div>
    <button
      ref="dropdownBtn"
      class="button primary-btn relative font-bold"
      @click="toggle"
    >
      <svg-vue icon="plus" />
      <span>{{
        translatedData['activity_index.add_activity_button.add_activity']
      }}</span>
      <div
        v-if="state.isVisible"
        class="button__dropdown absolute right-0 top-full z-10 w-56 bg-white p-2 text-left shadow-dropdown"
      >
        <ul>
          <li>
            <a
              id="add-activity-manually"
              href="#"
              :class="liClass"
              @click="modalValue = true"
              >{{ translatedData['common.common.add_activity_manually'] }}</a
            >
          </li>
          <li>
            <a id="import-activity" href="/import" :class="liClass">{{
              translatedData['common.common.import_activities_from_csv_xml']
            }}</a>
          </li>
          <li>
            <a id="import-xls" href="/import/xls" :class="liClass">{{
              translatedData['common.common.import_activities_from_xls']
            }}</a>
          </li>
        </ul>
      </div>
    </button>
    <CreateModal
      :modal-active="modalValue"
      @close="modalToggle"
      @close-modal="modalToggle"
    />
  </div>
</template>

<script lang="ts">
import { reactive, defineComponent, ref, onMounted, inject } from 'vue';
import CreateModal from '../CreateModal.vue';
import { useToggle } from '@vueuse/core';

export default defineComponent({
  name: 'AddActivityButton',
  components: {
    CreateModal,
  },
  setup() {
    const translatedData = inject('translatedData') as Record<string, string>;

    const state = reactive({
      isVisible: false,
    });

    const [modalValue, modalToggle] = useToggle();

    const modelVisible = ref(false);

    const toggleModel = (value: boolean) => {
      modelVisible.value = value;
    };

    const liClass =
      'block p-2.5 text-n-40 text-tiny leading-[1.5] font-bold hover:text-n-50 hover:bg-n-10';

    const dropdownBtn = ref();
    onMounted(() => {
      window.addEventListener('click', (e) => {
        if (!dropdownBtn.value.contains(e.target)) {
          state.isVisible = false;
        }
      });
    });

    const toggle = () => {
      state.isVisible = !state.isVisible;
    };

    return {
      state,
      liClass,
      modelVisible,
      modalValue,
      toggle,
      modalToggle,
      toggleModel,
      dropdownBtn,
      translatedData,
    };
  },
});
</script>
