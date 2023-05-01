<script>/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Changement du mot de passe pour un compte user
 * https://www.gekkode.com/developpement/expression-reguliere-pour-valider-un-mot-de-passe/
 */

import axios from "axios";

export default {
    name: "ChangePassword",
    props: {
        url_change_password: String,
        translate: Array
    },
    data() {
        return {
            password: '',
            classPassword: '',
            passwordConfirm: '',
            classPasswordConfirm: '',
            loading: false,
            btnSubmit: 'disabled',
            progressColor: 'bg-danger',
            msgUpdatePassword: '',
            nbCharacter: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            majuscule: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            minuscule: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            chiffre: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            special: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            rule: {
                'start': '^',
                'end': '$',
                'nbCharacter': '.{8,}',
                'majuscule': '(?=.*[A-Z])',
                'minuscule': '(?=.*?[a-z])',
                'chiffre': '(?=.*?[0-9])',
                'special': '(?=.*?[#?!@$%^&*-])'
            }
        }
    },
    computed: {
        progress() {
            let nb = this.nbCharacter.progress + this.majuscule.progress + this.minuscule.progress
                + this.chiffre.progress + this.special.progress;

            switch (nb) {
                case 20:
                case 40:
                    this.progressColor = 'bg-danger';
                    break;
                case 60:
                case 80:
                    this.progressColor = 'bg-warning';
                    break;
                case 100:
                    this.progressColor = 'bg-primary';
                    break;
                default:
                    this.progressColor = '';
            }

            return nb;
        }
    },
    methods: {
        validatePasswordFinal() {
            let reg = new RegExp(this.rule.start + this.rule.majuscule + this.rule.minuscule + this.rule.chiffre + this.rule.special + this.rule.nbCharacter)
            let test = reg.test(this.password);
            if (test && this.password === this.passwordConfirm) {
                this.btnSubmit = '';
                this.classPasswordConfirm = 'is-valid';
                this.classPassword = 'is-valid';
            } else {
                this.btnSubmit = 'disabled';
            }

            if (this.password !== this.passwordConfirm) {
                this.classPasswordConfirm = 'is-invalid';
            }

            if (!test) {
                this.classPassword = 'is-invalid';
            } else {
                this.classPassword = 'is-valid';
            }
        },

        checkPassword() {
            this.checkNbCharacter();
            this.checkMajuscule();
            this.checkMinuscule();
            this.checkChiffre();
            this.checkSpecial();

            this.validatePasswordFinal();
        },

        /**
         * Vérifie le nombre de caractères du mot de passe
         */
        checkNbCharacter() {
            let reg = new RegExp(this.rule.start + this.rule.nbCharacter + this.rule.end);
            let test = reg.test(this.password);
            this.updateRender(test, this.nbCharacter);
        },

        /**
         * Vérifie la présence d'au moins 1 majuscule
         */
        checkMajuscule() {
            let reg = new RegExp(this.rule.majuscule);
            let test = reg.test(this.password);
            this.updateRender(test, this.majuscule);
        },

        /**
         * Vérifie la présence d'au moins 1 minuscule
         */
        checkMinuscule() {
            let reg = new RegExp(this.rule.minuscule);
            let test = reg.test(this.password);
            this.updateRender(test, this.minuscule);
        },

        /**
         * Vérifie la présence d'au moins 1 chiffre
         */
        checkChiffre() {
            let reg = new RegExp(this.rule.chiffre);
            let test = reg.test(this.password);
            this.updateRender(test, this.chiffre);
        },

        /**
         * Vérifie la présence d'un caractère spécial
         */
        checkSpecial() {
            let reg = new RegExp(this.rule.special);
            let test = reg.test(this.password);
            this.updateRender(test, this.special);
        },

        /**
         * Met à jour l'affichage
         * @param test
         * @param rule
         */
        updateRender(test, rule) {
            if (test) {
                rule.progress = 20;
                rule.class = 'text-success';
                rule.icon = 'bi-check-circle-fill';

            } else {
                rule.class = 'text-danger';
                rule.icon = 'bi-x-circle-fill';
                rule.progress = 0;
            }
        },

        resetAll() {
            this.password = '';
            this.passwordConfirm = '';
            this.classPasswordConfirm = '';
            this.classPassword = '';
            this.btnSubmit = 'disabled';
            this.resetRender(this.nbCharacter);
            this.resetRender(this.majuscule);
            this.resetRender(this.minuscule);
            this.resetRender(this.chiffre);
            this.resetRender(this.special);
        },

        resetRender(rule)
        {
            rule.progress = 0;
            rule.class = '';
            rule.icon = 'bi-x-circle-fill';
        },

        /**
         * Sauvegarde le nouveau mot de passe
         */
        savePassword() {
            this.loading = true;
            axios.post(this.url_change_password, {
                data: this.password
            }).then((response) => {
                this.msgUpdatePassword = response.data.msg;
            }).catch((error) => {
                console.log(error);
            }).finally(() => {
                this.loading = false
                this.resetAll();
                setTimeout(() => {
                    this.msgUpdatePassword = '';
                }, 3000)
            });
        },
    }
}
</script>

<template>

    <div :class="loading === true ? 'block-grid' : ''">
        <div v-if="loading" class="overlay">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-border text-primary" role="status"></div>
                <span class="txt-overlay">{{ translate.loading }}</span>
            </div>
        </div>

        <div v-if="this.msgUpdatePassword !== ''" class="alert alert-info">
            {{ this.msgUpdatePassword }}
        </div>

        <div class="mb-3">
            <label for="input-password-1" class="form-label">{{ this.translate.password }}</label>
            <input type="text" class="form-control no-control" :class="this.classPassword" id="input-password-1" v-model="password" @keyup="this.checkPassword">
        </div>
        <div class="mb-3">
            <label for="input-password-2" class="form-label">{{ this.translate.password_2 }}</label>
            <input type="password" class="form-control no-control" :class="this.classPasswordConfirm" id="input-password-2" v-model="passwordConfirm" @keyup="this.validatePasswordFinal">
            <div class="invalid-feedback">
                {{ this.translate.error_password_2 }}
            </div>
        </div>

        <button class="btn btn-secondary" :class="btnSubmit" @click="savePassword">Modifier</button>
        <hr/>

        <div>
            <p class="mb-0">{{ this.translate.force }}</p>
            <div class="progress">
                <div class="progress-bar" :class="this.progressColor" :style="'width: ' + this.progress + '%;'" role="progressbar" aria-label="Basic example" :data-aria-valuenow="this.progress" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="row">
                <div class="col-6">
                    <p class="mb-0" :class="this.nbCharacter.class">
                        <i class="bi " :class="this.nbCharacter.icon"></i> {{ this.translate.force_nb_character }}
                    </p>
                </div>
                <div class="col-6">
                    <p class="mb-0" :class="this.majuscule.class">
                        <i class="bi " :class="this.majuscule.icon"></i> {{ this.translate.force_majuscule }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <p class="mb-0" :class="this.minuscule.class">
                        <i class="bi" :class="this.minuscule.icon"></i> {{ this.translate.force_minuscule }}
                    </p>
                </div>
                <div class="col-6">
                    <p class="mb-0" :class="this.chiffre.class">
                        <i class="bi" :class="this.chiffre.icon"></i> {{ this.translate.force_chiffre }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <p class="mb-0" :class="this.special.class">
                        <i class="bi" :class="this.special.icon"></i> {{ this.translate.force_character_spe }} - #?!@$%^&*-
                    </p>
                </div>
            </div>
        </div>
    </div>

</template>