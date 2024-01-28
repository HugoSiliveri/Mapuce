<?php

namespace App\PlusCourtChemin\Lib;

class HaversineStrategy extends HeuristiqueStrategy
{

    /**
     * Calcul l'heuristique (la distance en km) entre les deux noeuds présents sur la sphère terrestre et prend en conséquence cette particularité
     *
     * @param int $noeudDepartGid le noeud de départ à calculer pour l'heuristique
     * @param array $coordoneeArrivee le noeud d'arrivé à calculer pour l'heuristique
     * @return float le score heuristique calculé à partir de la formule de Haversine entre le @param $noeudDepartGid et le @param $noeudArriveGid
     */
    public function heuristique(int $noeudDepartGid, int $noeudArriveGid): float
    {
        $coordonnesDepart = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudDepartGid));
        $coordonnesArrivee = $this->getLongitudeLatitude(parent::getNoeudRoutierService()->getGeom($noeudArriveGid));

        /* Récupération des longitudes et latitudes des deux points */
        $longitudeD = $coordonnesDepart[1];
        $latitudeD = $coordonnesDepart[0];

        $longitudeA = $coordonnesArrivee[1];
        $latitudeA = $coordonnesArrivee[0];

        /* Conversion des coordonnes en radian */
        $latDRad = deg2rad(floatval($latitudeD));
        $longitudeDRad = deg2rad(floatval($longitudeD));
        $latARad = deg2rad(floatval($latitudeA));
        $longitudeARad = deg2rad(floatval($longitudeA));

        $deltaLat = $latARad - $latDRad;
        $deltaLongitude = $longitudeARad - $longitudeDRad;

        //$calcul1 = pow(sin($deltaLat / 2), 2) + cos($latDRad) * cos($latARad) * pow(sin($deltaLongitude / 2),2);

        $sinDeltaLat = sin($deltaLat / 2);
        $sinDeltaLon = sin($deltaLongitude / 2);

        /* Calcul la distance grace à la formule de Haversine */
        $calcul1 = $sinDeltaLat * $sinDeltaLat + cos($latDRad) * cos($latARad) * $sinDeltaLon * $sinDeltaLon;
        $calcul2 = 2 * atan2(sqrt($calcul1), sqrt(1 - $calcul1));
        $distance = 6371 * $calcul2;

        return $distance;

    }


}