<script>
import {Modal} from "bootstrap";

export default {
    name: "MyAccountDangerZone",
    props: {
        url_disabled: String,
        url_delete: String,
        translate: Object
    },
    data() {
        return {
            modal: '',
        }
    },
    mounted() {
        this.modal = new Modal(document.getElementById("modal-alert"), {});
    },

    methods: {
        /**
         * Action pour le delete
         */
        delete() {
            this.modal.show();
        }
    }
}
</script>

<template>
    <div class="modal fade" id="modal-alert" tabindex="-1" aria-labelledby="modal-alert" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-danger" @click="this.delete">{{ this.translate.btn_disabled }}</button>
</template>

<style scoped>

</style>