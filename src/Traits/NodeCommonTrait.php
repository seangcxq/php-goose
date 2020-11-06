<?php declare(strict_types = 1);

namespace Goose\Traits;

use DOMWrap\Element;
use Goose\Utils\Helper;

/**
 * Node Common Trait
 *
 * @package Goose\Traits
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
trait NodeCommonTrait
{
	/**
	 * Checks the density of links within a node, is there not much text and most of it contains linky shit?
	 * if so it's no good
	 *
	 * @param Element $node
	 * @param float $limit
	 *
	 * @return bool
	 */
	private function isHighLinkDensity(Element $node, float $limit = 1.0): bool
	{
		$links = $node->find('a, [onclick]');

		if($links->count() == 0)
		{
			return false;
		}

		$words = preg_split('@[\s]+@iu', $node->text(), -1, PREG_SPLIT_NO_EMPTY);

		if(!is_array($words) || empty($words))
		{
			return false;
		}

		$sb = [];
		foreach($links as $link)
		{
			$sb[] = Helper::textNormalise($link->text());
		}

		$linkText = implode('', $sb);
		$linkWords = explode(' ', $linkText);
		$numberOfLinkWords = count($linkWords);
		$numberOfLinks = $links->count();
		$linkDivisor = $numberOfLinkWords / count($words);
		$score = $linkDivisor * $numberOfLinks;

		if($score >= $limit)
		{
			return true;
		}

		return false;
	}
}
