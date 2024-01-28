<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;
use App\PlusCourtChemin\Service\Exception\ServiceException;

class TronconRouteService implements TronconRouteServiceInterface
{
    public function __construct(private readonly AbstractRepositoryInterface $tronconRouteRepository)
    {
    }

    public function recupererClesPrimaireParNoeuds($noeud_depart, $noeud_arrivee)
    {
        if (!isset($noeud_depart) || !isset($noeud_arrivee)) {
            throw new ServiceException("Noeud de départ ou d'arrivée manquant !");
        } else {
            $tronconRoute = $this->tronconRouteRepository->recupererClesPrimaireParNoeuds($noeud_depart, $noeud_arrivee);
            if ($tronconRoute === null) {
                throw new ServiceException("Troncon route inconnu !");
            } else {
                return $tronconRoute;
            }
        }
    }

    public function recupererGeomParClesPrimaire($troncon_gid)
    {
        if (!isset($troncon_gid)) {
            throw new ServiceException("gid manquant !");
        } else {
            $tronconRoute = $this->tronconRouteRepository->recupererGeomParClesPrimaire($troncon_gid);
            if ($tronconRoute === null) {
                throw new ServiceException("Troncon route inconnu !");
            } else {
                return $tronconRoute;
            }
        }
    }

    /**
     * @throws ServiceException
     */
    public function recupererInfosParClesPrimaire($troncon_gid)
    {
        if (!isset($troncon_gid)) {
            throw new ServiceException("gid manquant !");
        } else {
            $tronconRoute = $this->tronconRouteRepository->recupererInfosParClesPrimaire($troncon_gid);
            if ($tronconRoute === null) {
                throw new ServiceException("Troncon route inconnu !");
            } else {
                return $tronconRoute;
            }
        }
    }

    /**
     * @param $noeudDepart
     * @param $noeudArrive
     * @return array
     * @throws ServiceException
     */
    public function calculerTrajet($noeudDepart, $noeudArrive):array{
        if (!isset($noeudDepart) || !isset($noeudArrive)){
            throw new ServiceException("Noeud routiers manquants !");
        }else{
            $resultat = $this->tronconRouteRepository->calculerTrajet($noeudDepart, $noeudArrive);
            if ($resultat == null){
                throw new ServiceException("Le calcul à échouer !");
            }else{
                return $resultat;
            }
        }

    }
}