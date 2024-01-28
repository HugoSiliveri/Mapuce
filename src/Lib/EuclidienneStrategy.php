<?php

namespace App\PlusCourtChemin\Lib;

class EuclidienneStrategy extends HeuristiqueStrategy
{
    public function heuristique(int $noeudDepartGid, int $noeudArriveGid): float
    {
        $coordonnesDepart = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudDepartGid));
        $coordonnesArrivee = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudArriveGid));

        $xDepart = deg2rad(floatval($coordonnesDepart[1])) * 6371 * cos(deg2rad(floatval($coordonnesDepart[0])));
        $yDepart = deg2rad(floatval($coordonnesDepart[0])) * 6371;

        $xArrivee = deg2rad(floatval($coordonnesArrivee[1])) * 6371 * cos(deg2rad(floatval($coordonnesArrivee[0])));
        $yArrivee = deg2rad(floatval($coordonnesArrivee[0])) * 6371;

        /* Calcul de la distance euclidienne */
        return sqrt(pow($xArrivee - $xDepart, 2) + pow($yArrivee - $yDepart, 2));
    }

}