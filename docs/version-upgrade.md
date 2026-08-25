# Laravel version upgrade log

This file tracks the `laravel/framework` major-version upgrade chain, one
major per section, run via the repo's `laravel-version-upgrade` skill.

Trigger: `composer audit` flagged 4 `laravel/framework` advisories (signed
URL path confusion, CRLF injection in the default email rule) with no fix
available in the v9 line — the earliest patched releases are v12.60.0 /
v13.10.0. Target: v13.x.

## laravel/framework v9.52.22 -> v10.50.3 (2026-08-25)

`roave/security-advisories` (dev-latest metapackage) was temporarily removed
from `require-dev` for the duration of this chain: its conflict rules reject
*any* `illuminate/*` version below the final patched release, which makes
every intermediate major (v10, v11, v12<12.60) unresolvable while it's
present. It will be restored once the chain reaches v13.10+.

### composer.json

| Package | Before | After | Why |
|---|---|---|---|
| `php` | `^8.0` | `^8.1` | Laravel 10 requires PHP >=8.1. System PHP is already 8.4.23, no runtime switch needed. |
| `laravel/framework` | `^9.0` | `^10.0` | target of this pass |
| `arcanedev/log-viewer` | `9` (exact pin) | `^10.0` | pin capped `illuminate/contracts` at `^9.0` |
| `laravel/sanctum` | `^2.14` | `^3.2` | `^2.x` line caps `illuminate/contracts` at `^9.0` |
| `fruitcake/laravel-cors` | `^2.0.5` | removed | abandoned package; Laravel 9+ ships the same middleware natively as `Illuminate\Http\Middleware\HandleCors`. Zero other call sites. |
| `sentry/sentry-laravel` | `3.1.3` (exact pin) | `^4.0` | pin capped `illuminate/support` at `^9.0` |
| `laravel/ui` | `^3.4` | `^4.0` | `^3.x` line caps `illuminate/validation` at `^9.0` |
| `spatie/laravel-ignition` | `^1.0` | `^2.0` | `^1.x` line caps `illuminate/support` at `<=9.27` |

Resolved versions after `composer update --with-all-dependencies`:
`laravel/framework v10.50.3`, `laravel/sanctum v3.3.3`, `laravel/ui v4.6.3`,
`sentry/sentry-laravel 4.27.0`, `spatie/laravel-ignition 2.9.1`,
`arcanedev/log-viewer 10.1.1`.

`composer audit` after this pass: 3 advisories remain, all
`laravel/framework` (need >=12.60.0 / >=13.10.0), expected until later
passes in this chain.

### Code fixes (post-install)

- `app/Http/Kernel.php:20` — `use Fruitcake\Cors\HandleCors;` ->
  `use Illuminate\Http\Middleware\HandleCors;`, matching the composer.json
  removal above.
- `database/factories/IATI/Models/User/UserFactory.php` — dropped the
  `'password' => Hash::make('password')` field (and the now-unused `Hash`
  import). Pre-existing bug, not caused by this bump: migration
  `2026_01_27_044403_drop_depreciated_columns_from_user_table.php` (merged
  many months before this pass) dropped `users.password` outright as part
  of the app's move to SSO-only auth, but the factory (last touched 2022)
  was never updated. This alone was causing 207 of 232 real test failures
  once the test DB was pointed at a real, migrated schema (see below).
- `app/IATI/Traits/FillDefaultValuesTrait.php:207` — `getDefaultValuesFromSettings($defaultFieldValues)`
  now coalesces to `$defaultFieldValues ?? []` before the call.
  `getDefaultValuesFromActivity()` returns `mixed` (nullable) but
  `fillMissingDefaultsFromSettings()` is typed `array $defaultFieldValues`.
  Pre-existing bug from commit `5ff95ba2` (merged to `main` immediately
  before this branch), unrelated to this bump — surfaced as a `TypeError`
  in `ContactInfoTest::test_validation_pass_for_contact_info`.
- `public/mix-manifest.json` — resolved unresolved `git stash pop` conflict
  markers (`<<<<<<< Updated upstream` / `<<<<<<< HEAD` / `>>>>>>>
  1969-multiple-orgs-support` / `>>>>>>> Stashed changes`) that had been
  committed as-is in `dc3a607f`, making the file invalid JSON. Kept the
  `/js/app.js` hash that actually matches `public/js/app.js`'s real MD5
  (`91f9b4cc2b5d143466261f4c013f163b`). Pre-existing, unrelated to this
  bump — was causing every view-rendering test to fail with `Unable to
  locate Mix file`.
- Deleted `tests/Feature/AuthenticationTest.php`, `RegisterTest.php`,
  `ResetTest.php` (local username/password login, registration, and
  password-reset flows) and trimmed the now-nonexistent `web.join`,
  `web.register`, `web.password.email`, `web.password.confirm`,
  `web.iati.register` cases out of
  `tests/Feature/PageLoad/GuestPageLoadTest.php`'s data provider. All of
  these exercised local-auth routes that no longer exist post-SSO
  migration (confirmed obsolete by the user, not just inferred).

### Test environment fix (this session only, not part of the code fix)

`vendor/bin/phpunit` couldn't run at all going in: `.env`'s `DB_HOST` points
at a remote managed Postgres (`yi-prod-postgres-do-user-...`) that doesn't
have an `iati_db_test` database. Created `.env.testing` (gitignored, not
committed) pointing at the local Docker Postgres container already running
on this machine (`postgres-14`, port 5432), which already had a
correctly-named `iati_db_test` database — just needed a fresh
`php artisan migrate:fresh` run against it once to pick up all current
migrations.

`vendor/bin/phpunit` green after this pass: **606 tests, 964 assertions, 0
failures, 0 errors** (4 pre-existing "No tests found" warnings on abstract
base test classes — `Tests\Unit\Csv\CsvBaseTest`, `Tests\Unit\ImportBaseTest`,
`Tests\Unit\Xml\XmlBaseTest`, `Tests\Feature\Element\ElementCompleteTest` —
unrelated to this bump, not investigated further).

Deferred: `Model::preventLazyLoading()` N+1 smoke-test pass (per the skill's
step 5 guidance) was not run this pass — flagged for a later pass or a
separate audit, not blocking this bump.
