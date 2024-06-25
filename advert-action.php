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

$advert = $adverts[0]->generalize();

switch ($_POST['action']) {
    case 'Activate Advert':
        $advert->activate();
        header('Location: /advert.php?token=' . $advert->getToken());
        break;
    case 'Deactivate Advert':
        $advert->deactivate();
        header('Location: /advert.php?token=' . $advert->getToken());
        break;
    case 'Delete Advert':
        $advert->delete();
        header('Location: /');
        break;
    default:
        header('HTTP/1.1 400 Bad Request');
        exit("<h1>Error 400 Bad Request</h1><h2>Invalid action.</h2><a href='/'>Back to homepage</a>");
}
