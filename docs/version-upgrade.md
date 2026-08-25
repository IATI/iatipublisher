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

## laravel/framework v10.50.3 -> v11.56.0 (2026-08-25)

### composer.json

| Package | Before | After | Why |
|---|---|---|---|
| `php` | `^8.1` | `^8.2` | Laravel 11 requires PHP >=8.2. System PHP is already 8.4.23. |
| `laravel/framework` | `^10.0` | `^11.0` | target of this pass |
| `laravel/sanctum` | `^3.2` | `^4.0` | `^3.x` line caps `illuminate/contracts` at `^10.0` |
| `nunomaduro/collision` | `^6.1` | `^8.0` | Laravel 11 pairs with collision v8 (its own compatibility table); `^6.x`/`^7.x` cap `symfony/console` below what Laravel 11 needs |
| `phpunit/phpunit` | `^9.5.10` | `^11.5.50` | not a direct Laravel requirement — the real forcing function was `nunomaduro/collision` v8's own `conflicts: phpunit/phpunit <11.5.50`, per the skill's own known-package-replacements note |
| `symfony/psr-http-message-bridge` | `^2.1` | `^2.1\|^7.0` | `^2.x` line caps below what Laravel 11's Symfony 7 components need; kept `^2.1` as an alternative since nothing forced it out |

Resolved versions after `composer update --with-all-dependencies`:
`laravel/framework v11.56.0`, `laravel/sanctum v4.3.3`,
`nunomaduro/collision v8.9.5`, `phpunit/phpunit 11.5.56`,
`symfony/psr-http-message-bridge v7.4.8`, `spatie/laravel-ignition 2.12.0`,
`arcanedev/log-viewer 10.1.0`.

`composer audit` after this pass: same 3 `laravel/framework` advisories as
before (need >=12.60.0 / >=13.10.0), expected until later passes.

### phpunit.xml schema migration

Ran `vendor/bin/phpunit --migrate-configuration` (PHPUnit 10+ dropped the old
schema). Picked up automatically: `<coverage><include>` -> `<source><include>`,
`cacheDirectory=".phpunit.cache"` added, `backupStaticAttributes` ->
`backupStaticProperties`. No manual re-application needed since this repo
only tracks a single `phpunit.xml` (no separate `.dist` template).

### Code fixes (post-install)

- `tests/Feature/PageLoad/ActivityPageLoadTest.php` and
  `GuestPageLoadTest.php` — their `@dataProvider` target methods
  (`activityUrl()`, `guestUrl()`) were non-static instance methods.
  PHPUnit 11 rejects this outright (`Data Provider method ... is not
  static`), where PHPUnit 10 only deprecated it. Made both `static`; neither
  referenced `$this`. Also fixed the same issue in
  `tests/Feature/PageLoad/OrganizationPageLoad.php`'s `organization_page_url()`
  — that file isn't part of the suite (its filename doesn't end in
  `Test.php`, so PHPUnit's directory scan never picks it up), fixed for
  consistency anyway since it's the identical bug shape.
- `app/CsvImporter/Entities/Activity/Components/Factory/Validation.php` and
  `app/Http/Requests/Activity/ActivityBaseRequest.php` — both had a
  `date_greater_than` validation rule calling `Carbon::parse($value)->year`
  with no guard. `nesbot/carbon` was pulled to v3 as a transitive dependency
  of this Laravel major, and Carbon 3 tightened `parse()`'s signature to
  reject `bool` outright (`TypeError` instead of a loose cast), which
  crashed on `false` input (e.g. an unparseable/empty date field flowing
  through validation) instead of just failing the rule. The sibling
  implementations of this exact same rule in
  `app/Xml/Validator/Traits/RegistersValidationRules.php` and
  `app/XlsImporter/Validator/Traits/RegistersValidationRules.php` already
  guarded against this using the app's own `dateFormat('Y', $value)` helper
  (returns `false` for bool/array/unparseable input instead of throwing) —
  brought both remaining copies in line with that existing pattern rather
  than inventing a new one. Removed the now-unused `Carbon` import from
  `ActivityBaseRequest.php` (still used elsewhere in `Validation.php`, kept
  there).
- Converted all 124 `@test`-doc-comment and `@dataProvider`-doc-comment
  annotations across 58 test files (mechanical, scripted) to PHP attributes
  (`#[Test]` / `#[DataProvider('method')]` from `PHPUnit\Framework\Attributes\*`),
  per PHPUnit 11's deprecation of doc-comment metadata (removed outright in
  PHPUnit 12 — this repo's target v13 pulls in phpunit 12+, so this had to
  happen by this pass regardless). `vendor/bin/phpunit --display-phpunit-deprecations`
  confirmed all 124 were this single pattern before the sweep, 0 after.

### Known pre-existing failure (not fixed, not caused by this bump)

`Tests\Unit\ImportXls\ImportActivityTest::test_processed_data_against_actual_data`
fails comparing two dropdown-coded fields (`participating_org.0.crs_channel_code`,
`sector.1.category_code`): the importer returns `"11001 - Central Government"` /
`"113 - Secondary Education"` (code + label) where the fixture
(`tests/Unit/TestFiles/Xls/SystemData/activity.json`) expects the bare code.
Traced to `app/Helpers/general.php:getJsonFromSource()` — when `APP_ENV`
isn't `local` (true for `testing`), it fetches the IATI codelist JSON live
from the real `iatipublisher-dev` S3 bucket via `awsGetFile()`, not a local
fixture. `app/XlsImporter/Foundation/Mapper/Traits/XlsMapperHelper.php`'s
`mapDropDownValueToKey()` does an exact-string lookup of the raw cell value
against `array_flip(getCodeList(...))`'s keys (`"code - label"` strings) —
if the live S3 codelist's label text for a code has since changed from
whatever it was when the fixture was captured, the lookup misses and the
raw `"code - label"` string falls through unmodified. Confirmed via a
standalone script reproducing the mapper call. Unrelated to any composer
package or Laravel version touched in this chain — flagged for the user
rather than fixed, since the real fix is either updating the fixture to
match current live codelist content or making the test not depend on live
S3 data, and either is a call outside this task's scope.

`vendor/bin/phpunit` after this pass: **602 tests, 931 assertions, 1
failure** (the pre-existing one above), **0 errors, 0 deprecations** (4
pre-existing "No tests found" warnings on abstract base test classes,
unchanged from the previous pass, not investigated further).
