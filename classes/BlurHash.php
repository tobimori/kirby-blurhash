<?php

namespace tobimori;

use Kirby\Cms\File;
use kornrunner\Blurhash\Blurhash as BHEncoder;

class BlurHash
{
  public static function blurhash(File $file): string
  {
    $blurhash = self::encode($file);

    $pixelTarget = 100;
    // Aims for an image of ~P pixels (w * h = ~P)
    $height = round(sqrt($pixelTarget / ($ratio ?? $file->ratio())));
    $width = round($pixelTarget / $height);

    $image = self::decode($blurhash, $width, $height);

    return self::uri($image, $width, $height);
  }

  public static function encode(File $file): string
  {
    $id = $file->uuid() ?? $file->id();
    $kirby = kirby();
    $cache = $kirby->cache('tobimori.blurhash.encode');

    if (($cacheData = $cache->get($id)) !== null) {
      return $cacheData;
    }

    // Generate a sample image for encode to avoid memory issues.
    $max = 400; // Max width or height

    $height = round($file->height() > $file->width() ? $max : $max * $file->ratio());
    $width = round($file->width() > $file->height() ? $max : $max * $file->ratio());
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

    $blurhash = BHEncoder::encode($pixels, 4, 7);
    $cache->set($id, $blurhash);

    return $blurhash;
  }

  public static function decode(string $blurhash, int $width, int $height): string
  {
    $pixels = BHEncoder::decode($blurhash, $width, $height);
    $image = imagecreatetruecolor($width, $height);

    foreach ($pixels as $y => $row) {
      foreach ($row as $x => $pixel) {
        imagesetpixel($image, $x, $y, imagecolorallocate($image, $pixel[0], $pixel[1], $pixel[2]));
      }
    }

    ob_start();
    imagepng($image);
    $data = ob_get_contents();
    ob_end_clean();

    return $data;
  }

  /**
   * Returns the URI-encoded string of an SVG
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

    return $data;
  }

  private static function uri(string $image, int $width, int $height)
  {
    $uri = 'data:image/png;base64,' . base64_encode($image);

    $svgHeight = number_format($height, 2, '.', '');
    $svgWidth = number_format($width, 2, '.', '');

    // Wrap the blurred image in a SVG to avoid rasterizing the filter
    $svg = <<<EOD
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$svgWidth} {$svgHeight}">
          <filter id="b" color-interpolation-filters="sRGB">
            <feGaussianBlur stdDeviation=".2"></feGaussianBlur>
            <feComponentTransfer>
              <feFuncA type="discrete" tableValues="1 1"></feFuncA>
            </feComponentTransfer>
          </filter>
          <image filter="url(#b)" x="0" y="0" width="100%" height="100%" href="{$uri}"></image>
        </svg>
        EOD;

    return 'data:image/svg+xml;charset=utf-8,' . static::svgToUri($svg);
  }
}
