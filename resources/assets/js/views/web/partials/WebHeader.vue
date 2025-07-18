<template>
  <div class="iati-mobile-nav js-iati-mobile-nav">
    <div class="iati-mobile-nav__overlay js-iati-mobile-overlay"></div>
    <nav class="iati-mobile-nav__menu">
      <div class="iati-mobile-nav__header">
        <h2 class="iati-mobile-nav__label text-white">
          {{ props.translatedData['common.common.menu'] }}
        </h2>
        <button
          class="iati-menu-toggle iati-menu-toggle--close js-iati-menu-toggle-close"
        >
          <span>{{ props.translatedData['common.common.close'] }}</span>
        </button>
      </div>
      <ul class="">
        <li class="iati-mobile-nav__item">
          <a href="/" class="iati-mobile-nav__link">{{ title }}</a>
        </li>
      </ul>
      <ul class="">
        <li class="iati-mobile-nav__item">
          <a
            href="https://iatistandard.org/en/about/"
            class="iati-mobile-nav__link"
            >{{ props.translatedData['common.common.about_iati'] }}</a
          >
        </li>
        <li class="iati-mobile-nav__item">
          <a
            href="https://iatistandard.org/en/using-data/"
            class="iati-mobile-nav__link"
            >{{ props.translatedData['common.common.use_data'] }}</a
          >
        </li>
        <li class="iati-mobile-nav__item">
          <a
            href="https://iatistandard.org/en/guidance/publishing-data/"
            class="iati-mobile-nav__link"
          >
            {{ props.translatedData['common.common.publish_data'] }}
          </a>
        </li>
        <li class="iati-mobile-nav__item">
          <a
            href="https://iatistandard.org/guidance/get-support/"
            class="iati-mobile-nav__link"
          >
            {{ props.translatedData['common.common.contact'] }}
          </a>
        </li>
        <li class="iati-mobile-nav__item">
          <a href="#" class="iati-mobile-nav__link">{{
            translatedData['common.common.help_docs']
          }}</a>
        </li>
      </ul>
    </nav>
  </div>

  <header class="iati-header">
    <div class="iati-header__section iati-header__section--first">
      <div class="iati-header__container">
        <a href="https://iatistandard.org/" aria-label="Go to IATI homepage">
          <img
            class="iati-header__logo"
            alt=""
            src="https://iati.github.io/design-system/assets/logo-colour-Bag5CeA4.svg"
          />
        </a>

        <nav class="iati-header__general-nav">
          <ul class="iati-piped-list">
            <li>
              <a href="https://iatistandard.org/en/about/">{{
                props.translatedData['common.common.about_iati']
              }}</a>
            </li>
            <li>
              <a href="https://iatistandard.org/en/using-data/">{{
                props.translatedData['common.common.use_data']
              }}</a>
            </li>
            <li>
              <a href="https://iatistandard.org/en/guidance/publishing-data/">
                {{ props.translatedData['common.common.publish_data'] }}
              </a>
            </li>
            <li>
              <a href="https://iatistandard.org/guidance/get-support/">
                {{ props.translatedData['common.common.contact'] }}
              </a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <div
      class="iati-header__section iati-header__section--last iati-brand-background"
    >
      <div class="iati-header__container iati-brand-background__content">
        <div class="iati-header__actions">
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
              @change="onLanguageChange"
            >
              <option value="en">English</option>
              <option value="fr">Français</option>
              <option value="es">Español</option>
            </select>
          </div>

          <button
            class="iati-button iati-button--light hide--mobile-nav"
            @click="redirectUser"
          >
            <span>{{ props.translatedData['common.common.help_docs'] }}</span>
            <i class="iati-icon iati-icon--info"></i>
          </button>

          <button
            class="iati-menu-toggle iati-menu-toggle--open js-iati-menu-toggle-open"
          >
            <span class="iati-menu-toggle__label">
              {{ props.translatedData['common.common.menu'] }}
            </span>
          </button>
        </div>

        <div class="iati-header-title">
          <p class="iati-header-title__eyebrow">
            {{ props.translatedData['common.common.iati_tools'] }}
          </p>
          <p class="iati-header-title__heading">IATI Publisher</p>
        </div>

        <div class="iati-header__nav">
          <nav>
            <ul class="iati-tool-nav">
              <li><a href="/" class="iati-tool-nav-link">IATI Publisher</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { defineProps, ref } from 'vue';
import LanguageService from 'Services/language';

const props = defineProps({
  translatedData: {
    type: Object,
    required: true,
  },
  currentLanguage: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  auth: {
    type: String,
    required: true,
  },
  superAdmin: {
    type: Boolean,
    required: false,
    default: false,
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

const redirectUser = () => {
  window.open(
    'https://docs.publisher.iatistandard.org/en/latest/',
    '_blank',
    'noopener,noreferrer'
  );
};
</script>
