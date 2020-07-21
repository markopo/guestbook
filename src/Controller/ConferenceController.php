<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{

    /**
     * @var ConferenceRepository
     */
    private $conferenceRepository;


    public function __construct(ConferenceRepository $conferenceRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
            'conferences' => $this->conferenceRepository->findAll()
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
