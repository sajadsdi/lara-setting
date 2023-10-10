<?php

namespace Sajadsdi\LaraSetting\Services;

use Sajadsdi\ArrayDotNotation\DotNotation;
use Sajadsdi\LaraSetting\Contracts\CacheDriverInterface;
use Sajadsdi\LaraSetting\Contracts\StoreDriverInterface;

class SettingStore
{
    private array           $drivers = [];
    private array           $config  = [];
    private CacheDriverInterface $cache;

    public function __construct(array $config = [])
    {
        $this->config = $config ? $config : config('lara-setting');
        if ($this->cacheEnabled()) {
            $this->setCache();
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function get($name): mixed
    {
        $data = null;
        if ($this->cacheEnabled()) {
            $data = $this->cache->get($name);
        }
        if ($data === null) {
            $data = $this->getDriver($this->config['store']['default'])->get($name);
            if ($this->cacheEnabled() && $data !== null) {
                $this->cache->set($name, $data);
            }
        }
        return $data;
    }

    /**
     * @param string $name
     * @param mixed $data
     * @return void
     */
    public function set(string $name, mixed $data): void
    {
        $this->getDriver($this->config['store']['default'])->set($name, $data);
    }

    /**
     * @return bool
     */
    private function cacheEnabled(): bool
    {
        return $this->config['cache']['enabled'];
    }

    /**
     * @param string $name
     * @return StoreDriverInterface
     */
    private function getDriver(string $name): StoreDriverInterface
    {
        if (!isset($this->drivers[$name])) {
            $this->setDriver($name,new $this->config['store']['drivers'][$name]['class']($this->config['store']['drivers'][$name]));
        }
        return $this->drivers[$name];
    }

    /**
     * @param string $name
     * @param StoreDriverInterface $class
     * @return void
     */
    private function setDriver(string $name, StoreDriverInterface $class)
    {
        $this->drivers[$name] = $class;
    }

    /**
     * @return void
     */
    private function setCache(): void
    {
        $this->cache = new $this->config['cache']['class']($this->config['cache']);
    }

    /**
     * @return CacheDriverInterface
     */
    public function cache(): CacheDriverInterface
    {
        return $this->cache;
    }
}