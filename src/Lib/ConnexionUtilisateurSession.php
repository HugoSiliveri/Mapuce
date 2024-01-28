<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use App\PlusCourtChemin\Modele\HTTP\Session;
use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;

/**
 * ConnexionUtilisateurSession est une classe qui s'occupe de créer et gérer une instance d'un utilisateur sur le site.
 */
class ConnexionUtilisateurSession implements ConnexionUtilisateurInterface
{
    /**
     * @var string Est une clé qui permet de voir si un utilisateur est déjà connecté dans une session
     */
    private string $cleConnexion = "_utilisateurConnecte";

    public function __construct(private readonly AbstractRepositoryInterface $utilisateurRepository)
    {
    }


    /**
     * Méthode qui va enregistrer l'utilisateur dans une session
     *
     * @param string $loginUtilisateur
     * @return void
     */
    public function connecter(string $loginUtilisateur): void
    {
        $session = Session::getInstance();
        $session->enregistrer($this->cleConnexion, $loginUtilisateur);
    }

    /**
     * Méthode qui va renvoyer un booléen sur la connexion de l'utilisateur.
     *
     * @return bool <code>true</code> si l'utilisateur est déjà connecté, <code>false</code> sinon
     */
    public function estConnecte(): bool
    {
        $session = Session::getInstance();
        return $session->existeCle($this->cleConnexion);
    }

    /**
     * Méthode qui va déconnecter l'utilisateur de la session
     *
     * @return void
     */
    public function deconnecter()
    {
        $session = Session::getInstance();
        $session->supprimer($this->cleConnexion);
    }

    /**
     * Méthode qui va retourner le login de l'utilisateur connecté à cette session. Si l'utilisateur n'est pas
     * connecté, il va renvoyer <code>null</code>
     *
     * @return string|null
     */
    public function getLoginUtilisateurConnecte(): ?string
    {
        $session = Session::getInstance();
        if ($session->existeCle($this->cleConnexion)) {
            return $session->lire($this->cleConnexion);
        } else
            return null;
    }

    /**
     * Méthode qui va voir si le <code>$login</code> est un utilisateur
     *
     * @param $login
     * @return bool <code>true</code> si <code>$login</code> est un utilisateur, <code>false</code> sinon.
     */
    public function estUtilisateur($login): bool
    {
        return (ConnexionUtilisateurSession::estConnecte() &&
            ConnexionUtilisateurSession::getLoginUtilisateurConnecte() == $login
        );
    }

    /**
     * Méthode qui va renvoyer un booléen sur le status d'administrateur de l'utilisateur connecté.
     *
     * @return bool <code>true</code> si l'utilisateur est un administrateur, <code>false</code> sinon.
     */
    public function estAdministrateur(): bool
    {
        $loginConnecte = ConnexionUtilisateurSession::getLoginUtilisateurConnecte();

        // Si personne n'est connecté
        if ($loginConnecte === null)
            return false;

        /** @var Utilisateur $utilisateurConnecte */
        $utilisateurConnecte = $this->utilisateurRepository->recupererParClePrimaire($loginConnecte);

        return ($utilisateurConnecte !== null && $utilisateurConnecte->getEstAdmin());
    }

    public function getAvatarUtilisateurConnecte(): string
    {
        $login = ConnexionUtilisateurSession::getLoginUtilisateurConnecte();
        if ($login === null) {
            return "user.png";
        }
        $utilisateurConnecte = $this->utilisateurRepository->recupererParClePrimaire($login);
        return isset($utilisateurConnecte) ? $utilisateurConnecte->getAvatar() : "user.png";
    }
}
