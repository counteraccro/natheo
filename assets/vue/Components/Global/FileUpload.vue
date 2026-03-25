<script>
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Permet d'uploader un fichier
 */
export default {
  name: 'FileUpload',
  emits: ['file-uploaded', 'close-modale-upload'],
  props: {
    maxSize: { type: Number, default: 5, required: true },
    accept: { type: String, default: 'image/*' },
    translate: Object,
  },
  data() {
    return {
      errors: [],
      isLoading: false,
      uploadReady: true,
      isDragging: false,
      file: {
        name: '',
        size: 0,
        type: '',
        fileExtention: '',
        url: '',
        isImage: false,
        isUploaded: false,
        title: '',
        description: '',
      },
    };
  },
  methods: {
    handleFileChange(e) {
      this.errors = [];
      if (e.target.files && e.target.files[0]) {
        if (this.isFileValid(e.target.files[0])) {
          this.processFile(e.target.files[0]);
        }
      }
    },

    handleDrop(e) {
      this.isDragging = false;
      this.errors = [];
      const dropped = e.dataTransfer.files[0];
      if (dropped && this.isFileValid(dropped)) {
        this.processFile(dropped);
      }
    },

    processFile(file) {
      const fileSize = Math.round((file.size / 1024 / 1024) * 100) / 100;
      const fileExtention = file.name.split('.').pop();
      const fileName = file.name.split('.').shift();
      const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtention.toLowerCase());
      const reader = new FileReader();
      reader.addEventListener(
        'load',
        () => {
          this.file = {
            name: fileName,
            size: fileSize,
            type: file.type,
            fileExtention,
            isImage,
            url: reader.result,
            isUploaded: true,
            title: fileName,
            description: '',
          };
        },
        false
      );
      reader.readAsDataURL(file);
    },

    isFileSizeValid(fileSize) {
      if (fileSize > this.maxSize) {
        this.errors.push(this.translate.error_size + ' ' + this.maxSize + 'MB');
      }
    },

    isFileTypeValid(file) {
      const extension = file.name.split('.').pop().toLowerCase();
      const mimeType = file.type; // ex: "image/jpeg"

      const accepted = this.accept.split(',').map((a) => a.trim());

      const isValid = accepted.some((a) => {
        if (a.endsWith('/*')) {
          // ex: "image/*" → vérifie le début du MIME type
          const mimeGroup = a.split('/')[0];
          return mimeType.startsWith(mimeGroup + '/');
        }
        if (a.startsWith('.')) {
          // ex: ".jpg" → vérifie l'extension
          return '.' + extension === a;
        }
        // ex: "image/jpeg" → vérifie le MIME exact
        return mimeType === a;
      });

      if (!isValid) {
        this.errors.push(this.translate.error_ext + ' ' + this.accept);
      }
    },

    isFileValid(file) {
      this.isFileSizeValid(Math.round((file.size / 1024 / 1024) * 100) / 100);
      this.isFileTypeValid(file); // ← fichier complet, plus juste l'extension
      return this.errors.length === 0;
    },

    resetFileInput() {
      this.uploadReady = false;
      this.$nextTick(() => {
        this.uploadReady = true;
        this.file = {
          name: '',
          size: 0,
          type: '',
          data: '',
          fileExtention: '',
          url: '',
          isImage: false,
          isUploaded: false,
          title: '',
          description: '',
        };
        this.errors = [];
      });
    },

    closeModale() {
      this.resetFileInput();
      this.$emit('close-modale-upload');
    },

    sendDataToParent() {
      this.$emit('file-uploaded', this.file);
      this.resetFileInput();
    },
  },
};
</script>

<template>
  <!-- ── ÉTAPE 1 : Zone de dépôt ── -->
  <template v-if="!file.isUploaded">
    <!-- Drop zone -->
    <div
      v-if="uploadReady"
      class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed transition-colors cursor-pointer mb-4"
      :style="{
        borderColor: isDragging ? 'var(--primary)' : 'var(--border-dark)',
        backgroundColor: isDragging ? 'var(--primary-lighter)' : 'var(--bg-main)',
        padding: '1.5rem 1rem',
      }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="$refs.fileInput.click()"
    >
      <div
        class="w-10 h-10 rounded-full flex items-center justify-center"
        style="background-color: var(--primary-lighter)"
      >
        <svg class="w-5 h-5" style="color: var(--primary)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
          />
        </svg>
      </div>
      <div class="text-center">
        <p class="text-xs font-semibold" style="color: var(--text-primary)">Glisser un fichier ici</p>
        <p class="text-xs mt-0.5" style="color: var(--text-light)">
          ou <span style="color: var(--primary); text-decoration: underline">parcourir</span>
        </p>
      </div>
      <p class="text-xs" style="color: var(--text-light)">Max {{ maxSize }} MB · {{ accept }}</p>
      <input ref="fileInput" type="file" class="hidden" :accept="accept" @change="handleFileChange" />
    </div>

    <!-- Erreurs -->
    <div
      v-if="errors.length > 0"
      class="rounded-lg p-3 mb-4"
      style="background-color: #fee2e2; border: 1px solid #fca5a5"
    >
      <p class="text-xs font-semibold mb-1" style="color: #991b1b">{{ translate.error_title }}</p>
      <p v-for="(error, index) in errors" :key="index" class="text-xs" style="color: #b91c1c">
        {{ error }}
      </p>
    </div>
  </template>

  <!-- ── ÉTAPE 2 : Prévisualisation + formulaire ── -->
  <template v-if="file.isUploaded">
    <!-- Aperçu -->
    <div class="relative rounded-xl overflow-hidden mb-4" style="border: 1px solid var(--border-color)">
      <!-- Image -->
      <img v-if="file.isImage" :src="file.url" class="w-full object-cover" style="max-height: 130px" alt="" />
      <!-- Fichier non-image -->
      <div
        v-else
        class="flex flex-col items-center justify-center gap-2"
        style="height: 100px; background-color: var(--bg-main)"
      >
        <svg class="w-8 h-8" style="color: var(--text-light)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
          />
        </svg>
        <p class="text-xs font-bold uppercase" style="color: var(--text-secondary)">
          {{ file.fileExtention }}
        </p>
      </div>
      <!-- Badge extension -->
      <span class="type-badge">{{ file.fileExtention }}</span>
    </div>

    <!-- Infos du fichier -->
    <div class="info-row">
      <span class="info-label">Taille</span>
      <span class="info-value">{{ file.size }} Mo</span>
    </div>
    <div class="info-row mb-4">
      <span class="info-label">Type</span>
      <span class="info-value">{{ file.type }}</span>
    </div>

    <!-- Champ titre -->
    <div class="mb-3">
      <label class="block text-xs font-semibold mb-1" style="color: var(--text-secondary)">
        {{ translate.input_title }}
      </label>
      <input
        type="text"
        v-model="file.title"
        class="w-full text-sm px-3 py-2 rounded-lg outline-none transition"
        style="background-color: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary)"
        onfocus="
          this.style.borderColor = 'var(--primary)';
          this.style.boxShadow = '0 0 0 3px var(--primary-lighter)';
        "
        onblur="
          this.style.borderColor = 'var(--border-color)';
          this.style.boxShadow = 'none';
        "
      />
      <p class="text-xs mt-1" style="color: var(--text-light)">{{ translate.input_title_help }}</p>
    </div>

    <!-- Champ description -->
    <div class="mb-4">
      <label class="block text-xs font-semibold mb-1" style="color: var(--text-secondary)">
        {{ translate.input_description }}
      </label>
      <input
        type="text"
        v-model="file.description"
        class="w-full text-sm px-3 py-2 rounded-lg outline-none transition"
        style="background-color: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-primary)"
        onfocus="
          this.style.borderColor = 'var(--primary)';
          this.style.boxShadow = '0 0 0 3px var(--primary-lighter)';
        "
        onblur="
          this.style.borderColor = 'var(--border-color)';
          this.style.boxShadow = 'none';
        "
      />
      <p class="text-xs mt-1" style="color: var(--text-light)">{{ translate.input_description_help }}</p>
    </div>
  </template>

  <!-- ── Actions ── -->
  <div class="flex flex-col gap-2">
    <button
      v-if="file.isUploaded"
      @click="sendDataToParent"
      class="btn-primary w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"
        />
      </svg>
      {{ translate.btn_upload }}
    </button>
    <button
      v-if="file.isUploaded"
      @click="resetFileInput"
      class="btn-outline w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      {{ translate.btn_cancel }}
    </button>
  </div>
</template>

<style scoped></style>
