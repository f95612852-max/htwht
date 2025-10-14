<?php

namespace App\Util\Site;

use Cache;
use Illuminate\Support\Str;

class Config
{
    const CACHE_KEY = 'api:site:configuration:_v0.9';

    public static function get()
    {
        return Cache::remember(self::CACHE_KEY, 900, function () {
            $hls = [
                'enabled' => config('media.hls.enabled'),
            ];
            if (config('media.hls.enabled')) {
                $hls = [
                    'enabled' => true,
                    'debug' => (bool) config('media.hls.debug'),
                    'p2p' => (bool) config('media.hls.p2p'),
                    'p2p_debug' => (bool) config('media.hls.p2p_debug'),
                    'tracker' => config('media.hls.tracker'),
                    'ice' => config('media.hls.ice'),
                ];
            }

            return [
                'version' => config('pix.version'),
                'open_registration' => (bool) config_cache('pix.open_registration'),
                'show_legal_notice_link' => (bool) config('instance.has_legal_notice'),
                'uploader' => [
                    'max_photo_size' => (int) config_cache('pix.max_photo_size'),
                    'max_caption_length' => (int) config_cache('pix.max_caption_length'),
                    'max_altext_length' => (int) config_cache('pix.max_altext_length', 150),
                    'album_limit' => (int) config_cache('pix.max_album_length'),
                    'image_quality' => (int) config_cache('pix.image_quality'),

                    'max_collection_length' => (int) config_cache('pix.max_collection_length', 18),

                    'optimize_image' => (bool) config_cache('pix.optimize_image'),
                    'optimize_video' => (bool) config_cache('pix.optimize_video'),

                    'media_types' => config_cache('pix.media_types'),
                    'mime_types' => config_cache('pix.media_types') ? explode(',', config_cache('pix.media_types')) : [],
                    'enforce_account_limit' => (bool) config_cache('pix.enforce_account_limit'),
                ],

                'activitypub' => [
                    'enabled' => (bool) config_cache('federation.activitypub.enabled'),
                    'remote_follow' => config('federation.activitypub.remoteFollow'),
                ],

                'ab' => config('exp'),

                'site' => [
                    'name' => config_cache('app.name'),
                    'domain' => config('pix.domain.app'),
                    'url' => config('app.url'),
                    'description' => config_cache('app.short_description'),
                ],

                'account' => [
                    'max_avatar_size' => config('pix.max_avatar_size'),
                    'max_bio_length' => config('pix.max_bio_length'),
                    'max_name_length' => config('pix.max_name_length'),
                    'min_password_length' => config('pix.min_password_length'),
                    'max_account_size' => config('pix.max_account_size'),
                ],

                'username' => [
                    'remote' => [
                        'formats' => config('instance.username.remote.formats'),
                        'format' => config('instance.username.remote.format'),
                        'custom' => config('instance.username.remote.custom'),
                    ],
                ],

                'features' => [
                    'timelines' => [
                        'local' => true,
                        'network' => (bool) config('federation.network_timeline'),
                    ],
                    'mobile_apis' => (bool) config_cache('pix.oauth_enabled'),
                    'mobile_registration' => config('auth.in_app_registration'),
                    'stories' => (bool) config_cache('instance.stories.enabled'),
                    'video' => Str::contains(config_cache('pix.media_types'), 'video/mp4'),
                    'import' => [
                        'instagram' => (bool) config_cache('pix.import.instagram.enabled'),
                        'mastodon' => false,
                        'pix' => false,
                    ],
                    'label' => [
                        'covid' => [
                            'enabled' => (bool) config('instance.label.covid.enabled'),
                            'org' => config('instance.label.covid.org'),
                            'url' => config('instance.label.covid.url'),
                        ],
                    ],
                    'hls' => $hls,
                    'groups' => (bool) config('groups.enabled'),
                ],
            ];
        });
    }

    public static function refresh()
    {
        Cache::forget(self::CACHE_KEY);
        return self::get();
    }

    public static function json()
    {
        return json_encode(self::get(), JSON_FORCE_OBJECT);
    }
}
