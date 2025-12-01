<?php

/**
 * tirreno ~ open security analytics
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

namespace Sensor\Service;

use Sensor\Controller\CreateEventController;
use Sensor\Factory\EnrichedDataFactory;
use Sensor\Factory\EventFactory;
use Sensor\Factory\LogbookEntityFactory;
use Sensor\Factory\RequestFactory;
use Sensor\Model\Config\Config;
use Sensor\Model\Config\DatabaseConfig;
use Sensor\Repository\AccountRepository;
use Sensor\Repository\ApiKeyRepository;
use Sensor\Repository\BlacklistRepository;
use Sensor\Repository\CountryRepository;
use Sensor\Repository\DeviceRepository;
use Sensor\Repository\DomainRepository;
use Sensor\Repository\EmailRepository;
use Sensor\Repository\EventCountryRepository;
use Sensor\Repository\EventIncorrectRepository;
use Sensor\Repository\EventRepository;
use Sensor\Repository\FieldAuditTrailRepository;
use Sensor\Repository\FieldAuditRepository;
use Sensor\Repository\PayloadRepository;
use Sensor\Repository\IpAddressRepository;
use Sensor\Repository\IspRepository;
use Sensor\Repository\PhoneRepository;
use Sensor\Repository\RefererRepository;
use Sensor\Repository\SessionRepository;
use Sensor\Repository\UrlQueryRepository;
use Sensor\Repository\UrlRepository;
use Sensor\Repository\UserAgentRepository;
use Sensor\Repository\LogbookRepository;
use Sensor\Service\Debug\PdoProxy;
use Sensor\Service\Enrichment\DataEnrichmentClientInterface;
use Sensor\Service\Enrichment\DataEnrichmentCurlClient;
use Sensor\Service\Enrichment\DataEnrichmentPhpClient;
use Sensor\Service\Enrichment\DataEnrichmentService;
use Sensor\Service\DeviceDetectorService;
use Sensor\Entity\LogbookEntity;
use Sensor\Model\Http\RegularResponse;
use Sensor\Model\Http\ErrorResponse;
use Sensor\Model\Http\ValidationFailedResponse;
use Sensor\Model\Http\Request;

class DI {
    private ?\PDO $pdo = null;
    private ?Logger $logger = null;
    private ?Profiler $profiler = null;
    private ?Config $config = null;

    public function __construct() {
        $config = $this->getConfig();

        if ($config === null) {
            throw new \RuntimeException('DATABASE_URL is not set');
        }
    }

    public function getController(): ?CreateEventController {
        $config = $this->getConfig();
        $pdo = $this->getPdo();
        $profiler = $this->getProfiler();
        $logger = $this->getLogger();
        $ispRepository = new IspRepository($pdo);
        $accountRepository = new AccountRepository($pdo);
        $domainRepository = new DomainRepository($pdo);
        $emailRepository = new EmailRepository($domainRepository, $pdo);
        $phoneRepository = new PhoneRepository($pdo);
        $userAgentRepository = new UserAgentRepository($pdo);

        $enrichmentService = null;

        if (!empty($config->enrichmentApiUrl)) {
            $enrichmentService = new DataEnrichmentService(
                $this->getEnrichmentClient(),
                new EnrichedDataFactory($logger),
                new IpAddressRepository($ispRepository, $pdo),
                $emailRepository,
                $domainRepository,
                $phoneRepository,
                $config,
                $profiler,
                $logger,
            );

            $logger->logDebug('Using enrichment API ' . $config->enrichmentApiUrl);
        } else {
            $logger->logDebug('Skipping enrichment, because URL and/or key are not set');
        }

        return new CreateEventController(
            new RequestFactory(),
            new EventFactory(new CountryRepository($pdo)),
            new ConnectionService(),
            new QueryParser(),
            $enrichmentService,
            new DeviceDetectorService($userAgentRepository),
            new FraudDetectionService(
                new BlacklistRepository($this->getPdo()),
            ),
            new ApiKeyRepository($pdo),
            new EventRepository(
                $accountRepository,
                new SessionRepository($pdo),
                new IpAddressRepository($ispRepository, $pdo),
                $ispRepository,
                new UrlRepository(new UrlQueryRepository($pdo), $pdo),
                new DeviceRepository($userAgentRepository, $pdo),
                new RefererRepository($pdo),
                $emailRepository,
                $domainRepository,
                $phoneRepository,
                new EventCountryRepository($pdo),
                new FieldAuditTrailRepository($pdo),
                new FieldAuditRepository($pdo),
                new PayloadRepository($pdo),
                $pdo,
            ),
            $accountRepository,
            $pdo,
            $profiler,
            $logger,
        );
    }

    public function getLogger(): Logger {
        return $this->logger ??= new Logger($this->getConfig(true)?->debugLog || false);
    }

    public function getProfiler(): Profiler {
        return $this->profiler ??= new Profiler();
    }

    public function getLogbookManager(): LogbookManager {
        $pdo = $this->getPdo();

        return new LogbookManager(
            new LogbookEntityFactory(),
            new LogbookRepository($pdo),
            new ApiKeyRepository($pdo),
            new EventIncorrectRepository($pdo),
            $this->config?->allowEmailPhone ?? false,
            $this->config?->leakyBuckerRps ?? 5,
            $this->config?->leakyBucketWindow ?? 5,
        );
    }

    private function getPdo(): \PDO {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $config = $this->getConfig();
        $pdoConfig = [
            sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;',
                $config->databaseConfig->dbHost,
                $config->databaseConfig->dbPort,
                $config->databaseConfig->dbDatabaseName,
            ),
            $config->databaseConfig->dbUser,
            $config->databaseConfig->dbPassword,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ];

        if ($config->debugLog) {
            $this->pdo = new PdoProxy(...$pdoConfig);
            $this->pdo->setLogger($this->getLogger());
        } else {
            $this->pdo = new \PDO(...$pdoConfig);
        }

        return $this->pdo;
    }

    /**
     * @return array<string, string>
     */
    private function loadConfigFromFile(): array {
        /** @var string[] $iniFiles */
        $iniFiles = array_merge(
            glob(__DIR__ . '/../../../config/config.ini') ?: [],
            glob(__DIR__ . '/../../../config/local/config.local.ini') ?: [],
        );
        $config = [];

        foreach ($iniFiles as $file) {
            /** @var array<string, string> $settings */
            $settings = parse_ini_file($file, false, INI_SCANNER_TYPED);
            $config = array_merge($config, $settings);
        }

        return $config;
    }

    /**
     * @param array<string, string> $config
     */
    private function parseDatabaseConfig(array $config): ?DatabaseConfig {
        if (isset($config['DATABASE_URL'])) {
            $dbParts = parse_url($config['DATABASE_URL']);

            if (
                $dbParts === false || !isset(
                    $dbParts['scheme'],
                    $dbParts['user'],
                    $dbParts['pass'],
                    $dbParts['host'],
                    $dbParts['port'],
                    $dbParts['path']
                )
            ) {
                throw new \Exception('Invalid DB URL.');
            }

            return new DatabaseConfig(
                dbHost: $dbParts['host'],
                dbPort: is_numeric($dbParts['port']) ? intval($dbParts['port']) : 0,
                dbUser: $dbParts['user'],
                dbPassword: $dbParts['pass'],
                dbDatabaseName: ltrim($dbParts['path'], '/'),
            );
        }

        return null;
    }

    private function loadAppVersion(): ?string {
        $path = __DIR__ . '/../../../app/Utils/VersionControl.php';

        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $res = include_once $path;
        if ($res === false) {
            return null;
        }

        return \Utils\VersionControl::versionString();
    }

    private function loadConfigFromEnv(): array {
        $data = [];
        $keys = [
            'DATABASE_URL',
            'APP_USER_AGENT',
            'ENRICHMENT_API_URL',
            'ALLOW_EMAIL_PHONE',
            'LEAKY_BUCKET_RPS',
            'LEAKY_BUCKET_WINDOW',
            'DEBUG',
        ];

        foreach ($keys as $key) {
            $val = getenv($key);
            if ($val !== false) {
                $data[$key] = $val;
            }
        }

        return $data;
    }

    private function getConfig(bool $silent = false): ?Config {
        if ($this->config !== null) {
            return $this->config;
        }

        $config = array_merge($this->loadConfigFromFile(), $this->loadConfigFromEnv());
        $dbConfig = $this->parseDatabaseConfig($config);

        if ($dbConfig === null) {
            return null;
        }

        $version = $this->loadAppVersion();
        $useragent = $config['APP_USER_AGENT'] ?? null;
        $useragent = ($version && $useragent) ? $useragent . '/' . strval($version) : $useragent;

        $this->config = new Config(
            databaseConfig:     $dbConfig,
            enrichmentApiUrl:   $config['ENRICHMENT_API'] ?? null,
            userAgent:          $useragent,
            debugLog:           $this->toBool($config['DEBUG'] ?? null),
            allowEmailPhone:    $this->toBool($config['ALLOW_EMAIL_PHONE'] ?? null),
            leakyBucketRps:     $this->toInt($config['LEAKY_BUCKET_RPS'] ?? null, 5),
            leakyBucketWindow:  $this->toInt($config['LEAKY_BUCKET_WINDOW'] ?? null, 5),
        );

        if (!$silent) {
            $this->getLogger()->logDebug('Config loaded from ENV variables: ' . json_encode($this->config, \JSON_THROW_ON_ERROR));

            if (empty($this->config->enrichmentApiUrl)) {
                $this->getLogger()->logWarning('The enrichment API URL is missing in the configuration. This URL is required for the app\'s enrichment features to function properly.');
            }
        }

        return $this->config;
    }

    private function getEnrichmentClient(): DataEnrichmentClientInterface {
        $config = $this->getConfig();

        if (empty($config->enrichmentApiUrl)) {
            throw new \RuntimeException('Enrichment API URL or key are not set');
        }

        if (function_exists('curl_init')) {
            return new DataEnrichmentCurlClient($config->enrichmentApiUrl, $config->userAgent);
        } else {
            return new DataEnrichmentPhpClient($config->enrichmentApiUrl, $config->userAgent);
        }
    }

    private function toBool(string|int|bool|null $value): bool {
        return is_string($value) ? filter_var($value, FILTER_VALIDATE_BOOLEAN) : boolval($value);
    }

    public static function toInt(mixed $value, ?int $default = null): ?int {
        $validated = filter_var($value, FILTER_VALIDATE_INT);

        return $validated !== false ? $validated : (is_float($value) || is_bool($value) ? intval($value) : $default);
    }
}
