<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { MenuElement, MenuTreeTranslate } from '@/ts/Menu/type';

export default defineComponent({
  name: 'MenuTree',
  props: {
    translate: {
      type: Object as PropType<MenuTreeTranslate>,
      required: true,
    },
    menuElement: {
      type: Object as PropType<MenuElement>,
      required: true,
    },
    locale: {
      type: String,
      required: true,
    },
    deep: {
      type: Number,
      required: true,
    },
    idSelected: {
      type: Number,
    },
  },
});
</script>

<template>
  <div class="tree-node">
    <div class="tree-node-row">
      <span class="drag-handle">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M8 6a2 2 0 100-4 2 2 0 000 4zM16 6a2 2 0 100-4 2 2 0 000 4zM8 14a2 2 0 100-4 2 2 0 000 4zM16 14a2 2 0 100-4 2 2 0 000 4zM8 22a2 2 0 100-4 2 2 0 000 4zM16 22a2 2 0 100-4 2 2 0 000 4z"
          ></path>
        </svg>
      </span>
      <button class="tree-toggle open">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
      <span class="tree-node-label">{{ menuElement.id }}</span>
      <span class="node-type-badge badge-page">Page T</span>
      <div class="tree-node-actions">
        <button class="tree-action-btn add" data-tooltip="Ajouter un enfant" onclick="addChild(event, 1)">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
        </button>
        <button class="tree-action-btn edit" data-tooltip="Éditer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
            ></path>
          </svg>
        </button>
        <button class="tree-action-btn hide" data-tooltip="Masquer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            ></path>
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            ></path>
          </svg>
        </button>
        <button class="tree-action-btn delete" data-tooltip="Supprimer">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
            ></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tree-container {
  padding: 0.75rem;
}
.tree-node {
  position: relative;
  margin-bottom: 2px;
}

/* Ligne verticale de connexion */
.tree-node-children {
  position: relative;
  margin-left: 1.25rem;
  padding-left: 0.75rem;
  border-left: 2px dashed var(--border-color);
  margin-top: 2px;
}

.tree-node-row {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.625rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border-color);
  background-color: var(--bg-card);
  transition: all 0.15s ease;
  cursor: pointer;
  user-select: none;
}
.tree-node-row:hover {
  border-color: var(--primary-light);
  background-color: var(--primary-lighter);
  box-shadow: 0 0 0 1px var(--primary-lighter);
}
.tree-node-row.selected {
  border-color: var(--primary);
  background-color: var(--primary-lighter);
  box-shadow: 0 0 0 2px var(--primary-lighter);
}
.tree-node-row.hidden-node {
  opacity: 0.5;
}

/* Drag handle */
.drag-handle {
  color: var(--text-light);
  cursor: grab;
  padding: 2px;
  flex-shrink: 0;
}
.drag-handle:active {
  cursor: grabbing;
}
.drag-handle:hover {
  color: var(--primary);
}

/* Toggle expand */
.tree-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 1px solid var(--border-color);
  background-color: var(--bg-main);
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.15s ease;
  color: var(--text-secondary);
}
.tree-toggle:hover {
  border-color: var(--primary);
  color: var(--primary);
  background-color: var(--primary-lighter);
}
.tree-toggle svg {
  transition: transform 0.2s ease;
}
.tree-toggle.open svg {
  transform: rotate(90deg);
}
.tree-toggle.leaf {
  visibility: hidden;
  pointer-events: none;
}

/* Node label */
.tree-node-label {
  flex: 1;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Node type badge */
.node-type-badge {
  font-size: 0.65rem;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 4px;
  flex-shrink: 0;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.badge-page {
  background-color: var(--primary-lighter);
  color: var(--primary);
}
.badge-url {
  background-color: #dbeafe;
  color: #1d4ed8;
}
.badge-anchor {
  background-color: #d1fae5;
  color: #065f46;
}

/* Node actions */
.tree-node-actions {
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
}
.tree-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: none;
  background-color: transparent;
  cursor: pointer;
  transition: all 0.15s ease;
  color: var(--text-light);
}
.tree-action-btn:hover {
  background-color: var(--bg-hover);
}
.tree-action-btn.add:hover {
  color: var(--btn-success);
  background-color: #d1fae5;
}
.tree-action-btn.edit:hover {
  color: var(--primary);
  background-color: var(--primary-lighter);
}
.tree-action-btn.hide:hover {
  color: var(--btn-warning);
  background-color: #fef3c7;
}
.tree-action-btn.delete:hover {
  color: var(--btn-danger);
  background-color: #fee2e2;
}
.tree-action-btn.hidden-active {
  color: var(--btn-warning);
}

/* Add root element */
.btn-add-root {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--primary);
  border: 1.5px dashed var(--primary);
  border-radius: 0.5rem;
  background-color: transparent;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  justify-content: center;
}
.btn-add-root:hover {
  background-color: var(--primary-lighter);
  border-style: solid;
}
</style>
