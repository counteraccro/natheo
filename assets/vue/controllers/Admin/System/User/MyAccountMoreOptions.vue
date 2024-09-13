<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Permet de générer les events pour more options
 */

import axios from "axios";

export default {
  name: "MyAccountMoreOptions",
  props: {
    translate: Object,
    datas: Object,
    url: String,
  },
  data() {
    return {
      loading: false,
      btnHelpBlock: this.datas.help_first_connexion,
      msg: null
    }
  },
  mounted() {

  },

  methods: {
    /**
     * Action pour le delete
     */
    update(key, value) {

      this.loading = true;
      axios.post(this.url, {
        key: key,
        value: value
      }).then((response) => {

        console.log(response.data);

        if(response.data.success === true) {
          this.btnHelpBlock = !(value !== 1);
        }
        this.msg = response.data.msg;
      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false;
      });
    },

  }
}
</script>

<template>

  <div v-if="this.loading" class="mb-3">
    <div class="spinner-border text-secondary spinner-border-sm" role="status">
    </div>
    {{ this.translate.loading }}
  </div>

  <button v-if="!this.btnHelpBlock" class="btn btn-sm btn-secondary" :disabled="this.loading" @click="this.update(this.datas.user_data_key_first_connexion, 1)">
     <i class="bi bi-info-circle"></i> {{ this.translate.btn_show_help }}
  </button>
  <button v-else class="btn btn-sm btn-secondary" :disabled="this.loading" @click="this.update(this.datas.user_data_key_first_connexion, 0)">
    <i class="bi bi-eye-slash"></i> {{ this.translate.btn_hide_help }}
  </button>
</template>

<style scoped>

</style>