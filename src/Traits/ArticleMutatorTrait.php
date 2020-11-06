<?php declare(strict_types = 1);

namespace Goose\Traits;

use Goose\Article;

/**
 * Article Mutator Trait
 *
 * @package Goose\Traits
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
trait ArticleMutatorTrait
{
	/** @var Article */
	protected $article;

	/**
	 * @param Article $article
	 *
	 * @return Article
	 */
	protected function article(Article $article = NULL): ?Article
	{
		if($article === NULL)
		{
			return $this->article;
		}

		$this->article = $article;

		return $this->article;
	}
}
