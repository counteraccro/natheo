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

Directory shape under `src/` mirrors the domain, duplicated across `Controller/`, `Service/`, `Repository/`, `Entity/`, `Enum/`, `Utils/Translate/`, generally as `Admin/Content/<Domain>/…`, `Admin/System/…`, `Admin/Tools/…` (plus parallel `Api/` and `Front/` trees for those layers). When adding a feature, expect to touch the matching subfolder in several of these trees at once.

`Utils/Translate/<Domain>/<Domain>Translate.php` classes (extending `AppTranslate`) centralize building the translation arrays passed to Twig/Vue — controllers call these instead of calling the translator directly for anything non-trivial.

`src/Overwrite/` holds controllers that override/extend a bundle or base controller for demo/override behavior (e.g. read-only demo mode).

### Frontend: how Vue mounts into Twig
Admin pages are server-rendered Twig extending `admin/admin_base.html.twig`; Vue "islands" are mounted into them via the `vue_component()` Twig function from `symfony/ux-vue`:
```twig
<div {{ vue_component('Admin/Dashboard/Dashboard', { 'urls': urls, 'translate': translate, 'datas': datas }) }}></div>
```
The first argument is a path under `assets/vue/controllers/` (e.g. resolves to `assets/vue/controllers/Admin/Dashboard/Dashboard.vue`), and the object is the props passed in. Controllers commonly build three prop groups for a component: `urls` (generated routes), `translate` (from an `AppTranslate` subclass) and `datas`/`datas` (server-side state). Non-mounting reusable pieces live in `assets/vue/Components/...` instead of `assets/vue/controllers/...`.

Reusable Stimulus glue lives in `assets/controllers/` (currently just `vue_controller.js`, wired through `@symfony/stimulus-bundle`).

### TypeScript types (`assets/ts/`)
`assets/ts/` holds pure type-definition files only (no runtime logic), one domain per folder mirroring `assets/vue/Components/<Domain>` / `assets/vue/controllers/.../<Domain>`. Naming convention: **`<ComponentOrDomainName>.type.ts`** (PascalCase, singular `type`), and the file name must match the Vue component (or domain) it types — e.g. `Dashboard/BlockLastComment.type.ts` types `BlockLastComment.vue`. Import via the `@/ts/...` alias (`@` → `assets/`, configured in both `vite.config.ts` and `tsconfig.json`). Files that mix runtime logic with local types (e.g. `MarkdownEditor/modules/*.ts`, `MarkdownEditor/markdownEditorCore.ts`) are not part of this convention and keep plain `.ts` names. Ambient declaration files stay `.d.ts` (e.g. `Types/marked.d.ts`).

### Tests
`tests/AppWebTestCase` (extends Symfony's `WebTestCase`, uses `FixturesTrait`) is the base class for controller/service tests and provides helpers such as `checkNoAccess()`, `createUser*()`, and domain fixture factories (e.g. `createPage()`) from `tests/Helper/Fixtures/`. It boots the client against locale `fr` and seeds default system options in `setUp()`. Test structure under `tests/` mirrors `src/` (`Controller/Admin/Content/...`, `Service/Admin/...`, etc.).

### Multilingual content vs. UI locale
Content entities (Page, Menu, Faq, Comment, ...) store per-locale data via `*Translation` child entities/tables (e.g. `PageTranslation`, `MenuElementTranslation`), while UI/interface strings live in `translations/<domain>+intl-icu.<locale>.yaml` (or `<domain>.<locale>.yaml`), one domain per feature area, loaded via Symfony's translator with an explicit `domain:` on every `trans()` call. Supported UI locales are `fr`, `en`, `es`.
