<template>
  <div v-if="!isEveryValueNull(dlData)" class="documents">
    <template v-for="(post, i) in dlData" :key="i">
      <div class="item elements-detail">
        <div
          class="category w-[800px] max-w-[80%] overflow-x-hidden text-ellipsis whitespace-nowrap"
        >
          {{ post.title[0].narrative[0].narrative }}
        </div>
        <div class="ml-4">
          <table class="mb-3">
            <tbody>
              <tr>
                <td>{{ getTranslatedElement(translatedData, 'title') }}</td>
                <td>
                  <template v-for="(na, n) in post.title[0].narrative" :key="n">
                    <div class="title-content mb-1.5">
                      <div
                        v-if="na.narrative"
                        class="language subtle-darker mb-1"
                      >
                        ({{ getTranslatedLanguage(translatedData) }}:
                        {{
                          type.language[na.language]
                            ? type.language[na.language]
                            : 'N/A'
                        }})
                      </div>
                      <div v-else>
                        <span class="text-xs italic text-light-gray">N/A</span>
                      </div>
                      <div
                        class="description !w-[800px] !max-w-[50%] overflow-x-hidden text-ellipsis whitespace-nowrap text-xs"
                      >
                        {{ na.narrative }}
                      </div>
                    </div>
                  </template>
                </td>
              </tr>

              <tr v-if="post.url">
                <td>
                  {{ getTranslatedElement(translatedData, 'document_link') }}
                </td>
                <td>
                  <a
                    class="w-[800px] !max-w-[50%] overflow-x-hidden text-ellipsis whitespace-nowrap"
                    target="_blank"
                    :href="post.url"
                    >{{ post.url }}</a
                  >
                </td>
              </tr>

              <tr>
                <td>{{ getTranslatedElement(translatedData, 'format') }}</td>
                <td>
                  {{ post.format ? post.format : '' }}
                  <span
                    v-if="!post.format"
                    class="text-xs italic text-light-gray"
                    >N/A</span
                  >
                </td>
              </tr>

              <tr>
                <td>
                  {{ getTranslatedElement(translatedData, 'description') }}
                </td>
                <td>
                  <template
                    v-for="(na, n) in post.description[0].narrative"
                    :key="n"
                  >
                    <div class="description-content mb-1.5">
                      <div
                        v-if="na.narrative"
                        class="language subtle-darker mb-1"
                      >
                        ({{ getTranslatedLanguage(translatedData) }} :
                        {{
                          type.language[na.language]
                            ? type.language[na.language]
                            : ''
                        }})
                      </div>
                      <div v-else>
                        <span class="text-xs italic text-light-gray">N/A</span>
                      </div>
                      <div class="description text-xs">
                        {{ na.narrative }}
                      </div>
                    </div>
                  </template>
                </td>
              </tr>

              <tr>
                <td>{{ getTranslatedElement(translatedData, 'category') }}</td>
                <td>
                  <template v-for="(cat, c) in post.category" :key="c">
                    <div class="mb-1 text-xs">
                      {{
                        type.documentCategory[cat.code]
                          ? type.documentCategory[cat.code]
                          : ''
                      }}
                      <span
                        v-if="!type.documentCategory[cat.code]"
                        class="text-xs italic text-light-gray"
                        >N/A</span
                      >
                    </div>
                  </template>
                </td>
              </tr>

              <tr v-if="post.language.length > 0">
                <td>{{ getTranslatedLanguage(translatedData) }}</td>
                <td>
                  <div class="text-xs">
                    {{
                      post.language[0].language === null
                        ? ''
                        : post.language
                            .map((entry) => type.language[entry.language])
                            .join(', ')
                    }}
                    <span
                      v-if="post.language[0].language === null"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </div>
                </td>
              </tr>

              <tr>
                <td>
                  {{ getTranslatedElement(translatedData, 'document_date') }}
                </td>
                <td>
                  <div class="text-xs">
                    {{
                      post.document_date[0].date
                        ? post.document_date[0].date
                        : ''
                    }}
                    <span
                      v-if="!post.document_date[0].date"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
  <div v-else>
    <span class="text-xs italic text-light-gray">N/A</span>
  </div>
</template>

<script lang="ts">
import {
  getTranslatedElement,
  getTranslatedLanguage,
  isEveryValueNull,
} from 'Composable/utils';
import { defineComponent, inject, toRefs } from 'vue';

export default defineComponent({
  name: 'ResultDocumentLink',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
    type: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);
    const translatedData = inject('translatedData') as Record<string, string>;
    const dlData = data.value;

    return { dlData, isEveryValueNull, translatedData };
  },
  methods: { getTranslatedElement, getTranslatedLanguage },
});
</script>
