<?php

const MIN_PHP_VERSION = '8.0.0';
const MAX_PHP_VERSION = '8.4.0';
const MIN_MEMORY_LIM = 128 * 1024 * 1024;

$logo = (
'           888
   888     888
   888
 88888888  888  888.d88 888.d88  .d88b.   88.d8b.    .d88b.
   888     888  888P"   888P"   d8P  Y8b  888 "88b  d88""88b
   888     888  888     888     88888888  888  888  888  888
   888     888  888     888     Y8b  .qq  888  888  888  888
   888     888  888     888      d8bddr   888  888  R88  88P');

$style = (
'<style>
  body {
    background-color: #2b2a39;
    color: #d7e6e1;
    font-family: monospace, monospace;
    padding: 50px;
    font-size: 13px;
  }
  input[type="text"],input[type="email"]{
    width: 550px;
    background-color: #151220;
    color: #d7e6e1;
    font-size: 100%;
  }
  label {
    text-align: right;
    display: block;
  }
  a {
    color: #6e9fff;
  }
</style>');

$script = "<script>
window.addEventListener('DOMContentLoaded', function() {
    function parseUrl() {
        let input = dbUrlField.value.trim();

        if (!input) {
            return;
        }

        let scheme = null;
        let username = null;
        let password = null;
        let hostname = null;
        let port = null;
        let dbname = null;

        let parts = input.split('@');

        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return;
        }

        let part1 = parts[0];   // scheme + user + password     postgres://user:pass
        let part2 = parts[1];   // host + port + dbname         localhost:5432/mydb?sslmode=require

        parts = part1.split('://');
        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return;
        }

        scheme = parts[0];      // postgres | postgresql
        let part11 = parts[1];  // user:pass

        parts = part11.split(':');
        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return;
        }

        username = parts[0];
        password = parts[1];

        parts = part2.split('?');
        part2 = parts[0];       // localhost:5432/mydb
        if (!part2) {
            return;
        }

        parts = part2.split('/');
        if (parts.length !== 2 || !parts[0] || !parts[1]) {
            return;
        }

        let part21 = parts[0];  // localhost:5432 || [::ffff:192.168.1.1]:3306
        dbname = parts[1];

        if (part21[0] === '[') {
            parts = part21.split(']:');
            if (parts.length !== 2 || !parts[0] || !parts[1]) {
                return;
            }
            hostname = parts[0] + ']';
            port = parts[1];
        } else {
            parts = part21.split(':');
            if (parts.length !== 2 || !parts[0] || !parts[1]) {
                return;
            }
            hostname = parts[0];
            port = parts[1];
        }

        dbUserField.value = decodeURIComponent(username);
        dbPassField.value = decodeURIComponent(password);
        dbHostField.value = decodeURIComponent(hostname);
        dbPortField.value = decodeURIComponent(port);
        dbNameField.value = decodeURIComponent(dbname);
    }

    function submit(e) {
        e.preventDefault();
        const formData = new FormData(form, e.submitter);
        const obj = {};

        for (const [key, value] of formData.entries()) {
            obj[key] = value;
        }
        sessionStorage.setItem('connectionDetails', JSON.stringify(obj));

        if (e.submitter && e.submitter.name) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = e.submitter.name;
            hidden.value = e.submitter.value;
            form.appendChild(hidden);
            e.submitter.disabled = true;
        }

        form.submit();
    }

    function toggleTestButton(e) {
        const btn = document.getElementById('test-btn');
        if (btn) {
            btn.style.backgroundColor = '';
        }
    }

    function substituteForm(e) {
        const sessionDetails = sessionStorage.getItem('connectionDetails');
        if (!form || !sessionDetails) {
            return;
        }
        const data = JSON.parse(sessionDetails) || [];

        if (typeof data !== 'object') {
            return;
        }

        for (const [key, value] of Object.entries(data)) {
            if (value && fieldNameMap[key] !== undefined) {
                fieldNameMap[key].setAttribute('value', value);
            }
        }
    }

    const form = document.getElementById('db-form');

    const dbUrlField = document.getElementById('db-url');
    const dbUserField = document.getElementById('db-user');
    const dbPassField = document.getElementById('db-pass');
    const dbHostField = document.getElementById('db-host');
    const dbPortField = document.getElementById('db-port');
    const dbNameField = document.getElementById('db-name');
    const adminEmailField = document.getElementById('admin-email');

    const fieldIdMap = {
        'db-url': dbUrlField,
        'db-user': dbUserField,
        'db-pass': dbPassField,
        'db-host': dbHostField,
        'db-port': dbPortField,
        'db-name': dbNameField,
    };

    const fieldNameMap = {
        'db_url':       dbUrlField,
        'db_user':      dbUserField,
        'db_pass':      dbPassField,
        'db_host':      dbHostField,
        'db_port':      dbPortField,
        'db_name':      dbNameField,
        'admin_email':  adminEmailField,
    };

    // parse db url on db_url input change
    // parse db url if db_url was substituted and js doesnt contain sessionstorage
    if (dbUrlField) {
        dbUrlField.addEventListener('input', parseUrl);
        if (dbUrlField.value && !sessionStorage.getItem('connectionDetails')) {
            parseUrl();
        }

        Object.values(fieldIdMap).forEach(field => {
            field.addEventListener('input', toggleTestButton);
        });
    }

    if (form) {
        form.addEventListener('submit', submit);
        window.addEventListener('pageshow', substituteForm);
    }
});
</script>";

$installerHead = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Installer</title>' . $style . '</head>';
$okTile = '[  <span style="color: #25EAB5;">OK</span>  ]';
$failTile = '[ <span style="color: #fb6e88;">FAIL</span> ]';
$warnTile = '[ <span style="color: #f5b944;">WARN</span> ]';
$nullTile = '[  --  ]';
$backButton = '<input action="action" onclick="window.history.go(-1); return false;" type="submit" value="Try again"/>';

$formBody = (
'<body>
<h3>PostgreSQL connection details</h3>
<form action="./index.php" method="post" id="db-form">
  <table width="715" cellpadding="5" cellspacing="0" border="0">
    <tr>
      <td><label for="db_url">Database URL</label></td>
      <td><input type="text" id="db-url" name="db_url" autocomplete="off" autocapitalize="off" placeholder="postgresql://user:password@127.0.0.1:5432/dbname"></td>
    </tr>
    <tr>
      <td><label>or</label></td>
      <td></td>
    </tr>
    <tr>
      <td><label for="db_user">Database username</label></td>
      <td><input type="text" id="db-user" name="db_user" autocomplete="off" autocapitalize="off" placeholder="user" required></td>
    </tr>
    <tr>
      <td><label for="db_pass">Database password</label></td>
      <td><input type="text" id="db-pass" name="db_pass" autocomplete="off" autocapitalize="off" placeholder="password" required></td>
    </tr>
    <tr>
      <td><label for="db_host">Database host</label></td>
      <td><input type="text" id="db-host" name="db_host" autocomplete="off" autocapitalize="off" placeholder="127.0.0.1" required></td>
    </tr>
    <tr>
      <td><label for="db_port">Database port</label></td>
      <td><input type="text" id="db-port" name="db_port" autocomplete="off" autocapitalize="off" placeholder="5432" required></td>
    </tr>
    <tr>
      <td><label for="db_name">Database name</label></td>
      <td><input type="text" id="db-name" name="db_name" autocomplete="off" autocapitalize="off" placeholder="dbname" required></td>
    </tr>
    <tr>
      <td><label for="admin_email">Admin email</label></td>
      <td><input type="email" id="admin-email" name="admin_email" autocomplete="off" autocapitalize="off"></td>
    </tr>
    <tr>
        <td colspan="2"><hr></td>
    </tr>
    <tr>
        <td><input type="submit" id="test-btn" name="test" value="Test"></td>
        <td><input type="submit" name="connect" value="Connect"></td>
    </tr>
  </table>
</form>
</body>');

$formBody .= $script;

function resultHtmlStart(): string {
    global $installerHead, $logo;
    return $installerHead . '<body><pre>' . $logo;
}

function resultHtmlEnd(): string {
    return '</pre></body></html>';
}

function formHtml(): string {
    global $installerHead, $formBody;
    return $installerHead . $formBody . '</html>';
}

function finishOk(): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $pos = strrpos($uri, '/install');
    $path = $pos === false ? rtrim($uri, '/') : substr($uri, 0, $pos);

    $url = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');

    $out = "\n\n======================== Setup completed! ========================";
    $out .= "\n* Please delete the ./install directory and all its included files.";
    $out .= "\n* Visit <a href=\"$url/signup\">/signup</a> to create your account.";

    return $out;
}

function finishError(): string {
    global $backButton;

    $out = "\n====================== Something went wrong ======================";
    $out .= "\n$backButton";

    return $out;
}

$steps = [
    [
        'description' => 'tirreno version',
        'tasks' => [
            ['description' => 'Latest version check', 'status' => null],
        ],
    ],
    [
        'description' => 'Compatibility checks',
        'tasks' => [
            ['description' => 'PHP version', 'status' => null],
            ['description' => 'Apache Rewrite (mod_rewrite)', 'status' => null],
            ['description' => 'PDO PostgreSQL driver', 'status' => null],
            ['description' => 'Configuration folder (/config) read/write permission', 'status' => null],
            ['description' => '.htaccess available', 'status' => null],
            ['description' => 'cURL', 'status' => null],
            ['description' => 'Memory limit (Min. 128MB)', 'status' => null],
        ],
    ],
    [
        'description' => 'Database params',
        'tasks' => [
            ['description' => 'Schema accessible', 'status' => null],
            ['description' => 'Database name', 'status' => null],
            ['description' => 'Database user', 'status' => null],
            ['description' => 'Database password', 'status' => null],
            ['description' => 'Database host', 'status' => null],
            ['description' => 'Database port', 'status' => null],
        ],
    ],
    [
        'description' => 'Database setup',
        'tasks' => [
            ['description' => 'Database connection', 'status' => null],
            /*['description' => 'Database version', 'status' => null],*/
            ['description' => 'Apply database schema', 'status' => null],
        ],
    ],
    [
        'description' => 'Config build',
        'tasks' => [
            ['description' => 'Write config file', 'status' => null],
        ],
    ],
];

function proceed(): void {
    $out = '';
    if (configAlreadyExists()) {
        $out .= resultHtmlStart();
        $out .= "\nThe app is already configured.";
        $out .= resultHtmlEnd();

        echo $out;
        return;
    }
    if (!isset($_SERVER['REQUEST_METHOD'])) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connect'])) {
        $out .= resultHtmlStart();
        [$status, $result, $config] = execute($_POST);
        $out .= $result;
        $out .= $status ? finishOk() : finishError();
        $out .= resultHtmlEnd();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test'])) {
        $status = boolval(initDbConnection($_POST)[0]);
        substituteConnectionStatus($status);
        $out .= formHtml();
    } else {
        substituteFormWithEnv();
        $out .= formHtml();
    }

    echo $out;
}

function execute(array $values): array {
    global $steps;

    $out = '';
    $config = null;

    versionCheck(0, $steps);
    $out .= printTasks($steps[0]);

    compatibilityCheck(1, $steps);
    $out .= printTasks($steps[1]);
    if (!tasksCompleted($steps[1])) {
        return [false, $out, null];
    }

    dbConfig(2, $values, $steps);
    $out .= printTasks($steps[2]);
    if (!tasksCompleted($steps[2])) {
        return [false, $out, null];
    }

    dbSaveConfig(3, $values, $steps);
    $out .= printTasks($steps[3]);
    if (!tasksCompleted($steps[3])) {
        return [false, $out, null];
    }

    if (strval($values['mode'] ?? '') !== 'schema') {
        $config = saveConfig(4, $values, $steps);
        $out .= printTasks($steps[4]);
        if (!tasksCompleted($steps[4])) {
            return [false, $out, null];
        }
    }

    return [true, $out, $config];
}

function configAlreadyExists(): bool {
    return (getenv('SITE') && getenv('DATABASE_URL')) || file_exists('../config/local/config.local.ini');
}

function substituteConnectionStatus(bool $status): void {
    global $formBody;

    $statusColour = $status ? '#25eab5' : '#fb6e88';

    $pattern = '/(<td><input type="submit" id="test-btn" name="test" value="Test"><\/td>)/';
    $replacement = "<td><input type=\"submit\" id=\"test-btn\" name=\"test\" value=\"Test\" style=\"background-color: $statusColour;\"></td>";

    $formBody = preg_replace(
        $pattern,
        $replacement,
        $formBody,
    );
}

function substituteFormWithEnv(): void {
    global $formBody;

    if (strval($_GET['mode'] ?? '') === 'schema') {
        $formBody = preg_replace(
            '/(<form\b[^>]*>)/i',
            '$1<input type="hidden" name="mode" value="schema">',
            $formBody,
        );
    }

    $values = [];

    $dbUrl = getenv('DATABASE_URL');
    if ($dbUrl) {
        $values['db_url'] = $dbUrl;
    }

    foreach ($values as $key => $value) {
        $safe = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $formBody = preg_replace(
            '/(<input\b[^>]*\bname="' . preg_quote($key, '/') . '"(?![^>]*\bvalue=)[^>]*)(>)/i',
            '$1 value="' . $safe . '"$2',
            $formBody,
        );
    }
}

function versionCheck(int $step, array &$steps): void {
    $versionStatus = checkLatestVersion();
    $steps[$step]['tasks'][0]['status'] = $versionStatus;

    if ($versionStatus === false) {
        $steps[$step]['tasks'][0]['description'] = 'A newer version of tirreno is available';
        $steps[$step]['tasks'][0]['warn'] = true;
    } elseif ($versionStatus === null) {
        $steps[$step]['tasks'][0]['description'] = 'Unable to connect to version server';
        $steps[$step]['tasks'][0]['warn'] = true;
    } else {
        $steps[$step]['tasks'][0]['description'] = 'This is the latest version of tirreno';
    }
}

function compatibilityCheck(int $step, array &$steps): void {
    $versionSuits = version_compare(PHP_VERSION, MIN_PHP_VERSION) >= 0 && version_compare(PHP_VERSION, MAX_PHP_VERSION) < 0;
    $steps[$step]['tasks'][0]['status'] = $versionSuits;
    if (!$versionSuits) {
        $steps[$step]['tasks'][0]['error'] = 'Current PHP version is ' . strval(PHP_VERSION) . '. Allowed PHP versions are greater than ' . strval(MIN_PHP_VERSION) . ' and lower than ' . strval(MAX_PHP_VERSION);
    }

    $steps[$step]['tasks'][1]['status'] = false;
    if (function_exists('apache_get_modules')) {
        if (in_array('mod_rewrite', apache_get_modules())) {
            $steps[$step]['tasks'][1]['status'] = true;
        }
    } else {
        $steps[$step]['tasks'][1]['warn'] = true;
        $steps[$step]['tasks'][1]['error'] = 'Could not be autodetected. Please check manualy.';
    }

    $steps[$step]['tasks'][2]['status'] = extension_loaded('pdo_pgsql') && extension_loaded('pgsql');

    try {
        if (is_writable('../config')) {
            $file = fopen('../config/local/config.local.ini', 'w');
            if ($file !== false) {
                fclose($file);
                unlink('../config/local/config.local.ini');
                $steps[$step]['tasks'][3]['status'] = true;
            } else {
                $steps[$step]['tasks'][3]['status'] = false;
            }
        } else {
            $steps[$step]['tasks'][3]['status'] = false;
        }
    } catch (\Exception $e) {
        $steps[$step]['tasks'][3]['status'] = false;
    }

    $steps[$step]['tasks'][4]['status'] = is_file('../.htaccess') && is_readable('../.htaccess');

    $steps[$step]['tasks'][5]['status'] = extension_loaded('curl');

    $memoryLimit = @ini_get('memory_limit');
    $memLim = $memoryLimit;
    preg_match('#^(\d+)(\w+)$#', strtolower($memLim), $match);
    $memLim = match ($match[2]) {
        'g'     => intval($memLim) * 1024 * 1024 * 1024,
        'm'     => intval($memLim) * 1024 * 1024,
        'k'     => intval($memLim) * 1024,
        default => intval($memLim),
    };
    $steps[$step]['tasks'][6]['status'] = $memLim >= MIN_MEMORY_LIM;
}

function dbConfig(int $step, array &$values, array &$steps): void {
    $steps[$step]['tasks'][0]['status'] = is_file('./install.sql');

    $steps[$step]['tasks'][1]['status'] = $values['db_name'] !== '';
    $steps[$step]['tasks'][2]['status'] = $values['db_user'] !== '';
    $steps[$step]['tasks'][3]['status'] = $values['db_pass'] !== '';
    $steps[$step]['tasks'][4]['status'] = $values['db_host'] !== '';
    $steps[$step]['tasks'][5]['status'] = $values['db_port'] !== '';
}

function saveConfig(int $step, array $values, array &$steps): ?array {
    $configData = null;
    try {
        $currentHttpHost = strtolower(filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL) ?: '');
        $hosts = [$currentHttpHost];

        $forceHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https'); // loadbalancer

        $configData = [
            'FORCE_HTTPS'   => $forceHttps ? 'true' : 'false',
            'SITE'          => implode(',', $hosts),
            'DATABASE_URL'  => "postgres://$values[db_user]:$values[db_pass]@$values[db_host]:$values[db_port]/$values[db_name]",
            'PEPPER'        => strval(bin2hex(random_bytes(32))),
        ];

        if ($values['admin_email'] !== '') {
            $configData['ADMIN_EMAIL'] = $values['admin_email'];
        }

        $config = "\n[globals]";
        foreach ($configData as $key => $value) {
            $value = str_replace('\\', '\\\\', $value);
            $value = str_replace('"', '\\"', $value);
            $value = '"' . $value . '"';

            $config .= "\n$key=$value";
        }

        $config .= "\n";

        $configPath = '../config/local/config.local.ini';
        $configFile = fopen($configPath, 'w');
        if ($configFile !== false) {
            fwrite($configFile, $config);
            fclose($configFile);
            $steps[$step]['tasks'][0]['status'] = true;
            $site = getenv('SITE');
            if ($site !== false) {
                $site = htmlspecialchars($site, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $steps[$step]['tasks'][0]['warn'] = true;
                $steps[$step]['tasks'][0]['error'] = 'Environment variable SITE detected: ' . $site;
            }
        } else {
            $steps[$step]['tasks'][0]['status'] = false;
        }
    } catch (\Exception $e) {
        $steps[$step]['tasks'][0]['status'] = false;
        $steps[$step]['tasks'][0]['error'] = $e->getMessage();
    }

    return $configData;
}

function dbSaveConfig(int $step, array $values, array &$steps): void {
    $connection = initDbConnection($values);
    if (!$connection[0]) {
        $steps[$step]['tasks'][0]['status'] = false;
        $steps[$step]['tasks'][0]['error'] = $connection[1];
        return;
    }

    $database = $connection[0];

    $steps[$step]['tasks'][0]['status'] = true;

    /*$query = $database->query('SELECT VERSION()');
    [$dbVersion] = $query->fetch(\PDO::FETCH_NUM);
    if (!preg_match('/PostgreSQL (\d+\.\d+)/', $dbVersion, $matches) || version_compare($matches[1], '12.0', '<')) {
        $steps[$step]['tasks'][1]['status'] = false;
        return;
    }
    $steps[$step]['tasks'][1]['status'] = true;*/
    try {
        if (!lockDb($database)) {
            $steps[$step]['tasks'][1]['status'] = false;
            $steps[$step]['tasks'][1]['error'] = 'Database already locked by another installation process.';
            return;
        }

        if (checkMigrationAppliedDb($database)) {
            $steps[$step]['tasks'][1]['status'] = false;
            $steps[$step]['tasks'][1]['error'] = 'Database already has app\'s migrations applied.';
            return;
        }

        $sql = safeFileGetContents('./install.sql', null)['content'];
        $database->exec($sql);
        unlockDb($database);
    } catch (\Exception $e) {
        $steps[$step]['tasks'][1]['status'] = false;
        $steps[$step]['tasks'][1]['error'] = $e->getMessage();
        return;
    }

    $steps[$step]['tasks'][1]['status'] = true;
}

function initDbConnection(array $values): array {
    $database = false;
    $msg = null;

    $dsn = "pgsql:dbname=$values[db_name];host=$values[db_host];port=$values[db_port]";
    $driverOptions = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    );

    try {
        $database = new \PDO($dsn, $values['db_user'], $values['db_pass'], $driverOptions);
    } catch (\Exception $e) {
        $msg = $e->getMessage();
    }

    return [$database, $msg];
}

function checkMigrationAppliedDb(\PDO $database): bool {
    $query = (
        'SELECT
            COUNT(*) AS cnt
        FROM information_schema.tables
        WHERE table_name IN (\'dshb_api\', \'dshb_operators\', \'event_session\') LIMIT 1'
    );

    $stmt = $database->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchColumn();
    $cnt = $result === false ? 0  : intval($result);

    return $cnt === 3;
}

function lockDb(\PDO $database): bool {
    $query = (
        'CREATE TABLE IF NOT EXISTS dshb_install_flag (
            id smallint primary key,
            started timestamp without time zone DEFAULT now()
        )'
    );

    $database->exec($query);

    $query = 'INSERT INTO dshb_install_flag (id) VALUES (1) ON CONFLICT DO NOTHING';
    $stmt = $database->prepare($query);
    $stmt->execute();

    // rowCount() 0 if id already exists
    return $stmt->rowCount() === 1;
}

function unlockDb(\PDO $database): void {
    $query = 'DROP TABLE IF EXISTS dshb_install_flag';

    $database->exec($query);
}

function printTasks(array $tasks): string {
    global $okTile, $failTile, $warnTile, $nullTile;

    $out = '';
    $side = intdiv(64 - strlen($tasks['description']), 2);
    $header = str_repeat('=', $side) . ' ' . $tasks['description'] . ' ' . str_repeat('=', $side);
    if ($side * 2 + strlen($tasks['description']) < 64) {
        $header .= '=';
    }
    $out .= "\n\n" . $header;
    foreach ($tasks['tasks'] as $task) {
        $status = ($task['status'] === true) ? $okTile : (($task['status'] === false) ? $failTile : $nullTile);
        $err = array_key_exists('error', $task) ? ' (' . $task['error'] . ')' : '';
        if (isset($task['warn'])) {
            $status = $warnTile;
        }
        $out .=  "\n" . $status . ' ' . $task['description'] . $err;
    }

    return $out;
}

function tasksCompleted(array $tasks): bool {
    foreach ($tasks['tasks'] as $task) {
        if (!$task['status'] && !($task['warn'] ?? false)) {
            return false;
        }
    }

    return true;
}

function safeFileGetContents(string $path, ?array $options): array {
    set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    });

    $result = null;

    try {
        $context = null;
        if ($options) {
            $context = stream_context_create($options);
        }
        $result = file_get_contents($path, false, $context);
    } catch (\Throwable $e) {
        return [
            'content' => null,
            'headers' => [],
        ];
    }

    restore_error_handler();

    return [
        'content'   => $result !== false ? $result : null,
        'headers'   => $GLOBALS['http_response_header'] ?? [],
    ];
}

function performRequest(string $url, string $useragent): array {
    $code = null;
    $response = null;
    $error = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'code'  => $code,
                'data'  => [],
                'error' => $error,
            ];
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPGET         => true,
            CURLOPT_HTTPHEADER      => [$useragent],
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $response = null;
        }

        curl_close($ch);
    } else {
        $options = [
            'http' => [
                'method'    => 'GET',
                'header'    => $useragent,
                'timeout'   => 30,
            ],
        ];

        $result = safeFileGetContents($url, $options);
        $response = $result['content'];
        $responseHeaders = $result['headers'];

        $code = null;

        if (isset($responseHeaders[0])) {
            preg_match('{HTTP/\d\.\d\s+(\d+)}', $responseHeaders[0], $match);
            $code = intval($match[1]);
        }
    }

    $resp = [
        'code'  => $code,
        'data'  => $response !== null ? json_decode($response, true) : [],
        'error' => $error,
    ];

    return $resp;
}

function checkLatestVersion(): ?bool {
    $path = __DIR__ . '/../app/Utils/VersionControl.php';

    if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
        return null;
    }

    require_once $path;

    $version = \Tirreno\Utils\VersionControl::versionString();

    $useragent = 'tirreno-install';
    $useragent = $version ? $useragent . '/' . $version : $useragent;
    $useragent = 'User-Agent: ' . $useragent;

    $path = __DIR__ . '/../config/config.ini';
    if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
        return null;
    }
    $config = parse_ini_file($path, false, INI_SCANNER_TYPED);

    $url = $config['ENRICHMENT_API'] ?? null;

    if (!$url) {
        return null;
    }

    $url .= '/version';

    $resp = performRequest($url, $useragent);
    $result = $resp['data'];
    $jsonResponse = is_array($result) ? $result : [];
    $statusCode = $resp['code'] ?? 0;
    $error = $resp['error'] ?? '';

    if (strlen($error) > 0 || $statusCode !== 200 || !isset($jsonResponse['version'])) {
        return null;
    } elseif (version_compare($version, $jsonResponse['version'], '<')) {
        return false;
    }

    return true;
}

proceed();
