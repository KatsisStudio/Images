<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

$urlData = array_filter(explode("/", substr($_SERVER["REQUEST_URI"], 1)));
$target = count($urlData) > 0 ? strtolower($urlData[0]) : null;
$value = count($urlData) > 0 ? $urlData[1] : null;
if ($value == NULL) $target = NULL; // We can't have a target without a value

$metadata = json_decode(file_get_contents("data/info.json"), true);
$tags = json_decode(file_get_contents("data/tags.json"), true);

$json = isset($_GET["json"]) && $_GET["json"] === "1";
if ($json) {
    header('Content-Type: application/json; charset=utf-8');
}

function showAll($twig, $metadata)
{
    $images = [];
    
    foreach (array_reverse($metadata) as $info)
    {
        array_push($images, [ "id" => $info["id"], "format" => $info["format"]]);
    }
    
    if (isset($_GET["json"]) && $_GET["json"] === "1") {
        echo json_encode($images);
    } else {
        echo $twig->render("index.html.twig", [
            "css" => "index",
            "images" => $images
        ]);
    }
}

function  getTagCount($tag, $tags) {
    return [ "name" => $tag, "count" => count($tags[$tag]["images"]) ];
}

if ($target === "i")
{
    $isOk = false;

    foreach ($metadata as $info)
    {
        if ($value === $info["id"])
        {
            $tags = [
                "authors" => [
                    getTagCount("author_" . $info["author"], $tags),
                ],
                "names" => array_map(function($tag) use (&$tags) { return getTagCount("name_" . $tag, $tags); }, $info["tags"]["characters"]),
                "parodies" => array_map(function($tag) use (&$tags) { return getTagCount($tag, $tags); }, $info["tags"]["parodies"]),
                "others" => array_map(function($tag) use (&$tags) { return getTagCount($tag, $tags); }, $info["tags"]["others"])
            ];

            $info["tags_cleaned"] = $tags;
            unset($info["tags"]);
            unset($info["author"]);

            if ($json) {
                echo json_encode($info);
            } else {
                echo $twig->render("page.html.twig", [
                    "css" => "page",
                    "metadata" => $info,
                    "isimage" => true
                ]);
            }
            $isOk = true;
            break;
        }
    }

    if (!$isOk)
    {
        showAll($twig, $metadata);
    }
}
else if ($target === "t")
{
    $isOk = false;

    foreach ($tags as $key => $info) {
        if ($value === $key) {
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
                            "format" => $m["format"]
                        ];
                        break;
                    }
                }
            }
            
            if ($json) {
                echo json_encode([
                    "name" => $key,
                    "tag" => $info
                ]);
            } else {
                echo $twig->render("tag.html.twig", [
                    "css" => "tag",
                    "name" => $key,
                    "tag" => $info
                ]);
            }
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