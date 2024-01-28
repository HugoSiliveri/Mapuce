<?php

namespace App\PlusCourtChemin\Test;

use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\TronconRouteRepository;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneService;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;
use PHPUnit\Framework\TestCase;

class NoeudCommuneServiceTests extends TestCase
{
    private $service;
    private $noeudCommuneRepositoryMock;

    protected function setUp() : void {
        parent::setUp();
        $this->noeudCommuneRepositoryMock = $this->createMock(NoeudCommuneRepository::class);
        $this->service = new NoeudCommuneService($this->noeudCommuneRepositoryMock);
    }

    public function testGetNoeudCommuneNull() {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage('Immatriculation manquante !');
        $this->noeudCommuneRepositoryMock->method("recupererParClePrimaire")->with(null)->willReturn(null);
        $this->service->recupererNoeudCommune(null);
    }

    public function testGetNoeudCommuneInexistant() {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("gid inconnu !");
        $this->noeudCommuneRepositoryMock->method("recupererParClePrimaire")->with(100000000)->willReturn(null);
        $this->service->recupererNoeudCommune(100000000);
    }

    public function testGetNoeudCommuneExistant() {
        $fakeNoeudCommune = new NoeudCommune(1,"","","");
        $this->noeudCommuneRepositoryMock->method("recupererParClePrimaire")->with(1)->willReturn($fakeNoeudCommune);
        $this->assertEquals($fakeNoeudCommune,$this->service->recupererNoeudCommune(1));
    }

    //pas besoin de tester si le tableau est null, car la fonction attend seulement un tableau en paramètre
    public function testGetNoeudCommmuneByNull() {
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Critères manquants !");
        $this->noeudCommuneRepositoryMock->method("recupererPar")->with(null)->willReturn([]);
        $this->service->recupererPar(null);
    }

    public function testGetNoeudCommuneByAvecFauxCriteres() {
        $fakeArray = ["nom_comm" => "Exemple de commune inexistante"];
        $this->expectException(ServiceException::class);
        $this->expectExceptionMessage("Aucun noeud sélectionné");
        $this->noeudCommuneRepositoryMock->method("recupererPar")->with($fakeArray)->willReturn([]);
        $this->service->recupererPar($fakeArray);
    }

    public function testGetNoeudCommuneByNormal() {
        $fakeParametres = ["nom_comm" => "Montpellier"];
        $fakeNoeudsCommune = [new NoeudCommune(1,"","","")];
        $this->noeudCommuneRepositoryMock->method("recupererPar")->with($fakeParametres)->willReturn($fakeNoeudsCommune);
        $this->assertEquals($fakeNoeudsCommune,$this->service->recupererPar($fakeParametres));
    }

    public function testPlusCourtCheminGET() {
        $noeudCommuneRepositoryMock = $this->createMock(NoeudCommuneRepository::class);
        $noeudRoutierService = new NoeudRoutierService($noeudCommuneRepositoryMock);
        $tronconRouteRepository = $this->createMock(TronconRouteRepository::class);
        $tronconRouteService = new TronconRouteService($tronconRouteRepository);
        $elements = [];
        $resultat = ["post" => false];
        $this->assertEquals($resultat,$this->service->plusCourtChemin($elements,$noeudRoutierService,$tronconRouteRepository));
    }

//    public function testPlusCourtCheminPOST() {
//        $noeudCommuneRepositoryMock = $this->createMock(NoeudCommuneRepository::class);
//        $noeudRoutierService = new NoeudRoutierService($noeudCommuneRepositoryMock);
//        $tronconRouteRepository = $this->createMock(TronconRouteRepository::class);
//        $tronconRouteService = new TronconRouteService($tronconRouteRepository);
//        $elements = ["oui" => "non"];
//        $resultat = ["post" => true];
//        $this->assertEquals($resultat,$this->service->plusCourtChemin($elements,$noeudRoutierService,$tronconRouteRepository));
//    }

//    public function testRecupererRequeteVilleDepart() {
//        $resultat = ["nomCommuneDepart" => "Montpellier"];
//        $nomVille = "Montpellier";
//        $this->noeudCommuneRepositoryMock->method("selectByName")->with($nomVille)->willReturn($resultat);
//        $this->service->recupererRequeteVilleDepart();
//    }

}