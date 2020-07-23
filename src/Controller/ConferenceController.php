<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class ConferenceController extends AbstractController
{
    private ConferenceRepository $conferenceRepository;

    private AdapterInterface $cache;


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
        $item = $this->cache->getItem('testCacheKey');
        $itemFromCache = true;

        if(!$item->isHit()) {
            $itemFromCache = false;
            $item->set('bla bla');
            $item->expiresAfter(new \DateInterval('PT10S'));
            $this->cache->save($item);
        }

        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'conferences' => $this->conferenceRepository->findAll(),
            'isCached' => $itemFromCache ? 'true' : 'false',
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
}
