<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use tobimori\BlurHash;

App::plugin('tobimori/kirby-blurhash', [
  'fileMethods' => [
    'blurhash' => function () {
      return BlurHash::encode($this);
    },
    'blurhashUri' => function () {
      return BlurHash::blurhash($this);
    }
  ],
  'options' => [
    'cache.encode' => true,
    'cache.decode' => true
  ],
]);
