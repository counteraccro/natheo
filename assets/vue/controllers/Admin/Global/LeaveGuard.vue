<script lang="ts">
/**
 * Demande une confirmation avant de quitter une page dont un formulaire suivi a été modifié
 * @author Gourdon Aymeric
 * @version 3.0
 */
import { defineComponent, type PropType } from 'vue';
import Modal from '@/vue/Components/Global/Modal.vue';
import { leaveGuard } from '@/vue/Composables/Admin/leaveGuard';
import type { LeaveGuardTranslate } from '@/ts/Global/LeaveGuard.type';

const FORM_SELECTOR = 'form[data-leave-guard]';

export default defineComponent({
  name: 'LeaveGuard',
  components: { Modal },
  props: {
    // Valeur de l'option système OS_CONFIRM_LEAVE_FORM ('1' = active)
    option: { type: String, default: '1' },
    translate: { type: Object as PropType<LeaveGuardTranslate>, required: true },
  },
  data() {
    return {
      tmpUrl: '',
      confirmModal: false,
      leaving: false,
      formKeys: [] as string[],
    };
  },
  mounted() {
    if (this.option !== '1') {
      return;
    }
    this.trackSymfonyForms();
    document.addEventListener('click', this.onClick);
    window.addEventListener('beforeunload', this.onBeforeUnload);
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onClick);
    window.removeEventListener('beforeunload', this.onBeforeUnload);
    this.formKeys.forEach((key) => leaveGuard.untrack(key));
  },
  methods: {
    /**
     * Suit les formulaires Symfony marqués data-leave-guard, libérés à leur soumission
     */
    trackSymfonyForms(): void {
      document.querySelectorAll<HTMLFormElement>(FORM_SELECTOR).forEach((form, index) => {
        const key = 'symfony-form-' + index;
        leaveGuard.track(key, () => this.serializeForm(form));
        form.addEventListener('submit', () => leaveGuard.untrack(key));
        this.formKeys.push(key);
      });
    },

    /**
     * Valeurs d'un formulaire Symfony, fichiers réduits à leur nom et leur taille
     * @param form
     */
    serializeForm(form: HTMLFormElement): Array<[string, string]> {
      return Array.from(new FormData(form).entries()).map(([name, value]) => [
        name,
        typeof value === 'string' ? value : value.name + ':' + value.size,
      ]);
    },

    /**
     * Retourne le lien quittant la page visé par le clic, null sinon
     * @param ev
     */
    getLeavingLink(ev: MouseEvent): HTMLAnchorElement | null {
      if (ev.defaultPrevented || ev.button !== 0 || ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) {
        return null;
      }
      if (!(ev.target instanceof Element)) {
        return null;
      }

      const link = ev.target.closest('a[href]');
      if (!(link instanceof HTMLAnchorElement) || link.hasAttribute('download')) {
        return null;
      }
      if (link.target !== '' && link.target !== '_self') {
        return null;
      }

      const url = new URL(link.href, document.location.href);
      if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        return null;
      }
      const current = new URL(document.location.href);
      if (
        url.hash !== '' &&
        url.origin + url.pathname + url.search === current.origin + current.pathname + current.search
      ) {
        return null;
      }

      return link;
    },

    /**
     * Évènement onclick : retient la navigation si un formulaire suivi a été modifié
     * @param ev
     */
    onClick(ev: MouseEvent): void {
      const link = this.getLeavingLink(ev);
      if (link === null || !leaveGuard.isDirty()) {
        return;
      }

      ev.preventDefault();
      this.tmpUrl = link.href;
      this.confirmModal = true;
    },

    /**
     * Évènement beforeunload : boîte du navigateur pour le retour, le rechargement et la fermeture
     * @param ev
     */
    onBeforeUnload(ev: BeforeUnloadEvent): void {
      if (this.leaving || !leaveGuard.isDirty()) {
        return;
      }
      ev.preventDefault();
      ev.returnValue = '';
    },

    /**
     * Quitte la page vers le lien retenu
     */
    redirect(): void {
      this.leaving = true;
      document.location.href = this.tmpUrl;
    },
  },
});
</script>

<template>
  <modal
    :id="'confirmModal'"
    :show="confirmModal"
    @close-modal="confirmModal = false"
    :option-modal-backdrop="'static'"
    :option-show-close-btn="false"
  >
    <template #icon>
      <svg
        class="w-7 h-7 me-3"
        aria-hidden="true"
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        fill="none"
        viewBox="0 0 24 24"
      >
        <path
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
        />
      </svg>
    </template>
    <template #title>{{ translate.title }}</template>
    <template #body>
      <p v-html="translate.body"></p>
    </template>
    <template #footer>
      <button type="button" class="btn btn-outline-dark btn-sm" @click="confirmModal = false">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="m15 9-6 6m0-6 6 6m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.button_stay }}
      </button>
      <button type="button" class="btn btn-primary btn-sm" @click="redirect">
        <svg
          class="icon"
          aria-hidden="true"
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
          />
        </svg>

        {{ translate.button_leave }}
      </button>
    </template>
  </modal>
</template>
