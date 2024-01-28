<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Lib\EuclidienneStrategy;
use App\PlusCourtChemin\Lib\HaversineStrategy;
use App\PlusCourtChemin\Lib\LoxodromiqueStrategy;
use App\PlusCourtChemin\Lib\PlusCourtChemin;
use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;
use App\PlusCourtChemin\Service\Exception\ServiceException;

class NoeudCommuneService implements NoeudCommuneServiceInterface
{

    public function __construct(private readonly AbstractRepositoryInterface $noeudCommuneRepository)
    {
    }

    /**
     * Fonction qui renvoi tous les noeudCommunes présents dans la base de donnée
     * @return array
     */
    public function recupererNoeudsCommune(): array
    {
        //appel au modèle pour gérer la BDD
        return $this->noeudCommuneRepository->recuperer();
    }

    /**
     * Fonction qui permet de réucpérer les informations précises sur un noeudCommune
     * @param $gid
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function recupererNoeudCommune($gid)
    {
        if (!isset($gid)) {
            throw new ServiceException("Immatriculation manquante !");
        } else {
            $noeudCommune = $this->noeudCommuneRepository->recupererParClePrimaire($gid);
            if ($noeudCommune === null) {
                throw new ServiceException("gid inconnu !");
            } else {
                return $noeudCommune;
            }
        }
    }

    public function recupererPar(array $critereSelection, $limit = 200)
    {
        if (!isset($critereSelection)) {
            throw new ServiceException("Critères manquants !");
        } else {
            $noeuds = $this->noeudCommuneRepository->recupererPar($critereSelection, $limit);
            if ($noeuds === null) {
                throw new ServiceException("Aucun noeud sélectionné");
            } else {
                return $noeuds;
            }
        }
    }


    /**
     * @throws ServiceException
     */
    public function plusCourtChemin(array $elements, $noeudRoutierService, $tronconRouteService): array
    {
        if (!empty($elements)) {
            if (strcmp($elements["heuristique"], "") == 0) {
                throw new ServiceException("Vous devez selectionner une précision !");
            }

            $nomCommuneDepart = $elements["nomCommuneDepart"];
            $nomCommuneArrivee = $elements["nomCommuneArrivee"];

            $currentPosition = strcmp($elements['currentPosition'], "true") == 0;


            /** @var NoeudCommune $noeudCommuneDepart */
            if (!$currentPosition) $noeudCommuneDepart = $this->recupererPar(["nom_comm" => $nomCommuneDepart])[0];
            //il peut y avoir plusieurs communes du même nom, comment fait-on dans ce cas ?
            /** @var NoeudCommune $noeudCommuneArrivee */
            $noeudCommuneArrivee = $this->recupererPar(["nom_comm" => $nomCommuneArrivee])[0];
            //même commentaire

            if (!$currentPosition) $noeudRoutierDepartGid = $noeudRoutierService->recupererPar([
                "id_rte500" => $noeudCommuneDepart->getId_nd_rte()
            ])[0]->getGid();
            $noeudRoutierArriveeGid = $noeudRoutierService->recupererPar([
                "id_rte500" => $noeudCommuneArrivee->getId_nd_rte()
            ])[0]->getGid();

            if ($currentPosition) {
                $noeudRoutierDepartGid = intval($elements['nomCommuneDepart']);
            }

            $modeDebug = false;
            $modeCheat = false;

            if (strcmp($elements["modeCheat"], "true") == 0) {
                $modeCheat = true;
            }

            if (strcmp($elements["modeDebug"], "true") == 0) {
                $modeDebug = true;
            }
            //echo $modeDebug;

            $pcc = new PlusCourtChemin($noeudRoutierDepartGid, $noeudRoutierArriveeGid);

            if ($modeCheat){
                $tab = $pcc->calculerV2($tronconRouteService, $noeudRoutierService);
            }else{
                if (!isset($elements["heuristique"])) {
                    $pcc->setHeuristiqueStrategy(new HaversineStrategy($noeudRoutierService));
                    $tab = $pcc->calculer($tronconRouteService, $noeudRoutierService, $modeDebug);
                } else if (strcmp($elements["heuristique"], "Haversine") == 0) {
                    $pcc->setHeuristiqueStrategy(new HaversineStrategy($noeudRoutierService));
                    $tab = $pcc->calculer($tronconRouteService, $noeudRoutierService, $modeDebug);
                } else if (strcmp($elements["heuristique"], "Loxodromique") == 0) {
                    $pcc->setHeuristiqueStrategy(new LoxodromiqueStrategy($noeudRoutierService));
                    $tab = $pcc->calculer($tronconRouteService, $noeudRoutierService, $modeDebug);
                } else {
                    $pcc->setHeuristiqueStrategy(new EuclidienneStrategy($noeudRoutierService));
                    $tab = $pcc->calculer($tronconRouteService, $noeudRoutierService, $modeDebug);
                }
            }

            $distance = $tab[0];
            $troncons = $tab[1];
            $temp = $tab[2];
            $tronconsSupp = $tab[3];
            $geomNoeudsParcourus = $tab[4];

            $geomDepart = $noeudRoutierService->getGeom($noeudRoutierDepartGid);
            $geomArrive = $noeudRoutierService->getGeom($noeudRoutierArriveeGid);

            $tabCoord = ["depart" => $noeudRoutierService->getLongitudeLatitude($geomDepart), "arrivee" => $noeudRoutierService->getLongitudeLatitude($geomArrive)];

            $parametres["nomCommuneDepart"] = $nomCommuneDepart;
            $parametres["nomCommuneArrivee"] = $nomCommuneArrivee;
            $parametres["distance"] = $distance;
            $parametres["donnees"] = $tabCoord;
            $parametres["troncons"] = $troncons;
            $parametres["heures"] = $temp[0];
            $parametres["minutes"] = $temp[1];
            $parametres["gidNoeudDepart"] = $noeudRoutierDepartGid;
            $parametres["gidNoeudArrivee"] = $noeudRoutierArriveeGid;
            $parametres["geomNoeudsParcourus"] = $geomNoeudsParcourus;
            if (!$modeDebug) $parametres["debugChemin"] = null;
            else $parametres["debugChemin"] = $tronconsSupp;
        }
        $parametres["post"] = !empty($elements);
        return $parametres;
    }

    public function recupererRequeteVilleDepart(): array
    {
        $ville = $_GET["nomCommuneDepart"];
        return $this->noeudCommuneRepository->selectByName($ville);
    }

    public function recupererRequeteVilleArrivee(): array
    {
        $ville = $_GET["nomCommuneArrivee"];
        return $this->noeudCommuneRepository->selectByName($ville);
    }
}





