<script>
import Grid from '../../Components/Grid.vue'
import axios from "axios";

export default {
  name: "SidebarGrid",
  components: {
    Grid
  },
  props: {
    url: String,
    page: Number,
    limit: Number,
  },
  data() {
    return {
      searchQuery: '',
      /*gridColumns: ['name', 'power'],
      gridData: [
        { name: 'Chuck Norris', power: Infinity },
        { name: 'Bruce Lee', power: 9000 },
        { name: 'Jackie Chan', power: 7000 },
        { name: 'Jet Li', power: 8000 }
      ]*/
      gridColumns: [],
      gridData: [],
      sortOrders: [],
    }
  },
  mounted() {
    this.loadData();
  },
  methods: {
    async loadData() {
      axios.post(this.url, {
        page: this.page,
        limit: this.limit
      }).then((response) => {
        this.gridColumns = response.data.column;
        this.gridData = response.data.data;
        this.sortOrders = this.gridColumns.reduce((o, key) => ((o[key] = 1), o), {});
      }).catch((error) => {
        console.log(error);
      });
    }
  }
}
</script>

<template>
  <form id="search">
    Search <input name="query" v-model="searchQuery">
  </form>
  <Grid
      :data="gridData"
      :columns="gridColumns"
      :filter-key="searchQuery"
      :sortOrders="sortOrders">
  </Grid>
</template>

<style scoped>

</style>