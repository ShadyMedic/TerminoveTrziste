<?php

require 'init.php';

if (isset($_GET['success'])) {
    require 'Views/Reacted.phtml';
    exit();
} else if (isset($_GET['failure'])) {
    require 'Views/ReactionFailed.phtml';
    exit();
}

$advertId = $_REQUEST['advert'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $record = \TerminoveTrziste\Models\Database\Db::fetchQuery('SELECT * FROM advert WHERE id = ? AND active = 1;', [$advertId]);
    if ($record === false) {
        header('HTTP/1.1 404 Not Found');
        exit(
            "<h1>Error 404 Not Found</h1><h2>This Advert no longer exists or is hidden.</h2>
            <a href='#' onclick='history.back()'>
                Back to the advert board
            </a>"
        );
    }
    $advert = new \TerminoveTrziste\Models\Advert($record);

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        header("Location: /react.php?advert=$advertId&e=email&email=".$_POST['email']."&msg=".$_POST['message']);
        exit();
    }

    if (empty($_POST['message'])) {
        header("Location: /react.php?advert=$advertId&e=msg&email=".$_POST['email']."&msg=".$_POST['message']);
        exit();
    }

    $result = $advert->answer($_POST['email'], $_POST['message']);
    if ($result) {
        header('Location: /react.php?success');
        exit();
    } else {
        header('Location: /react.php?failure');
        exit();
    }
}

require 'Views/React.phtml';
