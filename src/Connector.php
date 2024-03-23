<?php

namespace Fusio\Worker;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Elasticsearch\ClientBuilder;
use GuzzleHttp\Client;
use PSX\Record\Record;

class Connector
{
    private Record $configs;
    private array $connections;

    public function __construct(Record $configs)
    {
        $this->configs = $configs;
        $this->connections = [];
    }

    public function getConnection(string $name): mixed
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (!$this->configs->containsKey($name)) {
            throw new \RuntimeException('Connection does not exist');
        }

        $connection = $this->configs->get($name);
        $config = \json_decode(\base64_decode($connection->config ?? ''));

        if ($connection->type === 'Fusio.Adapter.Sql.Connection.Sql') {
            $database = $config->database ?? null;
            $username = $config->username ?? null;
            $password = $config->password ?? null;
            $host = $config->host ?? null;
            $type = $config->type ?? null;

            return $this->connections[$name] = DriverManager::getConnection([
                'dbname'   => $database,
                'user'     => $username,
                'password' => $password,
                'host'     => $host,
                'driver'   => $type,
            ]);
        } else if ($connection->type === 'Fusio.Adapter.Sql.Connection.SqlAdvanced') {
            $params = (new DsnParser())->parse($config->url ?? '');
            return $this->connections[$name] = DriverManager::getConnection($params);
        } else if ($connection->type === 'Fusio.Adapter.Http.Connection.Http') {
            $options = [];

            $baseUri = $config->url ?? null;
            if (!empty($baseUri)) {
                $options['base_uri'] = $baseUri;
            }

            $username = $config->username ?? null;
            $password = $config->password ?? null;
            if (!empty($username) && !empty($password)) {
                $options['auth'] = [$username, $password];
            }

            $proxy = $config->proxy ?? null;
            if (!empty($proxy)) {
                $options['proxy'] = $proxy;
            }

            $options['http_errors'] = false;

            return $this->connections[$name] = new Client($options);
        } else if ($connection->type === 'Fusio.Adapter.Mongodb.Connection.MongoDB') {
            $client = new \MongoDB\Client($config->url);
            $database = $client->selectDatabase($config->database);

            return $this->connections[$name] = $database;
        } else if ($connection->type === 'Fusio.Adapter.Elasticsearch.Connection.Elasticsearch') {
            $client = ClientBuilder::create()
                ->setHosts(explode(',', $config->host))
                ->build();

            return $this->connections[$name] = $client;
        } else {
            throw new \RuntimeException('Provided a not supported connection type');
        }
    }
}
