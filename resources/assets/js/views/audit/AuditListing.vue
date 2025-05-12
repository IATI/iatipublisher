<template>
  <div class="px-6 py-4 md:px-10">
    <Loader v-if="isLoaderVisible" />
    <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
      <div class="flex items-center">
        <a class="whitespace-nowrap font-bold text-n-40" href="/audits">
          Audits
        </a>
      </div>
    </nav>

    <PageTitle title="Audits" back-link="" breadcrumb-data="">
      <div
        class="inline-flex flex-col items-end justify-end gap-2 md:flex-row"
      ></div>
    </PageTitle>

    <!--    {{auditData}}-->
  </div>
</template>
<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import Loader from '../../components/Loader.vue';
import PageTitle from 'Components/sections/PageTitle.vue';

const auditData = reactive({});
const isEmpty = ref(false);

const isLoaderVisible = ref(false);

onMounted(async () => {
  fetchAuditList(1);
});

function fetchAuditList(active_page: number, filtered = false) {
  let route = `/audit/page/${filtered ? '1' : active_page}`;

  let params = new URLSearchParams();

  axios.get(route, { params: params }).then((res) => {
    const response = res.data;
    Object.assign(auditData, response.data);
    isEmpty.value = response.data ? false : true;
  });
}
</script>
