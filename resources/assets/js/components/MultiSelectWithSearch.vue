<template>
  <div ref="publisherTypeMultiselect">
    <div class="margin-20">
      <p class="m-2 pb-2 text-xs font-bold uppercase text-n-50">
        {{ props.header }}
      </p>

      <div class="search" style="margin-right: 4px !important">
        <input
          v-model="searchInput"
          class="search__input mr-3.5"
          type="text"
          :placeholder="`${translatedData['common.common.search']} ${props.header}...`"
          style="width: 100%; height: 40px"
          @input="updateArrayBySearch()"
        />
        <svg-vue icon="search" />
      </div>

      <div class="my-1">
        <div v-if="showNoDataComponent" class="p-5 text-center capitalize">
          {{ translatedData['common.common.no_data_found'] }}
        </div>
        <ul v-else class="max-h-[350px] overflow-y-scroll">
          <li v-for="item in tempListItems" :key="item.key">
            <div v-if="item.show" class="mt-2">
              <span class="m-2">
                <input
                  :id="item.key"
                  v-model="checkedBoxes"
                  type="checkbox"
                  :value="item.key"
                />
              </span>
              <label class="m-2 px-3 text-n-40" :for="item.key">{{
                item.label
              }}</label>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="footer-ribbon" style="">
      <BtnComponent
        class="space"
        :text="translatedData['common.common.apply']"
        type="primary"
        @click="applyFilter"
      />
    </div>
  </div>
</template>
<script lang="ts" setup>
import {
  defineEmits,
  defineProps,
  ref,
  onMounted,
  onBeforeUnmount,
  computed,
  inject,
} from 'vue';
import BtnComponent from 'Components/ButtonComponent.vue';

const props = defineProps({
  listItems: {
    type: Object,
    required: true,
  },
  header: {
    type: String,
    required: true,
  },
});
interface TempItem {
  key: string;
  label: string;
  show: boolean;
}

const translatedData = inject('translatedData') as Record<string, string>;

let tempListItems = ref<TempItem[]>([]);
const publisherTypeMultiselect = ref();

const emit = defineEmits(['changeSelectedPublisher', 'close']);
let keys = Object.keys(props.listItems);
let searchInput = ref('');
let checkedBoxes = ref([]);

let changeSelectedPublisher = (selectedPublisherType) => {
  emit('changeSelectedPublisher', selectedPublisherType);
};

formatPublisherType();

function formatPublisherType() {
  let keys = Object.keys(props.listItems);
  for (let i = 0; i < keys.length; i++) {
    tempListItems.value.push({
      key: keys[i],
      label: props.listItems[keys[i]],
      show: true,
    });
  }
}
onMounted(() => {
  publisherTypeMultiselect.value.addEventListener(
    'click',
    keepPublisherModelOpen
  );
});

onBeforeUnmount(() => {
  publisherTypeMultiselect.value.removeEventListener(
    'click',
    keepPublisherModelOpen
  );
});

const showNoDataComponent = computed(() => {
  let count = 0;
  tempListItems.value.map((item) => {
    if (item.show) {
      count++;
    }
  });
  return !count;
});

const updateArrayBySearch = () => {
  let searchString = searchInput.value.toLowerCase();

  for (let i = 0; i < keys.length; i++) {
    let item = tempListItems.value[i];
    tempListItems.value[i].show = item.label
      .toLowerCase()
      .includes(searchString);
  }
};
const keepPublisherModelOpen = (event) => {
  event.stopPropagation();
};
const applyFilter = () => {
  emit('close');

  changeSelectedPublisher(checkedBoxes.value);
};
</script>
<style>
.border-this {
  border: 1px solid red;
}
.margin-20 {
  margin: 20px;
}

.footer-ribbon {
  display: flex;
  justify-content: flex-end;
  padding: 8px 20px;
  background: #fffde7;
}
</style>
