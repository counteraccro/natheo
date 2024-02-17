<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Modale Bootstrap 5.3
 */

import {watch} from "vue";
import {Modal} from "bootstrap";

export default {
  name: 'Modal',
  props: {
    id: String,
    show: Boolean,
    optionShowCloseBtn: {
      type: Boolean,
      default: true
    },
    optionModalSize: {
      type: String,
      default: ''
    }
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
          if (newValue !== oldValue) {
            if (newValue) {
              this.modal.show();
            }
            else {
              this.modal.hide();
            }
          }
        }, {immediate: true, deep: true}
    );
  },
  methods: {
    close() {
      this.modal.hide();
      this.$emit("close-modal", this.id);
    }
  }
}
</script>

<template>
  <div class="modal fade" :id="this.id" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" :class="this.optionModalSize">
      <div class="modal-content">
        <div class="modal-header bg-secondary">
          <h1 class="modal-title fs-5 text-white">
            <slot name="title"></slot>
          </h1>
          <button type="button" class="btn-close" @click="this.close()"></button>
        </div>
        <div class="modal-body">
          <slot name="body"></slot>
        </div>
        <div class="modal-footer">
          <slot name="footer"></slot>
          <button v-if="this.optionShowCloseBtn" type="button" class="btn btn-secondary" @click="this.close()">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>