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
    isFileSizeValid(fileSize) {
      if (fileSize <= this.maxSize) {
        console.log("File size is valid");
      } else {
        this.errors.push(this.translate.error_size + ' ' + this.maxSize + 'MB');
      }
    },
    isFileTypeValid(fileExtention) {
      if (this.accept.split(",").includes(fileExtention)) {
        console.log("File type is valid");
      } else {
        this.errors.push(this.translate.error_ext + ' ' + this.accept);
      }
    },
    isFileValid(file) {
      this.isFileSizeValid(Math.round((file.size / 1024 / 1024) * 100) / 100);
      this.isFileTypeValid(file.name.split(".").pop());
      if (this.errors.length === 0) {
        return true;
      } else {
        return false;
      }
    },
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
    sendDataToParent() {
      this.resetFileInput();
      this.$emit("file-uploaded", this.file);
    },
  },
};
</script>

<template>
  <div class="file-upload">
    <div class="file-upload__area">
      <div v-if="!file.isUploaded">
        <div class="mb-3">
          <label for="formFile" class="form-label">{{ this.translate.input_upload }}</label>
          <input class="form-control" type="file" id="formFile" @change="handleFileChange($event)">
          <div id="uploadHelp" class="form-text">{{ this.translate.help }}</div>
        </div>
        <div v-if="errors.length > 0">
          <div class="alert alert-danger">
            <h5 class="alert-heading">{{ this.translate.error_title }}</h5>
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
        <img :src="file.url" v-if="file.isImage" class="img-fluid" alt=""/>
        <div v-if="!file.isImage" class="file-extention">
          {{ file.fileExtention }}
        </div>
        <span class="file-name">
          {{ file.name }}{{ file.isImage ? `.${file.fileExtention}` : "" }}
        </span>
        <div class="">
          <button @click="resetFileInput">Change file</button>
        </div>
        <div class="" style="margin-top: 10px">
          <button @click="sendDataToParent">Select File</button>
        </div>
      </div>
    </div>
  </div>
</template>