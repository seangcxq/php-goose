<?php

require_once("./vendor/autoload.php");

use \Goose\Client as GooseClient;

$result = file_get_contents('./page.html');

$goose = new GooseClient([
	'content_extractor.min_stopword_count_addnode' => 2,
	'content_extractor.min_stopword_count_boost' => 5,
	'content_extractor.max_steps_away_from_node' => 3,
	'content_extractor.boost_score_factor' => 50,
	'content_extractor.total_node_mod' => 0.25,
	'content_extractor.min_nodes' => 15,
	'content_extractor.topnode_min_score' => 20,

]);
$article = $goose->extractContent($result);

$title = $article->getTitle();
$articleText = $article->getCleanedArticleText();

echo $title."\n\n";
echo $articleText."\n";