# AGENTS.md

This document describes how tirreno (https://www.tirreno.com) should be used as a open-source framework for building sovereign security, compliance and fraud prevention applications. This file is for building new apps and pages on it.

## Repo entrypoints
- Dev docs: https://github.com/tirrenotechnologies/DEVELOPMENT.md
- Admin docs: https://github.com/tirrenotechnologies/ADMIN.md
- API reference: https://github.com/tirrenotechnologies/API.md

## Standards

- PHP: `declare(strict_types=1)`, 4-space indent, LF. JavaScript: ES2016 (no `async`/`await`, no object spread), `$.ajax`.
- Start page files with the header from the recipe; without the `@var` lines PHPStan reports every variable as undefined.
- Put `redirectNotLoggedIn()` and `redirectImproperRole()` before any query.
- Pass arrays of scalars to templates: strings and arrays are HTML-escaped, entity objects are not.
- Forms send `<input type="hidden" name="token" value="{{ @CSRF }}">`; check with `$request->validateCsrf()` (`false` = valid). After a POST, `$response->redirect('/<name>')`.
- Log every change to data with the operator: `$log->info('... by operator %d', $sysop->id)`. Delete users through the deletion queue, not `User->delete()`, which is immediate.
- Keep data on the instance: no outbound requests, webhooks or CDN assets in new code.

## Recommendations
- When working with this codebase, prioritize readability over cleverness, and sovereignty over new dependencies.
- Prefer simple, local changes over architectural purity.
- Respect existing patterns in the area you are modifying.
- If you touch code, you own its quality, including tests and relevant checks.
- Prefer native JS and jQuery for new UI work where it fits the surrounding code.
- Tracking must remain fast and predictable. Avoid additional database queries, remote calls, heavy parsing, or per-request complexity growth, and prefer the tirreno API.

## Where custom code goes

| What | Where | Copy from |
|------|-------|-----------|
| Page, opens at `/<name>` with a menu item | `assets/pages/<name>.php` + `assets/pages/views/<name>.html` | `assets/pages/risk-users.example.php` |
| Detection rule | `assets/rules/custom/X01.php` … `X99.php` | `assets/rules/custom/X03.example.php` |
| Extra rule attributes | `assets/rules/custom/Context.php` | `assets/rules/custom/Context.example.php` |
| Pattern list | `assets/lists/<list>.php` (replaces the whole default list) | `app/Utils/Assets/Lists/*.php` |

- Never change `app/`, `ui/`, `sensor/` or `config/config.ini` for a customisation; updates overwrite them. `<name>` may contain only letters, digits, `-` and `_`; `.example.php` files are ignored. New rules become active after **Refresh** on the Rules page. If something isn't in the API list below, read the code: `app/Core/Services/<Name>.php`, columns in `app/Models/Query/<Name>.php`, entity properties in `app/Entities/<Name>.php`, constants in `app/Utils/Constants.php`.

## Recipe: page with a list and an action

`assets/pages/review.php`:

```php
<?php

declare(strict_types=1);

namespace Tirreno;

/** @var Core\Services\Page $page */
/** @var Core\Services\Request $request */
/** @var Core\Services\Response $response */
/** @var Core\Services\User $user */
/** @var Core\Services\Sysop $sysop */
/** @var Core\Services\Log $log */

$page->setTitle('Review');                                   // string literal: also the menu label
$response->redirectNotLoggedIn('/login');
$response->redirectImproperRole(['operator'], [], '/login');

if ($request->isPost()) {
    if ($request->validateCsrf() === false) {
        $u = $user->getById((int) $request->getIntRequestParam('id'));
        if ($u !== null) {
            $u->setBlacklist();
            $log->info('Review: user %d blacklisted by operator %d', $u->id, $sysop->id);
        }
    }
    $response->redirect('/review');
}

$rows = tirreno('queries')->users
    ->where('user_score_updated_at', 'IS NOT NULL')
    ->where('user_score', '<=', 50)
    ->orderBy('user_score', 'ASC')
    ->limit(50)
    ->get()->data;

$page->addParams([
    'ROWS' => array_map(fn($u) => ['id' => $u->id, 'userid' => $u->userid, 'score' => $u->score], $rows),
]);
```

`assets/pages/views/review.html` (more examples in `ui/templates/`):

```html
<repeat group="{{ @ROWS }}" value="{{ @row }}">
    <form method="POST" action="{{ @BASE }}/review">
        {{ @row['userid'] }} ({{ @row['score'] }})
        <input type="hidden" name="token" value="{{ @CSRF }}">
        <input type="hidden" name="id" value="{{ @row['id'] }}">
        <button type="submit">Blacklist</button>
    </form>
</repeat>
```

## tirreno API

`tirreno('<name>')` services: `page`, `request`, `response`, `session`, `sysop`, `helpers`, `queries`, `users`, `ips`, `user`, `ip`, `rules`, `rule`, `entities`, `db`, `storage`, `router` (Fat-Free `\Base`), `log`, `constants`, `utils`, `assets`, `models`, `grids`, `charts`, `controllers`, `pages`. In page files `$page`, `$request`, `$response`, `$session`, `$sysop`, `$utils`, `$helpers`, `$constants`, `$db`, `$log`, `$user`, `$ip` are predefined.

- **`$page`**: `setTitle(string)`, `getTitle()`, `addParams(array)` (overwrites keys), `setParams(array)`, `getParams()`, `setTemplate`/`getTemplate`, `setJavascript`/`getJavascript`, `setView('json')`/`getView`, `setAllowedRoles(array)`/`getAllowedRoles` (empty → `['operator']`), `setBlockedRoles`/`getBlockedRoles`.
- **`$request`**: `getRequestType()`, `isGet()`/`isPost()`/`isPut()`/`isDelete()`, `isAjax()`, `isCli()`, `isHttps()`, `getUri()`, `getPath()`, `getQuery()`, `getIp()`, `getUserAgent()`, `getHeaders()`, `getHeader(string)`, `getContentType()`, `contentTypeIsJson()`, `getGet()`, `getPost()`, `getBody()`, `getAllPayload()` (GET, POST and JSON merged), `getRequestParam(string)`.
  - `getStringRequestParam`/`getIntRequestParam`/`getArrayRequestParam`/`getDictionaryRequestParam(string $key, bool $nullable = true)`: missing → `null` (`''`, `0`, `[]` if `$nullable` is false); arrays are re-indexed, dictionaries keep keys.
  - `getUrlParams()` (`[0 => '/<name>']` in a page), `getUrlParam`/`getStringUrlParam`/`getIntUrlParam(string)`, `validateCsrf()` → `false` or `601`.
- **`$response`**: `redirect(string $route = '/')`; `error(int $code = 403)` (AJAX gets JSON `{status, code, message}`; `403` on a normal page goes to `/logout`); `redirectNotLoggedIn`/`redirectLoggedIn(string $route = '/')`; `redirectImproperRole`/`redirectProperRole(array $allowed, array $blocked = [], string $route = '/')`; `errorNotLoggedIn(int $code = 401)`; `errorImproperRole(array $allowed, array $blocked = [], int $code = 401)`.
- **`$session`**: `getCurrentOperator()` (`->isGuest()`, `->isLoggedIn()`, `->roles`), `getCurrentKey()` (`->id`, `null` without a key), `get`/`set`/`remove(string $key)`, `clear()` (logs out).
- **`$sysop`**: `->id`, `->email`, `->timezone`, `->roles`, `hasRole(string)`, `isSuperuser()`. **`$helpers`**: `formatTitle(string)` (escaped, ` | tirreno` suffix).
- **`$user->getById(int $id)`** → `?Entities\User`; **`$ip->getById(int $id)`** → `?Entities\Ip`. **`$rules->getAll()`** / **`getByUserId(int $userId)`** → `->rules` (`uid`, `name`, `value`); **`$rule->getById(string $uid)`** → `?Entities\Rule`.
- **`$db->initConnection()`**; **`$storage->get`/`set`/`remove(string $key)`** (config values and request data); **`$log->debug`/`info`/`warning`/`error(string $format, ...$args)`** (`debug` only in debug mode; `warning`/`error` also to stderr); **`$constants->NAME`** (e.g. `GUEST_OPERATOR_ID` 37, `DELETE_USER_QUEUE_ACTION_TYPE`).
- **`$utils`**: `nowUtc()` (`Y-m-d H:i:s`), `timezones->localizeForActiveOperator(string $utc)`, `conversion->intVal($value, ?int $default = null)`, `errorCodes->NAME` (e.g. `CSRF_ATTACK_DETECTED` 601).
- **`$assets`**: `aiBotList`, `asnList`, `emailList`, `userAgentList`, `urlList`, `fileExtensionsList` (each `->getList()`), `pages->getMenuPages()`.
- **`$entities->user->getById($id, $keyId)`** and the other entity classes; **`$models`**, **`$grids`**, **`$charts`**, **`$controllers`**, **`$pages`**: the console's own classes (names in `$objectsMap` of `app/Core/Services/<Name>.php`).

### Query builders

`tirreno('queries')->users`, `->ips`, `->devices`, `->sessions`, `->countries`, `->referers`, `->payloads`, `->queries`. Rows are limited to the current API key; a guest gets none.

- Methods: `where(string $column, string $op, $value = null)`, `andWhere(...)`, `orWhere(...)` (joined in order: `A OR B AND C` = `A OR (B AND C)`), `whereColumn(string, string $op, string)`, `orderBy(string $column, 'ASC'|'DESC')`, `limit(int)`, `offset(int)`, `getFields()`, `get()` → `->data` (array of entities; `->localizeTimestamps()`).
- Operators: `=` `!=` `<>` `<` `>` `<=` `>=` `LIKE` `NOT LIKE` `ILIKE` `NOT ILIKE` `~` `!~` `!~*`; `IN`/`NOT IN` (array), `BETWEEN`/`NOT BETWEEN` (two-item array); `IS [NOT] NULL|TRUE|FALSE|UNKNOWN`. Value `'null'`/`'true'`/`'false'` with `=`/`!=` becomes `IS …`. An unknown column or operator is silently ignored.
- Columns (aliases): `users`: `user_{id, userid, firstname, lastname, score, score_details, fraud, total_ip, total_device, total_visit, lastseen, created, score_updated_at, added_to_review, …}`, `email_{email, …}`, `phone_{phone_number, …}`; `ips`: `ip_{id, ip, vpn, tor, data_center, relay, blocklist, total_visit, lastseen, …}`, `isp_{asn, name, …}`, `country_{iso, name, …}`; `devices`: `device_{id, account_id, lang, …}`, `user_agent_{browser_name, os_name, device, …}`; `sessions`: `session_{account_id, total_visit, …}`. Full list: `->getFields()`.
- Entities: `User->id`, `->userid`, `->score`, `->scoreDetails` (matched rules), `->firstname`, `->lastname`, `->totalIp`, `->totalDevice`, `->fraud` (`true` blacklisted, `false` whitelisted, `null`), `->lastseen` (UTC), `->email->email`, `->phone->phoneNumber`, `setBlacklist()`, `setWhitelist()`. `Ip->id`, `->ip`, `->vpn`, `->tor`, `->dataCenter`, `->totalVisit`, `->lastseen`, `->isp->name`, `->isp->asn`, `->country->iso`, `->country->name`.

### Models for security, compliance and fraud prevention

`$apiKey` is `$session->getCurrentKey()->id`.

- Review queue: `tirreno('models')->reviewQueue->getFromReviewQueue($accountId, $apiKey)` (`[]` if not queued), `addToReviewQueue($accountId, $apiKey)`, `removeFromReviewQueue($accountId, $apiKey)`, `getCount($apiKey)`.
- Erasure: `tirreno('models')->queue->add($accountId, $constants->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey)`; the next cron run deletes the user and their events.
- Change history: `tirreno('models')->fieldAuditTrail->getByUserId($userId, $apiKey)`. Retention: `tirreno('models')->retentionPolicies->getRetentionKeys()` (Settings → Data retention, in weeks).
- Enforcement in the protected application: `POST /api/v1/blacklist/search` with `{"value": "<userName>"}` (matches user IDs only).

## Pitfalls

| Symptom | Cause / fix |
|---------|-------------|
| `TypeError` from `queries->users->get()` or `$user->getById()` | An account not yet scored by cron; add `where('user_score_updated_at', 'IS NOT NULL')` |
| `ip_ip = '1.2.3.4'` finds nothing | IPs compare as text with the mask: `'1.2.3.4/32'`, `LIKE '1.2.3.%'` or `IN` |
| Wrong numeric comparison | Pass numbers as `int`/`float`; `'42'` compares as text |
| `find('x>=1')` returns nothing | `find()` mishandles `>=`, `<=`, `!=` and compares `<`/`>` as text; use `where()` |
| SQL error with operator `*~` | Use `ILIKE` or `!~*` |
| `TypeError` from `tirreno('users')` / `tirreno('ips')` for a guest | Use `tirreno('queries')->users` / `->ips` |
| 500 "duplicate key … event_review_queue_account_uidx" | The user is already queued; check `getFromReviewQueue()` first |
| `$rule->getById()` shows another key's value | It returns API key 1's value |
| `$utils->nowForCurrentOperator()` is UTC | Use `$utils->timezones->localizeForActiveOperator($utils->nowUtc())` |
| `$queries->events`, `$queries->urls`, `tirreno('resources')`, `tirreno('resource')` fail | Broken in this version; don't use |
| Custom rule throws on `startsWith()` and similar | The attribute is an array; reduce it in `prepareParams()` first |
| Sensor answers `200` but no event appears | The event was rejected: accepted events get `204`; a missing API key or field, or a JSON body, gets `200`, all with no body. Send form-urlencoded fields; the reason is in the Logbook and the web server log (`docker logs` for the test instance) |
| Data endpoint such as `/loadIps` returns 403 | Send the `token` parameter from `<meta name="csrf-token">` |

## Live test instance

Test every page on a running tirreno before you finish. The fastest is Docker, started from the repository root: it runs the latest release and mounts `assets/pages`, `assets/rules/custom` and `assets/lists` from the repository, so new files work without a restart. The first start downloads about 1.4 GB of images.

```bash
docker compose -p tirreno-test -f - up -d <<'YAML'
services:
  tirreno-app:
    image: tirreno/tirreno:latest
    ports: ["8585:80"]
    environment: {DATABASE_URL: "postgres://tirreno:secret@tirreno-db:5432/tirreno", SITE: "localhost:8585", FORCE_HTTPS: "false"}
    volumes: ["./assets/pages:/var/www/html/assets/pages", "./assets/rules/custom:/var/www/html/assets/rules/custom", "./assets/lists:/var/www/html/assets/lists"]
    depends_on: {tirreno-db: {condition: service_healthy}}
  tirreno-db:
    image: postgres:15
    environment: {POSTGRES_DB: tirreno, POSTGRES_USER: tirreno, POSTGRES_PASSWORD: secret}
    healthcheck: {test: ["CMD-SHELL", "pg_isready -h 127.0.0.1 -U tirreno -d tirreno"], interval: 2s, retries: 30}
YAML
```
```bash
T='docker compose -p tirreno-test'
B=http://localhost:8585
J=/tmp/tirreno-test.cookies
# operator account (only the first signup works) and login
curl -s -c $J -b $J $B/signup | grep -o 'name="token" value="[^"]*"' | cut -d'"' -f4 > /tmp/t
curl -s -c $J -b $J -o /dev/null $B/signup --data-urlencode "token=$(cat /tmp/t)" -d email=test@example.com -d password=TestPassw0rd! -d timezone=UTC -d rules-preset=default
curl -s -c $J -b $J $B/login | grep -o 'name="token" value="[^"]*"' | cut -d'"' -f4 > /tmp/t
curl -s -c $J -b $J -o /dev/null $B/login --data-urlencode "token=$(cat /tmp/t)" -d email=test@example.com -d password=TestPassw0rd!
# test events: the sensor takes form-urlencoded fields (curl -d), not JSON; then score them
KEY=$($T exec -T tirreno-db psql -U tirreno -Atc 'SELECT key FROM dshb_api LIMIT 1')
for u in alice bob; do curl -s -o /dev/null -X POST $B/sensor/ -H "Api-Key: $KEY" -d userName=$u -d ipAddress=203.0.113.7 -d url=/login -d eventType=account_login; done
$T exec -T -u www-data tirreno-app php index.php /cron > /dev/null
# open a page as the operator; the AJAX header returns its data as JSON
curl -s -b $J -H 'X-Requested-With: XMLHttpRequest' $B/<name>
```

Stop and delete everything with `docker compose -p tirreno-test down -v`. Keep the Compose file on stdin: relative paths in a Compose file resolve from the file's own folder.

## Debugging

- Read `assets/logs/error.log` first (first command below). At the default `DEBUG = 0` it has the error message, but not the line in your file, and the page shows only a generic 500.
- For the file and line, use `DEBUG = 2` while you reproduce the error. Don't use level 3, which adds function arguments. Only logged-in operators see the details.

```bash
APP='docker compose -p tirreno-test exec -T tirreno-app'   # on an Apache install: APP='sudo -u www-data'
$APP tail -n 50 assets/logs/error.log                        # read the error
$APP sh -c "echo 'DEBUG = 2' >> config/local/config.local.ini" # start
$APP sed -i '/^DEBUG *=/d' config/local/config.local.ini        # stop: back to 0
$APP grep -c '^DEBUG' config/local/config.local.ini             # must print 0
```

- Always run "stop" before you finish. Never print `config/local/config.local.ini`: it holds the database password and `PEPPER`.

## Common Commands

```bash
./vendor/bin/phpunit
find tmp -name '*.php' ! -name index.php -delete   # compiled templates confuse the next two
./vendor/bin/phpstan analyse --configuration=phpstan.neon
./vendor/bin/phpcs -n
npx eslint ui/js/ --ignore-pattern 'ui/js/vendor/**'
```
