<template>
  <div class="wrapper smooth relative bg-bluecoral sm:h-72">
    <div class="mx-3 sm:mx-10 xl:mx-24 xl:px-1">
      <header class="header relative z-10 grid">
        <nav
          class="relative z-10 mt-6 flex items-center justify-between rounded-md bg-white px-3 pt-5 text-xs sm:mt-12 sm:px-10"
        >
          <a class="pb-5" href="/">
            <svg-vue class="w-52 text-6xl sm:w-60" icon="header-logo" />
          </a>
          <!-- remove width later -->
          <div class="languages hidden pt-11 xl:block">
            <div class="flex">
              <ul class="flex items-center justify-center">
                <li class="nav__links">
                  <a
                    href="https://docs.publisher.iatistandard.org/en/latest/"
                    >{{ translatedData['common.common.help_docs'] }}</a
                  >
                </li>
              </ul>
              <!--              <span class="mr-2 pt-5 pb-5 uppercase xl:pt-0"-->
              <!--                >{{ translatedData['elements.label.language'] }}:</span-->
              <!--              >-->
              <!--              <ul class="flex items-center justify-center">-->
              <!--                <li class="nav__links">-->
              <!--                  <button-->
              <!--                    :class="{-->
              <!--                      'nav__active links__active': currentLanguage === 'en',-->
              <!--                    }"-->
              <!--                    @click="changeLanguage('en')"-->
              <!--                  >-->
              <!--                    EN-->
              <!--                  </button>-->
              <!--                </li>-->
              <!--                <li class="nav__links">-->
              <!--                  <button-->
              <!--                    :class="{-->
              <!--                      'nav__active links__active': currentLanguage === 'fr',-->
              <!--                    }"-->
              <!--                    @click="changeLanguage('fr')"-->
              <!--                  >-->
              <!--                    FR-->
              <!--                  </button>-->
              <!--                </li>-->
              <!--                <li class="nav__links">-->
              <!--                  <button-->
              <!--                    :class="{-->
              <!--                      'nav__active links__active': currentLanguage === 'es',-->
              <!--                    }"-->
              <!--                    @click="changeLanguage('es')"-->
              <!--                  >-->
              <!--                    ES-->
              <!--                  </button>-->
              <!--                </li>-->
              <!--              </ul>-->
            </div>
          </div>
          <div id="menu-overlay"></div>
          <div
            id="hamburger"
            class="hamburger home-burger-menu mb-4 scale-90 xl:hidden"
          >
            <span class="bg-bluecoral" />
            <span class="bg-bluecoral" />
            <span class="bg-bluecoral" />
          </div>
        </nav>
        <div
          class="header__title mt-6 flex flex-wrap items-center justify-between gap-2 border-l-4 border-l-turquoise px-4 py-2 sm:px-6 sm:py-5"
        >
          <h1
            class="text-xl font-bold text-white sm:text-4xl sm:text-heading-2"
          >
            {{ title }}
          </h1>
          <a
            v-if="auth === '1'"
            :href="superAdmin ? '/list-organisations' : '/activities'"
            class="button secondary-btn"
          >
            {{
              superAdmin ? 'Go to Organisation List' : 'Go to Your Activities'
            }}
            <svg-vue class="text-2xl" icon="right-arrow" />
          </a>
        </div>
      </header>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, onUnmounted } from 'vue';
import LanguageService from 'Services/language';

export default defineComponent({
  components: {},
  props: {
    title: { type: String, required: true },
    auth: { type: String, required: true },
    superAdmin: { type: Boolean, required: false, default: false },
    translatedData: { type: Object, required: true },
    currentLanguage: { type: String, required: true },
  },
  setup() {
    onUnmounted(() => {
      document.body.classList.remove('no-nav');
    });

    const changeLanguage = (lang: string) => {
      LanguageService.changeLanguage(lang)
        .then(() => {
          window.location.reload();
        })
        .catch((error) => {
          console.log(error);
        });
    };

    return {
      changeLanguage,
    };
  },
});
</script>
