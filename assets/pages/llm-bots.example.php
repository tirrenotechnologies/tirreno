<?php

declare(strict_types=1);

namespace Tirreno;

/** @var Core\Services\Request $request */
/** @var Core\Services\Response $response */
/** @var Core\Services\Session $session */
/** @var Core\Services\Page $page */

// This is a reference example (.example.php), so it is intentionally excluded
// from the left navigation menu. Rename it to `llm-bots.php` to enable it.

// Set page title, it also appears in the navigation menu (for non-example pages).
$page->setTitle('LLM bots');

// Security gate: redirect not-logged-in users and non-operators to /login.
$response->redirectNotLoggedIn('/login');
$response->redirectImproperRole(['operator'], [], '/login');

// D13 ("Device is AI bot") matches accounts whose device user agent resolves to
// a known AI/LLM bot (assets/lists/ai-bot.php). Once a rule matches, its uid
// is stored on the account in the JSONB column score_details as
// [{"uid":"D13","score":N}, ...].
$entities = tirreno('queries')->users->where('user_score_details', 'ILIKE', '%"D13"%')->orderBy('user_lastseen', 'DESC')->limit(50)->get()->data;

// Flatten entities into scalar rows. The template must use array access
// ({{ @entity['x'] }}), which F3 HTML-escapes; object/arrow access
// ({{ @e->prop }}) is NOT auto-escaped and would expose ingested data to XSS.
$rows = array_map(static function (\Tirreno\Entities\User $user): array {
    return [
        'id'        => $user->id,
        'userid'    => $user->userid,
        'email'     => $user->email->email,
        'lastseen'  => $user->lastseen,
    ];
}, $entities);

$page->addParams([
    'ENTITIES'          => $rows,

    // Display labels are passed as params: file-based pages under assets/pages
    // do not load a page dictionary, so undefined @keys would render empty.
    'LB_TITLE'          => 'LLM bots (rule D13)',
    'LB_COL_USERID'     => 'User ID',
    'LB_COL_EMAIL'      => 'Email',
    'LB_COL_LASTSEEN'   => 'Last seen',
    'LB_NO_RESULTS'     => 'No entities match the AI bot rule (D13)',
]);
