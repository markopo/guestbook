<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Service\CacheService;
use App\Service\CommentTransformerService;
use App\Service\ConferenceTransformerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConferenceController extends AbstractController
{

    private CacheService $cacheService;

    private ConferenceTransformerService $conferenceTransformerService;

    private EntityManagerInterface $entityManager;

    private ConferenceRepository $conferenceRepository;

    private CommentRepository $commentRepository;

    public function __construct(ConferenceTransformerService $conferenceTransformerService,
                                CacheService $cacheService,
                                EntityManagerInterface $entityManager,
                                ConferenceRepository $conferenceRepository,
                                CommentRepository $commentRepository)
    {
        $this->cacheService = $cacheService;
        $this->conferenceTransformerService = $conferenceTransformerService;
        $this->entityManager = $entityManager;
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
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
    public function show(string $slug, Request $request, string $photoDir) {

        $conferenceCb = fn() => $this->conferenceTransformerService->getOne($slug, true);
        $confCacheKey = "guestbook.cache.conference.{$slug}";
        $conference = $this->cacheService->getItemFromCache($confCacheKey, $conferenceCb);
        $conferenceId = $conference !== null ? $conference['id'] : 0;

        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if($conferenceId > 0) {
                $conf = $this->conferenceRepository->find($conferenceId);
                $comment->setConference($conf);
                $comment->setCreatedAtValue();

                /** @var UploadedFile $brochureFile */
                $photo = $form->get('photoFileName')->getData();

                if($photo !== null) {
                    $fileName = bin2hex(random_bytes(6)).'.'. $photo->guessClientExtension();

                    try {
                        $photo->move($photoDir, $fileName);
                    }
                    catch (FileException $fileException) {
                        dd($fileException);
                    }

                    $comment->setPhotoFileName($fileName);
                }

                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                $this->cacheService->clearCacheKey($confCacheKey);

                return $this->redirectToRoute('conference', [ 'slug' => $conf->getSlug() ]);
            }
        }

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comment_form' => $form->createView()
        ]);
    }


}
