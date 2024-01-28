<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\PlusCourtChemin\Controleur\RouteurURL;
use Symfony\Component\HttpFoundation\Request;

$requete = Request::createFromGlobals();

RouteurURL::traiterRequete($requete)->send();


