<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);


$metadata = json_decode(file_get_contents("data/info.json"), true);
$images = [];

foreach ($metadata as $info)
{
    array_push($images, [ "id" => $info["id"], "extension" => $info["format"]]);
}

echo $twig->render("index.html.twig", [
    "images" => $images
]);