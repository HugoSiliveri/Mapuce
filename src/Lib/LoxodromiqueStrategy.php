<?php

namespace App\PlusCourtChemin\Lib;

class LoxodromiqueStrategy extends HeuristiqueStrategy
{
    /**
     * Calcul l'heuristique (la distance en km) entre les deux noeuds présents sur la sphère terrestre en utilisant la distance loxodromique
     *
     * @param int $noeudDepartGid le noeud de départ à calculer pour l'heuristique
     * @param int $noeudArriveGid le noeud d'arrivé à calculer pour l'heuristique
     * @return float le score heuristique calculé à partir de la formule de loxodromie entre le @param $noeudDepartGid et le @param $noeudArriveGid
     */
    public function heuristique(int $noeudDepartGid, int $noeudArriveGid): float
    {
        $coordonnesDepart = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudDepartGid));
        $coordonnesArrive = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudArriveGid));

        /* Récupération des longitudes et latitudes des deux points */
        $latitudeD = $coordonnesDepart[0];
        $longitudeD = $coordonnesDepart[1];
        $latitudeA = $coordonnesArrive[0];
        $longitudeA = $coordonnesArrive[1];

        /* Conversion des coordonnes en radian */
        $latDRad = deg2rad(floatval($latitudeD));
        $longitudeDRad = deg2rad(floatval($longitudeD));
        $latARad = deg2rad(floatval($latitudeA));
        $longitudeARad = deg2rad(floatval($longitudeA));

        /* Calcul de la distance loxodromique */
        $R = 6371; // Rayon de la Terre en km
        $deltaLong = $longitudeARad - $longitudeDRad;
        $deltaTheta = log(tan($latARad / 2 + pi() / 4) / tan($latDRad / 2 + pi() / 4));
        if (abs($deltaLong) > pi()) {
            if ($deltaLong > 0) {
                $deltaLong = -(2 * pi() - $deltaLong);
            } else {
                $deltaLong = (2 * pi() + $deltaLong);
            }
        }
        $distance = $R * sqrt(pow($deltaTheta, 2) + pow(cos($latDRad) * $deltaLong, 2));

        return $distance;

    }

}