# Frontend / Node upgrade log

This file tracks the Node.js LTS upgrade chain (16 → 18 → 20 → 22 → 24), one
LTS per section, together with the build toolchain changes each step forces.
It was run with the `node-version-upgrade` skill, adapted to this repo, which
uses npm with `package-lock.json` and `overrides` and builds its frontend in
the Dockerfiles' `frontend` stage.

## Node v16 → v18 (2026-10-28)

The toolchain needed no changes for Node 18. laravel-mix ^6 / webpack
5.95.0 builds byte-identical bundles on 18.20.8, and nothing in the
toolchain broke. This pass only moves the runtime pins.

### package.json / runtime pins

| Item | Before | After | Why |
|---|---|---|---|
| `.nvmrc` | (none) | `18` | new: pins the local Node major for `nvm use` |
| `engines.node` | `v16.17.1` | `>=18` | the exact 16 pin raised `EBADENGINE` on every `npm install` under 18 |
| `engines.npm` | `8.15.0` | `>=10` | Node 18 ships npm 10.8.2 |
| `Dockerfile.{develop,staging,production}` | `node:16@sha256:c94b82f9…` | `node:18@sha256:c6ae79e3…` | the `frontend` stage runs `npm install && npm run prod` at deploy time |
| `overrides.webpack` | `5.95.0` | `5.95.0` (kept) | unchanged this pass |

Resolved versions: `laravel-mix@6.0.49`, `webpack@5.95.0`,
`sass-loader@11.1.1`, `sass@1.54.7`, `vue-loader@17.4.2`,
`resolve-url-loader@5.0.0`, `ts-loader@9.6.2`, `postcss@8.5.26`.

`package-lock.json`: npm 10 drops the root `"name"` field because
`package.json` has none. It also syncs the root `engines` block.

### Build / code fixes

- None needed. `npm run prod` was green on the first run. Its only warning
  is the existing `"extendDefaultPlugins" utility is deprecated`, the same
  as the v16 baseline.
- `docker build --target frontend -f Dockerfile.develop .` on `node:18`
  ends with `✔ Compiled Successfully`.

### Browser smoke

The user checked locally (`php artisan serve`) that the app renders on the
Node 18 build.

### npm audit

Before: 75 advisory×root lines (critical 2, high 24, moderate 30, low 19).
After: 75. NEW: 0, cleared: 0. The Node bump alone moves no packages.
Toolchain fixes come in the follow-up audit commit.

#### Toolchain audit fixes (follow-up commit)

Each change was made on its own and checked before the next one: clean
install with no new peer warnings, a single resolved copy (`npm ls`), a
production build with no new warnings, bundle sizes within ±0.2%, and no
new advisories.

| Change | Rung | Cleared |
|---|---|---|
| `npm update proxy-addr` → 2.0.8 | in-range update | GHSA-jqcg-44mw-7w3h (critical) |
| `npm update shell-quote` → 1.12.0 | in-range update | GHSA-pqg4-j6r4-53mv (critical) |
| `npm update` body-parser 1.20.9, launch-editor 2.14.2, node-forge 1.4.2, compression 1.8.2, browserslist 4.29.5, fast-uri 3.1.9, source-map-js 1.2.2, brace-expansion 1.1.21 | in-range update | high: GHSA-27r3, -587h, -86w9, -vc2v, -73wf, -c83g, -58mr, -qw65, -68fv, -6j4f, -qhr7 |
| `npm update` colord 2.10.0, pbkdf2 3.1.7, postcss-selector-parser 6.1.5, qs (one copy) | in-range update | moderate |
| `webpackbar` `^7.0.0` devDependency + `overrides` (`$webpackbar`) | override | none (prerequisite) |
| `overrides.webpack` `5.95.0` → `5.106.2` | override | GHSA-38r7-794h-5758, GHSA-8fgc-7cc6-rx7x (buildHttp SSRF) |
| `overrides.express.qs` `^6.16.2` | scoped override | GHSA-4mjr-xmp4-gh2g, GHSA-6pq8-jhm2-2vcw |

- **webpackbar:** mix 6 nests `webpackbar@5.0.2`. On webpack newer than
  5.95, its options fail the `ProgressPlugin` schema:
  `[webpack-cli] Invalid options object. Progress Plugin ... unknown property 'name'`.
  Version 7 avoids that, and the override dedupes mix's nested copy.
- **webpack pin:** 5.106.2 is the last release that ships
  `lib/SizeFormatHelpers.js`, which laravel-mix's `BuildOutputPlugin` still
  `require()`s; 5.107.0 removes it. Keep the pin until laravel-mix stops
  requiring that path or the build moves off mix.
- **qs override:** express 4.22.2 declares `qs ~6.15.1`, and 6.16.2 is the
  same major. express only runs inside the `mix watch --hot` dev server,
  which was checked to start and serve `app.js`.

The browser smoke test passed again after the fixes. Audit: **75 → 21**
(critical 2 → 0, high 24 → 8, moderate 30 → 10, low 19 → 3).

Deferred, `side=dev`:
- `webpack-dev-server@4.15.2` (6 moderate), `webpack-dev-middleware`
  (high) and `uuid@8` (moderate, via sockjs and node-notifier). The fixes
  need webpack-dev-server 5, a cross-major change against the API mix 6
  calls for `mix watch --hot`. They are dev-server only and never used by
  `npm run prod` or Docker.
- `serialize-javascript@6` via `laravel-mix-compress` →
  `compression-webpack-plugin@10`. The fix (7.x) requires Node ≥ 20, so
  it is retried in the Node 20 pass.
- `elliptic@6.6.1`: no fixed release exists.

Handed off to `/frontend-deps-upgrade` (runtime libraries):
`follow-redirects` (via `axios` and `http-proxy-middleware`, 2 high),
`@vue/server-renderer` (via `vue`, high), `moment` (moderate) and the
direct `serialize-javascript` dependency.

### Tests

`vendor/bin/phpunit` was not run this pass. The local `.env` `DB_HOST`
points at a remote production Postgres cluster, and the tests use
`RefreshDatabase`, so they need a local test database first. No backend
code changed in this pass.

## Node v18 → v20 (2026-10-28)

The toolchain needed no changes for Node 20. `npm install` on 20.20.2 /
npm 10.8.2 resolves the same tree as the v18 lockfile, and the build is
green on the first run.

### package.json / runtime pins

| Item | Before | After | Why |
|---|---|---|---|
| `.nvmrc` | `18` | `20` | next LTS |
| `engines.node` | `>=18` | `>=20` | tracks the LTS this pass lands |
| `Dockerfile.{develop,staging,production}` | `node:18@sha256:c6ae79e3…` | `node:20@sha256:8f693eaa…` | the deploy-time `frontend` stage |

Resolved versions are unchanged from the v18 pass: `laravel-mix@6.0.49`,
`webpack@5.106.2` (override), `webpackbar@7.0.0`, `sass-loader@11.1.1`,
`vue-loader@17.4.2`, `postcss@8.5.26`.

### Build / code fixes

- None needed. The production build's only warning is the existing
  `extendDefaultPlugins` deprecation. The tracked `public/` bundles are
  byte-identical to the v18 build.
- `docker build --target frontend` on `node:20` ends with
  `✔ Compiled Successfully`.

### Browser smoke

The user checked locally that the app renders on the Node 20 build.

### npm audit

Before: 21 (critical 0, high 8, moderate 10, low 3). After: 21. NEW: 0.
`serialize-javascript@7`, deferred in the v18 pass because it requires
Node ≥ 20, is retried in this pass's audit-fix commit.

### Tests

`vendor/bin/phpunit` was not run. The local `.env` still points at the
remote production Postgres (see v16 → v18), and no backend code changed.
