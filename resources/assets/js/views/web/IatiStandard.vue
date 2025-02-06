<template>
  <div class="smooth -mb-7 px-5 py-7 sm:px-10 sm:py-14 md:-mb-20">
    <div class="mx-auto max-w-[700px]">
      <section class="rounded bg-white py-4 shadow-textbox sm:py-8">
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <p class="text-base">
            {{ translatedData['public.iati_standard.description'] }}
          </p>
        </article>
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="my-2 text-2xl font-bold text-bluecoral">
            {{ translatedData['public.iati_standard.point_one.heading'] }}
          </h3>
          <p class="text-base">
            {{
              translatedData[
                'public.iati_standard.point_one.paragraph_one.heading'
              ]
            }}
          </p>
          <ul class="ml-4 mt-4 list-disc text-base text-bluecoral">
            <li class="text-base">
              <span class="text-black"
                >{{
                  translatedData[
                    'public.iati_standard.point_one.paragraph_one.point_one'
                  ]
                }}
              </span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.iati_standard.point_one.paragraph_one.point_two'
                ]
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData[
                  'public.iati_standard.point_one.paragraph_one.point_three'
                ]
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black">{{
                translatedData['common.common.run_automatic_checks']
              }}</span>
            </li>
            <li class="text-base">
              <span class="text-black"
                >{{
                  translatedData[
                    'public.iati_standard.point_one.paragraph_one.point_five'
                  ]
                }}
              </span>
            </li>
          </ul>
          <p class="mt-4 text-base">
            <a target="_blank" rel="noopener noreferrer" href="/organisation">{{
              translatedData['public.iati_standard.point_one.ending']
            }}</a>
          </p>
        </article>
        <article class="px-6 py-4 sm:px-12 sm:py-6">
          <h3 class="my-2 text-2xl font-bold text-bluecoral">
            {{ translatedData['public.iati_standard.point_two.heading'] }}
          </h3>
          <p class="text-base">
            {{
              translatedData[
                'public.iati_standard.point_two.paragraph_one.heading'
              ]
            }}
          </p>
          <ul class="my-4 ml-4 list-disc text-base text-bluecoral">
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_one'
                  ]
                "
              >
              </span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_two'
                  ]
                "
              >
              </span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_three'
                  ]
                "
              >
              </span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_four'
                  ]
                "
              >
              </span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_five'
                  ]
                "
              >
              </span>
            </li>
            <li class="text-base">
              <span
                class="text-black"
                v-html="
                  translatedData[
                    'public.iati_standard.point_two.paragraph_one.point_six'
                  ]
                "
              >
              </span>
            </li>
          </ul>
          <h6 class="text-lg font-bold text-bluecoral">
            {{ translatedData['public.iati_standard.point_three.heading'] }}
          </h6>

          <p class="my-4 text-base">
            {{
              translatedData['public.iati_standard.point_three.paragraph_one']
            }}
          </p>
          <p class="my-4 text-base">
            {{
              translatedData['public.iati_standard.point_three.paragraph_two']
            }}
          </p>
          <p class="my-4 text-base">
            {{
              translatedData['public.iati_standard.point_three.paragraph_three']
            }}
          </p>
          <p class="my-4 text-base">
            <a target="_blank" rel="noopener noreferrer" href="/organisation"
              >{{ translatedData['public.iati_standard.point_one.ending'] }}
            </a>
          </p>
        </article>
        <!--        HIDDEN FOR NOW-->
        <!--        <article class="px-6 py-4 sm:px-12 sm:py-6">-->
        <!--          <h3 class="my-2 text-2xl font-bold text-bluecoral">-->
        <!--            {{ translatedData['public.iati_standard.point_four.heading'] }}-->
        <!--          </h3>-->
        <!--          <p class="text-base">-->
        <!--            {{-->
        <!--              translatedData['public.iati_standard.point_four.paragraph_one']-->
        <!--            }}-->
        <!--          </p>-->
        <!--          <a-->
        <!--            target="_blank"-->
        <!--            rel="noopener noreferrer"-->
        <!--            class="my-2 cursor-pointer"-->
        <!--            @click="downloadManual('organization')"-->
        <!--          >-->
        <!--            {{-->
        <!--              translatedData['public.iati_standard.point_four.organisation_pdf']-->
        <!--            }}-->
        <!--          </a>-->
        <!--          <br />-->
        <!--          <a-->
        <!--            target="_blank"-->
        <!--            rel="noopener noreferrer"-->
        <!--            class="my-2 cursor-pointer"-->
        <!--            @click="downloadManual('activity')"-->
        <!--          >-->
        <!--            {{ translatedData['public.iati_standard.point_four.activity_pdf'] }}-->
        <!--          </a>-->
        <!--        </article>-->
        <!--        <article class="px-6 py-4 sm:px-12 sm:py-6">-->
        <!--          <h3 class="my-2 text-2xl font-bold text-bluecoral">-->
        <!--            {{ translatedData['public.iati_standard.point_five.heading'] }}-->
        <!--          </h3>-->
        <!--          <p class="text-base">-->
        <!--            {{-->
        <!--              translatedData['public.iati_standard.point_five.paragraph_one']-->
        <!--            }}-->
        <!--          </p>-->
        <!--          <a-->
        <!--            target="_blank"-->
        <!--            rel="noopener noreferrer"-->
        <!--            href="https://iatistandard.org/en/iati-standard/203/activity-standard/"-->
        <!--            class="my-2"-->
        <!--          >-->
        <!--            <u>{{-->
        <!--              translatedData[-->
        <!--                'public.iati_standard.point_five.activity_standard'-->
        <!--              ]-->
        <!--            }}</u> </a-->
        <!--          ><br />-->
        <!--          <a-->
        <!--            target="_blank"-->
        <!--            rel="noopener noreferrer"-->
        <!--            href="https://iatistandard.org/en/iati-standard/203/organisation-standard/"-->
        <!--            class="my-2"-->
        <!--          >-->
        <!--            <u>{{-->
        <!--              translatedData[-->
        <!--                'public.iati_standard.point_five.organisation_standard'-->
        <!--              ]-->
        <!--            }}</u>-->
        <!--          </a>-->
        <!--        </article>-->
      </section>
    </div>
  </div>
</template>
<script lang="ts" setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import LanguageService from 'Services/language';

const translatedData = ref({});

function downloadManual(type: string) {
  let fileName = {
    activity: 'IATI_Publisher_Activity_Standard.pdf',
    organization: 'IATI_Publisher_Organisation_Standard.pdf',
  };
  let url = window.location.origin + `/Data/Manuals/${fileName[type]}`;

  axios({
    url: url,
    method: 'GET',
    responseType: 'arraybuffer',
  }).then((response) => {
    let blob = new Blob([response.data], {
      type: 'application/pdf',
    });
    let link = document.createElement('a');
    link.href = window.URL.createObjectURL(blob);
    link.download = fileName[type];
    link.click();
  });
}

onMounted(() => {
  LanguageService.getTranslatedData('common,public')
    .then((response) => {
      translatedData.value = response.data;
      console.log(translatedData.value);
    })
    .catch((error) => console.log(error));
});
</script>
