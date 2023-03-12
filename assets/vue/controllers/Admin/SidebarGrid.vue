<template>
  <form id="search">
    Search <input name="query" v-model="searchQuery">
  </form>
  <Grid
      :data="gridData"
      :columns="gridColumns"
      :filter-key="searchQuery">
  </Grid>
</template>

<script>
import Grid from '../../Components/Grid.vue'
export default {
  name: "SidebarGrid",
  components: {
    Grid
  },
  data: () => ({
    searchQuery: '',
    gridColumns: ['name', 'power'],
    gridData: [
      { name: 'Chuck Norris', power: Infinity },
      { name: 'Bruce Lee', power: 9000 },
      { name: 'Jackie Chan', power: 7000 },
      { name: 'Jet Li', power: 8000 }
    ]
  })
}
</script>

<style scoped>

</style>