<script>

export default {
  props: {
    dataMove: Object,
    translate: Object
  },
  emits: ['close-modale', 'move'],
  data() {
    return {}
  },
  computed: {},
  methods: {
    move()
    {
      let element = document.getElementById('select-folder-to-move');
      let id = element.value;
      this.$emit('move', id, this.dataMove.id, this.dataMove.type)
    }
  }

}
</script>

<template>
  <div class="modal-header bg-secondary">
    <h1 class="modal-title fs-5 text-white">
      <i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.title }}
    </h1>
    <button type="button" class="btn-close" @click="$emit('close-modale')"></button>
  </div>
  <div class="modal-body">
    <p v-html="this.dataMove.label"></p>
    <select class="form-select no-control" id="select-folder-to-move">
      <option selected>{{ this.translate.input_label }}</option>
      <option v-for="(folder) in this.dataMove.listeFolder" :value="folder.id">{{ folder.name }}</option>
    </select>
    <div id="list-folder-help" class="form-text">{{ this.translate.input_info }}</div>
  </div>
  <div class="modal-footer">
    <div class="btn btn-secondary" @click="this.move()">{{ this.translate.btn_validate }}</div>
    <div class="btn btn-dark" @click="$emit('close-modale')">{{ this.translate.btn_close }}</div>
  </div>
</template>