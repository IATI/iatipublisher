<template>
  <footer class="iati-design-system iati-footer iati-brand-background mt-14">
    <div class="iati-brand-background__content">
      <div class="iati-footer__section iati-footer__section--first">
        <div class="iati-footer__container">
          <div class="iati-footer-block">
            <h2 class="iati-footer-block__title text-white">
              {{ props.translatedData['common.common.useful_links'] }}
            </h2>
            <div class="iati-footer-block__content">
              <ul>
                <li>
                  <a href="/">{{
                    props.translatedData['footer.footer.home']
                  }}</a>
                </li>

                <li>
                  <a
                    rel="noopener noreferrer"
                    class="cursor-pointer"
                    target="_blank"
                    href="https://docs.publisher.iatistandard.org/en/latest/"
                    >{{ props.translatedData['common.common.help_docs'] }}</a
                  >
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="iati-footer__section">
        <div class="iati-footer__container">
          <div class="iati-footer-block">
            <h2 class="iati-footer-block__title text-white">
              {{ props.translatedData['common.common.additional_information'] }}
            </h2>
            <div
              class="iati-footer-block__content iati-footer-block__content--columns"
            >
              <div>
                <p>
                  {{
                    props.translatedData[
                      'footer.footer.part_of_the_iati_unified_platform'
                    ]
                  }}
                </p>
                <p
                  v-html="
                    props.translatedData[
                      'footer.footer.code_licensed_under_the_gnu_agpl_link'
                    ]
                  "
                />

                <p
                  v-html="
                    props.translatedData[
                      'footer.footer.documentation_licensed_under_cc_by3_link'
                    ]
                  "
                ></p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="iati-footer__section iati-footer__section--last">
        <div class="iati-footer__container">
          <nav>
            <ul class="iati-piped-list iati-footer__legal-nav">
              <li>
                <a href="https://iatistandard.org/en/privacy-policy/">{{
                  props.translatedData['footer.footer.privacy']
                }}</a>
              </li>
              <li>
                <a href="https://iatistandard.org/en/data-removal/">{{
                  props.translatedData['footer.footer.data_removal']
                }}</a>
              </li>
              <li>
                <span
                  >©
                  {{
                    props.translatedData['footer.footer.copyright_iati']
                  }}</span
                >
              </li>
            </ul>
          </nav>

          <div class="iati-country-switcher">
            <label
              for="iati-country-switcher"
              class="iati-country-switcher__label"
              >{{
                props.translatedData['common.common.choose_your_language']
              }}</label
            >
            <select
              id="iati-country-switcher"
              v-model="isActive"
              class="iati-country-switcher__control cursor-pointer"
              disabled
              @change="onLanguageChange"
            >
              <option value="en">English</option>
              <option value="fr">French</option>
              <option value="es">Spanish</option>
            </select>
          </div>

          <div class="iati-footer__social">
            <a
              href="https://www.linkedin.com/company/international-aid-transparency-initiative/"
              aria-label="LinkedIn"
            >
              <i class="iati-icon iati-icon--linkedin"></i>
            </a>
            <a href="https://x.com/IATI_aid" aria-label="X">
              <i class="iati-icon iati-icon--x"></i>
            </a>
            <a
              href="https://www.youtube.com/channel/UCAVH1gcgJXElsj8ENC-bDQQ"
              aria-label="YouTube"
            >
              <i class="iati-icon iati-icon--youtube"></i>
            </a>
            <a href="https://www.facebook.com/IATIaid/" aria-label="Facebook">
              <i class="iati-icon iati-icon--facebook"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { defineProps, ref } from 'vue';
import axios from 'axios';
import LanguageService from 'Services/language';

const props = defineProps({
  superAdmin: { type: Boolean, required: false, default: false },
  translatedData: { type: Object, required: true },
  currentLanguage: {
    type: String,
    required: true,
  },
});

const isActive = ref(props.currentLanguage);

const onLanguageChange = (event: Event) => {
  const selectedLang = (event.target as HTMLSelectElement).value;
  LanguageService.changeLanguage(selectedLang)
    .then(() => {
      window.location.reload();
    })
    .catch((error) => {
      console.error('Language change failed:', error);
    });
};

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
