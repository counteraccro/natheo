<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import type {
  InstallationStepOneUrls,
  InstallationStepOneTranslate,
  InstallationStepOneLocales,
  InstallationStepOneDatas,
  CheckDatabaseResponse,
} from '@/ts/Installation/InstallationStepOne';
import axios, { AxiosError } from 'axios';

export default defineComponent({
  name: 'InstallationStepOne',
  props: {
    urls: {
      type: Object as PropType<InstallationStepOneUrls>,
      required: true,
    },
    translate: {
      type: Object as PropType<InstallationStepOneTranslate>,
      required: true,
    },
    locales: {
      type: Object as PropType<InstallationStepOneLocales>,
      required: true,
    },
    datas: {
      type: Object as PropType<InstallationStepOneDatas>,
      required: true,
    },
  },
  data() {
    return {
      loading: false as boolean,
      isDataBaseConnected: null as boolean | null,
    };
  },
  mounted(): any {
    this.checkDataBaseConnexion();
  },
  methods: {
    /**
     * Test si la connexion est valide ou non
     */
    checkDataBaseConnexion(): void {
      this.loading = true;

      axios
        .get<CheckDatabaseResponse>(this.urls.check_database)
        .then((response) => {
          this.isDataBaseConnected = response.data.connexion;
        })
        .catch((error: AxiosError) => {
          console.error(error);
          this.isDataBaseConnected = false;
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
});
</script>

<template>
  <div id="installation-step-one"></div>
</template>
