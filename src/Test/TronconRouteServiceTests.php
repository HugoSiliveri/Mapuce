<?php

namespace App\PlusCourtChemin\Test;

use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Modele\DataObject\TronconRoute;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use App\PlusCourtChemin\Modele\Repository\TronconRouteRepository;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneService;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;
use PHPUnit\Framework\TestCase;

class TronconRouteServiceTests extends TestCase
{
    private $service;
    private $tronconRouteRepositoryMock;

    protected function setUp() : void {
        parent::setUp();
        $this->tronconRouteRepositoryMock = $this->createMock(TronconRouteRepository::class);
        $this->service = new TronconRouteService($this->tronconRouteRepositoryMock);
    }

    public function testRecupererClesPrimaireParNoeudsParametresNull() {
        $noeudDepart = null;
        $noeudArrivee = null;
        $this->expectException(ServiceException::class);
        $this->service->recupererClesPrimaireParNoeuds($noeudDepart,$noeudArrivee);
    }

    public function testRecupererClesPrimaireParNoeudsResultatCorrect() {
        $noeudDepart = 1;
        $noeudArrivee = 2;
        $resultat = new TronconRoute(1,"","","",1.0);
        $this->tronconRouteRepositoryMock->method("recupererClesPrimaireParNoeuds")->with($noeudDepart,$noeudArrivee)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererClesPrimaireParNoeuds($noeudDepart,$noeudArrivee));
    }

    public function testRecupererClesPrimaireParNoeudsResultatNull() {
        $noeudDepart = 1;
        $noeudArrivee = 2;
        $resultat = null;
        $this->expectException(ServiceException::class);
        $this->tronconRouteRepositoryMock->method("recupererClesPrimaireParNoeuds")->with($noeudDepart,$noeudArrivee)->willReturn($resultat);
        $this->service->recupererClesPrimaireParNoeuds($noeudDepart,$noeudArrivee);
    }

    public function testRecupererGeomParClesPrimaireParametreNull() {
        $parametre = null;
        $this->expectException(ServiceException::class);
        $this->service->recupererGeomParClesPrimaire($parametre);
    }

    public function testRecupererGeomParClesPrimaireResultatNull() {
        $parametre = 1;
        $this->expectException(ServiceException::class);
        $this->tronconRouteRepositoryMock->method("recupererGeomParClesPrimaire")->with($parametre)->willReturn(null);
        $this->service->recupererGeomParClesPrimaire($parametre);
    }

    public function testRecupererGeomParClesPrimaireResultatCorrect() {
        $parametre = 1;
        $resultat  ="163532";
        $this->tronconRouteRepositoryMock->method("recupererGeomParClesPrimaire")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererGeomParClesPrimaire($parametre));
    }

    public function testRecupererInfosParClesPrimaireParametreNull() {
        $parametre = null;
        $this->expectException(ServiceException::class);
        $this->service->recupererInfosParClesPrimaire($parametre);
    }

    public function testRecupererInfosParClesPrimaireResultatNull() {
        $parametre = 1;
        $this->expectException(ServiceException::class);
        $this->tronconRouteRepositoryMock->method("recupererInfosParClesPrimaire")->with($parametre)->willReturn(null);
        $this->service->recupererInfosParClesPrimaire($parametre);
    }

    public function testRecupererInfosParClesPrimaireResultatCorrect() {
        $parametre = 1;
        $resultat  ="Autoroute";
        $this->tronconRouteRepositoryMock->method("recupererInfosParClesPrimaire")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererInfosParClesPrimaire($parametre));
    }

    public function testCalculerTrajetParametreNull() {
        $parametre1 = null;
        $parametre2 = null;
        $this->expectException(ServiceException::class);
        $this->service->calculerTrajet($parametre1,$parametre2);
    }

    public function testCalculerTrajetResultatNull() {
        $parametre1 = 1;
        $parametre2 = 2;
        $this->expectException(ServiceException::class);
        $this->tronconRouteRepositoryMock->method("recupererInfosParClesPrimaire")->with($parametre1,$parametre2)->willReturn(null);
        $this->service->calculerTrajet($parametre1,$parametre2);
    }

    public function testCalculerTrajetResultatCorrect() {
        $parametre = 1;
        $parametre2 = 2;
        $resultat  = ["oui"];
        $this->tronconRouteRepositoryMock->method("calculerTrajet")->with($parametre)->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->calculerTrajet($parametre,$parametre2));
    }

}