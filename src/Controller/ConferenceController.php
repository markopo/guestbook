<?php

namespace App\Controller;


use App\Service\CacheService;
use App\Service\CommentTransformerService;
use App\Service\ConferenceTransformerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{

    private CacheService $cacheService;

    private ConferenceTransformerService $conferenceTransformerService;

    public function __construct(ConferenceTransformerService $conferenceTransformerService,
                                CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->conferenceTransformerService = $conferenceTransformerService;
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        $itemCb = fn() => 'bla bla';
        $itemFromCache = $this->cacheService->getItemFromCache('guestbook.cache.testcachekey', $itemCb);

        $confCb = fn() => $this->conferenceTransformerService->getAll();
        $conferences = $this->cacheService->getItemFromCache('guestbook.cache.conferences', $confCb);

        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'conferences' => $conferences,
            'isCached' => $itemFromCache,
            'now' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * @Route("/conference/{slug}", name="conference")
     */
    public function show(string $slug) {

        $conferenceCb = fn() => $this->conferenceTransformerService->getOne($slug, true);
        $confCacheKey = "guestbook.cache.conference.{$slug}";
        $conference = $this->cacheService->getItemFromCache($confCacheKey, $conferenceCb);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
        ]);
    }


}
