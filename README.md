![Kirby BlurHash Banner](./.github/banner.png)

# Kirby BlurHash

[BlurHash](https://blurha.sh) is an optimized image placeholder algorithm, developed at Wolt.
Placeholders are represented by small ∼20-50 bytes hashes, instead of larger (∼1kB+) base64-encoded images.

This plugin adds BlurHash support to Kirby 3, allowing you to implement UX improvements such as progressive image loading or content-aware spoiler images [like Mastodon](https://blog.joinmastodon.org/2019/05/improving-support-for-adult-content-on-mastodon/).

Under the hood, the heavy work gets done by a PHP implementation of BlurHash by [kornrunner](https://github.com/kornrunner): [kornrunner/php-blurhash](https://github.com/kornrunner/php-blurhash)

## Requirements

- Kirby 3.8+
- PHP 8.0+
- `gd` extension [(required by Kirby)](https://getkirby.com/docs/guide/quickstart#requirements)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby-blurhash`.

### Composer

```
composer require tobimori/kirby-blurhash
```

## Usage

### Client-side decoding

The default implementation of BlurHash expects the string to be decoded on the client-side, using a library like [Wolt's blurhash](https://github.com/woltapp/blurhash/tree/master/TypeScript) or [fast-blurhash](https://github.com/mad-gooze/fast-blurhash).

This provides the most benefits, most notably including better color representation and smaller payload size, but requires the initial execution of such a library on the client-side, and thus is better used with a headless site, a site that features many, high quality images or heavily makes use of client-side infinite scrolling/loading.

// TODO

### Server-side decoding

In addition to simply outputting the BlurHash string for usage on the client-side, this plugin also provides a server-side decoding option that allows you to output a base64-encoded image string, which can be used as a placeholder image without any client-side libraries, similar to [Kirby Blurry Placeholder](https://github.com/johannschopplich/kirby-blurry-placeholder).

This is especially useful when you only have a few images on your site or don't want to go through the hassle of using a client-side library for outputting placeholders. Using this approach, you'll still get better color representation of the BlurHash algorithm than with regularly downsizing an image, but image previews will still be about ~1kB large.

// TODO

## Options

// TODO

## Comparison

[![Comparison image](./.github/comparison/nighttime.png)](https://unsplash.com/photos/Ngu3tsqmcRg)
[![Comparison image](./.github/comparison/flowers.png)](https://unsplash.com/photos/VSt-8kKTjWo)
[![Comparison image](./.github/comparison/beach.png)](https://unsplash.com/photos/3ws2fq3VtXk)

## Credits

- Johann Schopplich's [Kirby Blurry Placeholder](https://github.com/johannschopplich/kirby-blurry-placeholder) plugin that set the baseline for this plugin (especially for rasterized BlurHashes)

## License

[MIT License](./LICENSE)
Copyright © 2023 Tobias Möritz
