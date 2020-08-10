<?php


namespace App\Service;


use App\Entity\Comment;
use Snipe\BanBuilder\CensorWords;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamCheckerService
{
    private HttpClientInterface $client;

    private string $endPoint;

    private string $siteUrl;

    private CensorWords $censorWords;

    public function __construct(HttpClientInterface $client, string $akismetKey, string $siteUrl, CensorWords $censorWords)
    {
        $this->client = $client;
        $this->endPoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
        $this->siteUrl = $siteUrl;
        $this->censorWords = $censorWords;

        $this->censorWords->setDictionary(['en-us', 'en-uk', 'fi']);
    }

    public function getSpamScore(Comment $comment, array $context): int {
        $commentText = $comment->getText();

        $response = $this->client->request('POST', $this->endPoint, [
             'body' => array_merge($context, [
                 'blog' => $this->siteUrl,
                 'comment_type' => 'comment',
                 'comment_author' => $comment->getAuthor(),
                 'comment_author_email' => $comment->getEmail(),
                 'comment_content' => $commentText,
                 'comment_date_gmt' => $comment->getCreatedAt()->format('d'),
                 'blog_lang' => 'en',
                 'blog_charset' => 'UTF-8',
                 'is_test' => true
             ]),
        ]);

        $statusCode = $response->getStatusCode();

        if(200 !== $statusCode) {
            throw new \RuntimeException(sprintf('%d - HTTP ERROR on Akismet API!', $statusCode));
        }

        $content = $response->getContent();

        $okNum = 'true' === $content ? 1 : 0;

        if($okNum === 0) {
           $censoredString = $this->censorWords->censorString($commentText, true);
           $okNum = $censoredString['clean'] != $commentText && !empty($censoredString['matched']) ? 2 : 0;
        }

        return $okNum;
    }
 }
