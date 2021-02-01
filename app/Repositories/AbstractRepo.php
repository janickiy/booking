<?php

namespace App\Repositories;


use Illuminate\{
    Database\Eloquent\Model as Eloquent,
    Support\Facades\Cache
};


/**
 * The base implementation of the repository.
 */
abstract class AbstractRepo
{
    /**
     * @var Eloquent
     */
    private $model;

    /**
     * @var bool
     */
    private $isLoaded = false;

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var
     */
    private $cacheKey;

    /**
     * @var
     */
    private $cacheTime;

    /**
     * @var bool
     */
    private $cacheEnable = true;

    /**
     * @return boolean
     */
    public function isCacheEnable()
    {
        return $this->cacheEnable;
    }

    /**
     * @param boolean $cacheEnable
     */
    public function setCacheEnable($cacheEnable)
    {
        $this->cacheEnable = $cacheEnable;
    }

    /**
     * @return int
     */
    public function getCacheTime()
    {
        return $this->cacheTime;
    }

    /**
     * @param int $cacheTime
     */
    public function setCacheTime($cacheTime)
    {
        $this->cacheTime = $cacheTime;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCache($name)
    {
        if (!$this->isCacheEnable()) {
            return false;
        }

        return Cache::tags([$this->cacheKey])->has($this->cacheKey . '_' . $name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getCache($name)
    {
        return Cache::tags([$this->cacheKey])->get($this->cacheKey . '_' . $name);
    }


    /**
     * @param $name
     * @param $data
     * @param null $time
     * @throws \ErrorException
     */
    public function putCache($name, $data, $time = null)
    {
        $time = (int)($time ?? $this->getCacheTime());
        if (!$time && config('app.debug')) {
            throw new \ErrorException("Для кеша {$name} не установлено время, кеш не будет записан");
        }
        if ($this->isCacheEnable()) {
            Cache::tags([$this->cacheKey])->put($this->cacheKey . '_' . $name, $data, $time);
        }

        return;
    }


    /**
     * Вызывает пользовательскую функцию и заносит ее результат в кеш, либо возвращает данные из кеша
     * @param string $name
     * @param \Closure $callback
     * @param null $time
     * @return mixed
     * @throws \ErrorException
     */
    public function rememberCache(string $name, \Closure $callback, $time = null)
    {
        if (!$this->hasCache($name)) {
            $data = $callback();
            $this->putCache($name, $data, $time);
            return $data;
        } else {
            return $this->getCache($name);
        }
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function forgetCache($name)
    {
        return Cache::tags([$this->cacheKey])->forget($this->cacheKey .'_' . $name);
    }

    /**
     * @return void
     */
    public function forgetCacheByKey()
    {
        Cache::tags($this->cacheKey)->flush();

        return;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey;
    }

    /**
     * Метод для подстановки ключа по общему шаблону
     * @param $pattern
     * @param $keys
     * @return mixed
     */
    public function getCacheKeyFromPattern($pattern, $keys)
    {
        foreach ($keys as $key_pattern => $key_value) {
            $pattern = str_replace($key_pattern, $key_value, $pattern);
        }

        return $pattern;
    }

    /**
     * @param string $cacheKey
     */
    public function setCacheKey($cacheKey)
    {
        $this->cacheKey = $cacheKey;
    }

    /**
     * @return Eloquent
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Eloquent $model
     *
     * @return static
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * @param boolean $isLoaded
     */
    public function setIsLoaded($isLoaded)
    {
        $this->isLoaded = $isLoaded;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @param string $name
     * @param array  $groups
     */
    public function addGroup($name, $groups)
    {
        $this->groups[$name] = $groups;
    }
}
