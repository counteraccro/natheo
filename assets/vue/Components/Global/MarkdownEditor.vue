<script lang="ts">
/**
 * @author Gourdon Aymeric
 * @version 2.0
 * Editeur Markdown version TypeScript
 */

export default {
  name: 'MarkdownEditor',
  props: {
    /*type: {
      type: String,
      default: 'alert-danger-solid',
    },
    text: {
      type: String,
      required: true,
    },*/
  },
  emits: [] as string[],
  data() {
    return {};
  },
  mounted() {},
  methods: {},
};
</script>

<template>
  <div class="md-editor-wrap">
    <!-- ── TOOLBAR ──────────────────────────────────── -->
    <div class="md-toolbar">
      <!-- Titres dropdown -->
      <details class="tb-dropdown" id="ddHeading">
        <summary class="tb-btn" style="padding: 0 0.625rem">
          H1
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M6 9l6 6 6-6"></path>
          </svg>
        </summary>
        <div class="tb-dropdown-menu" style="min-width: 12rem">
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H1</span>
            <span class="item-label h-preview-1">Titre 1</span>
            <span class="item-shortcut"># </span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H2</span>
            <span class="item-label h-preview-2">Titre 2</span>
            <span class="item-shortcut">## </span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H3</span>
            <span class="item-label h-preview-3">Titre 3</span>
            <span class="item-shortcut">### </span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H4</span>
            <span class="item-label h-preview-4">Titre 4</span>
            <span class="item-shortcut">#### </span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H5</span>
            <span class="item-label h-preview-5">Titre 5</span>
            <span class="item-shortcut">##### </span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">H6</span>
            <span class="item-label h-preview-6">Titre 6</span>
            <span class="item-shortcut">###### </span>
          </a>
        </div>
      </details>

      <!-- Mots clés dropdown -->
      <details class="tb-dropdown" id="ddKeywords">
        <summary class="tb-btn" style="padding: 0 0.75rem">
          <svg
            class="tb-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path
              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"
            ></path>
          </svg>
          Mots clés
          <svg
            class="tb-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path d="M6 9l6 6 6-6"></path>
          </svg>
        </summary>
        <div class="tb-dropdown-menu" style="min-width: 13rem">
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Adresse email</span>
            <span class="item-shortcut">[[user.email]]</span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Login</span>
            <span class="item-shortcut">[[user.login]]</span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Prénom</span>
            <span class="item-shortcut">[[user.first]]</span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Nom</span>
            <span class="item-shortcut">[[user.last]]</span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Url du site</span>
            <span class="item-shortcut">[[site.url]]</span>
          </a>
          <a href="#" class="tb-dropdown-item">
            <span class="item-badge">@</span>
            <span class="item-label">Nom du site</span>
            <span class="item-shortcut">[[site.name]]</span>
          </a>
        </div>
      </details>

      <div class="tb-sep"></div>

      <!-- Gras -->
      <button class="tb-btn" title="Gras (Ctrl+B)">
        <svg class="tb-icon" viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"
          ></path>
        </svg>
      </button>

      <!-- Italique -->
      <button class="tb-btn" title="Italique (Ctrl+I)">
        <svg class="tb-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"></path>
        </svg>
      </button>

      <!-- Barré -->
      <button class="tb-btn" title="Barré">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M16 4H9a3 3 0 000 6h6a3 3 0 010 6H6"></path>
          <line x1="4" y1="12" x2="20" y2="12"></line>
        </svg>
      </button>

      <!-- Citation -->
      <button class="tb-btn" title="Citation">
        <svg class="tb-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"></path>
        </svg>
      </button>

      <div class="tb-sep"></div>

      <!-- Liste non ordonnée -->
      <button class="tb-btn" title="Liste à puces">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <line x1="9" y1="6" x2="20" y2="6"></line>
          <line x1="9" y1="12" x2="20" y2="12"></line>
          <line x1="9" y1="18" x2="20" y2="18"></line>
          <circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"></circle>
          <circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"></circle>
          <circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"></circle>
        </svg>
      </button>

      <!-- Liste ordonnée -->
      <button class="tb-btn" title="Liste numérotée">
        <svg class="tb-icon" viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M2 17h2v.5H3v1h1v.5H2v1h3v-4H2v1zm1-9h1V4H2v1h1v3zm-1 3h1.8L2 13.1v.9h3v-1H3.2L5 10.9V10H2v1zm5-6v2h14V5H7zm0 14h14v-2H7v2zm0-6h14v-2H7v2z"
          ></path>
        </svg>
      </button>

      <!-- Table -->
      <button class="tb-btn" title="Tableau">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect x="3" y="3" width="18" height="18" rx="2"></rect>
          <line x1="3" y1="9" x2="21" y2="9"></line>
          <line x1="3" y1="15" x2="21" y2="15"></line>
          <line x1="12" y1="3" x2="12" y2="21"></line>
        </svg>
      </button>

      <div class="tb-sep"></div>

      <!-- Lien -->
      <button class="tb-btn" title="Lien">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"></path>
          <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"></path>
        </svg>
      </button>

      <!-- Image -->
      <button class="tb-btn" title="Image">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <rect x="3" y="3" width="18" height="18" rx="2"></rect>
          <circle cx="8.5" cy="8.5" r="1.5"></circle>
          <polyline points="21 15 16 10 5 21"></polyline>
        </svg>
      </button>

      <!-- Code inline -->
      <button class="tb-btn" title="Code">
        <svg
          class="tb-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <polyline points="16 18 22 12 16 6"></polyline>
          <polyline points="8 6 2 12 8 18"></polyline>
        </svg>
      </button>
    </div>
    <!-- /.md-toolbar -->

    <!-- ── TEXTAREA ──────────────────────────────────── -->
    <textarea class="md-textarea" id="mdInput" placeholder="Rédigez votre contenu en Markdown…">
# Editeur Markdown

Ceci est un éditeur qui prend en charge le markdown

Ceci est un [Lien Google](https://www.google.fr) et ceci une image ![Image](https://lh3.googleusercontent.com/ogw/AOLn63EBcXl7rZYf06N2bijCt_itpKuVkmD8mr0RGrJUVA=s32-c-mo)

| Fonction | Code | Rendu |
| -------- | -------- | -------- |
| Italic | `*text*` | *text*|
| Gras| `**text**` | **text**|
| barré| `~~text~~` | ~~text~~|

&gt;   Une citation</textarea
    >

    <!-- ── FOOTER HINT ────────────────────────────────── -->
    <div class="md-footer-hint">
      <svg
        style="width: 0.875rem; height: 0.875rem; color: var(--text-light); flex-shrink: 0"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      La mise en forme de votre contenu se fait avec le langage de balisage
      <a href="https://www.markdownguide.org" target="_blank" rel="noopener">Markdown</a>
    </div>
  </div>

  <div class="md-preview-wrap">
    <div class="md-preview-header">
      <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
        <circle cx="12" cy="12" r="3"></circle>
      </svg>
      <span>Prévisualisation</span>
    </div>

    <div class="md-preview-body">
      <h1>Editeur Markdown</h1>

      <p>Ceci est un éditeur qui prend en charge le markdown</p>

      <p>
        Ceci est un <a href="https://www.google.fr" target="_blank">Lien Google</a>
        et ceci une image
        <img
          src="https://lh3.googleusercontent.com/ogw/AOLn63EBcXl7rZYf06N2bijCt_itpKuVkmD8mr0RGrJUVA=s32-c-mo"
          alt="Image"
        />
      </p>

      <table>
        <thead>
          <tr>
            <th>Fonction</th>
            <th>Code</th>
            <th>Rendu</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Italic</td>
            <td><code>*text*</code></td>
            <td><em>text</em></td>
          </tr>
          <tr>
            <td>Gras</td>
            <td><code>**text**</code></td>
            <td><strong>text</strong></td>
          </tr>
          <tr>
            <td>barré</td>
            <td><code>~~text~~</code></td>
            <td><s>text</s></td>
          </tr>
        </tbody>
      </table>

      <blockquote>
        <p>Une citation</p>
      </blockquote>
    </div>
  </div>
</template>

<style scoped></style>
