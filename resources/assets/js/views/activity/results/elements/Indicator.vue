<template>
  <div
    id="indicator"
    class="activities__content--element !bg-red w-full basis-full px-3 py-3 text-n-50"
  >
    <div class="rounded-lg bg-white p-4">
      <div class="mb-4 flex">
        <div class="title flex grow items-center">
          <svg-vue class="mr-1.5 text-xl text-bluecoral" icon="bill"></svg-vue>
          <div class="title text-sm font-bold">
            {{ getTranslatedElement(translatedData, 'indicator') }}
          </div>
        </div>
        <div class="icons flex items-center">
          <Btn
            :text="translatedData['common.common.add_indicator']"
            icon="add"
            :link="`/result/${result.id}/indicator/create`"
            class="mr-2.5"
          />
          <Btn
            :text="translatedData['common.common.show_full_indicator_list']"
            icon=""
            design="bgText"
            :link="`/result/${result.id}/indicator`"
            class="mr-2.5"
          />
          <svg-vue class="mr-1.5" icon="core"></svg-vue>
          <div class="help text-n-40">
            <button>
              <svg-vue icon="help"></svg-vue>
            </button>
            <div class="help__text right-0 w-60">
              <span
                class="close-help absolute top-5 right-2 z-[50] scale-[2] cursor-pointer"
                ><svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 12 14"
                  fill="none"
                >
                  <path
                    fill="#2A2F30"
                    d="M4.588 3.5 7.212.88a.418.418 0 0 0-.591-.592L4 2.913 1.38.288a.418.418 0 1 0-.593.591L3.413 3.5.787 6.12a.417.417 0 0 0 .136.684.417.417 0 0 0 .456-.091L4 4.088l2.62 2.625a.417.417 0 0 0 .684-.136.417.417 0 0 0-.092-.456L4.588 3.5Z"
                  /></svg
              ></span>
              <span class="font-bold text-bluecoral"></span>
              <p class="!text-black" :v-html="toolTip"></p>
            </div>
          </div>
        </div>
      </div>
      <HelperText :helper-text="onlyDeprecatedStatusMap(indicatorData)" />
      <div class="divider mb-4 h-px w-full border-b border-n-20"></div>
      <div class="indicator">
        <template v-for="(post, ri) in indicatorData" :key="ri">
          <div class="item">
            <div class="elements-detail wider">
              <div class="category flex">
                <div class="mr-4">
                  <a
                    class="text-n-50"
                    :href="`/result/${result.id}/indicator/${post.id}`"
                  >
                    {{
                      getActivityTitle(post.indicator.title[0].narrative, 'en')
                    }}
                  </a>
                </div>
                <div class="flex shrink-0 grow justify-between">
                  <span class="flex">
                    <Btn
                      :text="translatedData['common.common.view_indicator']"
                      icon="eye"
                      :link="`/result/${result.id}/indicator/${post.id}`"
                      class="mr-2.5"
                    />
                    <Btn
                      :text="translatedData['common.common.edit_indicator']"
                      icon="edit"
                      :link="`/result/${result.id}/indicator/${post.id}/edit`"
                    />
                  </span>
                  <Btn
                    :text="translatedData['common.common.add_period']"
                    icon="edit"
                    :link="`/indicator/${post.id}/period/create`"
                    class="mr-2.5"
                  />
                </div>
              </div>
              <div class="ml-4">
                <div class="indicators">
                  <table class="mb-3">
                    <tbody>
                      <tr>
                        <td>
                          {{
                            getTranslatedElement(
                              translatedData,
                              'indicator_title'
                            )
                          }}
                        </td>
                        <td>
                          <template
                            v-for="(title, t) in post.indicator.title[0]
                              .narrative"
                            :key="t"
                          >
                            <div
                              class="title-content"
                              :class="{
                                'mb-1.5':
                                  t !==
                                  post.indicator.title[0].narrative.length - 1,
                              }"
                            >
                              <div
                                v-if="title.narrative"
                                class="language subtle-darker mb-1"
                              >
                                ({{ getTranslatedLanguage(translatedData) }}:
                                {{
                                  type.language[title.language]
                                    ? type.language[title.language]
                                    : 'N/A'
                                }})
                              </div>
                              <div v-else>
                                <span class="text-xs italic text-light-gray"
                                  >N/A</span
                                >
                              </div>
                              <div class="description text-xs">
                                {{ title.narrative }}
                              </div>
                            </div>
                          </template>
                        </td>
                      </tr>

                      <tr v-if="post.indicator.measure">
                        <td>
                          {{ getTranslatedElement(translatedData, 'measure') }}
                        </td>
                        <td>
                          {{ type.indicatorMeasure[post.indicator.measure] }}
                        </td>
                      </tr>

                      <tr v-if="post.indicator.aggregation_status">
                        <td>
                          {{
                            getTranslatedElement(
                              translatedData,
                              'aggregation_status'
                            )
                          }}
                        </td>
                        <td>{{ post.indicator.aggregation_status != 0 }}</td>
                      </tr>

                      <tr>
                        <td>
                          {{
                            getTranslatedElement(translatedData, 'description')
                          }}
                        </td>
                        <td>
                          <template
                            v-for="(description, d) in post.indicator
                              .description[0].narrative"
                            :key="d"
                          >
                            <div
                              class="title-content"
                              :class="{
                                'mb-1.5':
                                  d !==
                                  post.indicator.description[0].narrative
                                    .length -
                                    1,
                              }"
                            >
                              <div
                                v-if="description.narrative"
                                class="language subtle-darker mb-1"
                              >
                                ({{ getTranslatedLanguage(translatedData) }}:
                                {{
                                  type.language[description.language]
                                    ? type.language[description.language]
                                    : 'N/A'
                                }})
                              </div>
                              <div v-else>
                                <span class="text-xs italic text-light-gray"
                                  >N/A</span
                                >
                              </div>
                              <div class="description text-xs">
                                {{ description.narrative }}
                              </div>
                            </div>
                          </template>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          {{
                            getTranslatedElement(translatedData, 'reference')
                          }}
                        </td>

                        <td v-if="!isEveryValueNull(post.indicator.reference)">
                          <div
                            v-for="(ref, r) in post.indicator.reference"
                            :key="r"
                            :class="{
                              'mb-1.5':
                                r !== post.indicator.reference.length - 1,
                            }"
                          >
                            <span>
                              {{
                                getTranslatedElement(
                                  translatedData,
                                  'vocabulary'
                                )
                              }}: {{ ref.vocabulary ?? '' }}
                              <span
                                v-if="!ref.vocabulary"
                                class="text-xs italic text-light-gray"
                                >N/A</span
                              >,
                            </span>
                            <span>
                              {{ getTranslatedElement(translatedData, 'code') }}
                              : {{ ref.code ? ref.code : '' }}
                              <span
                                v-if="!ref.code"
                                class="text-xs italic text-light-gray"
                                >N/A</span
                              >,
                            </span>
                            <span>
                              {{
                                getTranslatedElement(
                                  translatedData,
                                  'indicator_uri'
                                )
                              }}:
                              <a
                                v-if="ref.indicator_uri"
                                :href="ref.indicator_uri"
                                class="cursor-pointer"
                                target="_blank"
                              >
                                {{ ref.indicator_uri }}</a
                              >
                              <span
                                v-else
                                class="text-xs italic text-light-gray"
                                >N/A</span
                              >
                            </span>
                          </div>
                        </td>
                        <td v-else>
                          <span class="text-xs italic text-light-gray"
                            >N/A</span
                          >
                        </td>
                      </tr>

                      <tr>
                        <td>
                          {{
                            getTranslatedElement(
                              translatedData,
                              'document_link'
                            )
                          }}
                        </td>
                        <td>
                          {{ countDocumentLink(post.indicator.document_link) }}
                          {{
                            getTranslatedElement(translatedData, 'documents')
                          }}
                        </td>
                      </tr>

                      <tr>
                        <td>
                          {{ getTranslatedElement(translatedData, 'baseline') }}
                        </td>

                        <td v-if="!isEveryValueNull(post.indicator.baseline)">
                          <div
                            v-for="(base, b) in post.indicator.baseline"
                            :key="b"
                            :class="{
                              'mb-1.5':
                                b !== post.indicator.baseline.length - 1,
                            }"
                          >
                            <div>
                              <span>
                                {{
                                  getTranslatedElement(translatedData, 'year')
                                }}:
                                <template v-if="base.year">
                                  {{ base.year }}
                                </template>
                                <template v-else>
                                  <span class="text-xs italic text-light-gray"
                                    >N/A</span
                                  >
                                </template>
                                ,
                              </span>
                              <span>
                                {{
                                  getTranslatedElement(translatedData, 'date')
                                }}:
                                <template v-if="base.date">
                                  {{ base.date }}
                                </template>
                                <template v-else>
                                  <span class="text-xs italic text-light-gray"
                                    >N/A</span
                                  ></template
                                >
                                ,
                              </span>
                              <span>
                                {{
                                  getTranslatedElement(translatedData, 'value')
                                }}:
                                <template v-if="base.value">
                                  {{ base.value }}
                                </template>
                                <template v-else>
                                  <span class="text-xs italic text-light-gray"
                                    >N/A</span
                                  ></template
                                >
                              </span>
                            </div>
                            <div class="flex">
                              <div>
                                {{
                                  getTranslatedElement(
                                    translatedData,
                                    'location'
                                  )
                                }}:&nbsp;
                              </div>
                              <div>
                                <div
                                  v-for="(loc, l) in base.location"
                                  :key="l"
                                  class="item"
                                  :class="{
                                    'mb-1.5': l !== base.location.length - 1,
                                  }"
                                >
                                  <template v-if="loc.reference">
                                    {{ loc.reference }}
                                  </template>
                                  <template v-else>
                                    <span class="text-xs italic text-light-gray"
                                      >N/A</span
                                    ></template
                                  >
                                </div>
                              </div>
                            </div>

                            <div class="flex">
                              <div>
                                {{
                                  getTranslatedElement(
                                    translatedData,
                                    'dimension'
                                  )
                                }}:&nbsp;
                              </div>
                              <div class="description">
                                <div
                                  v-for="(dim, d) in base.dimension"
                                  :key="d"
                                  :class="{
                                    'mb-1.5': d !== base.dimension.length - 1,
                                  }"
                                >
                                  <div>
                                    <span>
                                      <template v-if="dim.name">
                                        {{ dim.name }}
                                      </template>
                                      <template v-else>
                                        <span
                                          class="text-xs italic text-light-gray"
                                          >N/A</span
                                        >
                                      </template>
                                      &nbsp;
                                    </span>
                                    <span v-if="dim.name">
                                      <template v-if="dim.value">
                                        ({{ dim.value }})
                                      </template>
                                      <template v-else>
                                        (<span
                                          class="text-xs italic text-light-gray"
                                          >N/A</span
                                        >)
                                      </template>
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="flex">
                              <div>
                                {{
                                  getTranslatedElement(
                                    translatedData,
                                    'comment'
                                  )
                                }}:&nbsp;
                              </div>
                              <div class="description">
                                <div
                                  v-for="(com, c) in base.comment[0].narrative"
                                  :key="c"
                                  class="item"
                                  :class="{
                                    'mb-1.5':
                                      c !==
                                      base.comment[0].narrative.length - 1,
                                  }"
                                >
                                  <div>
                                    <span>
                                      <template v-if="com.narrative">
                                        {{ com.narrative }}
                                      </template>
                                      <template v-else
                                        ><span
                                          class="text-xs italic text-light-gray"
                                          >N/A</span
                                        >
                                      </template>
                                      &nbsp;
                                    </span>
                                    <span class="language subtle-darker">
                                      ({{
                                        getTranslatedLanguage(translatedData)
                                      }}:
                                      <template v-if="com.language">
                                        {{ type.language[com.language] }})
                                      </template>
                                      <template v-else> N/A) </template>
                                    </span>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="flex">
                              <div>
                                {{
                                  getTranslatedElement(
                                    translatedData,
                                    'document_link'
                                  )
                                }}:&nbsp;
                              </div>
                              <div>
                                {{ countDocumentLink(base.document_link) }}
                                {{
                                  getTranslatedElement(
                                    translatedData,
                                    'document'
                                  )
                                }}
                              </div>
                            </div>
                          </div>
                        </td>
                        <td v-else>
                          <span class="text-xs italic text-light-gray"
                            >N/A</span
                          >
                        </td>
                      </tr>

                      <tr v-if="post.periods.length === 0">
                        <td></td>
                        <td>
                          <div class="mt-3">
                            <NotYet
                              :link="`/indicator/${post.id}/period/create`"
                              :description="
                                translatedData[
                                  'common.common.you_havent_added_any_periods_yet'
                                ]
                              "
                              btn-text="Add period"
                              class="w-[442px]"
                            />
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <!-- for periods -->
                <div v-if="post.periods.length > 0" class="periods">
                  <table v-for="(item, key) in post.periods" :key="key">
                    <tbody>
                      <tr>
                        <td>
                          <div class="category">
                            {{ getTranslatedElement(translatedData, 'period') }}
                            {{ Number(key) + 1 }}
                          </div>
                        </td>
                        <td>
                          <div class="category flex">
                            <div class="mr-10">
                              <a
                                class="text-n-50"
                                :href="`/indicator/${post.id}/period/${item.id}`"
                              >
                                {{
                                  dateFormat(
                                    item.period.period_start[0].date,
                                    'MMMM DD, YYYY'
                                  )
                                }}
                                -
                                {{
                                  dateFormat(
                                    item.period.period_end[0].date,
                                    'MMMM DD, YYYY'
                                  )
                                }}
                              </a>
                            </div>
                            <div class="flex shrink-0 grow justify-between">
                              <Btn
                                :text="
                                  translatedData['common.common.view_period']
                                "
                                icon="eye"
                                :link="`/indicator/${post.id}/period/${item.id}`"
                                class="mr-2.5"
                              />
                              <Btn
                                :text="
                                  translatedData['common.common.edit_period']
                                "
                                icon="edit"
                                :link="`/indicator/${post.id}/period/${item.id}/edit`"
                              />
                            </div>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          {{
                            getTranslatedElement(translatedData, 'target_value')
                          }}
                        </td>
                        <td>
                          <template
                            v-for="(tar, t) in item.period.target"
                            :key="t"
                          >
                            <div
                              class="item"
                              :class="{
                                'mb-1.5': t !== item.period.target.length - 1,
                              }"
                            >
                              <div class="language target_value mb-1">
                                {{ tar.value }}
                              </div>

                              <div class="location_reference flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'location_reference'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(loc, l) in tar.location"
                                    :key="l"
                                    class="item"
                                    :class="{
                                      'mb-1.5': l !== tar.location.length - 1,
                                    }"
                                  >
                                    <div>
                                      <span>
                                        <template v-if="loc.reference">
                                          {{ loc.reference }}
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
                                </div>
                              </div>

                              <div class="dimension flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'dimension'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(dim, d) in tar.dimension"
                                    :key="d"
                                    class="item"
                                    :class="{
                                      'mb-1.5': d !== tar.dimension.length - 1,
                                    }"
                                  >
                                    <span>
                                      <template v-if="dim.name">
                                        {{ dim.name }}
                                      </template>
                                      <template v-else>
                                        <span
                                          class="text-xs italic text-light-gray"
                                          >N/A</span
                                        >
                                      </template>
                                    </span>
                                    <span v-if="dim.name">
                                      <template v-if="dim.value">
                                        ({{ dim.value }})
                                      </template>
                                      <template v-else>
                                        ({{
                                          getTranslatedMissing(translatedData)
                                        }})
                                      </template>
                                    </span>
                                  </div>
                                </div>
                              </div>

                              <div class="flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'comment'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(com, c) in tar.comment[0].narrative"
                                    :key="c"
                                    class="item"
                                    :class="{
                                      'mb-1.5': c !== tar.comment.length - 1,
                                    }"
                                  >
                                    <div>
                                      <span>
                                        <template v-if="com.narrative">
                                          {{ com.narrative }}
                                        </template>
                                        <template v-else>
                                          <span
                                            class="text-xs italic text-light-gray"
                                            >N/A</span
                                          >
                                        </template>
                                        &nbsp;
                                      </span>
                                      <span
                                        v-if="com.narrative"
                                        class="language subtle-darker"
                                      >
                                        ({{
                                          getTranslatedLanguage(translatedData)
                                        }}:
                                        <template v-if="com.language">
                                          {{ type.language[com.language] }})
                                        </template>
                                        <template v-else> N/A) </template>
                                      </span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </template>
                        </td>
                      </tr>

                      <tr>
                        <td>
                          {{
                            getTranslatedElement(translatedData, 'actual_value')
                          }}
                        </td>
                        <td>
                          <template
                            v-for="(tar, t) in item.period.actual"
                            :key="t"
                          >
                            <div
                              class="item"
                              :class="{
                                'mb-1.5': t !== item.period.actual.length - 1,
                              }"
                            >
                              <div class="language target_value mb-1">
                                {{ tar.value }}
                              </div>

                              <div class="location_reference flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'location_reference'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(loc, l) in tar.location"
                                    :key="l"
                                    class="item"
                                    :class="{
                                      'mb-1.5': l !== tar.location.length - 1,
                                    }"
                                  >
                                    <div>
                                      <span>
                                        <template v-if="loc.reference">
                                          {{ loc.reference }}
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
                                </div>
                              </div>

                              <div class="dimension flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'dimension'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(dim, d) in tar.dimension"
                                    :key="d"
                                    class="item"
                                    :class="{
                                      'mb-1.5': d !== tar.dimension.length - 1,
                                    }"
                                  >
                                    <span>
                                      <template v-if="dim.name">
                                        {{ dim.name }}
                                      </template>
                                      <template v-else>
                                        <span
                                          class="text-xs italic text-light-gray"
                                          >N/A</span
                                        >
                                      </template>
                                    </span>
                                    <span v-if="dim.name">
                                      <template v-if="dim.value">
                                        ({{ dim.value }})
                                      </template>
                                      <template v-else>
                                        ({{
                                          getTranslatedMissing(translatedData)
                                        }})
                                      </template>
                                    </span>
                                  </div>
                                </div>
                              </div>

                              <div class="flex">
                                <div>
                                  {{
                                    getTranslatedElement(
                                      translatedData,
                                      'comment'
                                    )
                                  }}:&nbsp;
                                </div>
                                <div>
                                  <div
                                    v-for="(com, c) in tar.comment[0].narrative"
                                    :key="c"
                                    class="item"
                                    :class="{
                                      'mb-1.5': c !== tar.comment.length - 1,
                                    }"
                                  >
                                    <div>
                                      <span>
                                        <template v-if="com.narrative">
                                          {{ com.narrative }}
                                        </template>
                                        <template v-else>
                                          <span
                                            class="text-xs italic text-light-gray"
                                            >N/A</span
                                          >
                                        </template>
                                        &nbsp;
                                      </span>
                                      <span
                                        v-if="com.narrative"
                                        class="language subtle-darker"
                                      >
                                        ({{
                                          getTranslatedLanguage(translatedData)
                                        }}:
                                        <template v-if="com.language">
                                          {{ type.language[com.language] }})
                                        </template>
                                        <template v-else> N/A) </template>
                                      </span>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </template>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div
            v-if="ri != indicatorData.length - 1"
            class="divider my-8 h-px w-full border-b border-n-20"
          ></div>
        </template>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject, toRefs } from 'vue';

//composable
import dateFormat from 'Composable/dateFormat';
import getActivityTitle from 'Composable/title';

//components
import NotYet from 'Components/sections/HaveNotAddedYet.vue';
import Btn from 'Components/buttons/Link.vue';

// helper function
import {
  countDocumentLink,
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
  isEveryValueNull,
  onlyDeprecatedStatusMap,
} from 'Composable/utils';
import HelperText from 'Components/HelperText.vue';

export default defineComponent({
  name: 'ResultIndicator',
  components: {
    HelperText,
    NotYet,
    Btn,
  },
  props: {
    result: {
      type: Object,
      required: true,
    },
    type: {
      type: Object,
      required: true,
    },
    toolTip: {
      type: String,
      required: false,
      default: '',
    },
  },
  setup(props) {
    let { result } = toRefs(props);
    const translatedData = inject('translatedData') as Record<string, string>;

    const indicatorData = result.value.indicators.reverse();

    return {
      indicatorData,
      dateFormat,
      getActivityTitle,
      countDocumentLink,
      isEveryValueNull,
      translatedData,
    };
  },
  methods: {
    getTranslatedMissing,
    getTranslatedElement,
    getTranslatedLanguage,
    onlyDeprecatedStatusMap,
  },
});
</script>
