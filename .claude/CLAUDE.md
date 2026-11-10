# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Nathéo is a CMS built on Symfony 8.1 (PHP 8.2+) with a Vue 3 / TypeScript admin frontend, built via Vite (`vite-plugin-symfony`, `symfony/ux-vue`). It is bilingual French/English in its own codebase (comments, commit-style docblocks and Twig strings are French) and supports FR/EN/ES as content locales.

## Commands

### PHP / Symfony
```bash
composer install                          # install PHP deps
php bin/console natheo:install             # full dev install: DB create + schema + fixtures
php bin/phpunit --display-deprecations     # run all tests
php bin/phpunit --filter=testLoadMedias --display-deprecation   # run a single test
php bin/console --env=test natheo:install-bdd-test               # (re)build the test DB
php bin/console messenger:consume async -vv                      # process async messenger queue
php bin/console translation:extract --force --format=yaml fr     # extract/update translations (also en, es)
```
Test env setup: copy `.env.test` to `.env.test.local` and set `DATABASE_URL`/`NATHEO_SCHEMA` before running `natheo:install-bdd-test`.

### Frontend
```bash
yarn install
yarn dev                # vite dev server with HMR (assets/, watches templates)
yarn build              # vue-tsc type-check + vite build
yarn type-check         # vue-tsc --noEmit only
```

## Architecture

### Backend layering
Controllers are thin; almost all logic lives in `Service` classes, which each extend an `App*Service` base for their layer:
- `App\Service\Admin\AppAdminService` (→ `AppAdminHandlerService` → `AppService`) for the admin backoffice
- `App\Service\Api\AppApiService` for the JSON API (`src/Controller/Api/v1`)
- `App\Service\Front\AppFrontService` for the public front-end

Concrete services (e.g. `DashboardService extends AppAdminService`) receive their sub-dependencies (repositories, other services) through a `#[AutowireLocator(self::HANDLERS)] ContainerInterface $handlers` lazy-locator pattern rather than plain constructor injection of everything — look at `HANDLERS` on the base class before adding a new collaborator. Controllers themselves (`AppAdminController` etc.) use the same `AutowireLocator` pattern for their own cross-cutting deps (translator, logger, `OptionUserService`, ...).

Directory shape under `src/` mirrors the domain, duplicated across `Controller/`, `Service/`, `Repository/`, `Entity/`, `Enum/`, generally as `Admin/Content/<Domain>/…`, `Admin/System/…`, `Admin/Tools/…` (plus parallel `Api/` and `Front/` trees for those layers). When adding a feature, expect to touch the matching subfolder in several of these trees at once.

`Translate/<Layer>/<Domain>/<Domain>Translate.php` classes (e.g. `src/Translate/Admin/Content/MenuTranslate.php`, extending `src/Translate/AppTranslate.php`) centralize building the translation arrays passed to Twig/Vue — controllers call these instead of calling the translator directly for anything non-trivial.

`src/Overwrite/` holds an example of overriding a base controller (`Controller/Admin/Tools/DemoOverwriteController.php`, extending `AppAdminController`, templates in `templates/overwrite/`).

### Where non-service code goes
The former `src/Utils/` and `assets/utils/` catch-all folders were removed: never recreate them. Place code by role instead:
- `src/Service/...` for services (and plain helper classes used by them), `src/Enum/...` for constants — constant holder classes are written as backed enums;
- `src/Factory/<Layer>/<Domain>/` for `*Factory`, `src/Populate/<Layer>/<Domain>/` for `*Populate`, `src/Formatter/<Layer>/<Domain>/` for `*Formatter`/`*Formater` — all three excluded from autowiring in `config/services.yaml`;
- `src/Entity/Trait/` (entity helpers) or `src/Repository/Trait/` (query helpers) for reusable traits;
- `assets/vue/Composables/` for front runtime helpers and constants (`Admin/`, `Front/`, `Global/`).

### Frontend: how Vue mounts into Twig
Admin pages are server-rendered Twig extending `admin/admin_base.html.twig`; Vue "islands" are mounted into them via the `vue_component()` Twig function from `symfony/ux-vue`:
```twig
<div {{ vue_component('Admin/Dashboard/Dashboard', { 'urls': urls, 'translate': translate, 'datas': datas }) }}></div>
```
The first argument is a path under `assets/vue/controllers/` (e.g. resolves to `assets/vue/controllers/Admin/Dashboard/Dashboard.vue`), and the object is the props passed in. Controllers commonly build three prop groups for a component: `urls` (generated routes), `translate` (from an `AppTranslate` subclass) and `datas`/`datas` (server-side state). Non-mounting reusable pieces live in `assets/vue/Components/...` instead of `assets/vue/controllers/...`; reusable runtime logic without a template (helpers, composables) lives in `assets/vue/Composables/...`.

Reusable Stimulus glue lives in `assets/controllers/` (currently just `vue_controller.js`, wired through `@symfony/stimulus-bundle`).

### TypeScript types (`assets/ts/`)
`assets/ts/` holds pure type-definition files only (no runtime logic), one domain per folder mirroring `assets/vue/Components/<Domain>` / `assets/vue/controllers/.../<Domain>`. Naming convention: **`<ComponentOrDomainName>.type.ts`** (PascalCase, singular `type`), and the file name must match the Vue component (or domain) it types — e.g. `Dashboard/BlockLastComment.type.ts` types `BlockLastComment.vue`. Import via the `@/ts/...` alias (`@` → `assets/`, configured in both `vite.config.ts` and `tsconfig.json`). Files that mix runtime logic with local types (e.g. `MarkdownEditor/modules/*.ts`, `MarkdownEditor/markdownEditorCore.ts`) are not part of this convention and keep plain `.ts` names. Ambient declaration files stay `.d.ts` (e.g. `Types/marked.d.ts`).

### Admin styling (theme colors)
The admin theme (color theme + dark mode) is driven by CSS variables in `assets/styles/admin/variables.scss`, exposed to Tailwind in `assets/styles/admin/theme.scss` (`@theme inline`). Use the theme utilities — `text-primary`, `text-fg` / `text-fg-muted` / `text-fg-subtle`, `border-line`, `bg-card` / `bg-surface` / `bg-surface-hover`, `bg-btn-danger`, `text-alert-danger-text`… — instead of `style="color: var(--…)"` or arbitrary values like `text-[var(--…)]`. Keep an inline style only when a custom class on the same element sets the same property: custom SCSS classes (`.card-icon`, `.btn-*`…) are unlayered and win over Tailwind utilities.

### Admin page header
Every admin page starts with `<twig:Admin:PageHeaderComponent>` (`templates/components/Admin/PageHeaderComponent.html.twig`): props `breadcrumb`, `title`, `description`, blocks `icon` (the page's sidebar icon, without size/color classes), `description` (when it contains HTML) and `actions` (buttons on the right; "New" buttons use the plus icon). Do not hand-write `h1.head-title` or `{{ breadcrumb() }}` in page templates.

Page titles: the `{% block title %}` uses the same expression as the header `title` (`admin_base.html.twig` appends ` – <site name>`). French wording: grid pages « Gestion des X », creation « Nouveau X » / « Nouvelle X », edition « Édition du X #{id} », tool pages a plain noun (« Médiathèque », « Options système »). Breadcrumb labels are translated without parameters, so the edit title key with `#{id}` stays separate from the `_h1` key used in the breadcrumb.

The sidebar (`SidebarExtension::getSidebar()`) also reads the page's `breadcrumb`: a link is active, and its submenu open, when its route is the current route or one of the breadcrumb routes. A child page (add, edit, detail) must therefore list its grid route in its breadcrumb (e.g. `'menu.index.page_title' => 'admin_menu_index'`).

### Admin buttons
Buttons use the classes of `assets/styles/admin/global/button.scss` (`btn` + a size + a variant), without extra padding or font-size utilities.
- **Form footer**: right-aligned, « Retour » (`btn-outline-dark`, a link or `history.back()`) then the main action (`btn-primary`) at the far right; « Supprimer » (`btn-danger`) on the left when present (`justify-between`). Never `flex-row-reverse`.
- **Confirmation modals**: « Annuler » (`btn-outline-dark`) then « Confirmer » at the right, in `btn-danger` when the action is destructive (delete, purge, reset), `btn-primary` otherwise. Grid confirmations take the color of the action (`'color' => 'danger'` → `btn-danger`).
- **Size**: `btn-sm` in the admin, `btn-xs` for compact toolbars and grid actions; `btn-md` only on login pages and for buttons placed next to a form input (grid search bar). Custom SCSS classes are unlayered, so Tailwind padding/flex utilities on a `.btn` have no effect: don't add them.
- **Colors**: `btn-primary` for the main action, `btn-outline-dark` for secondary actions, `btn-success` only for a positive action that does not save (preview, run, send a test), `btn-ghost-*` for icon-only action buttons in grids and lists. No `btn-secondary`.
- **Icons**: every text button of an action has an icon, `icon` in `btn-sm` buttons, `icon-sm` in `btn-xs` and icon-only (`btn-icon`) buttons.

### Tests
`tests/AppWebTestCase` (extends Symfony's `WebTestCase`, uses `FixturesTrait`) is the base class for controller/service tests and provides helpers such as `checkNoAccess()`, `createUser*()`, and domain fixture factories (e.g. `createPage()`) from `tests/Helper/Fixtures/`. It boots the client against locale `fr` and seeds default system options in `setUp()`. Test structure under `tests/` mirrors `src/` (`Controller/Admin/Content/...`, `Service/Admin/...`, etc.).

### Multilingual content vs. UI locale
Content entities (Page, Menu, Faq, Comment, ...) store per-locale data via `*Translation` child entities/tables (e.g. `PageTranslation`, `MenuElementTranslation`), while UI/interface strings live in `translations/<domain>+intl-icu.<locale>.yaml` (or `<domain>.<locale>.yaml`), one domain per feature area, loaded via Symfony's translator with an explicit `domain:` on every `trans()` call. Supported UI locales are `fr`, `en`, `es`.

Translation keys follow `<domain>.<screen>.<element>`: the domain name (file name) first, lowercase, dots between levels, underscores inside a name (`grid.search.placeholder_table`, `notification.confirm.button_ok`), English level names. A key is never also the prefix of another key (`dashboard.index.title`, not `dashboard.index` + `dashboard.index.description`). Common labels (Cancel, Delete, Yes/No…) get one key per domain, never a shared one: translations are editable from the CMS and must be changeable for a single button. `tests/Translation/TranslationKeyConventionTest.php` checks the format, the prefix rule and identical keys in fr/en/es for the domains listed in its `DOMAINS` constant (add every new compliant domain to it). Exceptions: `security` and `validators` (Symfony catalogues keyed by the English message), not checked. The `messages` domain holds keys translated without domain (stored in database or `config/cms/*.yaml`): they start with an origin prefix from the test's `MESSAGES_PREFIXES` instead of `messages` (`options_system.site_name.label`, `sidebar.page.label`, `mail.reset_password.title`); renaming one stored in database (sidebar labels, email titles) needs the fixtures and a data migration on both platforms. Keys built dynamically (`'notification.category.' . $value`) must stay lowercase. Every new key is translated in fr, en and es: `tests/Translation/TranslationCompletenessTest.php` fails on a value left with the `__` marker of `translation:extract` or with ICU parameters that differ from the French one.

### Database migrations (MySQL / MariaDB and PostgreSQL)
Both platforms are supported, so every migration exists **twice, with the same version number**, in one folder per minor version of Nathéo: `migrations/<version>/Mysql/VersionXXX.php` (namespace `DoctrineMigrations\<ns>\Mysql`) and `migrations/<version>/Postgresql/VersionXXX.php` (namespace `DoctrineMigrations\<ns>\Postgresql`), where `<version>` is the folder (`V2`, `V2.1`…) and `<ns>` the same name with `_` instead of `.` (`V2`, `V2_1`…). A new minor version gets its own folder pair, declared in `config/packages/doctrine_migrations.yaml` and in `VERSION_FOLDERS` of `tests/Doctrine/Migrations/PlatformMigrationFinderTest.php`. `App\Doctrine\Migrations\PlatformMigrationFinder` only loads the folders matching the current connection platform, and `App\Doctrine\Migrations\VersionNumberComparator` orders migrations by class name (`VersionAAAAMMJJHHMMSS`) across folders; each migration also starts with a `skipIf()` on its platform as a safety net.
- Generate each version with `php bin/console doctrine:migrations:diff --namespace='DoctrineMigrations\V2_1\Mysql'` against a MySQL `DATABASE_URL`, then the same with `--namespace='DoctrineMigrations\V2_1\Postgresql'` against a PostgreSQL one, and give both files the same version name.
- Hand-written data updates must use portable SQL or be written per platform (no `UPDATE … JOIN`, `REGEXP`, `SHA2()`, backticks on the PostgreSQL side; use `UPDATE … FROM`, `~`, `encode(sha256(…), 'hex')`, double quotes).
- Before merging, run the migrations up and down on both platforms and check `doctrine:schema:validate`.
- Reserved words used as table names are quoted in the mapping with backticks (e.g. `#[ORM\Table(name: '`user`')]`), so Doctrine quotes them on every platform; raw SQL must quote them too (`Connection::quoteSingleIdentifier()`).

### Admin security conventions
- **Grids:** `Grid.vue` renders every cell with `v-html`, so any user-entered value placed in `GridService::KEY_DATA` (titles, labels, colors, text used in confirmation messages) must be escaped server-side with `htmlspecialchars($value, ENT_QUOTES)`. Only HTML built by the service itself (badges, links, icons) stays unescaped. Colors are validated with `/^#[0-9a-fA-F]{6}$/`.
- **AJAX write actions** (save, delete, disable, switch): check a CSRF token with `AppAdminController::checkCsrf()` (header `X-CSRF-TOKEN`, sent from Vue with `withCsrf()` from `assets/vue/Composables/Admin/csrf.ts`; in grids, via the `'csrf'` key of the action).
- **Content rights** (Page, FAQ, Menu, Tag): check `ContentVoter::EDIT` / `DISABLE` / `DELETE` with `AppAdminController::checkContentAccess()`, which returns the translated `<domain>.error.forbidden` JSON error.

### API v2 (API Platform)
The v1 (`src/Controller/Api/v1`, `/api/v1`) stays as it is; the v2 is served by API Platform 5 under `/api/v2` (`config/packages/api_platform.yaml`), JSON-LD/Hydra by default, plain JSON with `Accept: application/json`, errors in `application/problem+json`. Swagger UI on `/api/v2/docs` is public while `OS_OPEN_API` is on (`ApiDocumentationListener` returns 404 otherwise).
- **Layout**: everything specific to API Platform lives in `src/Api/V2/` (`Resource/`, `Input/`, `Output/`, `Mapper/`, `State/Provider/`, `State/Processor/`, each split by `<Domain>/`, plus `Filter/`, `Exception/`, `Metadata/` and `Security/`); the business logic stays in `src/Service/Api/`, shared with the v1. `Resource/`, `Input/`, `Output/` and `Mapper/` are excluded from autowiring.
- **Resources**: DTO classes in `src/Api/V2/Resource/<Domain>/<Name>.php` (singular PascalCase, `shortName` = class name), never Doctrine entities: the Doctrine integration is disabled and only `src/Api/V2/Resource` is scanned. Operations are declared explicitly on each resource, each with `openapi: new OpenApiOperation(summary: …)` (plus `description:` for details) so Swagger shows it: the `description:` argument of the operation itself does not reach the OpenAPI. Objects embedded in a resource without their own IRI (author, tag…) go in `src/Api/V2/Output/<Domain>/`, with `#[ApiProperty(genId: false)]` on the property that holds them. Every output property carries `#[ApiProperty(required: true)]` (always present, `null` included), so the schemas list it as required. An action on a resource is itself a resource (`POST /page_reads` with `{"page": "<IRI>"}`), never an RPC route (`/pages/{id}/read`).
- **Mappers**: the entity → resource conversion goes in `src/Api/V2/Mapper/<Domain>/<Name>Mapper.php` (`map()`, `mapSummary()` for collections), instantiated by the provider with the locale; no service, no query: data that needs a service (rendered Markdown…) is computed by the service and passed to `map()`. Collection queries fetch-join what the summary reads (no query per item).
- **State**: one provider per resource read (`src/Api/V2/State/Provider/<Domain>/<Name>Provider.php`) and one processor per write operation (`src/Api/V2/State/Processor/<Domain>/<Operation><Name>Processor.php`, e.g. `CreatePageReadProcessor`). They only map data and call the services of `src/Service/Api/`, where the logic stays. A missing or unpublished resource throws `App\Api\V2\Exception\NotFoundException` with a translated message. A resource referenced by its IRI in a request body is resolved by its provider with `api_denormalize` in the context: return only what identifies it (summary), not the full resource. A resource never links to an unpublished one (menu element of a draft page hidden, blocks without content in the locale not listed).
- **Format**: camelCase fields, ISO 8601 dates, enum values as lowercase names (`two_block`, `header`), `null` values kept. Each operation sets its serialization group (`<resource>:read` for an item, `<resource>:list` for a collection) so an embedded resource only shows its summary next to its IRI. A resource has a single item operation (`/<resources>/{id}`): a lookup (by slug, landing page…) is an exact filter of the collection (`GET /pages?slug=…`, 0 or 1 member), never a second item route. A polymorphic field is always an object (one `Output` class per case, e.g. `TextContent`, `FaqContent`), never a mix of scalar and object.
- **Parameters**: declared as `QueryParameter` / `HeaderParameter` on each operation. A parameter with a fixed list of values uses a filter of `src/Api/V2/Filter/` (`FilterInterface` + `JsonSchemaFilterInterface`) that builds its schema from the source, never a copied list: enum cases (`PageCategoryFilter`, `MenuPositionFilter`, whose static `getValue()` returns the enum) or `app.supported_locales` (`LocaleFilter`). Likewise, a resource or input property holding enum slugs declares `extraProperties: [EnumSlugPropertyMetadataFactory::EXTRA_PROPERTY => <Enum>::class]` (`src/Api/V2/Metadata/`), which builds its schema from the enum cases. Each parameter sets `property` (resource property it filters) so it shows in the Hydra `search` template. Filters are exact (slug, id), not label searches. Content resources take `locale` and the `Accept-Language` header. `ApiLocaleListener` resolves the language once per request (`AppApiService::resolveLocale()`: supported `locale` first, then the first supported language of the header, then the site default), applies it to the translator (contents and errors) and answers with `Content-Language` and `Vary: Accept-Language`; providers read it with `getContentLocale()`. An invalid query parameter returns a 422.
- **Writes**: input DTO validated with Symfony constraints (`input:` class in `src/Api/V2/Input/<Domain>/<Operation><Name>Input.php`), PATCH in `application/merge-patch+json`, no CSRF (stateless token).
- **Errors**: problem+json, `title` translated by status (`api_errors.title.<status>`, `TranslatedErrorProvider`), `detail` translated by the code that throws. Each operation lists the errors it can return with `errors:` (`NotFoundException::class`, `ValidationException::class`), so the OpenAPI documents them; `errors: []` when none.
- **Security**: authentication stays on the `api` firewall (`ApiAuthenticator`, Bearer token, IP filter, `OS_OPEN_API`); each operation declares its role with `security: "is_granted('ROLE_READ_API')"` (`ROLE_WRITE_API`, `ROLE_ADMIN_API`). A back-office user acting through the API is given by the `X-User-Token` header, declared on the operation with `new UserTokenHeader()` (`documentedAsRequired: true` when the operation needs it) and read with `UserTokenResolver::getUser()`: missing header → `null`, invalid or expired token → `ForbiddenException` (403), also thrown when the user lacks the required rights. A write operation that checks this user before loading the resource sets `read: false` and loads it in its processor, so a forbidden caller gets 403 rather than 404. Content rights (`ContentVoter`) are checked on the entity in the processor or the service, not on the DTO.
- **Tests**: `tests/Api/V2/` with `AppApiV2TestCase` (fixtures of `AppWebTestCase`, `requestV2()`, no `Accept-Language` unless given): every successful read checks its body with `assertMatchesResourceItemJsonSchema()` / `assertMatchesResourceCollectionJsonSchema()`, built like API Platform's assertions on the `WebTestCase` client (do not `use ApiTestAssertionsTrait`, which duplicates the static client of `BrowserKitAssertionsTrait`). `tests/Api/V2/Resource/` holds resources loaded in the test env only.

## Code style

Inline (`//`) comments must stay short and concise: 2 lines maximum, and only when they explain the *why*, not narrate the *what* — if a comment only restates what the code already says, remove it. Prefer a clearer name or a shorter comment over a long explanation. Neither rule applies to method-level PHPDoc blocks (`@param`, `@return`, `@throws`), which stay descriptive as elsewhere in the codebase.

Every comment (inline, PHPDoc, Twig, Vue, SCSS) is short, concise and only talks about the element it is attached to (function, constant, block): no instruction for future changes, no context about other code, and never a reference to an issue, an Epic, a PR or a work batch (`#437`, « lot 2 », « Epic »…). The history belongs in git and on GitHub.

## GitHub workflow

The issue → branch → PR workflow, branch/title conventions and label taxonomy are defined in `CONTRIBUTING.md` (imported below) and enforced by `.github/workflows/`. Never commit on `master`. An Epic has its own branch `epic/E-<n>-<slug>`: the branches of its child issues start from it and their PRs target it (`gh pr create --base epic/E-<n>-<slug>`); only the Epic branch is merged into `master`. Security vulnerabilities never go into public issues: follow `SECURITY.md`.

When creating an issue with `gh issue create`, the YAML forms are not applied: write the body with the same `### <Field>` headings as the matching form in `.github/ISSUE_TEMPLATE/` (so the triage workflow can label it), and use the `[BUG] ` / `[AUDIT] ` / `[FEATURE] ` / `[CLEAN CODE] ` / `[EPIC] ` title prefix (the triage workflow adds the issue number to `[BUG]` titles only; other types keep their title without number). The `Epic` label comes from the form, so add it with `--label Epic`. Attach an Epic's child issues as sub-issues (`gh api -X POST repos/<owner>/<repo>/issues/<epic>/sub_issues -F sub_issue_id=<child issue id>`, the `id` field, not the number) instead of checkboxes.

@../CONTRIBUTING.md
