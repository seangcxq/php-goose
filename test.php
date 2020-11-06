<?php

require_once("./vendor/autoload.php");

use \Goose\Client as GooseClient;

$result = file_get_contents('./page.html');

$goose = new GooseClient();
$article = $goose->extractContent($result);

$title = $article->getTitle();
$articleText = $article->getCleanedArticleText();

echo $title."\n\n";
echo $articleText."\n";