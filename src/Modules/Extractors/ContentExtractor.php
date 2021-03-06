<?php declare(strict_types = 1);

namespace Goose\Modules\Extractors;

use DOMWrap\Element;
use Goose\Article;
use Goose\Modules\
{AbstractModule, ModuleInterface};
use Goose\Traits\
{ArticleMutatorTrait, NodeCommonTrait, NodeGravityTrait};

/**
 * Content Extractor
 *
 * @package Goose\Modules\Extractors
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 */
class ContentExtractor extends AbstractModule implements ModuleInterface
{
	use ArticleMutatorTrait, NodeGravityTrait, NodeCommonTrait;

	/** @inheritdoc */
	public function run(Article $article): self
	{
		$this->article($article);

		$article->setTopNode($this->getTopNode());

		return $this;
	}

	/**
	 * @param Article $article
	 *
	 * @return array
	 */
	private function getTopNodeCandidatesByContents(Article $article): array
	{
		$results = [];

		$nodes = $article->getDoc()->find('p, td, pre');

		foreach($nodes as $node)
		{
			$wordStats = $this->config()->getStopWords()->getStopwordCount($node->text());
			$highLinkDensity = $this->isHighLinkDensity($node);

			if($wordStats->getStopWordCount() > $this->config()->get('content_extractor.min_stopword_count_addnode') && !$highLinkDensity)
			{
				$results[] = $node;
			}
		}

		return $results;
	}

	/**
	 * @param Element $node
	 * @param int $i
	 * @param int $totalNodes
	 *
	 * @return float
	 */
	private function getTopNodeCandidateScore(Element $node, int $i, int $totalNodes): float
	{
		$boostScore = (1.0 / ($i + 1)) * $this->config()->get('content_extractor.boost_score_factor');
		$bottomNodesForNegativeScore = $totalNodes * $this->config()->get('content_extractor.total_node_mod');

		if($totalNodes > $this->config()->get('content_extractor.min_nodes'))
		{
			if($totalNodes - $i <= $bottomNodesForNegativeScore)
			{
				$booster = $bottomNodesForNegativeScore - ($totalNodes - $i);
				$boostScore = pow($booster, 2) * -1;
				$negscore = abs($boostScore);
				if($negscore > $this->config()->get('content_extractor.min_negative_score'))
				{
					$boostScore = $this->config()->get('content_extractor.score_increment_amount');
				}
			}
		}

		$wordStats = $this->config()->getStopWords()->getStopwordCount($node->text());
		$upscore = $wordStats->getStopWordCount() + $boostScore;

		return $upscore;
	}

	/**
	 * @param array $nodes
	 *
	 * @return Element|null
	 */
	private function getTopNodeByScore(array $nodes): ?Element
	{
		$topNode = NULL;
		$topNodeScore = 0;

		foreach($nodes as $node)
		{
			$score = $this->getScore($node);

			if($score > $topNodeScore)
			{
				$topNode = $node;
				$topNodeScore = $score;
			}

			if($topNode === false)
			{
				$topNode = $node;
			}
		}

		if($topNode && $this->getScore($topNode) < $this->config()->get('content_extractor.topnode_min_score'))
		{
			return NULL;
		}

		return $topNode;
	}

	/**
	 * @param Element $node
	 * @param float $upscore
	 *
	 * @return self
	 */
	private function calculateBestNodeCandidateScores(Element $node, float $upscore): self
	{
		if($node->parent() instanceof Element)
		{
			$this->updateScore($node->parent(), $upscore);
			$this->updateNodeCount($node->parent(), 1);

			if($node->parent()->parent() instanceof Element)
			{
				$this->updateScore($node->parent()->parent(), $upscore / 2);
				$this->updateNodeCount($node->parent()->parent(), 1);
			}
		}

		return $this;
	}

	/**
	 * @param Element $node
	 * @param array $nodeCandidates
	 *
	 * @return array
	 */
	private function updateBestNodeCandidates(Element $node, array $nodeCandidates): array
	{
		if(!in_array($node->parent(), $nodeCandidates, true))
		{
			if($node->parent() instanceof Element)
			{
				$nodeCandidates[] = $node->parent();
			}
		}

		if($node->parent() instanceof Element)
		{
			if(!in_array($node->parent()->parent(), $nodeCandidates, true))
			{
				if($node->parent()->parent() instanceof Element)
				{
					$nodeCandidates[] = $node->parent()->parent();
				}
			}
		}

		return $nodeCandidates;
	}

	/**
	 * We're going to start looking for where the clusters of paragraphs are. We'll score a cluster based on the number of stopwords
	 * and the number of consecutive paragraphs together, which should form the cluster of text that this node is around
	 * also store on how high up the paragraphs are, comments are usually at the bottom and should get a lower score
	 *
	 * @return Element|null
	 */
	public function getTopNode(): ?Element
	{
		$nodes = $this->getTopNodeCandidatesByContents($this->article());

		$nodeCandidates = [];

		$i = 0;
		foreach($nodes as $node)
		{
			if($this->isOkToBoost($node))
			{
				$upscore = $this->getTopNodeCandidateScore($node, $i, count($nodes));

				$this->calculateBestNodeCandidateScores($node, $upscore);
				$nodeCandidates = $this->updateBestNodeCandidates($node, $nodeCandidates);

				$i++;
			}
		}

		return $this->getTopNodeByScore($nodeCandidates);
	}

	/**
	 * A lot of times the first paragraph might be the caption under an image so we'll want to make sure if we're going to
	 * boost a parent node that it should be connected to other paragraphs, at least for the first n paragraphs
	 * so we'll want to make sure that the next sibling is a paragraph and has at least some substantial weight to it
	 *
	 * @param Element $node
	 *
	 * @return bool
	 */
	private function isOkToBoost(Element $node): bool
	{
		$stepsAway = 0;
		$minimumStopWordCount = $this->config()->get('content_extractor.min_stopword_count_boost');
		$maxStepsAwayFromNode = $this->config()->get('content_extractor.max_steps_away_from_node');

		// Find all previous sibling element nodes
		$siblings = $node->precedingAll(function($node)
		{
			return $node instanceof Element;
		});

		foreach($siblings as $sibling)
		{
			if($sibling->is('p, strong'))
			{
				if($stepsAway >= $maxStepsAwayFromNode)
				{
					return false;
				}

				$wordStats = $this->config()->getStopWords()->getStopwordCount($sibling->text());

				if($wordStats->getStopWordCount() > $minimumStopWordCount)
				{
					return true;
				}

				$stepsAway += 1;
			}
		}

		return false;
	}
}
