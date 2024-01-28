<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Service\Exception\ServiceException;

interface NoeudCommuneServiceInterface
{
    /**
     * Fonction qui renvoi tous les noeudCommunes présents dans la base de donnée
     * @return array
     */
    public function recupererNoeudsCommune(): array;

    /**
     * Fonction qui permet de réucpérer les informations précises sur un noeudCommune
     * @param $gid
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function recupererNoeudCommune($gid);
}