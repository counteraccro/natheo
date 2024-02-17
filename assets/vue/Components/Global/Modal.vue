<script>
import {watch} from "vue";
import {Modal} from "bootstrap";

export default {
  name: 'Modal',
  props: {
    id: String,
    show: Boolean
  },
  emits: ['close-modal'],
  data() {
    return {
      modal: {},
    }
  },
  mounted() {
    this.modal = new Modal(document.getElementById(this.id), {});

    watch(() => this.show, (newValue, oldValue) => {

      console.log(newValue)

      if (newValue !== oldValue) {
        if (newValue) {
          this.modal.show();
        } else {
          //this.modal.hide();
        }
      }
    }, {immediate: true, deep: true});
  },
  watch() {

  },
  computed: {},
  methods: {
    close()
    {
      this.modal.hide();
      this.$emit("close-modal", this.id);
    }
  }
}
</script>

<template>
  <div class="modal fade" :id="this.id" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
          <button type="button" class="btn-close" @click="this.close()"></button>
        </div>
        <div class="modal-body">
          <slot name="body"></slot>
        </div>
        <div class="modal-footer">
          <slot name="footer"></slot>
          <button type="button" class="btn btn-secondary" @click="this.close()" >Close</button>
          <button type="button" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </div>
  </div>
</template>