<?php

declare(strict_types=1);

namespace Tirreno;

/** @var Core\Services\Request $request */
/** @var Core\Services\Response $response */
/** @var Core\Services\Session $session */
/** @var Core\Services\Page $page */

// Set page title, it also appears in the navigation menu.
$page->setTitle('Risk users');

// Security gate: redirect not-logged-in users and non-operators to /login.
$response->redirectNotLoggedIn('/login');
$response->redirectImproperRole(['operator'], [], '/login');

// Use the tirreno query builder (auto-scoped to the current API key) to list
// entities with a trust score of 20 or higher. get()->data returns an array
// of \Tirreno\Entities\User objects.
$entities = tirreno('queries')->users->where('user_score', '>=', 20)->limit(50)->get()->data;

// Flatten entities into scalar rows. The template must use array access
// ({{ @entity['x'] }}), which F3 HTML-escapes; object access ({{ @e->prop }})
// is NOT auto-escaped and would expose ingested data (userid, email) to XSS.
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
    'AB_TITLE'          => 'Top risk users',
    'AB_COL_USERID'     => 'User ID',
    'AB_COL_EMAIL'      => 'Email',
    'AB_COL_LASTSEEN'   => 'Last seen',
    'AB_NO_RESULTS'     => 'No entities match',
]);
