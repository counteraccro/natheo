<script>
export default {
  name: "FileUpload",
  props: {
    maxSize: {
      type: Number,
      default: 5,
      required: true,
    },
    accept: {
      type: String,
      default: "image/*",
    },
    translate: Object
  },
  data() {
    return {
      errors: [],
      isLoading: false,
      uploadReady: true,
      file: {
        name: "",
        size: 0,
        type: "",
        fileExtention: "",
        url: "",
        isImage: false,
        isUploaded: false,
        title: '',
        description: ''
      },
    };
  },
  methods: {

    handleFileChange(e) {
      this.errors = [];
      // Check if file is selected
      if (e.target.files && e.target.files[0]) {
        // Check if file is valid
        if (this.isFileValid(e.target.files[0])) {
          // Get uploaded file
          const file = e.target.files[0],
              // Get file size
              fileSize = Math.round((file.size / 1024 / 1024) * 100) / 100,
              // Get file extention
              fileExtention = file.name.split(".").pop(),
              // Get file name
              fileName = file.name.split(".").shift(),
              // Check if file is an image
              isImage = ["jpg", "jpeg", "png", "gif"].includes(fileExtention);
          // Print to console
          console.log(fileSize, fileExtention, fileName, isImage);
          // Load the FileReader API
          let reader = new FileReader();
          reader.addEventListener(
              "load",
              () => {
                // Set file data
                this.file = {
                  name: fileName,
                  size: fileSize,
                  type: file.type,
                  fileExtention: fileExtention,
                  isImage: isImage,
                  url: reader.result,
                  isUploaded: true,
                  title: fileName,
                  description: '',
                };
              },
              false
          );
          // Read uploaded file
          reader.readAsDataURL(file);
        } else {
          console.log("Invalid file");
        }
      }
    },

    /**
     * Control taille du fichier
     * @param fileSize
     */
    isFileSizeValid(fileSize) {
      if (fileSize > this.maxSize) {
        this.errors.push(this.translate.error_size + ' ' + this.maxSize + 'MB');
      }
    },

    /**
     * Control de l'extension
     * @param fileExtention
     */
    isFileTypeValid(fileExtention) {
      if (!this.accept.split(",").includes(fileExtention)) {
        this.errors.push(this.translate.error_ext + ' ' + this.accept);
      }
    },

    /**
     * Validation
     * @param file
     * @returns {boolean}
     */
    isFileValid(file) {
      this.isFileSizeValid(Math.round((file.size / 1024 / 1024) * 100) / 100);
      this.isFileTypeValid(file.name.split(".").pop());
      return this.errors.length === 0;
    },

    /**
     * Réinitialisation des champs
     */
    resetFileInput() {
      this.uploadReady = false;
      this.$nextTick(() => {
        this.uploadReady = true;
        this.file = {
          name: "",
          size: 0,
          type: "",
          data: "",
          fileExtention: "",
          url: "",
          isImage: false,
          isUploaded: false,
          title: '',
          description: '',
        };
      });
    },

    renderPreviewFile(extension) {
      let icon = '';
      switch (extension) {
        case 'csv':
          icon = 'filetype-csv'
          break;
        case 'pdf':
          icon = 'filetype-pdf'
          break;
        case 'xls':
        case 'xlsx':
          icon = 'filetype-xls'
          break;
        case 'doc':
        case 'docx':
          icon = 'filetype-doc'
          break;
        default :
          icon = 'file'
          break;
      }
      return 'bi-' + icon;
    },

    /**
     * Envoi des données
     */
    sendDataToParent() {
      this.resetFileInput();
      this.$emit("file-uploaded", this.file);
    },
  },
};
</script>

<template>
  <div v-if="!file.isUploaded">
    <div class="mb-3">
      <label for="formFile" class="form-label">{{ this.translate.input_upload }}</label>
      <input class="form-control" type="file" id="formFile" @change="handleFileChange($event)">
      <div id="uploadHelp" class="form-text">{{ this.translate.help }}</div>
    </div>
    <div v-if="errors.length > 0">
      <div class="alert alert-danger">
        <b>{{ this.translate.error_title }}</b>
        <div
            v-for="(error, index) in errors"
            :key="index"
        >
          <span>{{ error }}</span>
        </div>
      </div>
    </div>
  </div>
  <div v-if="file.isUploaded" class="upload-preview">
    <h4>{{ this.translate.preview }}</h4>
    <img :src="file.url" v-if="file.isImage" class="img-fluid" alt=""/>
    <div v-else>
      <i class="text-warning">
        <i class="bi" :class="this.renderPreviewFile(file.fileExtention)"></i>
        {{ this.translate.no_preview }} : {{ file.fileExtention }}
      </i>
    </div>
    <hr/>
    <fieldset>
      <legend>{{ this.translate.form_title }}</legend>

      <div class="mb-3">
        <label for="mediaTitle" class="form-label">{{ this.translate.input_title }}</label>
        <input type="text" v-model="this.file.title" class="form-control" id="mediaTitle"
               aria-describedby="emailMediaTitle">
        <div id="emailMediaTitle" class="form-text">{{ this.translate.input_title_help }}</div>
      </div>
      <div class="mb-3">
        <label for="mediaDescription" class="form-label">{{ this.translate.input_description }}</label>
        <input type="text" v-model="this.file.description" class="form-control" id="mediaDescription"
               aria-describedby="emailMediaDescription">
        <div id="emailMediaDescription" class="form-text">{{ this.translate.input_description_help }}</div>
      </div>
    </fieldset>
    <!--<div v-if="!file.isImage" class="file-extention">
      {{ file.fileExtention }}
    </div>
    <span class="file-name">
          {{ file.name }}{{ file.isImage ? `.${file.fileExtention}` : "" }}
        </span> -->
    <button class="btn btn-warning float-end" @click="resetFileInput"><i class="bi bi-file-earmark-x-fill"></i>
      {{ this.translate.btn_cancel }}
    </button>
    <button class="btn btn-secondary float-end me-2" @click="sendDataToParent"><i class="bi bi-upload"></i>
      {{ this.translate.btn_upload }}
    </button>
  </div>
</template>