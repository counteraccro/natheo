<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Composant card derniers commentaires
 */
import axios from "axios";

export default {
  name: "BlockLastComment",
  components: {},
  emit: [],
  props: {
    urls: Object,
    translate: Object,
  },
  emits: ['reload-grid'],
  data() {
    return {
      loading: false,
      errorMessage: null,
      result: null
    }
  },
  mounted() {
    this.load();
  },
  methods: {

    /**
     * Chargement du module
     */
    load() {
      this.loading = true;
      axios.get(this.urls.load_block_dashboard).then((response) => {

        if (response.data.success === false) {
          this.errorMessage = response.data.error;
        }
        else {
          this.result = response.data.body;
        }

      }).catch((error) => {
        console.error(error);
      }).finally(() => {
        this.loading = false
        this.reload();
      });
    },

    reload()
    {
      this.$emit("reload-grid");
    }
  }
}
</script>

<template>
  <div class="card">
    <h5 class="card-header"><i class="bi bi-chat-left-text"></i> {{ this.translate.title }} </h5>

    <div class="card-body" v-if="this.loading">
      <div class="spinner-border spinner-border-sm text-secondary" role="status">
        <span class="visually-hidden">{{ this.translate.loading }}</span>
      </div>
      {{ this.translate.loading }}
    </div>

    <div class="card-body" v-else>
      <div v-if="this.errorMessage !== null">
        <i class="bi bi-exclamation-circle"></i> {{ this.errorMessage }}
      </div>

      <div v-if="this.result !== null">

        <table class="table table-striped">
          <thead>
          <tr>
          <th>{{ this.translate.table_id }}</th>
          <th>{{ this.translate.table_author }}</th>
            <th>{{ this.translate.table_status }}</th>
            <th>{{ this.translate.table_date }}</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="comment in this.result">
            <td>{{ comment.id }}</td>
            <td>{{ comment.author }}</td>
            <td><span v-html="comment.status"></span></td>
            <td>{{ comment.date }}</td>
          </tr>
          </tbody>
        </table>

        <a :href="this.urls.url_comments" class="float-end"><i>{{ this.translate.link_comment }}</i></a>

      </div>

    </div>

  </div>
</template>