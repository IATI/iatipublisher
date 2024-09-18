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
            {{ toKebabCase(elementName) }}
          </div>
        </div>
        <div class="icons flex items-center">
          <HoverText :hover-text="hoverText" class="text-n-40"></HoverText>
        </div>
      </div>
      <div class="divider mb-4 h-px w-full bg-n-20"></div>

      <div>
        <template
          v-if="elementName === 'title' || elementName === 'description'"
        >
          <TitleDescription :data="elementData" :type="types.language" />
        </template>

        <template v-else-if="elementName === 'aggregation_status'">
          <span class="text-sm capitalize"
            >{{ parseInt(data as string) ? 'True' : data ? 'False' : '' }}
            <span v-if="!data" class="text-xs italic text-light-gray">N/A</span>
          </span>
        </template>

        <template v-else-if="elementName === 'document_link'">
          <DocumentLink :data="elementData" :type="types" />
        </template>

        <template v-else-if="elementName === 'reference'">
          <Reference :data="elementData" :type="resultVocabulary" />
        </template>

        <template v-else-if="elementName === 'type'">
          <ResultType :data="Number(elementData)" :type="resultType" />
        </template>

        <template v-else>
          {{ data }}
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs } from 'vue';
import HoverText from 'Components/HoverText.vue';
import {
  TitleDescription,
  DocumentLink,
  Reference,
  ResultType,
} from './elements/Index';
import { toKebabCase } from 'Composable/utils';

export default defineComponent({
  name: 'ActivityElement',
  components: {
    HoverText,
    TitleDescription,
    DocumentLink,
    Reference,
    ResultType,
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
  },
  setup(props) {
    let { data, types } = toRefs(props),
      elementData = data.value,
      resultType = types.value.resultType,
      resultVocabulary = types.value.resultVocabulary,
      language = types.value.language;

    return { elementData, resultType, resultVocabulary, language };
  },
  methods: { toKebabCase },
});
</script>
