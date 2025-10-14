<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ActivityPubTagObjectTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    #[Test]
    public function gotosocial(): void
    {
        $res = [
            "tag" => [
                "href" => "https://gotosocial.example.org/users/GotosocialUser",
                "name" => "@GotosocialUser@gotosocial.example.org",
                "type" => "Mention"
            ]
        ];

        if(isset($res['tag']['type'], $res['tag']['name'])) {
            $res['tag'] = [$res['tag']];
        }

        $tags = collect($res['tag'])
        ->filter(function($tag) {
            return $tag &&
                $tag['type'] == 'Mention' &&
                isset($tag['href']) &&
                substr($tag['href'], 0, 8) === 'https://';
        });
        $this->assertTrue($tags->count() === 1);
    }

    #[Test]
    public function pix_hashtags(): void
    {
        $res = [
            "tag" => [
                [
                    "type" => "Mention",
                    "href" => "https://pix.social/dansup",
                    "name" => "@dansup@pix.social"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/dogsofpix",
                    "name" => "#dogsOfPixelFed"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/doggo",
                    "name" => "#doggo"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/dog",
                    "name" => "#dog"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/drake",
                    "name" => "#drake"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/blacklab",
                    "name" => "#blacklab"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/iconic",
                    "name" => "#Iconic"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/majestic",
                    "name" => "#majestic"
                ]
            ]
        ];

        if(isset($res['tag']['type'], $res['tag']['name'])) {
            $res['tag'] = [$res['tag']];
        }

        $tags = collect($res['tag'])
        ->filter(function($tag) {
            return $tag &&
                $tag['type'] == 'Hashtag' &&
                isset($tag['href']) &&
                substr($tag['href'], 0, 8) === 'https://';
        });
        $this->assertTrue($tags->count() === 7);
    }

    #[Test]
    public function pix_mentions(): void
    {
        $res = [
            "tag" => [
                [
                    "type" => "Mention",
                    "href" => "https://pix.social/dansup",
                    "name" => "@dansup@pix.social"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/dogsofpix",
                    "name" => "#dogsOfPixelFed"
                ],
                [
                    "type" => "Hashtag",
                    "href" => "https://pix.social/discover/tags/doggo",
                    "name" => "#doggo"
                ],
            ]
        ];

        if(isset($res['tag']['type'], $res['tag']['name'])) {
            $res['tag'] = [$res['tag']];
        }

        $tags = collect($res['tag'])
        ->filter(function($tag) {
            return $tag &&
                $tag['type'] == 'Mention' &&
                isset($tag['href']) &&
                substr($tag['href'], 0, 8) === 'https://';
        });
        $this->assertTrue($tags->count() === 1);
    }
}
