<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Service\Exception\ServiceException;

interface NoeudRoutierServiceInterface
{
    /**
     * @throws ServiceException
     */
    public function recupererPar(array $critereSelection, $limit = 200);


}