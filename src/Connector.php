<?php

namespace Fusio\Worker;

use Doctrine\DBAL\DriverManager;
use GuzzleHttp\Client;

class Connector
{
    private \stdClass $configs;
    private array $connections;

    public function __construct(\stdClass $configs)
    {
        $this->configs = $configs;
        $this->connections = [];
    }

    public function getConnection(string $name)
    {
        if ($this->connections[$name]) {
            return $this->connections[$name];
        }

        if (!isset($this->configs->{$name})) {
            throw new \RuntimeException('Connection does not exist');
        }

        $config = $this->configs->{$name};

        if ($config->type === 'Fusio.Adapter.Sql.Connection.Sql') {
            $params = [
                'dbname'   => $config->config->database,
                'user'     => $config->config->username,
                'password' => $config->config->password,
                'host'     => $config->config->host,
                'driver'   => $config->config->type,
            ];

            return $this->connections[$name] = DriverManager::getConnection($params);
        } else if ($config->type === 'Fusio.Adapter.Sql.Connection.SqlAdvanced') {
            $params = [
                'url' => $config->config->url,
            ];

            return $this->connections[$name] = DriverManager::getConnection($params);
        } else if ($config->type === 'Fusio.Adapter.Http.Connection.Http') {
            $options = [];

            $baseUri = $config->config->url ?? null;
            if (!empty($baseUri)) {
                $options['base_uri'] = $baseUri;
            }

            $username = $config->config->username ?? null;
            $password = $config->config->password ?? null;
            if (!empty($username) && !empty($password)) {
                $options['auth'] = [$username, $password];
            }

            $proxy = $config->config->proxy ?? null;
            if (!empty($proxy)) {
                $options['proxy'] = $proxy;
            }

            $options['http_errors'] = false;

            return $this->connections[$name] = new Client($options);
        } else {
            throw new \RuntimeException('Provided a not supported connection type');
        }
    }
}
