<?php

require __DIR__ . '/../bootstrap.php';

$result = $oidc->authenticate($_GET);

if ($result->error()) {

    die($result->error());

}

header('Location: index.php');
