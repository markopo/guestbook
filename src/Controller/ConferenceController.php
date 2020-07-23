<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use App\Transformers\ConferenceTransformerTrait;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\False_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ConferenceController extends AbstractController
{
    use ConferenceTransformerTrait;

    private ConferenceRepository $conferenceRepository;

    private AdapterInterface $cache;

    /**
     * 30 seconds cache
     */
    const CACHE_TIME = 'PT30S';

    public function __construct(ConferenceRepository $conferenceRepository,
                                AdapterInterface $cache)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->cache = $cache;
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $itemCb = fn() => 'bla bla';
        $itemFromCache = $this->getItemFromCache('guestbook.cache.testcachekey', $itemCb);

        $confCb = fn() => $this->transform($this->conferenceRepository->findAll());
        $conferences = $this->getItemFromCache('guestbook.cache.conferences', $confCb);

        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'conferences' => $conferences,
            'isCached' => $itemFromCache,
            'now' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @Route("/conference/{id}", name="conference")
     */
    public function show(Conference $conference) {

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $conference->getComments()
        ]);
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return \Symfony\Component\Cache\CacheItem
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function getItemFromCache(string $key, Callable $callback) {
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
