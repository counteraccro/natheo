<script>
/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Affichage au format GRID et listing des médias
 */
export default {
  props: {
    medias: Object,
    translate: Object,
    render: String
  },
  emits: ['load-data-folder', 'edit-folder', 'show-info', 'edit-media', 'move', 'trash'],
  data() {
    return {}
  },
  computed: {},
  methods: {

    /**
     * Permet d'ouvrir un média
     * @param path
     */
    openMedia(path) {
      window.open(path, "_blank");
    }
  }
}
</script>

<template>

  <div id="block-media-grid" class="mt-5 row" v-if="this.render === 'grid'">
    <div v-if="this.medias.length > 0" class="media col-auto mb-4" v-for="media in this.medias">
      <img v-if="media.type === 'media'" height="200" width="200" class="rounded-3"
           :src="media.thumbnail" style="cursor:pointer;"
           :alt="media.name" @click="this.openMedia(media.webPath)"/>
      <div v-else class="folder" alt="media.name" @click="$emit('load-data-folder', media.id)"></div>
      <div class="info-media rounded-bottom-3">
        <div class="btn-group">
          <button type="button" class="btn btn-link btn-sm dropdown-toggle pt-2"
                  style="color: #FFFFFF; margin-top: -5px;" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-justify"></i>
          </button>
          <ul class="dropdown-menu">
            <li>
              <!-- lien information -->
              <a class="dropdown-item" @click="$emit('show-info', media.type, media.id)">
                <i class="bi bi-info-circle-fill"></i> {{ this.translate.link_info }}
              </a>
            </li>
            <li>
              <!-- lien éditer -->
              <a v-if="media.type === 'media'" class="dropdown-item" @click="$emit('edit-media', media.id)">
                <i class="bi bi-pencil-square"></i> {{ this.translate.link_edit_media }}
              </a>
              <a v-else class="dropdown-item" @click="$emit('edit-folder', media.id)">
                <i class="bi bi-pencil-fill"></i> {{ this.translate.link_edit }}
              </a>
            </li>
            <li>
              <a class="dropdown-item" @click="$emit('move', media.type, media.id)">
                <i class="bi bi-arrow-right-circle-fill"></i> {{ this.translate.link_move }}
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" @click="$emit('trash', media.type, media.id, media.name, false)">
              <i class="bi bi-trash-fill"></i> {{ this.translate.link_remove }}
            </a>
            </li>
          </ul>
        </div>
        <span class="d-inline-block text-truncate" style="max-width: 140px;vertical-align: middle;"> {{
            media.name
          }} </span>

      </div>
    </div>
    <div v-else class="text-center mt-3">
      <i class="bi bi-folder-x"></i> <i>
      {{ this.translate.no_media }}
    </i>
    </div>
  </div>
  <div v-else>
    <div class="table-responsive mt-5">
      <table class="table table-striped table-hover align-middle">
        <caption>
          <div class="btn btn-secondary btn-sm float-end">{{ this.translate.table_caption }}</div>
        </caption>
        <thead>
        <tr>
          <th style="width: 3%">#</th>
          <th>{{ this.translate.table_name }}</th>
          <th style="width: 15%">{{ this.translate.table_date }}</th>
          <th style="width: 10%">{{ this.translate.table_size }}</th>
          <th style="width: 20%">{{ this.translate.table_action }}</th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="this.medias.length > 0" v-for="media in this.medias">
          <td>
            <span v-if="media.type === 'media'"><i class="bi bi-file-fill"></i> </span>
            <span v-else><i class="bi bi-folder-fill"></i> </span>
          </td>
          <td><a class="link-info" style="cursor:pointer;"
                 @click.stop="media.type === 'media' ? this.openMedia(media.webPath) : $emit('load-data-folder', media.id)">{{
              media.name
            }}</a></td>
          <td>{{ media.date }}</td>
          <td><span v-if="media.type === 'media'">{{ media.size }}</span></td>
          <td>

            <a class="btn btn-sm btn-secondary me-1 mt-1"
               @click="media.type === 'media' ? this.openMedia(media.webPath) : $emit('load-data-folder', media.id)">
              <i class="bi " :class="media.type === 'media' ? 'bi-eye' : 'bi-folder2-open'"></i>
            </a>

            <!-- lien information -->
            <a class="btn btn-sm btn-secondary me-1 mt-1" @click="$emit('show-info', media.type, media.id)">
              <i class="bi bi-info-circle"></i>
            </a>

            <!-- lien éditer -->
            <a class="btn btn-sm btn-secondary me-1 mt-1"
               @click="media.type === 'media' ? $emit('edit-media', media.id) : $emit('edit-folder', media.id)">
              <i class="bi bi-pencil-square"></i>
            </a>

            <!-- lien move -->
            <a class="btn btn-sm btn-secondary me-1 mt-1" @click="$emit('move', media.type, media.id)">
              <i class="bi bi-arrow-right-circle"></i>
            </a>

            <!-- lien corbeille -->
            <a class="btn btn-sm btn-secondary mt-1" @click="$emit('trash', media.type, media.id, media.name, false)">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <tr v-else>
          <td colspan="4" class="text-center">
            {{ this.translate.no_media }}
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>


</template>

<style scoped>

</style>