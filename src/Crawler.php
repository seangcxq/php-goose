<?php declare(strict_types = 1);

namespace Goose;

use DOMWrap\Document;
use Goose\Utils\Helper;

/**
 * Crawler
 *
 * @package Goose
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
class Crawler
{
	/** @var Configuration */
	protected $config;

	/**
	 * @param Configuration $config
	 */
	public function __construct(Configuration $config)
	{
		$this->config = $config;
	}

	/**
	 * @return Configuration
	 */
	public function config(): Configuration
	{
		return $this->config;
	}

	/**
	 * @param string $url
	 * @param string|null $rawHTML
	 *
	 * @return Article
	 */
	public function crawl(string $url, string $rawHTML = NULL): ?Article
	{
		$article = new Article();

		$parseCandidate = Helper::getCleanedUrl($url);

		$xmlInternalErrors = libxml_use_internal_errors(true);

		if(empty($rawHTML))
		{
			$guzzle = new \GuzzleHttp\Client();
			$response = $guzzle->get($parseCandidate->url, $this->config()->get('browser'));
			$article->setRawResponse($response);
			$rawHTML = $response->getBody()->getContents();
		}

		// Generate document
		$doc = $this->getDocument($rawHTML);

		// Set core mutators
		$article->setFinalUrl($parseCandidate->url);
		$article->setDomain($parseCandidate->parts->host);
		$article->setLinkhash($parseCandidate->linkhash);
		$article->setRawHtml($rawHTML);
		$article->setDoc($doc);
		$article->setRawDoc(clone $doc);

		// Pre-extraction document cleaning
		$this->modules('cleaners', $article);

		// Extract content
		$this->modules('extractors', $article);

		// Post-extraction content formatting
		$this->modules('formatters', $article);

		libxml_use_internal_errors($xmlInternalErrors);

		return $article;
	}

	/**
	 * @param string $rawHTML
	 *
	 * @return Document
	 */
	protected function getDocument(string $rawHTML): Document
	{
		$doc = new Document();
		$doc->html($rawHTML);

		return $doc;
	}

	/**
	 * @param string $category
	 * @param Article $article
	 *
	 * @return self
	 */
	public function modules(string $category, Article $article): self
	{
		$modules = $this->config->getModules($category);

		foreach($modules as $module)
		{
			$obj = new $module($this->config());
			$obj->run($article);
		}

		return $this;
	}
}

