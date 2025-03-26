<template>
  <div class="elements-detail wider">
    <div
      v-for="(cou, c) in country"
      :key="c"
      class="item"
      :class="{
        'mb-4': c !== Object.keys(country).length - 1,
      }"
    >
      <div class="category">
        <span>{{ type.countryCode[cou.country_code] }}</span>
      </div>
      <div class="ml-4">
        <table class="mb-3">
          <tbody>
            <tr>
              <td>{{ getTranslatedElement(translatedData, 'description') }}</td>
              <td>
                <div
                  v-for="(sd, i) in cou.narrative"
                  :key="i"
                  class="title-content mb-4"
                  :class="{
                    'mb-4': i !== cou.narrative.length - 1,
                  }"
                >
                  <div v-if="sd.narrative" class="language mb-1.5">
                    (
                    {{
                      sd.language
                        ? `${getTranslatedLanguage(translatedData)} : ${
                            type.languages[sd.language]
                          }`
                        : `${getTranslatedLanguage(translatedData)} : N/A`
                    }})
                  </div>
                  <div class="text-sm">
                    {{ sd.narrative ?? '' }}
                    <span
                      v-if="!sd.narrative"
                      class="text-xs italic text-light-gray"
                      >N/A</span
                    >
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, toRefs, inject } from 'vue';
import { getTranslatedElement, getTranslatedLanguage } from 'Composable/utils';

export default defineComponent({
  name: 'TransactionRecipientCountry',
  components: {},
  props: {
    data: {
      type: [Object, String],
      required: true,
    },
  },
  setup(props) {
    const { data } = toRefs(props);

    interface ArrayObject {
      [index: number]: {
        country_code: string;
        narrative: [{ language: string; narrative: string }];
      };
    }
    const country = data.value as ArrayObject;

    interface TypesInterface {
      countryCode: [];
      languages: [];
    }

    const type = inject('types') as TypesInterface;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { country, type, translatedData };
  },
  methods: { getTranslatedElement, getTranslatedLanguage },
});
</script>
