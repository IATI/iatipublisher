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

### Tests

`vendor/bin/phpunit` was not run this pass. The local `.env` `DB_HOST`
points at a remote production Postgres cluster, and the tests use
`RefreshDatabase`, so they need a local test database first. No backend
code changed in this pass.
