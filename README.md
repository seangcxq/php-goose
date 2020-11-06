# php-goose
### Article Text Extractor - HTTP Client Agnostic Fork

This is an HTTP Client agnostic fork for use with an alternative HTTP Client. It focuses on text extraction only. For the original which uses guzzle, and has additional functionality, see original author repo https://github.com/scotteh/php-goose

## Dependencies

 * PHP 7.1+
 * Composer
 
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
$articleText = $article->getCleanedArticleText();
$entities = $article->getPopularWords();
```

## License
Apache 2.0 license, see the LICENSE file for more details.
