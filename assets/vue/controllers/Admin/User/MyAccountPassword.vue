<script>

/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Changement du mot de passe pour un compte user
 */

export default {
    name: "MyAccountPassword",
    props: {
        url_change_password: String,
        translate: Array
    },
    data() {
        return {
            password: '',
            passwordConfirm: '',
            nbCharacter: {
                'class': '',
                'icon': 'bi-x-circle-fill',
                'progress': 0
            },
            rule: {
                'start': '^',
                'end': '$',
                'nbCharacter': '.{8,}',
            }
        }
    },
    computed: {
      progress() {
          return this.nbCharacter.progress;
      }
    },
    methods: {
        validatePasswordFinal() {
            let reg = new RegExp(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/);
            return reg.test(this.password);
        },

        checkPassword() {
            this.checkNbCharacter();
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
         * Met à jour l'affichage
         * @param test
         * @param rule
         */
        updateRender(test, rule)
        {
            if (test) {
                rule.progress = 20;
                rule.class = 'text-success';
                rule.icon = 'bi-check-circle-fill';

            } else {
                rule.class = 'text-danger';
                rule.icon = 'bi-x-circle-fill';
                rule.progress = 0;
            }
        }

    }
}
</script>

<template>

    <div class="mb-3">
        <label for="input-password-1" class="form-label">{{ this.translate.password }}</label>
        <input type="password" class="form-control" id="input-password-1" v-model="password" @keyup="this.checkPassword">
    </div>
    <div class="mb-3">
        <label for="input-password-2" class="form-label">{{ this.translate.password_2 }}</label>
        <input type="password" class="form-control" id="input-password-2" v-model="passwordConfirm">
    </div>

    <div>
        <p class="mb-0">{{ this.translate.force }}</p>
        <div class="progress">
            <div class="progress-bar" :style="'width: ' + this.progress + '%;'" role="progressbar" aria-label="Basic example" :data-aria-valuenow="this.progress" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="row">
            <div class="col-6">
                <p class="mb-0" :class="this.nbCharacter.class">
                    <i class="bi " :class="this.nbCharacter.icon"></i> {{ this.translate.force_nb_character }}
                </p>
            </div>
            <div class="col-6">
                <p class="text-success mb-0">
                    <i class="bi bi-x-circle-fill"></i> {{ this.translate.force_majuscule }}
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <p class="text-success mb-0">
                    <i class="bi bi-x-circle-fill"></i> {{ this.translate.force_minuscule }}
                </p>
            </div>
            <div class="col-6">
                <p class="text-success mb-0">
                    <i class="bi bi-x-circle-fill"></i> {{ this.translate.force_chiffre }}
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <p class="text-success mb-0">
                    <i class="bi bi-check-circle-fill"></i> {{ this.translate.force_character_spe }}
                </p>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>