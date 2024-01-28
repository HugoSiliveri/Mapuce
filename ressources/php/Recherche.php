<?php

use App\PlusCourtChemin\Configuration\ConfigurationBDDPostgreSQL;
use App\PlusCourtChemin\Modele\Repository\ConnexionBaseDeDonnees;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;


require_once "../../src/Configuration/ConfigurationBDDInterface.php";
require_once "../../src/Configuration/ConfigurationBDDPostgreSQL.php";
require_once "../../src/Configuration/Configuration.php";
require_once "../../src/Modele/Repository/ConnexionBaseDeDonneesInterface.php";
require_once "../../src/Modele/Repository/ConnexionBaseDeDonnees.php";
require_once "../../src/Modele/Repository/AbstractRepository.php";
require_once "../../src/Modele/DataObject/AbstractDataObject.php";
require_once "../../src/Modele/DataObject/NoeudCommune.php";
require_once "../../src/Modele/Repository/NoeudCommuneRepository.php";
require_once "../../src/Modele/DataObject/NoeudRoutier.php";
require_once "../../src/Modele/Repository/NoeudRoutierRepository.php";


$nomCommuneDepart = $_POST["nomCommuneDepart"];
$nomCommuneArrivee = $_POST["nomCommuneArrivee"];

$noeudCommuneRepository = new NoeudCommuneRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL()));

$noeudCommuneDepart = $noeudCommuneRepository->recupererPar(["nom_comm" => $nomCommuneDepart])[0];
//il peut y avoir plusieurs communes du même nom, comment fait-on dans ce cas ?

$noeudCommuneArrivee = $noeudCommuneRepository->recupererPar(["nom_comm" => $nomCommuneArrivee])[0];

$noeudRoutierRepository = new NoeudRoutierRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL()));
$noeudRoutierDepartGid = $noeudRoutierRepository->recupererPar([
    "id_rte500" => $noeudCommuneDepart->getId_nd_rte()
])[0]->getGid();
$noeudRoutierArriveeGid = $noeudRoutierRepository->recupererPar([
    "id_rte500" => $noeudCommuneArrivee->getId_nd_rte()
])[0]->getGid();

$geomDepart = (new NoeudRoutierRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL())))->getGeom($noeudRoutierDepartGid);
$geomArrive = (new NoeudRoutierRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL())))->getGeom($noeudRoutierArriveeGid);

$tabCoord = ["depart" => (new NoeudRoutierRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL())))->getLongitudeLatitude($geomDepart), "arrivee" => (new NoeudRoutierRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL())))->getLongitudeLatitude($geomArrive)];

echo json_encode($tabCoord);
