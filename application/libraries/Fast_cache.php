<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Fast_cache
| -------------------------------------------------------------------
| Small key/value cache shared by all web servers.
| Uses Redis/Valkey (config/redis.php) when the phpredis extension is loaded
| and the server is reachable, otherwise falls back to CodeIgniter's file cache.
| Every call fails soft: a cache problem never breaks the calling request.
|
| 2026-10-30: persistent connection (one TLS connection per PHP-FPM worker
| instead of one per request) and gzip for values larger than 8 KB.
| 2026-10-30 (v2): two-level cache. Each server keeps a short-lived local copy
| in APCu (shared memory, default 30 s), so most pages never call Redis.
| Load test run 9 showed every page pulling ~40 KB from Redis, which hit the
| serverless ECPU limit at ~1,000 users. 'cfg_ver' is never kept locally, so
| admin changes still show immediately on every server.
*/
class Fast_cache
{
    private $CI;
    private $redis = null;
    private $prefix = 'oes_';
    private $default_ttl = 300;
    private $local = false;             // APCu available?
    private $local_ttl = 30;            // seconds a server keeps its local copy
    private $local_skip = array('cfg_ver' => true);

    public function __construct()
    {
        $this->CI =& get_instance();

        if ($this->CI->config->load('redis', TRUE, TRUE)) {
            $this->default_ttl = (int)($this->CI->config->item('default_ttl', 'redis') ?: 300);
            $lt = $this->CI->config->item('local_ttl', 'redis');
            if ($lt !== NULL && $lt !== FALSE) {
                $this->local_ttl = (int)$lt;
            }
        }
        $this->local = $this->local_ttl > 0 && function_exists('apcu_fetch') && (bool)ini_get('apc.enabled');

        if (extension_loaded('redis')) {
            try {
                $redis      = new Redis();
                $host       = $this->CI->config->item('host', 'redis') ?: '127.0.0.1';
                $port       = (int)($this->CI->config->item('port', 'redis') ?: 6379);
                $timeout    = (float)($this->CI->config->item('timeout', 'redis') ?: 1);
                $persistent = (bool)$this->CI->config->item('persistent', 'redis');

                if ($this->CI->config->item('socket_type', 'redis') === 'unix') {
                    $connected = $redis->connect($host);
                } elseif ($persistent) {
                    $connected = $redis->pconnect($host, $port, $timeout, 'oes');
                } else {
                    $connected = $redis->connect($host, $port, $timeout);
                }

                $password = $this->CI->config->item('password', 'redis');
                if ($connected && !empty($password)) {
                    $connected = $redis->auth($password);
                }
                if ($connected) {
                    $redis->setOption(Redis::OPT_READ_TIMEOUT, $timeout);
                    $this->redis = $redis;
                }
            } catch (Throwable $e) {
                log_message('debug', 'Fast_cache: Redis unavailable, using file cache. ' . $e->getMessage());
            }
        }

        if ($this->redis === null) {
            $this->CI->load->driver('cache', ['adapter' => 'file']);
        }
    }

    /** True when values are going to Redis/Valkey (not the local file cache). */
    public function using_redis()
    {
        return $this->redis !== null;
    }

    /** True when the per-server APCu layer is active. */
    public function using_local()
    {
        return $this->local;
    }

    public function get($key)
    {
        $use_local = $this->local && !isset($this->local_skip[$key]);
        if ($use_local) {
            $hit = false;
            $value = apcu_fetch($this->prefix . $key, $hit);
            if ($hit) {
                return $value;
            }
        }
        try {
            if ($this->redis !== null) {
                $raw = $this->redis->get($this->prefix . $key);
                $value = $raw === false ? false : $this->decode($raw);
            } else {
                $value = $this->CI->cache->get($this->prefix . $key);
            }
        } catch (Throwable $e) {
            return false;
        }
        if ($use_local && $value !== false) {
            apcu_store($this->prefix . $key, $value, $this->local_ttl);
        }
        return $value;
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ? (int)$ttl : $this->default_ttl;
        if ($this->local && !isset($this->local_skip[$key])) {
            apcu_store($this->prefix . $key, $value, min($ttl, $this->local_ttl));
        }
        try {
            if ($this->redis !== null) {
                return $this->redis->setex($this->prefix . $key, $ttl, $this->encode($value));
            }
            return @$this->CI->cache->save($this->prefix . $key, $value, $ttl);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function delete($key)
    {
        if ($this->local) {
            apcu_delete($this->prefix . $key);
        }
        try {
            if ($this->redis !== null) {
                return (bool)$this->redis->del($this->prefix . $key);
            }
            return $this->CI->cache->delete($this->prefix . $key);
        } catch (Throwable $e) {
            return false;
        }
    }

    // Large values (the translation table) are gzipped: less network and
    // fewer ElastiCache Serverless ECPUs, which are billed per KB transferred.
    private function encode($value)
    {
        $s = serialize($value);
        if (strlen($s) > 8192 && function_exists('gzcompress')) {
            return 'z:' . gzcompress($s, 6);
        }
        return $s;
    }

    private function decode($raw)
    {
        if (strncmp($raw, 'z:', 2) === 0) {
            $raw = @gzuncompress(substr($raw, 2));
            if ($raw === false) {
                return false;
            }
        }
        $v = @unserialize($raw);
        return ($v === false && $raw !== 'b:0;') ? false : $v;
    }
}
