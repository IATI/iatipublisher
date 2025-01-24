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
          <div>
            <ul
              id="nav-list"
              class="nav__list flex pt-10 leading-5 xl:space-x-3"
            >
              <li class="nav__links active dropdown">
                <a href="/about">ABOUT</a>
                <NavDropdown
                  name="About"
                  text="IATI Publisher helps small and medium-sized organisations publish IATI data on development and humanitarian financing and activities"
                  btn-text="Learn more"
                  btn-link="/about"
                />
              </li>
              <li class="nav__links active dropdown">
                <a href="/publishing-checklist">PUBLISHING CHECKLIST</a>
                <NavDropdown
                  name="Publishing checklist"
                  text="New to IATI? Use our checklist to track each step required for your organisation to successfully publish IATI data"
                  btn-text="Read more"
                  btn-link="/publishing-checklist"
                />
              </li>
              <li class="nav__links active dropdown relative">
                <a href="/iati-standard">IATI STANDARD</a>
                <NavDropdown
                  name="IATI Standard"
                  text="The IATI Standard provides information and guidance on all the data fields that
                        your organisation can publish IATI data on"
                  btn-text="See all data fields"
                  btn-link="/iati-standard"
                />
              </li>
              <li class="nav__links active dropdown">
                <a href="/support">SUPPORT</a>
                <NavDropdown
                  name="Support"
                  text=" Any questions? Get help to publish your organisation’s data"
                  btn-text="Read more"
                  btn-link="/support"
                />
              </li>

              <!-- commented to temporarily hide language buttons -->

              <!-- <li class="absolute bottom-4 left-0 right-0  xl:hidden">
                <div class="flex items-center justify-center">
                  <span class="mr-2 pt-5 pb-5 uppercase text-white xl:pt-0"
                    >Language:</span
                  >
                  <ul class="languages flex items-center justify-center">
                    <li class="nav__links">
                      <a class="nav__active links__active" href="/">EN</a>
                    </li>
                    <li class="nav__links">
                      <a href="/">FR</a>
                    </li>
                    <li class="nav__links">
                      <a href="/">ES</a>
                    </li>
                  </ul>
                </div>
              </li> -->
            </ul>
          </div>
          <!-- remove width later -->
          <div class="languages hidden w-[170px] pt-11 xl:block">
            <!-- commented to temporarily hide language buttons -->

            <!-- <div class="flex">
              <span class="mr-2 pt-5 pb-5 uppercase xl:pt-0">Language:</span>
              <ul class="flex items-center justify-center">
                <li class="nav__links">
                  <a class="nav__active links__active" href="/">EN</a>
                </li>
                <li class="nav__links">
                  <a href="/">FR</a>
                </li>
                <li class="nav__links">
                  <a href="/">ES</a>
                </li>
              </ul>
            </div> -->
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
import { defineComponent, onMounted, onUnmounted } from 'vue';
import NavDropdown from '../../../components/NavDropdown.vue';

export default defineComponent({
  components: {
    NavDropdown,
  },
  props: {
    title: { type: String, required: true },
    auth: { type: String, required: true },
    superAdmin: { type: Boolean, required: false, default: false },
  },
  setup() {
    onMounted(() => {
      document.body.classList.add('no-nav');
    });
    onUnmounted(() => {
      document.body.classList.remove('no-nav');
    });
  },
});
</script>
