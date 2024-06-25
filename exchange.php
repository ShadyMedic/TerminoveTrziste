<?php

require 'init.php';

//TODO: validate form data $_POST['email'], $_POST['link']

$advert = new TerminoveTrziste\Models\Advert();
$advert->generate();
$advert->setEmail($_POST['email']);
$subjects = $advert->loadFromSis($_POST['link']);
$adverts = $advert->replicateForSubjects($subjects);
$counteroffers = $advert->loadAvailableCounteroffers();

require 'Views/WantedDates.phtml';