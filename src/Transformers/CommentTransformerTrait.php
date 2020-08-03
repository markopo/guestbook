<?php

namespace App\Transformers;

use App\Entity\Comment;

trait CommentTransformerTrait {


    /**
     * @param Comment[] $comments
     * @return array
     */
    public function transformCommentList($comments) {
        $commentsArr = [];

        foreach ($comments as $comment) {

            $commentsArr[] = [
                'id' => $comment->getId(),
                'author' => $comment->getAuthor(),
                'text' => $comment->getText(),
                'email' => $comment->getEmail(),
                'createdAt' => date_format($comment->getCreatedAt(), 'Y-m-d H:i:s'),
                'photoFileName' => $comment->getPhotoFileName()
            ];

        }

        return $commentsArr;
    }

}
