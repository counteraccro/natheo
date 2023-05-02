<script>
import {Modal} from "bootstrap";
import axios from "axios";

export default {
    name: "MyAccountDangerZone",
    props: {
        url_disabled: String,
        url_delete: String,
        translate: Object,
        can_delete: String,
        can_replace: String
    },
    data() {
        return {
            modalDisabled: '',
            confirm_disabled: false,
            msg_return: this.translate.loading,
            url_reload: '',
            modalDelete: ''
        }
    },
    mounted() {
        this.modalDisabled = new Modal(document.getElementById("modal-alert-disabled"), {});
        this.modalDelete = new Modal(document.getElementById("modal-alert-delete"), {});
    },

    methods: {
        /**
         * Action pour le delete
         */
        delete() {
            this.modalDelete.show();
        },

        /**
         * Action pour désactiver le compte
         */
        disabled(confirm) {
            this.confirm_disabled = confirm;
            if (!confirm) {
                this.modalDisabled.show();
            }
            else {
                axios.post(this.url_disabled, {
                }).then((response) => {
                    this.msg_return = response.data.msg;
                    this.url_reload = response.data.redirect;
                }).catch((error) => {
                    console.log(error);
                }).finally(() => {
                    setTimeout(() => {
                        document.location.href = this.url_reload
                    }, 3000)
                });
            }
        }
    }
}
</script>

<template>

  <!-- Modal disabled -->
    <div class="modal fade" id="modal-alert-disabled" tabindex="-1" aria-labelledby="modal-alert" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h1 class="modal-title fs-5 text-white">{{ this.translate.disabled_title }}</h1>
                    <button v-if="!this.confirm_disabled" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="!this.confirm_disabled">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ this.translate.disabled_content_1 }} <br/>
                        <p class="mt-2"><i>{{ this.translate.disabled_content_2 }}</i></p>
                    </div>
                    <div v-else>
                        <p v-html="this.msg_return"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button v-if="!this.confirm_disabled" type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ this.translate.disabled_btn_cancel }}</button>
                    <button type="button" class="btn btn-danger" @click="this.disabled(true)">{{ this.translate.disabled_btn_confirm }}</button>
                </div>
            </div>
        </div>
    </div>

  <!-- Modal delete -->
    <div class="modal fade" id="modal-alert-delete" tabindex="-1" aria-labelledby="modal-alert" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-secondary">
                    <h1 class="modal-title fs-5 text-white">{{ this.translate.disabled_title }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ this.translate.disabled_content }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-danger me-3" @click="this.disabled(false)">{{ this.translate.btn_disabled }}</button>
    <button v-if="this.can_delete === '1' && this.can_replace === '0'" class="btn btn-danger" @click="this.delete(false)">{{ this.translate.btn_delete }}</button>
    <button v-if="this.can_delete === '1' && this.can_replace === '1'"  class="btn btn-danger" @click="this.delete(false)">{{ this.translate.btn_replace }}</button>
</template>

<style scoped>

</style>