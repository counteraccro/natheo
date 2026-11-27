/**
 * @author Gourdon Aymeric
 * @version 1.0
 * Natheo-Editor, éditeur de texte du back-office
 */
import NatheoEditor from '@admin/Editors/NatheoEditor/NatheoEditor.vue';
import { defineAdminEditor } from '@/vue/Composables/Admin/Editor/defineAdminEditor';

export default defineAdminEditor({ id: 'natheo_editor', component: NatheoEditor });
