<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use tobimori\BlurHash;

App::plugin('tobimori/blurhash', [
  'fileMethods' => [
    'blurhash' => fn (float $ratio = null) => BlurHash::encode($this, $ratio),
    'blurhashUri' => fn (float $ratio = null) => BlurHash::blur($this, $ratio),
  ],
  'options' => [
    'cache.encode' => true,
    'cache.decode' => true,
    'sampleMaxSize' => 100, // Max width or height for smaller image that gets encoded (Memory constraints)
    'componentsTarget' => 12, // Max number of components for encoding (x*y <= ~P)
    'decodeTarget' => 100, // Pixel Target (width * height = ~P) for decoding
  ],
]);
