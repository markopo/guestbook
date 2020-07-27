<?php


namespace App\Transformers;


use App\Entity\Conference;

trait ConferenceTransformerTrait
{
    use CommentTransformerTrait;

    /**
     * @param $conferences
     * @param bool $hasComments
     * @return array
     */
    public function transformList($conferences, bool $hasComments = false): array {
        $confArr = [];

        foreach ($conferences as $conference) {
             $confArr[] = $this->transformOne($conference, $hasComments);
        }

        return $confArr;
    }

    /**
     * @param Conference $conference
     * @param bool $hasComments
     * @return array
     */
    public function transformOne(Conference $conference, bool $hasComments = false): array  {
        return [
            'id' => $conference->getId(),
            'city' => $conference->getCity(),
            'year' => $conference->getYear(),
            'isInternational' => $conference->getIsInternational(),
            'comments' => $hasComments ? $this->transformCommentList($conference->getComments()) : [],
        ];
    }
}
