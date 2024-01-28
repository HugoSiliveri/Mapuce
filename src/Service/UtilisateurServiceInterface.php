<?php

namespace App\PlusCourtChemin\Service;

use App\PlusCourtChemin\Modele\DataObject\AbstractDataObject;
use App\PlusCourtChemin\Service\Exception\ServiceException;

interface UtilisateurServiceInterface
{
    /**
     * Fonction qui permet de récupérer tous les utilisateurs présents dans la base de donnée
     * @return array
     */
    public function recupererUtilisateurs(): array;

    /**
     * @param $login
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function recupererUtilisateur($login);

    /**
     * @param $login
     * @return void
     * @throws ServiceException
     */
    public function supprimerUtilisateur($login): void;

    /**
     * @param $login
     * @param $mdp
     * @param $mdp2
     * @param $prenom
     * @param $nom
     * @param $email
     * @param $avatar
     * @return void
     * @throws ServiceException
     */
    public function creerUtilisateur($login, $mdp, $mdp2, $prenom, $nom, $email, $avatar);

    /**
     * @param $login
     * @return AbstractDataObject
     * @throws ServiceException
     */
    public function droitsModifsUtilisateurs($login);

    /**
     * @param $login
     * @param $prenom
     * @param $nom
     * @param $mdp
     * @param $mdp2
     * @param $mdpAncien
     * @param $email
     * @param $estAdmin
     * @param $avatar
     * @return void
     * @throws ServiceException
     */
    public function modifierUtilisateur($login, $prenom, $nom, $mdp, $mdp2, $mdpAncien, $email, $estAdmin, $avatar);

    /**
     * @param $login
     * @param $mdp
     * @throws ServiceException
     */
    public function droitConnexion($login, $mdp);

    public function valideEmail($login, $nonce);
}