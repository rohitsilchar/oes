<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Small key/value cache used by the report endpoints.
| Uses Redis (config/redis.php) when the phpredis extension is loaded and the
| server is reachable, otherwise falls back to CodeIgniter's file cache.
| Every call fails soft: a cache problem never breaks the calling request.
*/
class Fast_cache
{
    private $CI;
    private $redis = null;
    private $prefix = 'oes_';
    private $default_ttl = 300;

    public function __construct()
    {
        $this->CI =& get_instance();

        if ($this->CI->config->load('redis', TRUE, TRUE)) {
            $this->default_ttl = (int)($this->CI->config->item('default_ttl', 'redis') ?: 300);
        }

        if (extension_loaded('redis')) {
            try {
                $redis = new Redis();
                $host = $this->CI->config->item('host', 'redis') ?: '127.0.0.1';
                $port = (int)($this->CI->config->item('port', 'redis') ?: 6379);
                $timeout = (float)($this->CI->config->item('timeout', 'redis') ?: 1);
                $connected = $this->CI->config->item('socket_type', 'redis') === 'unix'
                    ? $redis->connect($host)
                    : $redis->connect($host, $port, $timeout);

                $password = $this->CI->config->item('password', 'redis');
                if ($connected && !empty($password)) {
                    $connected = $redis->auth($password);
                }
                if ($connected) {
                    $this->redis = $redis;
                }
            } catch (Exception $e) {
                log_message('debug', 'Fast_cache: Redis unavailable, using file cache. ' . $e->getMessage());
            }
        }

        if ($this->redis === null) {
            $this->CI->load->driver('cache', ['adapter' => 'file']);
        }
    }

    public function get($key)
    {
        try {
            if ($this->redis !== null) {
                $value = $this->redis->get($this->prefix . $key);
                return $value === false ? false : unserialize($value);
            }
            return $this->CI->cache->get($this->prefix . $key);
        } catch (Exception $e) {
            return false;
        }
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ? (int)$ttl : $this->default_ttl;
        try {
            if ($this->redis !== null) {
                return $this->redis->setex($this->prefix . $key, $ttl, serialize($value));
            }
            return @$this->CI->cache->save($this->prefix . $key, $value, $ttl);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($key)
    {
        try {
            if ($this->redis !== null) {
                return (bool)$this->redis->del($this->prefix . $key);
            }
            return $this->CI->cache->delete($this->prefix . $key);
        } catch (Exception $e) {
            return false;
        }
    }
}
