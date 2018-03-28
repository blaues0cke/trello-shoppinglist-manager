<?php

require_once('config.php');

function beautifyName($id, $name)
{
    $urlPart    = getCardUrlPart($id);
    $newName    = ucfirst($name);
    $parameters = [
        'name' => $newName,
    ];

    return put($urlPart, $parameters);
}

function buildUrl($urlPart, $optionalQueryParts = [])
{
    $baseQueryParts = [
        'key'   => TRELLO_API_KEY,
        'token' => TRELLO_API_TOKEN,
    ];
    $queryParts     = array_merge($baseQueryParts, $optionalQueryParts);
    $queryString    = http_build_query($queryParts);
    $absoluteUrl    = TRELLO_API_BASE_PATH . $urlPart . '?' . $queryString;

    return $absoluteUrl;
}

function delete($urlPart)
{
    return request($urlPart, [], 'DELETE');
}

function deleteCard($id)
{
    $urlPart = getCardUrlPart($id);

    return delete($urlPart);
}

function firstCharacterIsLowerCase($string)
{
    return strlen($string) > 0 && ctype_lower($string[0]);
}

function getCardUrlPart($id)
{
    $urlPart = 'cards/' . $id;

    return $urlPart;
}

function getListUrlPart()
{
    $parts      = [
        'lists',
        TRELLO_LIST_ID,
        'cards',
    ];
    $partString = implode('/', $parts);

    return $partString;
}

function get($urlPart)
{
    return request($urlPart);
}

function put($urlPart, $fields)
{
    return request($urlPart, $fields, 'PUT');
}

function request($urlPart, $queryParts = [], $customRequest = false)
{
    $absoluteUrl = buildUrl($urlPart, $queryParts);
    $resource    = curl_init($absoluteUrl);

    if ($customRequest) {
        curl_setopt($resource, CURLOPT_CUSTOMREQUEST, $customRequest);
    }

    curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);

    $response     = curl_exec($resource);
    $responseJSON = json_decode($response);

    return $responseJSON;
}

$listUrlPart = getListUrlPart();
$knownCards  = [];
$cards       = get($listUrlPart);

foreach ($cards as $card) {
    $name = $card->name;
    $id   = $card->id;
    $key  = md5(strtolower(trim($name)));

    if (!isset($knownCards[$key])) {
        $knownCards[$key] = $id;

        if (firstCharacterIsLowerCase($name)) {
            $response = beautifyName($id, $name);
        }
    } else {
        $response = deleteCard($id);
    }
}

echo 1;