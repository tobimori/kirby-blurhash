<?php

namespace tobimori;

use Kirby\Cms\File;
use kornrunner\Blurhash\Blurhash as BHEncoder;

class BlurHash
{
  /**
   * Blurs an image based on the BlurHash algorithm, returns a data URI with an SVG filter.
   */
  public static function blur(File $file, float|null $ratio = null): string
  {
    $ratio ??= $file->ratio();

    $blurhash = self::encode($file, $ratio); // Encode image with BlurHash Algorithm
    [$width, $height] = self::calcWidthHeight(option('tobimori.blurhash.decodeTarget'), $ratio); // Get target width and height for decoding
    $image = self::decode($blurhash, $width, $height); // Decode BlurHash to image

    return self::uri($image, $width, $height); // Output image as data URI with SVG blur
  }

  /**
   * Returns the BlurHash for a Kirby file object.
   */
  public static function encode(File $file, float|null $ratio = null): string
  {
    $kirby = kirby();

    $id = $file->uuid() ?? $file->id();
    $ratio ??= $file->ratio();
    $cache = $kirby->cache('tobimori.blurhash.encode');

    if (($cacheData = $cache->get($id)) !== null) {
      return $cacheData;
    }

    // Generate a sample image for encode to avoid memory issues.
    $max = $kirby->option('tobimori.blurhash.sampleMaxSize'); // Max width or height

    $height = round($file->height() > $file->width() ? $max : $max * $ratio);
    $width = round($file->width() > $file->height() ? $max : $max * $ratio);
    $options = [
      'width' => $width,
      'height' => $height,
      'crop'  => true,
      'quality' => 70,
    ];

    // Create a GD image from the file.
    $image = imagecreatefromstring($file->thumb($options)->read());
    $height = imagesy($image);
    $width = imagesx($image);
    $pixels = [];

    // Convert image to two-dimensional array of colors
    // as required by the Blurhash encoder.
    for ($y = 0; $y < $height; ++$y) {
      $row = [];

      for ($x = 0; $x < $width; ++$x) {
        $index = imagecolorat($image, $x, $y);
        $colors = imagecolorsforindex($image, $index);

        $row[] = [$colors['red'], $colors['green'], $colors['blue']];
      }

      $pixels[] = $row;
    }

    [$x, $y] = self::calcWidthHeight($kirby->option('tobimori.blurhash.componentsTarget'), $ratio);
    $blurhash = BHEncoder::encode($pixels, $x, $y);
    $cache->set($id, $blurhash);

    return $blurhash;
  }

  /**
   * Decodes a BlurHash string to a binary image string.
   */
  public static function decode(string $blurhash, int $width, int $height): string
  {
    $kirby = kirby();
    $cache = $kirby->cache('tobimori.blurhash.decode');
    $id = $blurhash . $width . $height;

    if (($cacheData = $cache->get($id)) !== null) {
      return $cacheData;
    }

    $pixels = BHEncoder::decode($blurhash, $width, $height);
    $image = imagecreatetruecolor($width, $height);

    foreach ($pixels as $y => $row) {
      foreach ($row as $x => [$r, $g, $b]) {
        imagesetpixel($image, $x, $y, imagecolorallocate($image, $r, $g, $b));
      }
    }

    ob_start();
    imagepng($image);
    $data = ob_get_contents();
    ob_end_clean();

    $cache->set($id, $data);
    return $data;
  }

  /**
   * Returns an optimized URI-encoded string of an SVG for using in a src attribute.
   * Based on https://github.com/johannschopplich/kirby-blurry-placeholder/blob/main/BlurryPlaceholder.php#L65
   */
  private static function svgToUri(string $data): string
  {
    // Optimizes the data URI length by deleting line breaks and
    // removing unnecessary spaces
    $data = preg_replace('/\s+/', ' ', $data);
    $data = preg_replace('/> </', '><', $data);

    $data = rawurlencode($data);

    // Back-decode certain characters to improve compression
    // except '%20' to be compliant with W3C guidelines
    $data = str_replace(
      ['%2F', '%3A', '%3D'],
      ['/', ':', '='],
      $data
    );

    return 'data:image/svg+xml;charset=utf-8,' . $data;
  }

  /**
   * Applies SVG filter and base64-encoding to binary image.
   * Based on https://github.com/johannschopplich/kirby-blurry-placeholder/blob/main/BlurryPlaceholder.php#L10
   */
  private static function svgFilter(string $image, int $width, int $height): string
  {
    $uri = 'data:image/png;base64,' . base64_encode($image);

    $svgHeight = number_format($height, 2, '.', '');
    $svgWidth = number_format($width, 2, '.', '');

    // Wrap the blurred image in a SVG to avoid rasterizing the filter
    $svg = <<<EOD
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$svgWidth} {$svgHeight}">
          <filter id="a" color-interpolation-filters="sRGB">
            <feGaussianBlur stdDeviation=".2"></feGaussianBlur>
            <feComponentTransfer>
              <feFuncA type="discrete" tableValues="1 1"></feFuncA>
            </feComponentTransfer>
          </filter>
          <image filter="url(#a)" x="0" y="0" width="100%" height="100%" href="{$uri}"></image>
        </svg>
        EOD;

    return $svg;
  }

  /**
   * Returns a decoded BlurHash as a URI-encoded SVG with blur filter applied.
   */
  public static function uri(string $image, int $width, int $height): string
  {
    $svg = self::svgFilter($image, $width, $height);
    $uri = self::svgToUri($svg);

    return $uri;
  }

  /**
   * Returns the width and height for a given ratio, based on a target entity count.
   * Aims for a size of ~x entities (width * height = ~x)
   */
  private static function calcWidthHeight(int $target, float $ratio): array
  {
    $height = round(sqrt($target / $ratio));
    $width = round($target / $height);

    return [$width, $height];
  }
}
