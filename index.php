<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use tobimori\BlurHash;

App::plugin('tobimori/blurhash', [
  'fileMethods' => [
    'blurhash' => fn (float $ratio = null) => BlurHash::encode($this, $ratio),
    'bh' => fn (float $ratio = null) => $this->blurhash($ratio),
    'blurhashUri' => fn (float $ratio = null) => BlurHash::blur($this, $ratio),
    'bhUri' => fn (float $ratio = null) => $this->blurhashUri($ratio),
    'blurhashColor' => fn (float $ratio = null) => BlurHash::averageColor($this, $ratio),
    'bhColor' => fn (float $ratio = null) => $this->blurhashColor($ratio),
  ],
  'options' => [
    'cache.encode' => true,
    'cache.decode' => true,
    'sampleMaxSize' => 100, // Max width or height for smaller image that gets encoded (Memory constraints)
    'componentsTarget' => 12, // Max number of components for encoding (x*y <= ~P)
    'decodeTarget' => 100, // Pixel Target (width * height = ~P) for decoding
  ],
  'hooks' => [
    'file.update:before' => fn ($file) => BlurHash::clearCache($file),
    'file.replace:before' => fn ($file) => BlurHash::clearCache($file),
  ]
]);
