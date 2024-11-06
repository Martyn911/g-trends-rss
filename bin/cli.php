<?php
require __DIR__ . '/../vendor/autoload.php';

use XFran\GTrends\GTrends;

$gt = new GTrends();

/*
$gt->setProxyConfigs([
    'proxy_host' => 'your_proxy_host',
    'proxy_port' => 8000,
    'proxy_user' => 'your_proxy_user',
    'proxy_pass' => 'your_proxy_pass',
]);

$gt->setProxyConfigs(null); // clear proxy if you want
*/

$results = $gt->getRealTimeSearchTrends();

/**
//for debug
file_put_contents('tmp.json', json_encode($results));
$results = json_decode(file_get_contents('tmp.json'), true);
*/

$items = [];
if(!empty($results['storySummaries']['trendingStories'])) {
    foreach ($results['storySummaries']['trendingStories'] as $trendingStory) {
        foreach ($trendingStory['articles'] as $article){
            $items[] = [
                'title' => $article['articleTitle'],
                'link' => $article['url'],
                'pubDate' => strtotime($article['time']),
                'description' => $article['snippet'],
                'keys' => $trendingStory['title'],
            ];
        }
    }
}

if(!$items) {
    exit;
}

$xml = new DOMDocument("1.0", "UTF-8");
$xml->preserveWhiteSpace = true;
$xml->formatOutput = true;
$rss = $xml->createElement("rss");
$rss_node = $xml->appendChild($rss);
$rss_node->setAttribute("version","2.0");
$channel = $xml->createElement("channel");
$channel->appendChild($xml->createElement("title", 'Google Trends feed'));
$channel->appendChild($xml->createElement("link", 'https://rss.4in.top'));
$channel->appendChild($xml->createElement("description", 'Google Trends to RSS feed for PHP'));
$channel_node = $rss_node->appendChild($channel);

foreach ($items as $item) {
    $channel_item = $xml->createElement("item");
    $channel_item->appendChild($xml->createElement("title", htmlspecialchars($item['title'], ENT_QUOTES)));
    $channel_item->appendChild($xml->createElement("description", htmlspecialchars($item['description'] . "\n" . $item['keys'], ENT_QUOTES)));
    $channel_item->appendChild($xml->createElement("link", htmlspecialchars($item['link'], ENT_QUOTES)));
    $channel_item->appendChild($xml->createElement("pubDate", date('r', $item['pubDate'])));
    $channel_item->appendChild($xml->createElement("guid", $item['link']));
    $channel_node->appendChild($channel_item);
}
file_put_contents(__DIR__ . '/../public/rss.xml', $xml->saveXML());