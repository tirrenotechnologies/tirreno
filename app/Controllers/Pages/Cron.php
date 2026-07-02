<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

namespace Tirreno\Controllers\Pages;

// can accept time params as `* * * * 0,1,2`, `0-15 * * 1 3`, but
// not step values like `23/4 10/2 * * *`
// comma-expressions should be wrapped in quotes like "0-10 0,12 * * *"
class Cron extends \Tirreno\Controllers\Pages\Base {
    private int $handler = 0;
    private int $expression = 1;
    private string $method = 'process';

    protected array $jobs = [];
    protected array $forceRun = [];
    protected bool $runForcedOnly = false;

    protected int $timer;

    protected string $page = 'cron';

    protected \Tirreno\Entities\Operator $operator;

    public function __construct() {
        $this->timer = tirreno('request')->setTimer();

        tirreno('storage')->set('ONERROR', tirreno('utils')->errorHandler->getCronErrorHandler());

        if (!tirreno('utils')->database->initConnect(false)) {
            tirreno('response')->error(404);
        }

        if (tirreno('request')->isCli()) {
            tirreno('session')->set('active_user_id', tirreno('utils')->constants->DAEMON_OPERATOR_ID);
        }

        tirreno('utils')->routes->setCurrentRequestOperator();

        $this->operator = tirreno('utils')->routes->getCurrentRequestOperator();

        if (!$this->isAllowed()) {
            $this->notAllowed();
        }

        tirreno('utils')->routes->callExtra('PAGE_BASE');

        tirreno('log')->debug('cron construct for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function beforeroute(): void {
        tirreno('log')->debug('operator %s with roles %s accessing %s', $this->operator->email, json_encode($this->operator->roles), tirreno('request')->getUri());

        $timer = tirreno('request')->setTimer();

        if (!tirreno('request')->isCli()) {
            tirreno('response')->error(404);
        }

        $this->response = new \Tirreno\Views\Json();
        $this->response->data = [];

        tirreno('log')->debug('cron beforeroute for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($timer));
    }

    public function afterroute(): void {
        //echo $this->response->render();
        tirreno('log')->debug('whole route processing for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function index(): void {
        $this->assertCanAdmin();

        while (ob_get_level()) {
            ob_end_flush();
        }
        ob_implicit_flush(true);

        tirreno('utils')->updates->syncUpdates();

        $this->readArguments();
        $this->loadCrons();
        $this->validateForcedJobs();
        $this->run();
    }

    public function addJob(string $jobName, string $handler, string $expression): void {
        if (!preg_match('/^[\w\-]+$/', $jobName)) {
            throw new \Exception('Invalid job name.');
        }

        $this->jobs[$jobName] = [$handler, $expression];
    }

    public function run(\DateTime|null $time = null): void {
        if (!$time) {
            $time = new \DateTime();
        }

        $toRun = $this->getJobsToRun($time);
        if (!count($toRun)) {
            echo sprintf('No jobs to run at %s%s', $time->format('Y-m-d H:i:s'), PHP_EOL);
            exit;
        }

        foreach ($toRun as $jobName) {
            $this->execute($jobName);
        }
    }

    private function readArguments(): void {
        $argv = $GLOBALS['argv'];

        foreach ($argv as $position => $argument) {
            if ($argument === '--force') {
                if (array_key_exists($position + 1, $argv)) {
                    $this->forceRun[] = $argv[$position + 1];
                } else {
                    echo 'No job specified to force. Ignoring flag.' . PHP_EOL;
                }
            } elseif ($argument === '--force-only') {
                $this->runForcedOnly = true;
            }
        }
    }

    private function loadCrons(): void {
        tirreno('router')->config('config/crons.ini');

        $crons = (array) tirreno('storage')->get('crons');
        foreach (array_keys($crons) as $jobName) {
            if (substr($jobName, 0, 1) !== '#') {
                $cron = $crons[$jobName];
                $this->addJob($jobName, $cron[$this->handler], $cron[$this->expression]);
            }
        }
    }

    private function validateForcedJobs(): void {
        $notFound = array_diff($this->forceRun, array_keys($this->jobs));
        foreach ($notFound as $flagArgument) {
            echo sprintf('Job not found. Ignoring --force %s flag.%s', $flagArgument, PHP_EOL);
        }

        $this->forceRun = array_diff($this->forceRun, $notFound);
    }

    public function execute(string $jobName): void {
        if (!isset($this->jobs[$jobName])) {
            throw new \Exception('Job does not exist.');
        }

        $job = $this->jobs[$jobName];
        $class = $job[$this->handler];
        $method = $this->method;
        $instance = new $class();

        if (!method_exists($instance, $method)) {
            throw new \Exception('Invalid job handler.');
        }

        $instance->$method();
        tirreno('utils')->cron->printLogs($instance->getLog());
    }

    private function isDue(\DateTime $time, string $expression): bool {
        $parts = tirreno('utils')->cron->parseExpression($expression);
        if (!$parts) {
            return false;
        }

        foreach (tirreno('utils')->cron->parseTimestamp($time) as $i => $k) {
            if (!in_array($k, $parts[$i])) {
                return false;
            }
        }

        return true;
    }

    private function getJobsToRun(\DateTime $time): array {
        if ($this->runForcedOnly) {
            return $this->forceRun;
        }

        $toRun = array_keys($this->jobs);
        $toRun = array_filter($toRun, function ($jobName) use ($time) {
            return $this->isDue($time, $this->jobs[$jobName][$this->expression]);
        });

        return array_unique(array_merge($toRun, $this->forceRun));
    }
}
