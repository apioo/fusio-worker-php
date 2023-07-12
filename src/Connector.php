<?php

namespace Fusio\Worker;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Elasticsearch\ClientBuilder;
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

    public function getConnection(string $name): mixed
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (!isset($this->configs->{$name})) {
            throw new \RuntimeException('Connection does not exist');
        }

        $config = $this->configs->{$name};

        if ($config->type === 'Fusio.Adapter.Sql.Connection.Sql') {
            $database = $config->config->database ?? null;
            $username = $config->config->username ?? null;
            $password = $config->config->password ?? null;
            $host     = $config->config->host ?? null;
            $type     = $config->config->type ?? null;

            return $this->connections[$name] = DriverManager::getConnection([
                'dbname'   => $database,
                'user'     => $username,
                'password' => $password,
                'host'     => $host,
                'driver'   => $type,
            ]);
        } else if ($config->type === 'Fusio.Adapter.Sql.Connection.SqlAdvanced') {
            $params = (new DsnParser())->parse($config->config->url ?? '');
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
        } else if ($config->type === 'Fusio.Adapter.Mongodb.Connection.MongoDB') {
            $client = new \MongoDB\Client($config->config->url);
            $database = $client->selectDatabase($config->config->database);

            return $this->connections[$name] = $database;
        } else if ($config->type === 'Fusio.Adapter.Elasticsearch.Connection.Elasticsearch') {
            $client = ClientBuilder::create()
                ->setHosts(explode(',', $config->config->host))
                ->build();

            return $this->connections[$name] = $client;
        } else {
            throw new \RuntimeException('Provided a not supported connection type');
        }
    }
}
