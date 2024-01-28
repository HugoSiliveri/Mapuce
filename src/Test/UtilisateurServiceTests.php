<?php

namespace App\PlusCourtChemin\Test;

use App\PlusCourtChemin\Lib\ConnexionUtilisateurInterface;
use App\PlusCourtChemin\Modele\DataObject\NoeudCommune;
use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\TronconRouteRepository;
use App\PlusCourtChemin\Modele\Repository\UtilisateurRepository;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneService;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;
use App\PlusCourtChemin\Service\UtilisateurService;
use PHPUnit\Framework\TestCase;

class UtilisateurServiceTests extends TestCase
{
    private $service;
    private $utilisateurRepositoryMock;
    private $connexionUtilisateur;

    protected function setUp() : void {
        parent::setUp();
        $this->utilisateurRepositoryMock = $this->createMock(UtilisateurRepository::class);
        $this->connexionUtilisateur = $this->createMock(ConnexionUtilisateurInterface::class);
        $this->service = new UtilisateurService($this->utilisateurRepositoryMock,$this->connexionUtilisateur);
    }

    public function testGetUserParametreNull() {
        $this->expectException(ServiceException::class);
        $this->service->recupererUtilisateur(null);
    }

    public function testGetUserResultatNull() {
        $this->expectException(ServiceException::class);
        $this->utilisateurRepositoryMock->method("recupererParClePrimaire")->with("l")->willReturn(null);
        $this->service->recupererUtilisateur("l");
    }

    public function testGetUserResultatCorrect() {
        $resultat = new Utilisateur("","","","",false,"","","","");
        $this->utilisateurRepositoryMock->method("recupererParClePrimaire")->with("l")->willReturn($resultat);
        $this->assertEquals($resultat,$this->service->recupererUtilisateur("l"));
    }



}