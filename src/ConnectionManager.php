<?php
/**
 * Created by PhpStorm.
 * User: marco.bunge
 * Date: 02.12.2016
 * Time: 14:00
 */

namespace Hawkbit\Database;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;

final class ConnectionManager
{

    const DEFAULT_CONNECTION = 'default';

    /**
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * @var Connection[]
     */
    protected $previousConnections = [];

    /**
     * @var Connection
     */
    protected $defaultConnection = null;

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * Get connection manager instance to share
     * connections between different instances.
     *
     * @return \Hawkbit\Database\ConnectionManager
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new self;
        }

        return static::$instance;
    }


    /**
     * Create a new connection from definition.
     *
     * If definition is a string, the manager tries to get definition from ioc container,
     * otherwise the manager assumes a valid dsn string and converts definition to an array.
     *
     * If definition is a string manager is determining wrapper class and tries to get wrapper
     * class from container.
     *
     * @param $definition
     * @return Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function create($definition)
    {
        if($definition instanceof Connection){
            $connection = $definition;
        }else{
            // assume a valid service from IoC container
            // or assume a valid dsn and convert to connection array
            if (is_string($definition)) {
                $definition = ['url' => $definition];
            }

            if (!is_array($definition)) {
                throw new DBALException('Unable to determine parameter array from definition');
            }

            $definition['wrapperClass'] = Connection::class;
            $connection = DriverManager::getConnection($definition);

            if (!($connection instanceof Connection)) {
                throw new \RuntimeException(sprintf('Connection needs to be an instance of %s', Connection::class));
            }
        }

        //setup special configuration for connections
        if ($connection instanceof Connection) {
            if (array_key_exists('prefix', $definition)) {
                $connection->setPrefix($definition['prefix']);
            }
        }

        return $connection;
    }

    /**
     * Close all connections on
     */
    public function __destruct()
    {
        $this->closeAll();
    }

    /**
     * Disconnect all connections and remove all
     * connections. Collect garbage at least.
     */
    public function closeAll()
    {
        $connections = $this->all();

        foreach ($connections as $connection) {
            if ($connection->isConnected()) {
                $connection->close();
            }
        }

        $this->connections = [];
        gc_collect_cycles();

    }

    /**
     * Get all connections
     *
     * @return Connection[]
     */
    public function all()
    {
        return $this->connections;
    }

    /**
     * Add a new connection to internal cache. Create connection
     * with `Blast\Orm\ConnectionManager::create`
     *
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection
     *
     * @param array|Connection|string $connection
     * @param string $name
     *
     * @return $this
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function add($connection, $name = self::DEFAULT_CONNECTION)
    {
        if ($this->has($name)) {
            throw new DBALException(sprintf('Connection with name %s already exists!', $name));
        }

        $this->connections[$name] = static::create($connection);

        //set first connection as active connection
        if (count($this->connections) === 1) {
            $this->swapActiveConnection($name);
        }

        return $this;
    }

    /**
     * Check if connections exists
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Swap current connection with another connection
     * by name and add previous connection to previous
     * connection stack.
     *
     * @param string $name
     *
     * @return $this
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function swapActiveConnection($name)
    {
        if (!$this->has($name)) {
            throw new DBALException(sprintf('Connection with name %s not found!', $name));
        }

        if ($this->defaultConnection !== null) {
            $this->previousConnections[] = $this->defaultConnection;
        }
        $this->defaultConnection = $this->get($name);

        return $this;
    }

    /**
     * Get connection by name.
     *
     * @param $name
     *
     * @return Connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function get($name = null)
    {
        if ($name === null) {
            return $this->defaultConnection;
        }
        if ($this->has($name)) {
            return $this->connections[$name];
        }

        throw new DBALException('Unknown connection ' . $name);
    }

    /**
     * @return array
     */
    public function getPrevious()
    {
        return $this->previousConnections;
    }
}