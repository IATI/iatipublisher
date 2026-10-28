<!-- eslint-disable vue/no-v-html -->
<template>
  <tr>
    <td>Baseline</td>
    <td v-if="!isEveryValueNull(baseline)">
      <div
        v-for="(base, b) in baseline"
        :key="b"
        :class="{
          'mb-3': Number(b) !== baseline.length - 1,
        }"
      >
        <div :class="elementSpacing">
          <span>
            Year:
            <template v-if="base.year">
              {{ base.year }}
            </template>
            <template v-else>
              <span class="text-xs italic text-light-gray">N/A</span></template
            >,
          </span>
          <span>
            Date:
            <template v-if="base.date">
              {{ base.date }}
            </template>
            <template v-else>
              <span class="text-xs italic text-light-gray">N/A</span></template
            >,
          </span>
          <span>
            Value:
            <template v-if="base.value">
              {{ base.value }}
            </template>
            <template v-else>
              <span class="text-xs italic text-light-gray">N/A</span></template
            >
          </span>
        </div>

        <div class="flex" :class="elementSpacing">
          <div>Location:&nbsp;</div>
          <div>
            {{ location(base.location) ? location(base.location) : '' }}
            <span
              v-if="!location(base.location)"
              class="text-xs italic text-light-gray"
              >N/A</span
            >
          </div>
        </div>

        <div class="flex" :class="elementSpacing">
          <div>Dimension:&nbsp;</div>
          <div class="description" v-html="dimensions(base.dimension)"></div>
        </div>

        <div class="flex" :class="elementSpacing">
          <div>Comment:&nbsp;</div>
          <div>
            <div
              v-for="(com, c) in base.comment[0].narrative"
              :key="c"
              class="item"
              :class="{
                'mb-1.5': Number(c) !== base.comment[0].narrative.length - 1,
              }"
            >
              <div>
                <div class="description">
                  {{ com.narrative ? com.narrative : '' }}
                  <span
                    v-if="!com.narrative"
                    class="text-xs italic text-light-gray"
                    >N/A</span
                  >
                  <span v-if="com.narrative" class="language subtle-darker">
                    (Language:
                    {{ com.language ? baseType.language[com.language] : ''
                    }}<span
                      v-if="!com.language"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >)</span
                  >
                </div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <div class="mb-2.5 flex">
            <div>
              Document Link:&nbsp;
              <span
                v-if="isEveryValueNull(base.document_link)"
                class="text-xs italic text-light-gray"
                >N/A</span
              >
            </div>
          </div>
          <div v-if="!isEveryValueNull(base.document_link)">
            <div class="divider mb-4 h-px w-full border-b border-n-20"></div>
            <DocumentLink
              :data="base.document_link"
              :type="baseType"
              alignment=""
            />
          </div>
        </div>
      </div>
    </td>
    <td v-else>
      <span class="text-xs italic text-light-gray">N/A</span>
    </td>
  </tr>
</template>

<script lang="ts">
import { defineComponent, toRefs } from 'vue';
import { DocumentLink } from './Index';
import { countDocumentLink, isEveryValueNull } from 'Composable/utils';

export default defineComponent({
  name: 'IndicatorBaseline',
  components: { DocumentLink },
  props: {
    data: {
      type: Array,
      required: true,
    },
    baseType: {
      type: Object,
      required: true,
    },
  },
  setup(props) {
    let { data } = toRefs(props);
    const elementSpacing = 'mb-1';

    /**
     * Baseline
     */
    interface Comment {
      [index: number]: {
        narrative: [
          {
            language: string;
            narrative: string;
          }
        ];
      };
    }

    interface Dimension {
      name: string;
      value: string;
    }

    interface BaselineElements {
      comment: Comment;
      date: string;
      dimension: Dimension[];
      document_link: [];
      location: Location[];
      value: number;
      year: number;
    }

    const baseline = data.value as BaselineElements[];

    /**
     * Function to return all locations of baseline
     * @param data
     */

    interface Location {
      reference: string;
    }

    const location = (data: Location[]) => {
      let locations: string[] = [];

      locations = data.map((item) => {
        return item.reference;
      });

      const lastLocation = locations.slice(-1)[0];
      locations = locations.slice(0, -1);

      if (locations.length > 0) {
        return locations.join(', ') + ' ' + 'and' + ' ' + lastLocation;
      } else {
        return lastLocation;
      }
    };

    /**
     * Function to return dimensions of baseline
     * @param data
     */
    const dimensions = (data: Dimension[]) => {
      return data
        .map((item) => {
          const name = item.name
            ? `<span>${item.name}</span>`
            : `<span class="text-xs italic text-light-gray">N/A</span>`;
          const value = item.value
            ? `<span>${item.value}</span>`
            : `<span class="text-xs italic text-light-gray">N/A</span>`;
          return `code - ${name}, value - ${value}`;
        })
        .join('; ');
    };

    return {
      baseline,
      location,
      dimensions,
      elementSpacing,
      countDocumentLink,
      isEveryValueNull,
    };
  },
});
</script>
