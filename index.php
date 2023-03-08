<?php

@include_once __DIR__ . '/vendor/autoload.php';

use Kirby\Cms\App;
use tobimori\BlurHash;

App::plugin('tobimori/blurhash', [
  'fileMethods' => [
    /** @kql-allowed */
    'blurhash' => fn (float|null $ratio = null) => BlurHash::encode($this, $ratio),
    /** @kql-allowed */
    'bh' => fn (float|null $ratio = null) => $this->blurhash($ratio),
    /** @kql-allowed */
    'blurhashUri' => fn (float|null $ratio = null) => BlurHash::blur($this, $ratio),
    /** @kql-allowed */
    'bhUri' => fn (float|null $ratio = null) => $this->blurhashUri($ratio),
    /** @kql-allowed */
    'blurhashColor' => fn (float|null $ratio = null) => BlurHash::averageColor($this, $ratio),
    /** @kql-allowed */
    'bhColor' => fn (float|null $ratio = null) => $this->blurhashColor($ratio),
  ],
  'assetMethods' => [
    /** @kql-allowed */
    'blurhash' => fn (float|null $ratio = null) => BlurHash::encode($this, $ratio),
    /** @kql-allowed */
    'bh' => fn (float|null $ratio = null) => $this->blurhash($ratio),
    /** @kql-allowed */
    'blurhashUri' => fn (float|null $ratio = null) => BlurHash::blur($this, $ratio),
    /** @kql-allowed */
    'bhUri' => fn (float|null $ratio = null) => $this->blurhashUri($ratio),
    /** @kql-allowed */
    'blurhashColor' => fn (float|null $ratio = null) => BlurHash::averageColor($this, $ratio),
    /** @kql-allowed */
    'bhColor' => fn (float|null $ratio = null) => $this->blurhashColor($ratio),
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
