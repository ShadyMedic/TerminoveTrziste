<?php

require 'init.php';

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    header('Location: /new.php?e=email');
    exit();
}
if (
    !filter_var($_POST['link'], FILTER_VALIDATE_URL) ||
    !str_starts_with($_POST['link'], 'https://is.cuni.cz/studium/') ||
    !str_contains($_POST['link'], '&ztid=') ||
    !str_contains($_POST['link'], '/term_st2/index.php?') ||
    !str_contains($_POST['link'], 'sub=detail')
) {
    header('Location: /new.php?e=link');
    exit();
}

$advert = new TerminoveTrziste\Models\Advert();
$advert->generate();
$advert->setEmail($_POST['email']);
$subjects = $advert->loadFromSis($_POST['link']);
$adverts = $advert->replicateForSubjects($subjects);
$counteroffers = $advert->loadAvailableCounteroffers();

require 'Views/WantedDates.phtml';