<?php declare(strict_types = 1);

namespace Goose;

use DOMWrap\Document;

/**
 * Client
 *
 * @package Goose
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
class Client
{
	/** @var Configuration */
	protected $config;

	/**
	 * @param mixed[] $config
	 */
	public function __construct($config = [])
	{
		$this->config = new Configuration($config);
	}

	/**
	 * @param string $name
	 * @param mixed[] $arguments
	 *
	 * @return mixed
	 */
	public function __call(string $name, $arguments)
	{
		if(method_exists($this->config, $name))
		{
			return call_user_func_array([$this->config, $name], $arguments);
		}

		return NULL;
	}

	protected function getDocument(string $rawHTML): Document
	{
		$doc = new Document();
		$doc->html($rawHTML);

		return $doc;
	}

	public function modules(string $category, Article $article): self
	{
		$modules = $this->config->getModules($category);

		foreach($modules as $module)
		{
			$obj = new $module($this->config);
			$obj->run($article);
		}

		return $this;
	}

	public function extractContent(string $raw_html): Article
	{
		$article = new Article();

		$link_hash = sha1($raw_html);

		$xmlInternalErrors = libxml_use_internal_errors(true);

		// Generate document
		$doc = $this->getDocument($raw_html);

		// Set core mutators
		$article->setDomain('');
		$article->setLinkhash($link_hash);
		$article->setRawHtml($raw_html);
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
}