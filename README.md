# PHP Goose - Article Extractor

## HTTP Client Agnostic Fork

This is an HTTP Client agnostic fork for use with an alternative HTTP Client. For the original, which uses guzzle, see original author repo https://github.com/scotteh/php-goose

## Dependencies

 - PHP 7.1 or later
 - PSR-4 compatible autoloader
 
## Install

    composer require seangcxq/php-goose

## Usage

``` php
use \Goose\Client as GooseClient;

$goose = new GooseClient();
$article = $goose->extractContent($raw_html);

$title = $article->getTitle();
$metaDescription = $article->getMetaDescription();
$metaKeywords = $article->getMetaKeywords();
$canonicalLink = $article->getCanonicalLink();
$domain = $article->getDomain();
$tags = $article->getTags();
$links = $article->getLinks();
$videos = $article->getVideos();
$articleText = $article->getCleanedArticleText();
$entities = $article->getPopularWords();
$image = $article->getTopImage();
$allImages = $article->getAllImages();
```

## Configuration

All config options are not required and are optional. Default (fallback) values have been used below.

``` php
use \Goose\Client as GooseClient;

$goose = new GooseClient([
    // Language - Selects common word dictionary
    //   Supported languages (ISO 639-1):
    //     ar, cs, da, de, en, es, fi, fr, hu, id, it, ja,
    //     ko, nb, nl, no, pl, pt, ru, sv, vi, zh
    'language' => 'en',
    // Minimum image size (bytes)
    'image_min_bytes' => 4500,
    // Maximum image size (bytes)
    'image_max_bytes' => 5242880,
    // Minimum image size (pixels)
    'image_min_width' => 120,
    // Maximum image size (pixels)
    'image_min_height' => 120,
    // Fetch best image
    'image_fetch_best' => true,
    // Fetch all images
    'image_fetch_all' => false,
]);
```

## License
Apache 2.0 license, see the LICENSE file for more details.
