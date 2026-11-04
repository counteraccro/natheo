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

`src/Overwrite/` holds controllers that override/extend a bundle or base controller for demo/override behavior (e.g. read-only demo mode).

### Deprecated `Utils` folders (`src/Utils/`, `assets/utils/`)
Both are leftovers of an earlier architecture and are being removed gradually:
- **Never create a new file** in `src/Utils/` or `assets/utils/`.
- **Migrate as you go:** when an issue touches a `Utils` file, move it to the matching folder by role (`src/Service/...` for services, `src/Factory/<Layer>/<Domain>/` for `*Factory`, `src/Populate/<Layer>/<Domain>/` for `*Populate`, `src/Formatter/<Layer>/<Domain>/` for `*Formater` — all three excluded from autowiring in `config/services.yaml` —, `src/Entity/Trait/`, `src/Enum/...`, etc. for PHP; `assets/vue/Composables/` for front runtime helpers) and update its usages in the same PR. Remaining files are tracked in a dedicated `[CLEAN CODE]` issue.
- **Constants become enums:** constant holder classes (`*Const`, `*Key`, `FlashKey`, ...) are converted to backed enums under `src/Enum/` when migrated.
- Reusable traits go in `src/Entity/Trait/` (entity helpers) or `src/Repository/Trait/` (query helpers).

### Frontend: how Vue mounts into Twig
Admin pages are server-rendered Twig extending `admin/admin_base.html.twig`; Vue "islands" are mounted into them via the `vue_component()` Twig function from `symfony/ux-vue`:
```twig
<div {{ vue_component('Admin/Dashboard/Dashboard', { 'urls': urls, 'translate': translate, 'datas': datas }) }}></div>
```
The first argument is a path under `assets/vue/controllers/` (e.g. resolves to `assets/vue/controllers/Admin/Dashboard/Dashboard.vue`), and the object is the props passed in. Controllers commonly build three prop groups for a component: `urls` (generated routes), `translate` (from an `AppTranslate` subclass) and `datas`/`datas` (server-side state). Non-mounting reusable pieces live in `assets/vue/Components/...` instead of `assets/vue/controllers/...`; reusable runtime logic without a template (helpers, composables) lives in `assets/vue/Composables/...`.

Reusable Stimulus glue lives in `assets/controllers/` (currently just `vue_controller.js`, wired through `@symfony/stimulus-bundle`).

### TypeScript types (`assets/ts/`)
`assets/ts/` holds pure type-definition files only (no runtime logic), one domain per folder mirroring `assets/vue/Components/<Domain>` / `assets/vue/controllers/.../<Domain>`. Naming convention: **`<ComponentOrDomainName>.type.ts`** (PascalCase, singular `type`), and the file name must match the Vue component (or domain) it types — e.g. `Dashboard/BlockLastComment.type.ts` types `BlockLastComment.vue`. Import via the `@/ts/...` alias (`@` → `assets/`, configured in both `vite.config.ts` and `tsconfig.json`). Files that mix runtime logic with local types (e.g. `MarkdownEditor/modules/*.ts`, `MarkdownEditor/markdownEditorCore.ts`) are not part of this convention and keep plain `.ts` names. Ambient declaration files stay `.d.ts` (e.g. `Types/marked.d.ts`).

### Tests
`tests/AppWebTestCase` (extends Symfony's `WebTestCase`, uses `FixturesTrait`) is the base class for controller/service tests and provides helpers such as `checkNoAccess()`, `createUser*()`, and domain fixture factories (e.g. `createPage()`) from `tests/Helper/Fixtures/`. It boots the client against locale `fr` and seeds default system options in `setUp()`. Test structure under `tests/` mirrors `src/` (`Controller/Admin/Content/...`, `Service/Admin/...`, etc.).

### Multilingual content vs. UI locale
Content entities (Page, Menu, Faq, Comment, ...) store per-locale data via `*Translation` child entities/tables (e.g. `PageTranslation`, `MenuElementTranslation`), while UI/interface strings live in `translations/<domain>+intl-icu.<locale>.yaml` (or `<domain>.<locale>.yaml`), one domain per feature area, loaded via Symfony's translator with an explicit `domain:` on every `trans()` call. Supported UI locales are `fr`, `en`, `es`.

### Database migrations (MySQL / MariaDB and PostgreSQL)
Both platforms are supported, so every migration exists **twice, with the same version number**: `migrations/V2/Mysql/VersionXXX.php` (namespace `DoctrineMigrations\V2\Mysql`) and `migrations/V2/Postgresql/VersionXXX.php` (namespace `DoctrineMigrations\V2\Postgresql`). `App\Doctrine\Migrations\PlatformMigrationFinder` (wired in `config/packages/doctrine_migrations.yaml`) only loads the folder matching the current connection platform; each migration also starts with a `skipIf()` on its platform as a safety net.
- Generate each version with `php bin/console doctrine:migrations:diff --namespace='DoctrineMigrations\V2\Mysql'` against a MySQL `DATABASE_URL`, then the same with `--namespace='DoctrineMigrations\V2\Postgresql'` against a PostgreSQL one, and give both files the same version name.
- Hand-written data updates must use portable SQL or be written per platform (no `UPDATE … JOIN`, `REGEXP`, `SHA2()`, backticks on the PostgreSQL side; use `UPDATE … FROM`, `~`, `encode(sha256(…), 'hex')`, double quotes).
- Before merging, run the migrations up and down on both platforms and check `doctrine:schema:validate`.
- Reserved words used as table names are quoted in the mapping with backticks (e.g. `#[ORM\Table(name: '`user`')]`), so Doctrine quotes them on every platform; raw SQL must quote them too (`Connection::quoteSingleIdentifier()`).

### Admin security conventions
- **Grids:** `Grid.vue` renders every cell with `v-html`, so any user-entered value placed in `GridService::KEY_DATA` (titles, labels, colors, text used in confirmation messages) must be escaped server-side with `htmlspecialchars($value, ENT_QUOTES)`. Only HTML built by the service itself (badges, links, icons) stays unescaped. Colors are validated with `/^#[0-9a-fA-F]{6}$/`.
- **AJAX write actions** (save, delete, disable, switch): check a CSRF token with `AppAdminController::checkCsrf()` (header `X-CSRF-TOKEN`, sent from Vue with `withCsrf()` from `assets/vue/Composables/Admin/csrf.ts`; in grids, via the `'csrf'` key of the action).
- **Content rights** (Page, FAQ, Menu, Tag): check `ContentVoter::EDIT` / `DISABLE` / `DELETE` with `AppAdminController::checkContentAccess()`, which returns the translated `<domain>.error.forbidden` JSON error.

## Code style

Inline (`//`) comments must stay short and concise: 2 lines maximum, and only when they explain the *why*, not narrate the *what* — if a comment only restates what the code already says, remove it. Prefer a clearer name or a shorter comment over a long explanation. Neither rule applies to method-level PHPDoc blocks (`@param`, `@return`, `@throws`), which stay descriptive as elsewhere in the codebase.

## GitHub workflow

The issue → branch → PR workflow, branch/title conventions and label taxonomy are defined in `CONTRIBUTING.md` (imported below) and enforced by `.github/workflows/`. Never commit on `master`. Security vulnerabilities never go into public issues: follow `SECURITY.md`.

When creating an issue with `gh issue create`, the YAML forms are not applied: write the body with the same `### <Field>` headings as the matching form in `.github/ISSUE_TEMPLATE/` (so the triage workflow can label it), and use the `[BUG] ` / `[AUDIT] ` / `[FEATURE] ` / `[CLEAN CODE] ` / `[EPIC] ` title prefix (the triage workflow adds the issue number to `[BUG]` titles only; other types keep their title without number).

@../CONTRIBUTING.md
