<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$metadata = json_decode(file_get_contents("data/info.json"), true);

function showAll()
{
    $images = [];
    
    foreach ($metadata as $info)
    {
        array_push($images, [ "id" => $info["id"], "extension" => $info["format"]]);
    }
    
    echo $twig->render("index.html.twig", [
        "css" => "index",
        "images" => $images
    ]);
}

if (isset($_GET["id"]))
{
    $isOk = false;

    foreach ($metadata as $info) {
        if ($_GET["id"] === $info["id"]) {
            
            echo $twig->render("page.html.twig", [
                "css" => "page",
                "metadata" => $info
            ]);
            break;
        }
    }

    if (!$isOk)
    {
        showAll();
    }
}
else
{
    showAll();
}