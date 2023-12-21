<?php

include(__DIR__ . '/vendor/autoload.php');

$content = file_get_contents('https://g0v.hackmd.io/api/overview?v=' . time());
$posts = json_decode($content);
$output_posts = new StdClass;
foreach ($posts as $post) {
    if (!property_exists($post, 'tags') or !is_array($post->tags)) {
        continue;
    }
    if (!in_array('民防', $post->tags)) {
        continue;
    }
    $output_posts->{$post->id} = $post;

    $target = __DIR__ . '/data/' . $post->id . '.md';
    $lastchangeAt = strtotime($post->lastchangeAt);
    if (file_exists($target) and filemtime($target) >= $lastchangeAt) {
        continue;
    }
    error_log($target);
   
    $id = $post->id;
    $content = file_get_contents("https://g0v.hackmd.io/{$id}");
    $doc = new DOMDocument();
    @$doc->loadHTML($content);
    file_put_contents($target, $doc->getElementById('doc')->textContent);
    touch($target, $lastchangeAt);
}
file_put_contents(__DIR__ . '/data/posts.json', json_encode($output_posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
