<template>
  <footer
    id="footer"
    class="mt-7 bg-bluecoral text-sm leading-6 text-n-20 sm:mt-10 md:mt-20"
  >
    <div class="border-b border-white border-opacity-20">
      <div class="mx-3 py-10 sm:mx-10 xl:mx-24 xl:px-1">
        <div
          class="footer__container grid grid-flow-row gap-8 sm:gap-y-10 md:grid-cols-2 lg:grid-cols-4 lg:justify-items-center"
        >
          <div>
            <a href="/">
              <svg-vue
                class="h-auto w-60 text-6xl sm:w-64"
                icon="footer-logo"
              />
            </a>
          </div>

          <div class="footer__links">
            <span class="font-bold text-n-10">{{
              translatedData['footer.footer.iati_publisher']
            }}</span>
            <ul class="mt-2 flex flex-col">
              <li>
                <a :href="superAdmin ? '/list-organisations' : '/activities'">
                  {{
                    superAdmin
                      ? translatedData['common.common.organisation_list']
                      : translatedData['common.common.your_activities']
                  }}
                </a>
              </li>
              <li>
                <a href="/about">{{ translatedData['common.common.about'] }}</a>
              </li>
              <li>
                <a
                  target="_blank"
                  rel="noopener noreferrer"
                  class="cursor-pointer"
                  @click="downloadManual('user')"
                  >{{ translatedData['footer.footer.user_manual_v1'] }}</a
                >
              </li>
            </ul>
          </div>
          <div class="footer__links">
            <span class="font-bold text-n-10">{{
              translatedData['common.common.iati_standard']
            }}</span>
            <ul class="mt-2 flex flex-col">
              <li>
                <a href="/iati-standard">{{
                  translatedData['common.common.iati_standard']
                }}</a>
              </li>
              <li>
                <a href="/publishing-checklist">{{
                  translatedData['common.common.publishing_checklist']
                }}</a>
              </li>
              <li>
                <a href="/support">{{
                  translatedData['common.common.support']
                }}</a>
              </li>
              <li>
                <a
                  href="https://iatistandard.org/en/privacy-policy/"
                  target="_blank"
                  >{{ translatedData['footer.footer.privacy_policy'] }}</a
                >
              </li>
            </ul>
          </div>
          <div class="footer__links lg:justify-self-end">
            <div class="text-xs leading-5">
              <p>
                {{
                  translatedData[
                    'footer.footer.part_of_the_iati_unified_platform'
                  ]
                }}
              </p>
              <p>
                {{
                  translatedData[
                    'footer.footer.code_licensed_under_the_gnu_agpl'
                  ]
                }}
              </p>
              <p>
                {{
                  translatedData[
                    'footer.footer.documentation_licensed_under_cc_by3'
                  ]
                }}
              </p>
            </div>
            <div class="my-5 flex items-center space-x-2 text-n-10">
              <svg-vue class="text-2xl" icon="headphone" />
              <span class="text-xs font-bold uppercase">{{
                translatedData['footer.footer.any_questions_contact_support']
              }}</span>
            </div>
            <ul>
              <li>
                <a
                  class="text-sm text-n-10"
                  href="mailto:support@iatistandard.org"
                  >{{
                    translatedData['footer.footer.support_iati_standard_org']
                  }}</a
                >
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="mx-5 py-7 sm:mx-10 xl:mx-24 xl:px-1">
      <div class="footer__container grid gap-3 sm:grid-cols-2">
        <span class="flex items-center text-n-30">
          <svg-vue class="mr-1 text-base" icon="copyright" />
          {{
            translatedData[
              'footer.footer.copyright_iati_2022_all_rights_reserved'
            ]
          }}</span
        >
        <div class="flex sm:justify-end">
          <a
            href="https://www.youtube.com/channel/UCAVH1gcgJXElsj8ENC-bDQQ"
            target="_blank"
          >
            <svg-vue class="mr-1 mt-1 text-4xl" icon="youtube" />
          </a>
          <a class="ml-4" href="https://twitter.com/IATI_aid" target="_blank">
            <svg-vue class="mr-1 mt-1 text-4xl" icon="twitter" />
          </a>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';
import axios from 'axios';

defineProps({
  superAdmin: { type: Boolean, required: false, default: false },
  translatedData: { type: Object, required: true },
});

function downloadManual(type: string) {
  let fileName = {
    user: 'IATI_Publisher-User_Manual_v1.1.pdf',
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
</script>
