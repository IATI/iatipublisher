/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
import { createApp } from 'vue';
import VueSmoothScroll from 'vue3-smooth-scroll';
import SvgVue from 'svg-vue3';
import WebHeader from './views/web/partials/WebHeader.vue';
import WebFooter from './views/web/partials/WebFooter.vue';
import AdminFooter from './views/web/partials/AdminFooter.vue';
import WelcomeSignIn from './views/web/WelcomePage.vue';
import AboutPage from './views/web/AboutPage.vue';
import SupportPage from './views/web/SupportPage.vue';
import IatiStandard from './views/web/IatiStandard.vue';
import PublishingChecklist from './views/web/PublishingChecklist.vue';

/**
 * Vue components for Activities Listing
 */
import ResultDetail from './views/activity/results/ResultDetail.vue';
import ResultList from './views/activity/results/ResultList.vue';
import IndicatorDetail from './views/activity/indicators/IndicatorDetail.vue';
import IndicatorList from './views/activity/indicators/IndicatorList.vue';
import PeriodsDetail from './views/activity/periods/PeriodsDetail.vue';
import PeriodsList from './views/activity/periods/PeriodsList.vue';
import TransactionList from './views/activity/transactions/TransactionList.vue';
import TransactionDetail from './views/activity/transactions/TransactionDetail.vue';
import ElementsNote from './views/activity/partials/ElementsNote.vue';
import Activity from './views/activity/ActivityIndex.vue';
import LoggedInHeader from './components/AdminHeader.vue';
import ActivitiesDetail from './views/activity/ActivityDetail.vue';
import SidebarHelpBlock from './views/activity/partials/SidebarHelpBlock.vue';
import ActivityUpload from './views/import/ActivityUpload.vue';
import ActivityXlsUpload from './views/import/ActivityXlsUpload.vue';
import DashboardPage from './views/dashboard/DashboardPage.vue';
import ImportList from './views/import/ImportList.vue';
import XlsList from './views/import/XlsList.vue';

/**
 * Setting page
 */
import SettingPage from './views/setting/SettingPage.vue';

//Activity Default Values
import ActivityDefaultValues from './views/activity/ActivityDefaultValue.vue';

/**
 * vue component for password reset
 */
import ResetPage from './views/reset/ResetPage.vue';
import PasswordRecovery from './views/reset/PasswordRecovery.vue';
import ResetPassword from './views/reset/ResetPassword.vue';

/**
 * Organisation data
 */
import OrganisationData from './views/organisation/OrganisationData.vue';

/**
 * Organisation List / Proxy
 */
import OrganisationList from './views/superadmin/OrganisationList.vue';
import AdminBar from './views/superadmin/components/AdminBar.vue';

/**
 * User Module components
 */
import UserProfile from './views/user/UserProfile.vue';
import UserListing from './views/user/UserListing.vue';

/**
 * Audit component
 */
import AuditListing from './views/audit/AuditListing.vue';

/**
 * Additional Components
 */
import HoverText from './components/HoverText.vue';
import PageTitle from './components/sections/PageTitle.vue';
import LoadingState from './components/Loader.vue';
import SystemVersion from './views/superadmin/SystemVersion.vue';
import VueApexCharts from 'vue3-apexcharts';

const app = createApp({});
app.use(VueApexCharts);

/**
 * Global Components
 */

app
  .component('WebHeader', WebHeader) //y
  .component('WebFooter', WebFooter) //y
  .component('AdminFooter', AdminFooter) //y

  .component('WelcomeSignin', WelcomeSignIn);

/**
 * registering web portal pages
 */

app
  .component('AboutPage', AboutPage)
  .component('SupportPage', SupportPage)
  .component('IatiStandard', IatiStandard)
  .component('PublishingChecklist', PublishingChecklist);

/**
 * Registering vue component for activity listing
 */
app
  .component('ActivityTemplate', Activity)
  .component('LoggedinHeader', LoggedInHeader) //y
  .component('ActivitiesDetail', ActivitiesDetail)
  .component('ElementsNote', ElementsNote) //y
  .component('ResultDetail', ResultDetail)
  .component('ResultList', ResultList)
  .component('IndicatorDetail', IndicatorDetail)
  .component('IndicatorList', IndicatorList)
  .component('PeriodsDetail', PeriodsDetail)
  .component('PeriodsList', PeriodsList)
  .component('TransactionList', TransactionList)
  .component('TransactionDetail', TransactionDetail)
  .component('SidebarHelpBlock', SidebarHelpBlock); //y

/*
 * Import page
 */
app
  .component('ActivityUpload', ActivityUpload)
  .component('ActivityXlsUpload', ActivityXlsUpload)
  .component('ImportList', ImportList)
  .component('XlsList', XlsList);

// dashboard page
app.component('DashboardPage', DashboardPage);

/*
 * Setting page
 */
app.component('SettingPage', SettingPage);

app.component('SystemVersion', SystemVersion);

app.component('ActivityDefaultValues', ActivityDefaultValues);
/*
Registering vue component for password reset
*/
app
  .component('ResetPage', ResetPage)
  .component('PasswordRecovery', PasswordRecovery)
  .component('ResetPassword', ResetPassword);

/**
 * Registering user module related vue components
 */
app.component('UserProfile', UserProfile).component('UserListing', UserListing);

/**
 * Registering Additional Components
 */
app.component('HoverText', HoverText);
app.component('PageTitle', PageTitle);
app.component('LoadingState', LoadingState);

/**
 * Organisation data
 */
app.component('OrganisationData', OrganisationData);

/**
 * Proxy
 */
app.component('OrganisationList', OrganisationList);
app.component('AdminBar', AdminBar);

/**
 * Audit
 */
app.component('AuditListing', AuditListing);

/**
 * Extension to inline SVG files with Vue.js and optimize them automatically with SVGO
 */
app.use(SvgVue);

app.use(VueSmoothScroll);

// detect scroll up or down
let lastScrollTop = 0,
  affixType = 'sticky-none';

const stickySidebar = (
  el: {
    firstChild: HTMLElement;
    offsetWidth: number;
    getBoundingClientRect: () => {
      (): object;
      new (): object;
      left: number;
      top: number;
      bottom: number;
    };
    style: { cssText: string };
  },
  parentWrapper: string
) => {
  //sticky element/child data
  const stickyElement = el.firstChild,
    elHeight = stickyElement.offsetHeight,
    stickyCurrentTop = stickyElement.getBoundingClientRect().top,
    stickyCurrentBottom = stickyElement.getBoundingClientRect().bottom;

  //sticky element's parent/wrapper data
  const elWidth = el.offsetWidth,
    elScrollLeft = el.getBoundingClientRect().left,
    elScrollTop = el.getBoundingClientRect().top,
    elScrollBottom = el.getBoundingClientRect().bottom,
    viewportHeight = window.innerHeight;

  // window/document data
  const currentWindowsScrollPosition = window.pageYOffset,
    targetScrollPosition =
      elScrollBottom + currentWindowsScrollPosition - viewportHeight;

  // parent wrapper / sticky boundary
  const elParent = stickyElement.closest(parentWrapper),
    elParentBottom = elParent?.getBoundingClientRect().bottom;

  // scroll behaviour
  const isScrollDown =
    currentWindowsScrollPosition > lastScrollTop ? true : false;

  const isScrollUp = !isScrollDown;
  lastScrollTop =
    currentWindowsScrollPosition <= 0 ? 0 : currentWindowsScrollPosition;

  function scrollDownStickyBottom() {
    stickyElement.style.cssText = `position : absolute;  width:280px; bottom: 16px`;
    affixType = 'sticky-bound';
  }

  function scrollDownStickyTop() {
    stickyElement.style.cssText = `position: relative; transform: translate3d(0, ${
      stickyCurrentTop - elScrollTop
    }px, 0);`;
    affixType = 'sticky-translate';
  }

  function scrollDownStickyTranslate() {
    {
      (window.scrollY,
      window.scrollY +
        document.documentElement.clientHeight +
        476 -
        document.documentElement.scrollHeight >
        0)
        ? window.scrollY +
          document.documentElement.clientHeight +
          476 -
          document.documentElement.scrollHeight
        : 16;
    }
    stickyElement.style.cssText = `position: fixed; top: auto; left: ${elScrollLeft}; bottom:${
      (window.scrollY,
      window.scrollY +
        document.documentElement.clientHeight +
        476 -
        document.documentElement.scrollHeight >
        16)
        ? window.scrollY +
          document.documentElement.clientHeight +
          476 -
          document.documentElement.scrollHeight
        : 16
    }px; width: ${elWidth}px`;

    affixType = 'sticky-bottom';
  }

  function scrollDownFixedTop() {
    el.style.cssText = `position: fixed; top:0px`;
    affixType = 'sticky-translate';
  }

  function scrollDownStickyNone() {
    if (targetScrollPosition <= currentWindowsScrollPosition) {
      if (viewportHeight + window.scrollY + 450 >= document.body.offsetHeight) {
        el.style.cssText = `position: sticky; top:0px`;
      } else {
        stickyElement.style.cssText = `position: fixed; top: auto; left: ${elScrollLeft}; bottom: 0; width: ${elWidth}px`;
      }
      affixType = 'sticky-bottom';
    }
  }

  function scrollDownStickyBound() {
    if (elParentBottom && elParentBottom < stickyCurrentBottom) {
      stickyElement.style.cssText = `position : absolute;  width:280px; bottom: 16px`;
      affixType = 'sticky-bound';
    }
  }

  function handleScrollDown() {
    switch (affixType) {
      case 'sticky-top':
        scrollDownStickyTop();
        break;

      case 'sticky-bottom':
        if (elParentBottom && elParentBottom < stickyCurrentBottom) {
          scrollDownStickyBottom();
        }

        break;

      case 'sticky-translate':
        if (stickyCurrentBottom <= viewportHeight) {
          scrollDownStickyTranslate();
        }
        break;
      case 'fixed-top':
        scrollDownFixedTop();
        break;

      case 'sticky-none':
        scrollDownStickyNone();
        break;

      case 'sticky-bound':
        scrollDownStickyBound();
        break;
    }
  }

  function scrollUpStickyTop() {
    if (elScrollTop >= 0) {
      stickyElement.style.cssText = `position: relative;  `;
      affixType = 'sticky-none';
    } else {
      stickyElement.style.cssText = `position: fixed; top: auto; bottom:${
        (window.scrollY,
        window.scrollY +
          document.documentElement.clientHeight +
          476 -
          document.documentElement.scrollHeight >
          16)
          ? window.scrollY +
            document.documentElement.clientHeight +
            476 -
            document.documentElement.scrollHeight
          : 16
      }px; left: ${elScrollLeft}; width: ${elWidth}px `;
    }
  }

  function scrollUpStickyBottom() {
    stickyElement.style.cssText = `position: fixed; top: 0px; left: ${elScrollLeft}; width: ${elWidth}px `;
    affixType = 'sticky-bound';
  }

  function scrollUpFixedTop() {
    el.style.cssText = `position: fixed; top:0px`;
    affixType = 'sticky-translate';
  }

  function scrollUpStickyTranslate() {
    if (stickyCurrentTop >= 0) {
      stickyElement.style.cssText = `position: fixed; top: 0px; left: ${elScrollLeft}; width: ${elWidth}px`;
      affixType = 'sticky-top';
    }
  }

  function scrollUpStickyBound() {
    if (stickyCurrentTop >= 0 && currentWindowsScrollPosition != 0) {
      stickyElement.style.cssText = `position:fixed; top: 0; left: ${elScrollLeft}; width: ${elWidth}px`;
      affixType = 'sticky-top';
    }
    if (stickyCurrentTop >= 0 && currentWindowsScrollPosition == 0) {
      stickyElement.style.cssText = ` top: 0; left: ${elScrollLeft}; width: ${elWidth}px`;
      affixType = 'sticky-top';
    }
  }

  function handleScrollUp() {
    switch (affixType) {
      case 'sticky-top':
        scrollUpStickyTop();
        break;

      case 'sticky-bottom':
        scrollUpStickyBottom();
        break;
      case 'fixed-top':
        scrollUpFixedTop();
        break;

      case 'sticky-translate':
        scrollUpStickyTranslate();
        break;

      case 'sticky-none':
        //nothing to do here
        break;

      case 'sticky-bound':
        scrollUpStickyBound();
        break;
    }
  }

  if (elHeight < viewportHeight) {
    el.style.cssText = `position: sticky; top:0px`;
    stickyElement.style.cssText = ``;
  } else {
    el.style.cssText = `height: ${elHeight}px;`;
    if (isScrollDown && currentWindowsScrollPosition != 0) {
      handleScrollDown();
    } else if (isScrollUp && currentWindowsScrollPosition != 0) {
      handleScrollUp();
    } else {
      el.style.cssText = `position: sticky; top:0px`;
      stickyElement.style.cssText = ``;
    }
  }
};
window.onload = () => {
  //check constantly in a inter for when support button enters the dom

  const checkSupportButton = setInterval(() => {
    const supportButton: HTMLElement = document.querySelector(
      '#launcher'
    ) as HTMLElement;

    if (supportButton !== null) {
      supportButton.style.display = 'block';
      supportButton.style.border = '2px solid rgb(21, 83, 102)';
      clearInterval(checkSupportButton);
    }
  }, 10);
};

// custom directive
app.directive('sticky-component', {
  mounted(el, binding) {
    let { boundary } = binding.value || {};
    boundary = boundary || 'body';
    window.addEventListener('scroll', () => stickySidebar(el, boundary));
  },
  unmounted(el, binding) {
    const parent = binding.value.boundary;
    window.removeEventListener('scroll', () => stickySidebar(el, parent));
  },
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
app.mount('#app');
