<template>
  <div
    class="right join-now m-auto flex basis-2/4 items-center rounded-l-lg rounded-r-lg bg-white px-5 py-5 sm:px-7 sm:py-10 md:my-0 md:rounded-l-none lg:py-28 xl:px-14"
  >
    <div class="right__container flex flex-col">
      <h2 class="mb-2 hidden sm:block">Join Now</h2>
      <span class="mb-8 text-n-40 xl:pr-6">
        To begin, your organisation needs to be registered as an IATI publisher.
        Select an option and we’ll guide you through this process.
      </span>
      <a href="/iati/register" class="right__content mb-6">
        <div class="right__icon">
          <svg-vue class="text-6xl" icon="default-1" />
        </div>
        <div class="details mx-4 xl:px-1">
          <span class="text-sm font-bold text-bluecoral"
            >My organisation is new to IATI</span
          >
          <p class="text-xs leading-5 text-n-40">
            Use this option if your organisation <u>has not</u> registered an
            account with IATI on the
            <a href="https://www.iatiregistry.org/" target="_blank"
              >IATI Registry</a
            >
          </p>
        </div>
        <div>
          <svg-vue class="right__arrow text-2xl" icon="right-arrow" />
        </div>
      </a>
      <a href="/register" class="right__content mb-4">
        <div class="right__icon">
          <svg-vue class="text-6xl" icon="default-2" />
        </div>
        <div class="details mx-4 xl:px-1">
          <span class="text-sm font-bold text-bluecoral"
            >My organisation has registered with IATI</span
          >
          <p class="text-xs leading-5 text-n-40">
            Use this option if your organisation has already registered on the
            <a href="https://www.iatiregistry.org/" target="_blank"
              >IATI Registry</a
            >, but is new to using IATI Publisher
          </p>
        </div>
        <div>
          <svg-vue class="right__arrow text-2xl" icon="right-arrow" />
        </div>
      </a>
      <span class="text-sm text-n-40">
        If you are a new user of an organisation already using IATI Publisher,
        an admin user from your organisation can create an account for you. If
        you need help, or are not sure which option to select, please
        <a
          class="border-b-2 border-b-transparent font-bold text-bluecoral hover:border-b-2 hover:border-b-turquoise hover:text-bluecoral"
          href="mailto:support@iatistandard.org"
          target="_blank"
          >Contact Support.</a
        ></span
      >
    </div>
  </div>
</template>
<script setup lang="ts"></script>
