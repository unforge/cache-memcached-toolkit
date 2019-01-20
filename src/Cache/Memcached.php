<?php
/**
 * This file is part of the Cache library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ronam Unstirred (unforge.coder@gmail.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Unforge\Toolkit\Cache;

use Unforge\Toolkit\Arr;
use Unforge\Toolkit\Logger; // todo
use Unforge\Abstraction\Cache\AbstractCache;

/**
 * Class Memcached
 *
 * @package Unforge\Toolkit\Cache
 */
class Memcached extends AbstractCache
{
    /**
     * @var \Memcached
     */
    private $client;

    /**
     * @param array $config
     *
     * @throws \Exception
     */
    public function connect(array $config)
    {
        if (!extension_loaded('memcached')) {
            throw new \Exception("Extension memcached.so not installed");
        }

        $host   = Arr::getString($config, 'host');
        $port   = Arr::getInt($config, 'port', 11211);
        $weight = Arr::getFloat($config, 'weight', 0);

        if (!$host) {
            throw new \Exception("Host is required");
        }

        try {
            $this->client = new \Memcached();
            $this->client->addServer($host, $port, $weight);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string $key
     * @param string $value
     * @param string $prefix
     *
     * @return bool
     */
    public function set(string $key, string $value, string $prefix = 'cache'): bool
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->set($key, $value);
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $key
     * @param string $prefix
     *
     * @return string
     */
    public function get(string $key, string $prefix = 'cache'): string
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->get($key);
        } catch (\Exception $e) {
            // todo Logger
            return '';
        }
    }

    /**
     * @param string $key
     * @param string $prefix
     *
     * @return bool
     */
    public function del(string $key, string $prefix = 'cache'): bool
    {
        $key = $this->prepareKeyToString($prefix, $key);

        try {
            return $this->client->delete($key);
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $prefix
     *
     * @return bool
     */
    public function flush(string $prefix = 'cache'): bool
    {
        try {
            return $this->client->flush();
        } catch (\Exception $e) {
            // todo Logger
            return false;
        }
    }

    /**
     * @param string $prefix
     * @param string $key
     *
     * @return string
     */
    protected function prepareKeyToString(string $prefix, string $key): string
    {
        return $prefix . "_" . str_replace("//", "_", $key) . "_" . md5($key);
    }
}
