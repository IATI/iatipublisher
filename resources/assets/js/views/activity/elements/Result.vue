<template>
  <div
    id=""
    class="activities__content--element basis-full px-3 py-3 text-n-50"
  >
    <div :id="title" class="rounded-lg bg-white p-4">
      <div class="mb-4 flex">
        <div class="title flex grow items-center">
          <svg-vue class="mr-1.5 text-xl text-bluecoral" icon="bill"></svg-vue>
          <div class="title text-sm font-bold">
            {{ title.toString().replace(/_/g, '-') }}
          </div>
          <div
            class="status ml-2.5 flex text-xs leading-5"
            :class="{
              'text-spring-50': completed,
              'text-crimson-50': !completed,
            }"
          >
            <span v-if="!completed">
              <b class="mr-2 text-base leading-3">.</b>
              {{ translatedData['common.common.not_completed']?.toLowerCase() }}
            </span>
          </div>
        </div>
        <div class="icons flex items-center">
          <Btn
            :text="translatedData['common.common.add_new_result']"
            icon="add"
            :link="`/activity/${activityId}/${title}/create`"
            class="mr-2.5"
          />
          <Btn
            :text="translatedData['common.common.show_full_result_list']"
            icon=""
            design="bgText"
            :link="`/activity/${activityId}/${title}`"
            class="mr-2.5"
          />
          <svg-vue class="mr-1.5" icon="core"></svg-vue>
          <HoverText
            :name="title.toString().replace(/_/g, '-')"
            :hover-text="tooltip"
            :show-iati-reference="true"
            class="text-sm text-n-40"
          ></HoverText>
        </div>
      </div>

      <HelperText
        v-if="elementHasDeprecatedCode"
        helper-text="This element data contains deprecated codelist value."
      />

      <div class="divider mb-4 h-px w-full bg-n-20"></div>
      <div class="results">
        <template v-for="(result, r) in resultData" :key="r">
          <div class="item">
            <HelperText :helper-text="result['deprecation_status_map']" />

            <div class="elements-detail">
              <div>
                <!-- title -->
                <div class="category flex">
                  <div
                    class="mr-4 max-w-[500px] overflow-x-hidden text-ellipsis whitespace-nowrap"
                  >
                    {{
                      getActivityTitle(result.result.title[0].narrative, 'en')
                    }}
                  </div>
                  <div class="flex shrink-0">
                    <Btn
                      :text="translatedData['common.common.view_result']"
                      icon="eye"
                      :link="`/activity/${activityId}/${title}/${result.id}`"
                      class="mr-2.5"
                    />
                    <Btn
                      :text="translatedData['common.common.edit_result']"
                      icon="edit"
                      :link="`/activity/${activityId}/${title}/${result.id}/edit`"
                    />
                  </div>
                </div>
                <!-- content -->
                <div class="ml-4">
                  <table class="mb-3">
                    <tbody>
                      <tr>
                        <td>Result Type</td>
                        <td>
                          <div>
                            {{ types.resultType[result.result.type] ?? '' }}
                            <span
                              v-if="!types.resultType[result.result.type]"
                              class="text-xs italic text-light-gray"
                              >N/A</span
                            >
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>Description</td>
                        <td>
                          <div class="description-content">
                            <div class="language subtle-darker mb-1.5">
                              (Language:
                              {{
                                getActivityTitle(
                                  result.result.description[0].narrative,
                                  currentLanguage
                                ) === 'Untitled'
                                  ? 'N/A'
                                  : types.languages[
                                      result?.result?.description?.[0]
                                        ?.narrative?.[0]?.language ??
                                        defaultLanguage
                                    ]
                              }})
                            </div>
                            <div class="w-[500px] max-w-full">
                              {{
                                getActivityTitle(
                                  result.result.description[0].narrative,
                                  currentLanguage
                                )
                              }}
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr v-if="result.indicators.length === 0">
                        <td></td>
                        <td>
                          <div>
                            <NotYet
                              :link="`/${title}/${result.id}/indicator/create`"
                              :description="
                                translatedData[
                                  'common.common.you_havent_added_any_indicator_yet'
                                ]
                              "
                              :btn-text="
                                translatedData[
                                  'common.common.add_new_indicator'
                                ]
                              "
                            />
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                  <!-- indicator -->
                  <div
                    v-if="result.indicators.length > 0"
                    class="indicator overflow-hidden rounded-t-lg border border-n-20"
                  >
                    <div class="items-center border-b border-n-20 px-6 py-2">
                      <div class="head flex items-center">
                        <div class="grow text-xs font-bold text-n-50">
                          Indicator
                        </div>
                        <div class="inline-flex shrink-0">
                          <Btn
                            :text="
                              translatedData['common.common.add_new_indicator']
                            "
                            icon="add"
                            :link="`/${title}/${result.id}/indicator/create`"
                            class="mr-2.5"
                          />
                          <Btn
                            :text="
                              translatedData[
                                'common.common.show_full_indicator_list'
                              ]
                            "
                            icon=""
                            design="bgText"
                            :link="`/${title}/${result.id}/indicator`"
                          />
                        </div>
                      </div>
                      <div class="block">
                        <HelperText
                          :helper-text="
                            onlyDeprecatedStatusMap(result['indicators'])
                          "
                        />
                      </div>
                    </div>
                    <div>
                      <template
                        v-for="(indic, i) in result.indicators"
                        :key="i"
                      >
                        <div
                          class="indicator-content flex px-6 py-2"
                          :class="{
                            'mb-2 border-b border-n-20':
                              i !== result.indicators.length - 1,
                          }"
                        >
                          <div class="elements-detail grow">
                            <div class="category flex">
                              <div class="mr-4">
                                {{
                                  indic.indicator.title[0].narrative[0]
                                    .narrative ??
                                  getTranslatedUntitled(translatedData)
                                }}
                              </div>
                              <div class="flex shrink-0 grow justify-between">
                                <span class="flex">
                                  <Btn
                                    :text="
                                      translatedData[
                                        'common.common.view_indicator'
                                      ]
                                    "
                                    icon="eye"
                                    :link="`/${title}/${result.id}/indicator/${indic.id}`"
                                    class="mr-2.5"
                                  />
                                  <Btn
                                    :text="
                                      translatedData[
                                        'common.common.edit_indicator'
                                      ]
                                    "
                                    :link="`/${title}/${result.id}/indicator/${indic.id}/edit`"
                                    class="mr-2.5"
                                  />
                                </span>
                                <Btn
                                  :text="
                                    translatedData['common.common.add_period']
                                  "
                                  icon="add"
                                  :link="`/indicator/${indic.id}/period/create`"
                                />
                              </div>
                            </div>
                            <table>
                              <tbody>
                                <tr>
                                  <td>Baseline:</td>
                                  <td>
                                    <div
                                      v-for="(baseline, b) in indic.indicator
                                        .baseline"
                                      :key="b"
                                      class=""
                                      :class="{
                                        'mb-1':
                                          b !==
                                          indic.indicator.baseline.length - 1,
                                      }"
                                    >
                                      <div class="description text-xs">
                                        <span>
                                          Value:
                                          <template v-if="baseline.value">
                                            {{ baseline.value }},
                                          </template>
                                          <template v-else>
                                            <span
                                              class="text-xs italic text-light-gray"
                                              >N/A</span
                                            >,
                                          </template>
                                        </span>
                                        <span>
                                          Date:
                                          <template v-if="baseline.date">
                                            {{ baseline.date }}
                                          </template>
                                          <template v-else>
                                            <span
                                              class="text-xs italic text-light-gray"
                                              >N/A</span
                                            >
                                          </template>
                                        </span>
                                      </div>
                                    </div>
                                  </td>
                                </tr>
                                <tr v-if="indic.periods.length > 0">
                                  <td>Period:</td>
                                  <td>
                                    <div class="inline-flex gap-4">
                                      <div>
                                        <div
                                          v-for="(period, p) in indic.periods"
                                          :key="p"
                                          class="flex"
                                          :class="{
                                            'mb-1':
                                              p !== indic.periods.length - 1,
                                          }"
                                        >
                                          <div class="text-xs">
                                            <a
                                              class="text-xs text-n-50"
                                              :href="`/indicator/${indic.id}/period/${period.id}`"
                                            >
                                              {{
                                                dateFormat(
                                                  period.period.period_start[0]
                                                    .date,
                                                  format
                                                )
                                              }}
                                              -
                                              {{
                                                dateFormat(
                                                  period.period.period_end[0]
                                                    .date,
                                                  format
                                                )
                                              }}
                                            </a>
                                          </div>
                                          <div class="ml-2">
                                            <Btn
                                              :text="
                                                translatedData[
                                                  'common.common.edit'
                                                ]
                                              "
                                              icon="edit"
                                              :link="`/indicator/${indic.id}/period/${period.id}/edit`"
                                            />
                                          </div>
                                        </div>
                                      </div>
                                      <div class="shrink-0">
                                        <Btn
                                          class="-mt-1"
                                          :text="
                                            translatedData[
                                              'common.common.show_full_period_list'
                                            ]
                                          "
                                          icon=""
                                          design="bgText"
                                          :link="`/indicator/${indic.id}/period`"
                                        />
                                      </div>
                                    </div>
                                  </td>
                                </tr>
                                <tr v-else>
                                  <td></td>
                                  <td>
                                    <div>
                                      <NotYet
                                        :link="`/indicator/${indic.id}/period/create`"
                                        :description="
                                          translatedData[
                                            'common.common.you_havent_added_any_period_yet'
                                          ]
                                        "
                                      />
                                    </div>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div
            v-if="r !== data.content.length - 1"
            class="divider my-5 h-px w-full border-b border-n-20"
          ></div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, toRefs } from 'vue';
import moment from 'moment';

//components
import Btn from 'Components/buttons/Link.vue';
import NotYet from 'Components/sections/HaveNotAddedYet.vue';

// composable
import getActivityTitle from 'Composable/title';
import dateFormat from 'Composable/dateFormat';
import HelperText from 'Components/HelperText.vue';

import indicator from 'Activity/results/elements/Indicator.vue';
import {
  getTranslatedUntitled,
  onlyDeprecatedStatusMap,
} from 'Composable/utils';
import { traceSegment } from '@jridgewell/trace-mapping';

export default defineComponent({
  name: 'ActivityResult',
  components: {
    // NavDropdown,
    // BtnComponent,
    HelperText,
    Btn,
    NotYet,
  },
  props: {
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
    defaultLanguage: {
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
    elementHasDeprecatedCode: {
      type: [Boolean],
      required: false,
      default: false,
    },
  },

  setup(props) {
    const format = 'MMMM DD, YYYY';
    const translatedData = inject('translatedData') as Record<string, string>;
    const { data } = toRefs(props);

    let resultData = data.value.content;

    const currentLanguage = 'en';

    return {
      moment,
      format,
      resultData,
      getActivityTitle,
      currentLanguage,
      dateFormat,
      translatedData,
    };
  },
  computed: {
    indicator() {
      return indicator;
    },
  },
  methods: { traceSegment, getTranslatedUntitled, onlyDeprecatedStatusMap },
});
</script>
