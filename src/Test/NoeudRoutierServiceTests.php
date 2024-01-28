<?php

namespace App\PlusCourtChemin\Test;

use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use App\PlusCourtChemin\Modele\Repository\TronconRouteRepository;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneService;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;
use PHPUnit\Framework\TestCase;

class NoeudRoutierServiceTests extends TestCase
{
    private $service;
    private $noeudRoutierRepositoryMock;

    protected function setUp() : void {
        parent::setUp();
        $this->noeudRoutierRepositoryMock = $this->createMock(NoeudRoutierRepository::class);
        $this->service = new NoeudRoutierService($this->noeudRoutierRepositoryMock);
    }

    public function testGetByNormal() {
            $fakeParametres = ["nature" => "Carrefour simple"];
            $fakeNoeudsRoutier = [new NoeudRoutier(1,"",$this->noeudRoutierRepositoryMock)];
            $this->noeudRoutierRepositoryMock->method("recupererPar")->with($fakeParametres)->willReturn($fakeNoeudsRoutier);
            $this->assertEquals($fakeNoeudsRoutier,$this->service->recupererPar($fakeParametres));
    }

    //pas besoin de mettre une condition null dans la fonction car la fonction spécifie déjà d'avoir un nombre entier en paramètre
    public function testGetGeomNull() {
        $parametre = null;
        $this->expectException(ServiceException::class);
        $this->service->getGeom($parametre);
    }

    public function testGetGeomExistant() {
        $parametre = 1;
        $resultat = "651332";
        $this->noeudRoutierRepositoryMock->method("getGeom")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->getGeom($parametre));
    }

    //ce cas n'est pas prévu dans la fonction, à corriger
    public function testGetGeomInexistant() {
        $parametre = 1000000000;
        $this->expectException(ServiceException::class);
        $this->noeudRoutierRepositoryMock->method("getGeom")->with($parametre)->willReturn(null);
        $this->service->getGeom($parametre);
    }

    public function testGetLongitudeLatitudeVide() {
        $parametre = "";
        $this->expectException(ServiceException::class);
        $this->service->getLongitudeLatitude($parametre);
    }

    public function testGetLongitudeLatitudeCorrect() {
        $parametre = "POINT(56.785435 4.543515)";
        $resultat = ["56.785435","4.543515"];
        $this->noeudRoutierRepositoryMock->method("getLongitudeLatitude")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->getLongitudeLatitude($parametre));
    }

    //cas inutile dans le code car la fonction attend un paramètre seulement de type string
    public function testGetByClePrimaireNull() {
        $parametre = null;
        $this->expectException(ServiceException::class);
        $this->service->recupererParClePrimaire($parametre);
    }

    public function testGetByClePrimaireCorrect() {
        $parametre = "1";
        $resultat = new NoeudRoutier(1,"",$this->noeudRoutierRepositoryMock);
        $this->noeudRoutierRepositoryMock->method("recupererParClePrimaire")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererParClePrimaire($parametre));
    }

    public function testGetByClePrimaireInexistant() {
        $parametre = "10653200000";
        $resultat = null;
        $this->noeudRoutierRepositoryMock->method("recupererParClePrimaire")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererParClePrimaire($parametre));
    }

    public function testGetGidCurrentPositionNull() {
        $longitude = null;
        $latitude = null;
        $this->expectException(ServiceException::class);
        $this->service->getGidCurrentPosition($longitude,$latitude);
    }

    public function testGetGidCurrentPositionVide() {
        $longitude = "";
        $latitude = "";
        $this->expectException(ServiceException::class);
        $this->service->getGidCurrentPosition($longitude,$latitude);
    }

    public function testGetGidCurrentPositionCorrect() {
        $longitude = "56.0521352";
        $latitude = "4.165132";
        $resultat = "1";
        $this->noeudRoutierRepositoryMock->method("getGidFromCoord")->with($longitude,$latitude)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->getGidCurrentPosition($longitude,$latitude));
    }

}