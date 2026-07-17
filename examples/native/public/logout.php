<?php

require __DIR__ . '/../bootstrap.php';

$oidc->logout();

header('Location: index.php');
