<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Service\NoeudRoutierServiceInterface;

abstract class HeuristiqueStrategy
{
    public function __construct(private readonly NoeudRoutierServiceInterface $noeudRoutierService)
    {
    }

    public abstract function heuristique(int $noeudRoutierDepartGid, int $noeudRoutierArriveeGid): float;

    /**
     * Récupère la latitute et la longitude du résultat en string d'une requete SQL renvoyant les coordonnés d'un point
     *
     * @param string $geom le point géométrique écrit littéralement comme résultat de la requete SQL
     * @return array contenant uniquement la latitude et la longitude respectivement en [0] et [1] du tableau
     */
    protected function getLongitudeLatitude(string $geom): array
    {
        $sansPoint = substr($geom, 6);
        $sansDerniereP = substr($sansPoint, 0, strlen($sansPoint) - 1);
        return explode(" ", $sansDerniereP);
    }

    /**
     * @return NoeudRoutierServiceInterface
     */
    protected function getNoeudRoutierService(): NoeudRoutierServiceInterface
    {
        return $this->noeudRoutierService;
    }


}