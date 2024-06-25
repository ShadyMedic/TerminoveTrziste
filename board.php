<?php

require 'init.php';

if (isset($_GET['subject'])) {
    $adverts = (new \TerminoveTrziste\Models\AdvertManager())->loadAdvertsForSubject($_GET['subject']);
} else {
    $adverts = (new \TerminoveTrziste\Models\AdvertManager())->loadAdverts();
}


require 'Views/Board.phtml';