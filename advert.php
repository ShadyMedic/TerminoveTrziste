<?php

require 'init.php';

if (!isset($_REQUEST['token'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("<h1>Error 401 Unauthorized</h1><h2>No token was provided.</h2><a href='/'>Back to homepage</a>");
}

$aManager = new \TerminoveTrziste\Models\AdvertManager();
$adverts = $aManager->loadRelatedAdverts($_REQUEST['token']);

if ($adverts === false) {
    header("HTTP/1.1 403 Forbidden");
    exit("<h1>Error 403 Forbidden</h1><h2>Token is incorrect.</h2><a href='/'>Back to homepage</a>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($adverts as $advert) {
        $advert->replicateForSearches(['2024-06-25', '2024-06-26']); //$_POST['counteroffer']
    }
}

$advert = $adverts[0]->generalize();

if (isset($_POST['sendmail'])) {
    $advert->sendCreationMail();
}

$aManager->activateAllRelated($advert->getToken());

$uniqueSearchDates = (new \TerminoveTrziste\Models\AdvertManager())->listSearchesAmongRelated($advert->getToken());

require 'Views/Advert.phtml';

