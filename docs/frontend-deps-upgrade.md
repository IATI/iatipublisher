# Frontend runtime dependency upgrades

## In-range audit fix — vue v3.2.38 → v3.5.44 (2026-10-28)

`npm audit fix` (in-range only, no `--force`) on Node 24, following the
Node v22 → v24 bump. `package.json` unchanged; lockfile only.

### package-lock.json

| Package | Before | After | Why |
|---|---|---|---|
| vue (+ @vue/*) | 3.2.38 | 3.5.44 | in-range update pulled by audit fix |
| follow-redirects | 1.16.0 | 1.16.2 | advisory fix |
| csstype | 2.6.20 | 3.2.3 | transitive (vue 3.5) |
| magic-string | 0.25.9 | 0.30.21 | transitive (vue 3.5 compiler-sfc) |

21 packages moved, 2 added, 7 removed. No other major-version jumps.

### Migration guide

https://github.com/vuejs/core/blob/main/changelogs/CHANGELOG-3.3.md,
CHANGELOG-3.4.md, CHANGELOG-3.5.md. Checked: reactivity transform removal,
`v-is` / `@vnode-*` removal, props destructure. None used in this repo.

### Code / template fixes

Vue 3.5 types the `v-for` index as `string | number` in generated render code,
so `ts-loader` failed the build (34 `[tsl] ERROR`s):

- 14 `.vue` files under `resources/assets/js/views/{activity,organisation}/`:
  `k !== arr.length - 1` → `Number(k) !== arr.length - 1`.
- `resources/assets/js/views/activity/partials/ActivityElement.vue:314`: real bug.
  `item.narrative - 1` → `item.narrative.length - 1`, so the last narrative no
  longer gets `mb-0`.
- `resources/assets/js/views/import/ActivityXlsUpload.vue`: `loaderText` read
  `props` before `defineProps` (TDZ under 3.5's compiled `<script setup>`). It
  now comes after `defineProps`.

### npm audit

18 → 14 (high 3, moderate 6, low 5). Remaining: serialize-javascript
(`<=7.1.2`), elliptic (no fix), uuid / webpack-dev-middleware (dev server,
need mix → Vite). Handled in later passes.

### Browser smoke

User confirmed: login page with a clean console, dashboard charts and a filter,
activity and organisation detail narrative spacing, the XLS import page loader,
and an element form save plus validation errors.

`npm run prod` green (no new warnings), browser smoke passed. `vendor/bin/phpunit`
not run: `.env` DB_HOST points at prod, and there is no local test DB.

## serialize-javascript v6.0.2 → removed (2026-10-28)

Unused direct dependency. It was added in the squash commit `ef2b383e` (a
Snyk/dependabot batch), presumably to hoist a patched copy for the old terser.
No package in the tree requires it any more: `terser-webpack-plugin` 5.6.1
bundles its own copy into `dist/serialize-javascript.js` and lists it only as
a devDependency. Nothing in `resources/` or `webpack.mix.js` imports it.

### package.json

| Package | Before | After | Why |
|---|---|---|---|
| serialize-javascript | ^6.0.2 | — | unused; 3 advisories on 6.x |

`randombytes` stays in the tree as a dev-only dependency of crypto-browserify.

### Code / template fixes

None.

### npm audit

14 → 13 (high 2). Cleared GHSA-5c6j-r48x-rmvq (RCE), GHSA-qj8w-gfj5-8c6v
(DoS) and GHSA-m5mm-p8h7-m29v (XSS). The copy bundled inside terser runs only
at build time and `npm audit` can't see it. It's left to terser's maintainers.

### Browser smoke

Not re-run: the rebuilt `app.js` / `vendor.js` hashes in `mix-manifest.json`
are identical to the previous commit, so the served assets are the ones already
smoke-tested.

`npm run prod` green (no new warnings). `vendor/bin/phpunit` not run (prod DB_HOST).

## vue-apexcharts v1.6.2 → removed (2026-10-28)

Unused direct dependency. `vue-apexcharts` is the Vue 2 wrapper. The app is
Vue 3 and registers `vue3-apexcharts` (`resources/assets/js/app.ts:84`), and
nothing imports `vue-apexcharts`.

| Package | Before | After | Why |
|---|---|---|---|
| vue-apexcharts | ^1.6.2 | — | unused Vue 2 wrapper |

No code fixes. npm audit unchanged (13). The rebuilt `app.js` / `vendor.js`
hashes are identical to the previous commit, so the browser smoke wasn't re-run.
`npm run prod` green (no new warnings). `vendor/bin/phpunit` not run (prod DB_HOST).
