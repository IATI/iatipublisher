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
            <span class="font-bold text-n-10">IATI Publisher</span>
            <ul class="mt-2 flex flex-col">
              <li>
                <a :href="superAdmin ? '/list-organisations' : '/activities'">{{
                  superAdmin ? 'Organisation List' : 'Your Activities'
                }}</a>
              </li>
              <li><a href="/about">About</a></li>
              <li>
                <a
                  target="_blank"
                  rel="noopener noreferrer"
                  class="cursor-pointer"
                  @click="downloadManual('user')"
                  >User Manual v1.1</a
                >
              </li>
            </ul>
          </div>
          <div class="footer__links">
            <span class="font-bold text-n-10">IATI Standard</span>
            <ul class="mt-2 flex flex-col">
              <li><a href="/iati-standard">IATI Standard</a></li>
              <li><a href="/publishing-checklist">Publishing Checklist</a></li>
              <li><a href="/support">Support</a></li>
              <li>
                <a
                  href="https://iatistandard.org/en/privacy-policy/"
                  target="_blank"
                  >Privacy Policy</a
                >
              </li>
            </ul>
          </div>
          <div class="footer__links lg:justify-self-end">
            <div class="text-xs leading-5">
              <p>Part of the IATI Unified Platform</p>
              <p>Code licensed under the GNU AGPL.</p>
              <p>Documentation licensed under CC BY 3.0</p>
            </div>
            <div class="my-5 flex items-center space-x-2 text-n-10">
              <svg-vue class="text-2xl" icon="headphone" />
              <span class="text-xs font-bold uppercase"
                >Any questions? Contact Support</span
              >
            </div>
            <ul>
              <li>
                <a
                  class="text-sm text-n-10"
                  href="mailto:support@iatistandard.org"
                  >support@iatistandard.org</a
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
          Copyright IATI 2022. All rights reserved.</span
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
