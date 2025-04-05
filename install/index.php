<?php

const MIN_PHP_VERSION = '8.0.0';
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
    background-color: #2b2a3d;
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

$installerHead = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Installer</title>' . $style . '<head>';
$okTile = '[  <span style="color: #01ee99;">OK</span>  ]';
$failTile = '[ <span style="color: #fb6e88;">FAIL</span> ]';
$nullTile = '[  --  ]';
$backButton = '<input action="action" onclick="window.history.go(-1); return false;" type="submit" value="Try again"/>';

$formBody = (
'<body>
<h3>Database connection details</h3>
<form action="/install/index.php" method="post">
  <table width="688" cellpadding="5" cellspacing="0" border="0">
    <tr>
      <td><label for="db_user">Username</label></td>
      <td><input type="text" id="db-user" name="db_user" autocomplete="off" autocapitalize="off" required></td>
    </tr>
    <tr>
      <td><label for="db_pass">Password</label></td>
      <td><input type="text" id="db-pass" name="db_pass" autocomplete="off" autocapitalize="off" required></td>
    </tr>
    <tr>
      <td><label for="db_host">Host</label></td>
      <td><input type="text" id="db-host" name="db_host" autocomplete="off" autocapitalize="off" required></td>
    </tr>
    <tr>
      <td><label for="db_port">Port</label></td>
      <td><input type="text" id="db-port" name="db_port" autocomplete="off" autocapitalize="off" required></td>
    </tr>
    <tr>
      <td><label for="db_name">Database name</label></td>
      <td><input type="text" id="db-name" name="db_name" autocomplete="off" autocapitalize="off" required></td>
    </tr>
    <tr>
      <td><label for="admin_email">Admin email</label></td>
      <td><input type="email" id="admin-email" name="admin_email" autocomplete="off" autocapitalize="off"></td>
    </tr>
    <tr>
    <td colspan="2">
    <hr>
    </td>
    </tr>
    <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Connect"></td>
    </tr>
  </table>
</form>
</body>');

function resultHtmlStart() {
    global $installerHead, $logo;
    return $installerHead . '<body><pre>' . $logo;
}

function resultHtmlEnd() {
    return '</pre></body></html>';
}

function formHtml() {
    global $installerHead, $formBody;
    return $installerHead . $formBody . '</html>';
}

function finishOk($site) {
    $out = "\n\n======================== Setup completed! ========================";
    $out .= "\n* Please delete the ./install directory and all its included files.";
    $out .= "\n* Visit <a href=\"/signup\">/signup</a> to create your account.";

    return $out;
}

function finishError() {
    global $backButton;

    $out = "\n====================== Something went wrong ======================";
    $out .= "\n$backButton";

    return $out;
}

$steps = [
    [
        'description' => 'Compatibility checks',
        'tasks' => [
            ['description' => 'PHP version', 'status' => null],
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

function proceed() {
    $out = '';
    if (configAlreadyExists()) {
        $out .= resultHtmlStart();
        $out .= "\nThe app is already configured.";
        $out .= resultHtmlEnd();

        echo $out;
        return;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $out .= resultHtmlStart();
        [$status, $result, $config] = execute($_POST);
        $out .= $result;
        $out .= $status ? finishOk($config['SITE']) : finishError();
        $out .= resultHtmlEnd();
    } else {
        $out .= formHtml();
    }

    echo $out;
}

function execute(array $values) {
    global $steps;

    $out = '';

    compatibilityCheck(0, $steps);
    $out .= printTasks($steps[0]);
    if (!tasksCompleted($steps[0])) {
        return [false, $out, null];
    }

    dbConfig(1, $values, $steps);
    $out .= printTasks($steps[1]);
    if (!tasksCompleted($steps[1])) {
        return [false, $out, null];
    }

    dbSaveConfig(2, $values, $steps);
    $out .= printTasks($steps[2]);
    if (!tasksCompleted($steps[2])) {
        return [false, $out, null];
    }

    $config = saveConfig(3, $values, $steps);
    $out .= printTasks($steps[3]);
    if (!tasksCompleted($steps[3])) {
        return [false, $out, null];
    }

    return [true, $out, $config];
}

function configAlreadyExists(): bool {
    return (getenv('SITE') && getenv('DATABASE_URL')) || file_exists('../config/config.local.ini');
}

function compatibilityCheck(int $step, array &$steps) {
    $steps[$step]['tasks'][0]['status'] = version_compare(PHP_VERSION, MIN_PHP_VERSION) >= 0;
    $steps[$step]['tasks'][1]['status'] = extension_loaded('pdo_pgsql') && extension_loaded('pgsql');

    try {
        if (is_writable('../config')) {
            $f = fopen('../config/config.local.ini', 'w');
            fclose($f);
            unlink('../config/config.local.ini');
            $steps[$step]['tasks'][2]['status'] = true;
        } else {
            $steps[$step]['tasks'][2]['status'] = false;
        }
    } catch (\Exception $e) {
        $steps[$step]['tasks'][2]['status'] = false;
    }

    $steps[$step]['tasks'][3]['status'] = is_file('../.htaccess') && is_readable('../.htaccess');

    $steps[$step]['tasks'][4]['status'] = extension_loaded('curl');

    $memoryLimit = @ini_get('memory_limit');
    $memLim = $memoryLimit;
    preg_match('#^(\d+)(\w+)$#', strtolower($memLim), $match);
    $memLim = match ($match[2]) {
        'g'     => intval($memLim) * 1024 * 1024 * 1024,
        'm'     => intval($memLim) * 1024 * 1024,
        'k'     => intval($memLim) * 1024,
        default => intval($memLim),
    };
    $steps[$step]['tasks'][5]['status'] = $memLim >= MIN_MEMORY_LIM;
}

function dbConfig(int $step, array &$values, array &$steps) {
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
        $httpHost = strtolower(filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL));
        if (strpos($httpHost, 'www.') === 0) {
            $httpHosts = substr($httpHost, 4);
        } elseif (substr_count($httpHost, '.') == 1) {
            $httpHost = 'www.' . $httpHost;
        }
        $httpHost = explode(':', $httpHost)[0];

        $configData = [
            'SITE'          => $httpHost,
            'DATABASE_URL'  => "postgres://$values[db_user]:$values[db_pass]@$values[db_host]:$values[db_port]/$values[db_name]",
            'PEPPER'        => strval(bin2hex(random_bytes(32))),
        ];
        if ($values['admin_email'] !== '') {
            $configData['ADMIN_EMAIL'] = $values['admin_email'];
        }

        $config = "\n[globals]";
        foreach ($configData as $key => $value) {
            $config .= "\n$key=$value";
        }

        $configPath = '../config/config.local.ini';
        $configFile = fopen($configPath, 'w');
        fwrite($configFile, $config);
        fclose($configFile);
        $steps[$step]['tasks'][0]['status'] = true;
    } catch (\Exception $e) {
        $steps[$step]['tasks'][0]['status'] = false;
        $steps[$step]['tasks'][0]['error'] = $e->getMessage();
    }

    return $configData;
}

function dbSaveConfig(int $step, array $values, array &$steps) {
    $database = null;

    $dsn = "pgsql:dbname=$values[db_name];host=$values[db_host];port=$values[db_port]";
    $driverOptions = array(
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
    );

    try {
        $database = new \PDO($dsn, $values['db_user'], $values['db_pass'], $driverOptions);
    } catch (\Exception $e) {
        $steps[$step]['tasks'][0]['status'] = false;
        $steps[$step]['tasks'][0]['error'] = $e->getMessage();
        return;
    }

    $steps[$step]['tasks'][0]['status'] = true;

    /*$query = $database->query('SELECT VERSION()');
    [$dbVersion] = $query->fetch(\PDO::FETCH_NUM);
    if (!preg_match('/PostgreSQL (\d+\.\d+)/', $dbVersion, $matches) || version_compare($matches[1], '12.0', '<')) {
        $steps[$step]['tasks'][1]['status'] = false;
        return;
    }
    $steps[$step]['tasks'][1]['status'] = true;*/

    try {
        $sql = file_get_contents('./install.sql');
        $database->exec($sql);
    } catch (\Exception $e) {
        $steps[$step]['tasks'][1]['status'] = false;
        $steps[$step]['tasks'][1]['error'] = $e->getMessage();
        return;
    }

    $steps[$step]['tasks'][1]['status'] = true;
}

function printTasks(array $tasks) {
    global $okTile, $failTile, $nullTile;

    $out = '';
    $side = intdiv(64 - strlen($tasks['description']), 2);
    $extra = (64 - strlen($tasks['description'])) % 2;
    $header = str_repeat('=', $side) . ' ' . $tasks['description'] . ' ' . str_repeat('=', $side);
    $out .= "\n\n" . $header;
    foreach ($tasks['tasks'] as $task) {
        $st = ($task['status'] === true) ? $okTile : (($task['status'] === false) ? $failTile : $nullTile);
        $err = array_key_exists('error', $task) ? ' (' . $task['error'] . ')' : '';
        $out .=  "\n" . $st . ' ' . $task['description'] . $err;
    }

    return $out;
}

function tasksCompleted(array $tasks) {
    $results = array_column($tasks['tasks'], 'status');

    return count(array_filter($results)) === count($results);
}


proceed();
