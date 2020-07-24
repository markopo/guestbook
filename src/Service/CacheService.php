<?php


namespace App\Service;


use Symfony\Component\Cache\Adapter\AdapterInterface;

class CacheService
{
    /**
     * 30 seconds cache
     */
    const CACHE_TIME = 'PT30S';

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getItemFromCache(string $key, Callable $callback) {
        $item = $this->cache->getItem($key);

        if(!$item->isHit()) {
            $value = $callback();
            $item->set(json_encode($value));
            $item->expiresAfter(new \DateInterval(self::CACHE_TIME));
            $this->cache->save($item);
            return $value;
        }

        return json_decode($item->get(), true);
    }
}
