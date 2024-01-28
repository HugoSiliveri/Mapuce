<?php

use App\PlusCourtChemin\Configuration\ConfigurationBDDPostgreSQL;
use App\PlusCourtChemin\Modele\Repository\ConnexionBaseDeDonnees;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Service\NoeudCommuneService;

//phpinfo();
//die();


require_once __DIR__ . '/../../vendor/autoload.php';

//require_once __DIR__ . '../../src/Controleur/ControleurNoeudCommune.php';

// lancement de la requête SQL avec selectByName et
// récupération du résultat de la requête SQL
// ...
$tab = (new NoeudCommuneService(new NoeudCommuneRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL()))))->recupererRequeteVilleArrivee();

// délai fictif
// sleep(1);

// affichage en format JSON du résultat précédent
// ...
echo json_encode($tab);
