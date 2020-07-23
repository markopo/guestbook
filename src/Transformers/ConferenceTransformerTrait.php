<?php


namespace App\Transformers;


use App\Entity\Conference;

trait ConferenceTransformerTrait
{

    /**
     * @param Conference[] $conferences
     * @return array
     */
    public function transform($conferences): array {
        $confArr = [];

        foreach ($conferences as $conference) {
             $confArr[] = [
                 'id' => $conference->getId(),
                 'city' => $conference->getCity(),
                 'year' => $conference->getYear(),
                 'isInternational' => $conference->getIsInternational()
             ];
        }

        return $confArr;
    }
}
