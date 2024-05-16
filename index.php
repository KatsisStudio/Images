<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$metadata = json_decode(file_get_contents("data/info.json"), true);

function showAll($twig, $metadata)
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
            $isOk = true;
            break;
        }
    }

    if (!$isOk)
    {
        showAll($twig, $metadata);
    }
}
else if (isset($_GET["tag"]))
{
    $isOk = false;

    $tags = json_decode(file_get_contents("data/tags.json"), true);
    foreach ($tags as $info) {
        if ($_GET["tag"] === $info["name"]) {
            // For all ids, correct the image field to include the extension
            
            for ($i = 0; $i < count($info["images"]); $i++)
            {
                $img = $info["images"][$i];
                foreach ($metadata as $m)
                {
                    if ($img == $m["id"])
                    {
                        $info["images"][$i] = [
                            "id" => $img,
                            "extension" => $m["format"]
                        ];
                        break;
                    }
                }
            }
            
            echo $twig->render("tag.html.twig", [
                "css" => "tag",
                "tag" => $info
            ]);
            $isOk = true;
            break;
        }
    }

    if (!$isOk)
    {
        showAll($twig, $metadata);
    }
}
else
{
    showAll($twig, $metadata);
}