<?php

namespace App\PlusCourtChemin\Service;

interface TronconRouteServiceInterface
{
    public function recupererClesPrimaireParNoeuds($noeud_depart, $noeud_arrivee);

    public function recupererGeomParClesPrimaire($troncon_gid);

    public function recupererInfosParClesPrimaire($troncon_gid);
}