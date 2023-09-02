<script>

export default {
  props: {
    dataMove: Object,
    translate: Object
  },
  emits: ['close-modale'],
  data() {
    return {}
  },
  computed: {},
  methods: {}

}
</script>

<template>
  <div class="modal-header bg-secondary">
    <h1 class="modal-title fs-5 text-white">
      <i class="bi bi-upload"></i> {{ this.translate.title }}
    </h1>
    <button type="button" class="btn-close" @click="$emit('close-modale')"></button>
  </div>
  <div class="modal-body">
    body
  </div>
  <div class="modal-footer">
    footer
  </div>
</template>