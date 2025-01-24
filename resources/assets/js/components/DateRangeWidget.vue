<template>
  <div id="date-range-main" ref="dateRangeMain" class="flex space-x-1">
    <div>
      <div class="relative">
        <!--Range Dropdown-->
        <div
          v-if="dropdownRange && Object.keys(dropdownRange).length"
          class="flex hover:cursor-pointer"
          @click="toggleShowRangeDropdown"
        >
          <span>{{ dateType }} </span>
          <span style="height: fit-content; font-size: 20px; margin-top: 2px">
            <svg-vue icon="arrow-down"></svg-vue>
          </span>
        </div>
        <ul
          v-show="showRangeDropdown"
          ref="dateDropdown"
          class="absolute w-fit bg-white p-2 shadow-sm"
          style="top: 32px; right: 8px; z-index: 99"
        >
          <li
            v-for="(value, key) in dropdownRange"
            :key="key"
            class="daterange-item w-full"
            :class="value === dateType ? 'daterange-item-active' : ''"
            style="z-index: 100; min-width: 250px"
            @click="setDateRangeTypeInDropdown(value, key)"
          >
            {{ value }}
          </li>
        </ul>
      </div>
    </div>

    <div class="h-fit w-fit">
      <span v-if="dateLabel" class="mx-2 text-sm text-n-50">{{
        dateLabel
      }}</span>

      <span
        id="fixed-date-range"
        class="w-fit rounded bg-n-10 px-2 py-1 text-center text-xs text-bluecoral hover:cursor-pointer"
        @click="openCalendar"
        >{{ fixed }}
      </span>
    </div>
    <div class="">
      <div
        :class="{
          empty: !selectedDate[0],
          'all-time': fixed === translatedData['common.common.all_time'],
        }"
        class="relative flex"
      >
        <VueDatePicker
          ref="datepicker"
          v-model="selectedDate"
          range
          month-name-format="long"
          :placeholder="translatedData['common.common.select_date']"
          mode-height="650"
          :clearable="true"
          :format="format"
          :preset-ranges="presetRanges"
          :enable-time-picker="false"
          :teleport="true"
          :alt-position="customPosition"
          @open="addEventsForCalendar"
          @cleared="resetDate"
        >
          <template #yearly="{ label, range, presetDateRange }">
            <span @click="presetDateRange(range)">
              {{ label }}
            </span>
          </template>

          <template #action-buttons>
            <div class="flex">
              <button
                class="font-neutral mx-2 w-fit p-2 font-bold uppercase"
                @click="closeCalendar"
              >
                {{ translatedData['common.common.cancel'] }}
              </button>
              <button
                class="font-spring mx-2 w-fit p-2 font-bold uppercase"
                @click="selectDate"
              >
                {{ translatedData['common.common.apply'] }}
              </button>
            </div>
          </template>
        </VueDatePicker>

        <span
          class="absolute right-0 top-1/2 -translate-y-1/2 cursor-pointer"
          style="height: fit-content; font-size: 20px; margin-top: 2px"
          @click="openCalendar"
        >
          <svg-vue icon="arrow-down"></svg-vue>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import {
  ref,
  watch,
  defineEmits,
  Ref,
  defineProps,
  onMounted,
  computed,
  inject,
} from 'vue';
import {
  subDays,
  startOfWeek,
  endOfMonth,
  startOfMonth,
  startOfYear,
  subMonths,
  startOfDay,
  endOfDay,
} from 'date-fns';

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import type { DatePickerInstance } from '@vuepic/vue-datepicker';
import moment from 'moment';

const props = defineProps({
  dropdownRange: {
    type: Object,
    required: false,
    default: () => ({}),
  },
  firstDate: {
    type: String,
    required: true,
  },
  dateLabel: {
    type: String,
    required: false,
    default: '',
  },
  currentView: {
    type: String,
    required: false,
    default: '',
  },
  clearDate: {
    type: Boolean,
    required: false,
    default: false,
  },
  startingDate: {
    type: String,
    required: false,
    default: '',
  },
  endingDate: {
    type: String,
    required: false,
    default: '',
  },
  dateName: {
    type: String,
    required: false,
    default: '',
  },
});

const selectedPresentIndex = ref(99);
const dateRangeMain: Ref<Element | null> = ref(null);
const dateType = ref('');
const dateDropdown = ref();
const dateTypeName = ref(props.dateName);

const translatedData = inject('translatedData') as Record<string, string>;

dateType.value = props.dropdownRange && Object.values(props.dropdownRange)[0];

const dateTypeKey = ref('');

dateTypeKey.value = props.dropdownRange && Object.keys(props.dropdownRange)[0];

const showRangeDropdown = ref(false);

const emit = defineEmits([
  'triggerSetDateRange',
  'triggerSetDateType',
  'dateCleared',
]);
const initialDate = computed(() => props.firstDate);

const fixed = ref(props.dateName);
const todayDate = moment(new Date()).format('YYYY-MM-DD');
const selectedDate: Ref<Date[] | string[]> = ref([
  new Date(),
  new Date(new Date().setDate(new Date().getDate() + 7)),
]);

const datepicker = ref<DatePickerInstance>(null);

onMounted(() => {
  selectedDate.value[0] = '';
  selectedDate.value[1] = todayDate;
  triggerSetDateRange('', todayDate, fixed.value);
});

watch(
  () => [props.endingDate, props.startingDate],
  () => {
    if (props.endingDate && props.startingDate) {
      selectedDate.value[0] = props.startingDate;
      selectedDate.value[1] = props.endingDate;
    }
  },
  { deep: true }
);

const handlePresentRangeItemClick = (index) => {
  const presentRangeItems = document.getElementsByClassName('dp__preset_range');
  selectedPresentIndex.value = index;
  presentRangeItems[index].classList.add('preset-range-item-active');

  for (let j = 0; j < presentRangeItems.length; j++) {
    if (j !== index) {
      presentRangeItems[j].classList.remove('preset-range-item-active');
    }
  }
};

watch(
  () => props.clearDate,
  () => {
    resetDate().then(() => {
      emit('dateCleared');
    });
  },
  { deep: true }
);

const handleCalendarItemClick = () => {
  selectedPresentIndex.value = 99;
};

const addEventsForCalendar = () => {
  showRangeDropdown.value = false;
  const presentRangeItems = document.getElementsByClassName('dp__preset_range');

  for (let i = 0; i < presentRangeItems.length; i++) {
    presentRangeItems[i].addEventListener('click', () => {
      handlePresentRangeItemClick(i);
    });
  }

  const calendarItems = document.getElementsByClassName('dp__calendar_item');

  for (let i = 0; i < calendarItems.length; i++) {
    calendarItems[i].addEventListener('click', handleCalendarItemClick);
  }
};

const removeEventsOfCalendar = () => {
  const presentRangeItems = document.getElementsByClassName('dp__preset_range');

  for (let i = 0; i < presentRangeItems.length; i++) {
    presentRangeItems[i].removeEventListener('click', () => {
      handlePresentRangeItemClick(i);
    });
  }

  const calendarItems = document.getElementsByClassName('dp__calendar_item');

  for (let i = 0; i < calendarItems.length; i++) {
    calendarItems[i].removeEventListener('click', handleCalendarItemClick);
  }
};

const toggleShowRangeDropdown = () => {
  showRangeDropdown.value = !showRangeDropdown.value;
};

const resetDate = async () => {
  triggerSetDateRange('', '');
  selectedDate.value[0] = '';
  selectedDate.value[1] = '';
  fixed.value = translatedData['common.common.all_time'];
  return { success: true };
};

const presetRanges = computed(() => [
  {
    label: translatedData['common.common.today'],
    range: [startOfDay(new Date()), endOfDay(new Date())],
  },
  {
    label: translatedData['common.common.this_week'],
    range: [startOfWeek(new Date()), endOfDay(new Date())],
  },
  {
    label: translatedData['common.common.last_7_days'],
    range: [subDays(new Date(), 6), endOfDay(new Date())],
  },
  {
    label: translatedData['common.common.this_month'],
    range: [startOfMonth(new Date()), endOfMonth(new Date())],
  },
  {
    label: translatedData['common.common.last_6_month'],
    range: [startOfMonth(subMonths(new Date(), 6)), endOfMonth(new Date())],
  },
  {
    label: translatedData['common.common.this_year'],
    range: [startOfYear(new Date()), endOfDay(new Date())],
  },
  {
    label: translatedData['common.common.last_12_months'],
    range: [startOfMonth(subMonths(new Date(), 12)), endOfDay(new Date())],
  },
  {
    label: translatedData['common.common.all_time'],
    range: [new Date(initialDate.value), endOfDay(new Date())],
  },
]);

const convertDate = (date) => {
  const dateObj = new Date(date);
  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const openCalendar = () => {
  if (datepicker.value) {
    datepicker.value.openMenu();
  }
};

const closeCalendar = () => {
  if (datepicker.value) {
    removeEventsOfCalendar();
    datepicker.value.closeMenu();
  }
};

const selectDate = () => {
  if (datepicker.value) {
    datepicker.value.selectDate();
  }
};

const format = (dates) => {
  const tempArray: string | number[] = [];

  for (let i = 0; i < dates.length; i++) {
    tempArray[i] = dates[i].toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }

  return tempArray.join(' - ');
};

const closeDateDropdown = () => {
  showRangeDropdown.value = false;
};

watch(
  () => showRangeDropdown.value,
  (value) => {
    if (value) {
      document.addEventListener('click', closeDateDropdown);
      dateRangeMain.value?.addEventListener('click', keepModelOpen);
    } else {
      document.removeEventListener('click', closeDateDropdown);
      dateRangeMain.value?.removeEventListener('click', keepModelOpen);
    }
  }
);
const keepModelOpen = (event) => {
  event.stopPropagation();
};

watch(
  () => [selectedDate],
  () => {
    const startDate =
      selectedDate.value && selectedDate.value[0]
        ? convertDate(selectedDate.value[0])
        : false;
    const endDate =
      selectedDate.value && selectedDate.value[1]
        ? convertDate(selectedDate.value[1])
        : false;

    if (startDate && endDate) {
      setSelectedPresentDayText();

      triggerSetDateRange(startDate, endDate, fixed.value);
    }
  },
  { deep: true }
);

watch(
  () => [dateType],
  () => {
    triggerSetDateType(dateTypeKey.value);
  },
  { deep: true }
);

watch(
  () => [props.currentView],
  () => {
    selectedDate.value[0] = '';
    selectedDate.value[1] = '';
    fixed.value = translatedData['common.common.all_time'];
  },
  { deep: true }
);

const triggerSetDateRange = (startDate, endDate, filteredDateType = '') => {
  emit('triggerSetDateRange', startDate, endDate, filteredDateType);
};
watch(
  () => props.dateName,
  (value) => {
    dateTypeName.value = value;
  }
);

watch(
  () => fixed.value,
  () => {
    const startDate =
      selectedDate.value && selectedDate.value[0]
        ? convertDate(selectedDate.value[0])
        : false;
    const endDate =
      selectedDate.value && selectedDate.value[1]
        ? convertDate(selectedDate.value[1])
        : false;

    if (startDate && endDate) {
      triggerSetDateRange(startDate, endDate, fixed.value);
    }
  }
);

const triggerSetDateType = (eventType) => {
  emit('triggerSetDateType', eventType);
};

const setSelectedPresentDayText = () => {
  fixed.value =
    presetRanges.value[selectedPresentIndex.value]?.label ?? 'Custom';

  // selectedPresentIndex.value = 99;

  if (dateTypeName.value) {
    fixed.value = dateTypeName.value;
    dateTypeName.value = '';
  }
};

const customPosition = () => {
  let leftPosition = 0;
  if (dateRangeMain.value) {
    leftPosition =
      window.innerWidth - dateRangeMain.value?.getBoundingClientRect()?.right >
      150
        ? Number(dateRangeMain.value?.getBoundingClientRect().right) - 300
        : Number(dateRangeMain.value?.getBoundingClientRect().right) - 420;
  }
  return {
    top: Number(dateRangeMain.value?.getBoundingClientRect().bottom) + 20,
    left: leftPosition,
  };
};

const setDateRangeTypeInDropdown = (value, key) => {
  showRangeDropdown.value = false;
  dateType.value = value;
  dateTypeKey.value = key;
};
</script>
