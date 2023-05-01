<script>
import {Modal} from "bootstrap";

export default {
    name: "MyAccountDanger",
    props: {
        url_disabled: String,
        url_delete: String,
        translate: Array
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
    <div class="modal fade" id="modal-alert" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
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

    <button class="btn btn-danger" @click="this.delete">aaa</button>
</template>

<style scoped>

</style>