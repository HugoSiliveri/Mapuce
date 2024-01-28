<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;
use App\PlusCourtChemin\Service\Exception\ServiceException;

class NoeudRoutierService implements NoeudRoutierServiceInterface
{
    public function __construct(private readonly AbstractRepositoryInterface $noeudRoutierRepository)
    {
    }

    /**
     * @throws ServiceException
     */
    public function recupererPar(array $critereSelection, $limit = 200)
    {
        if (!isset($critereSelection)) {
            throw new ServiceException("Critères manquants !");
        } else {
            $noeuds = $this->noeudRoutierRepository->recupererPar($critereSelection, $limit);
            if ($noeuds === null) {
                throw new ServiceException("Aucun noeud sélectionné");
            } else {
                return $noeuds;
            }
        }
    }

    public function getGeom(int $noeudRoutierGid): string
    {
        if ($noeudRoutierGid === null) {
            throw new ServiceException("Identifiant null");
        } else {
            return $this->noeudRoutierRepository->getGeom($noeudRoutierGid);
        }
    }

    public function getLongitudeLatitude(string $geom): array
    {
        if ($geom === "") {
            throw new ServiceException("geom vide ou null");
        } else {
            return $this->noeudRoutierRepository->getLongitudeLatitude($geom);
        }
    }

    public function recupererParClePrimaire(string $valeurClePrimaire): ?AbstractDataObject
    {
        if (!isset($valeurClePrimaire)) {
            throw new ServiceException("clé primaire manquante !");
        } else {
            $noeud = $this->noeudRoutierRepository->recupererParClePrimaire($valeurClePrimaire);
            return $noeud;
        }
    }

    public function getGidCurrentPosition($longitude, $latitude): string
    {
        if (!isset($longitude) || !isset($latitude)) {
            throw new ServiceException("données manquantes !");
        }
        if ($longitude === "" || $latitude === "") {
            throw new ServiceException("Coordonnées vides !");
        }
        return $this->noeudRoutierRepository->getGidFromCoord($longitude, $latitude);
    }
}