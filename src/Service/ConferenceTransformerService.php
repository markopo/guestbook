<?php


namespace App\Service;


use App\Repository\ConferenceRepository;
use App\Transformers\ConferenceTransformerTrait;

class ConferenceTransformerService
{
    use ConferenceTransformerTrait;

    private ConferenceRepository $conferenceRepository;

    public function __construct(ConferenceRepository $conferenceRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
    }

    public function getAll(bool $hasComments = false) {
        return $this->transformList($this->conferenceRepository->findAll(), $hasComments);
    }

    public function getOne(int $id, bool $hasComments = false) {
        return $this->transformOne($this->conferenceRepository->find($id), $hasComments);
    }
}
