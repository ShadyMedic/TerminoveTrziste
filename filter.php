<?php

require 'init.php';

if (isset($_GET['search'])) {
    $loadedCodes = (new \TerminoveTrziste\Models\SubjectManager())->searchSubjects($_GET['search']);
}

require 'Views/Filter.phtml';