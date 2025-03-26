<template>
  <div
    v-for="(post, key) in data"
    :key="key"
    class="elements-detail"
    :class="{ 'mb-4': Number(key) !== data.length - 1 }"
  >
    <div class="category text-sm font-bold">
      <span v-if="post.type">{{ types.contactType[post.type] }}</span>
      <span v-else class="italic">{{
        getTranslatedMissing(translatedData, 'type')
      }}</span>
    </div>

    <div class="ml-5">
      <table>
        <tbody>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'organization') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.organisation[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.organisation[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  ({{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'person_name') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.person_name[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.person_name[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  ({{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>

          <tr>
            <td>{{ getTranslatedElement(translatedData, 'department') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.department[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.department[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  ({{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'job_title') }}</td>
            <td>
              <div
                v-for="(narrative, k) in post.job_title[0].narrative"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.job_title[0].narrative.length - 1,
                }"
              >
                <div class="language mb-1.5">
                  ({{ getTranslatedLanguage(translatedData) }}:
                  {{
                    narrative.language
                      ? types.languages[narrative.language]
                      : getTranslatedMissing(translatedData)
                  }})
                </div>
                <div class="w-[500px] max-w-full">
                  {{
                    narrative.narrative ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'email') }}</td>
            <td>
              <div
                v-for="(email_value, k) in post.email"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.email.length - 1,
                }"
              >
                <div class="w-[500px] max-w-full">
                  {{
                    email_value.email ?? getTranslatedMissing(translatedData)
                  }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'telephone') }}</td>
            <td>
              <div
                v-for="(tel, k) in post.telephone"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.telephone.length - 1,
                }"
              >
                <div class="w-[500px] max-w-full">
                  {{ tel.telephone ?? getTranslatedMissing(translatedData) }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>{{ getTranslatedElement(translatedData, 'website') }}</td>
            <td>
              <div
                v-for="(w, k) in post.website"
                :key="k"
                class="description-content"
                :class="{
                  'mb-4': k !== post.website.length - 1,
                }"
              >
                <div class="w-[500px] max-w-full">
                  {{ w.website ?? getTranslatedMissing(translatedData) }}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              {{ getTranslatedElement(translatedData, 'mailing_address') }}
            </td>
            <td>
              <div
                v-for="(address, address_index) in post.mailing_address"
                :key="address_index"
                :class="{
                  'mb-4': k !== address.narrative.length - 1,
                }"
              >
                <div
                  v-for="(narrative, k) in address.narrative"
                  :key="k"
                  class="description-content"
                >
                  <div class="language mb-1.5">
                    ({{ getTranslatedLanguage(translatedData) }}:
                    {{
                      narrative.language
                        ? types.languages[narrative.language]
                        : getTranslatedMissing(translatedData)
                    }})
                  </div>
                  <div class="w-[500px] max-w-full">
                    {{
                      narrative.narrative ??
                      getTranslatedMissing(translatedData)
                    }}
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, inject } from 'vue';
import {
  getTranslatedElement,
  getTranslatedLanguage,
  getTranslatedMissing,
} from 'Composable/utils';

export default defineComponent({
  name: 'ActivityContactInfo',
  components: {},
  props: {
    data: {
      type: Object,
      required: true,
    },
  },
  setup() {
    interface Types {
      contactType: [];
      languages: [];
    }
    const types = inject('types') as Types;
    const translatedData = inject('translatedData') as Record<string, string>;

    return { types, translatedData };
  },
  methods: {
    getTranslatedElement,
    getTranslatedMissing,
    getTranslatedLanguage,
  },
});
</script>
