<template>
  <div class="smooth -mb-7 px-5 py-7 sm:px-10 sm:py-14 md:-mb-20">
    <div class="mx-auto max-w-[700px]">
      <section
        class="rounded bg-white px-6 py-4 shadow-textbox sm:px-12 sm:py-8"
      >
        <h3 class="text-lg font-bold text-bluecoral">
          {{ translatedData['public.publishing_checklist.heading'] }}:
        </h3>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_one.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_one.paragraph_one'
              ]
            }}
          </p>
          <a target="_blank" href="/iati/register">
            {{
              translatedData[
                'public.publishing_checklist.point_one.paragraph_two'
              ]
            }}
          </a>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_one.paragraph_three'
              ]
            }}
          </p>
          <a target="_blank" href="/register">
            {{
              translatedData[
                'public.publishing_checklist.point_one.paragraph_four'
              ]
            }}
          </a>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_two.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_one'
              ]
            }}
          </p>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_two'
              ]
            }}
          </p>
          <a target="_blank" href="/iati-standard">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_three'
              ]
            }}
          </a>
          <br />
          <a target="_blank" href="/activities" @click="triggerModel">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_four'
              ]
            }}
          </a>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_three.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_three.paragraph_one'
              ]
            }}
          </p>
          <p class="mt-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_three.paragraph_two.heading'
              ]
            }}
          </p>
          <ul class="ml-4 mt-4 list-disc text-base text-bluecoral">
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.publishing_checklist.point_three.paragraph_two.point_one'
                ]
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.publishing_checklist.point_three.paragraph_two.point_two'
                ]
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.publishing_checklist.point_three.paragraph_two.point_three'
                ]
              }}</span>
            </li>
          </ul>
          <p class="mt-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_three.paragraph_three.heading'
              ]
            }}
          </p>
          <ul class="ml-4 mt-4 list-disc text-base text-bluecoral">
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.publishing_checklist.point_three.paragraph_three.point_one'
                ]
              }}</span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.publishing_checklist.point_three.paragraph_three.point_two'
                  ]
                "
              ></span>
            </li>
          </ul>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_three.paragraph_four'
              ]
            }}
          </p>

          <a target="_blank" href="/iati-standard">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_three'
              ]
            }}
          </a>
          <br />
          <a target="_blank" href="/activities" @click="triggerModel">
            {{
              translatedData[
                'public.publishing_checklist.point_two.paragraph_four'
              ]
            }}
          </a>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_four.heading']
            }}
          </h3>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_four.paragraph_one'
              ]
            "
          ></p>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_four.paragraph_two'
              ]
            "
          ></p>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_five.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_five.paragraph_one'
              ]
            }}
          </p>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_six.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_six.paragraph_one'
              ]
            }}
          </p>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_six.paragraph_two'
              ]
            "
          ></p>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_seven.heading']
            }}
          </h3>
          <p class="my-2 text-base">
            {{
              translatedData[
                'public.publishing_checklist.point_seven.paragraph_one'
              ]
            }}
          </p>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_seven.paragraph_two'
              ]
            "
          ></p>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_seven.paragraph_three'
              ]
            "
          ></p>
        </article>
        <article class="py-4 sm:py-6">
          <h3 class="py-2 text-2xl font-bold text-bluecoral">
            {{
              translatedData['public.publishing_checklist.point_eight.heading']
            }}
          </h3>
          <p
            class="my-2 text-base"
            v-html="
              translatedData[
                'public.publishing_checklist.point_eight.paragraph_one'
              ]
            "
          ></p>
        </article>
        <article
          class="py-4 sm:py-6"
          v-html="translatedData['public.publishing_checklist.ending']"
        ></article>
      </section>
    </div>
  </div>
</template>
<script lang="ts" setup>
import LanguageService from 'Services/language';
import { onMounted, ref } from 'vue';

const translatedData = ref({});

function triggerModel() {
  localStorage.setItem('openAddModel', 'true');
}

onMounted(async () => {
  const translatedLanguage = await LanguageService.getTranslatedData(
    'common,public'
  );
  console.log('Translated Data', translatedLanguage);

  LanguageService.getTranslatedData('common,public')
    .then((response) => {
      translatedData.value = response.data;
    })
    .catch((error) => console.log(error));
});
</script>
