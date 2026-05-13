<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/scripts/app.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/turbo' => [
        'version' => '8.0.23',
    ],
    'video.js' => [
        'version' => '8.23.7',
    ],
    'video.js/dist/video-js.min.css' => [
        'version' => '8.23.7',
        'type' => 'css',
    ],
    'aos' => [
        'version' => '2.3.4',
    ],
    'aos/dist/aos.css' => [
        'version' => '2.3.4',
        'type' => 'css',
    ],
    'sweetalert2' => [
        'version' => '11.26.24',
    ],
    'axios' => [
        'version' => '1.16.0',
    ],
    'global/window' => [
        'version' => '4.4.0',
    ],
    'global/document' => [
        'version' => '4.4.0',
    ],
    '@videojs/xhr' => [
        'version' => '2.7.0',
    ],
    'videojs-vtt.js' => [
        'version' => '0.15.5',
    ],
    '@babel/runtime/helpers/extends' => [
        'version' => '7.28.6',
    ],
    '@videojs/vhs-utils/es/resolve-url.js' => [
        'version' => '4.1.1',
    ],
    'm3u8-parser' => [
        'version' => '7.2.0',
    ],
    '@videojs/vhs-utils/es/codecs.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/media-types.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/byte-helpers' => [
        'version' => '4.1.1',
    ],
    'mpd-parser' => [
        'version' => '1.3.1',
    ],
    'mux.js/lib/tools/parse-sidx' => [
        'version' => '7.1.0',
    ],
    '@videojs/vhs-utils/es/id3-helpers' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/containers' => [
        'version' => '4.1.1',
    ],
    'mux.js/lib/utils/clock' => [
        'version' => '7.1.0',
    ],
    'is-function' => [
        'version' => '1.0.2',
    ],
    '@videojs/vhs-utils/es/stream.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/decode-b64-to-uint8-array.js' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/resolve-url' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/media-groups' => [
        'version' => '4.1.1',
    ],
    '@videojs/vhs-utils/es/decode-b64-to-uint8-array' => [
        'version' => '4.1.1',
    ],
    '@xmldom/xmldom' => [
        'version' => '0.8.10',
    ],
];
