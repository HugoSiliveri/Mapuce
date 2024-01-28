<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\Utilisateur;
use App\PlusCourtChemin\Modele\HTTP\Cookie;
use App\PlusCourtChemin\Modele\Repository\AbstractRepositoryInterface;

class ConnexionUtilisateurJWT implements ConnexionUtilisateurInterface
{
    public function __construct(private readonly AbstractRepositoryInterface $utilisateurRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function connecter(string $loginUtilisateur): void
    {
        Cookie::enregistrer("auth_token", JsonWebToken::encoder(["loginUtilisateur" => $loginUtilisateur]));
    }

    /**
     * @inheritDoc
     */
    public function estConnecte(): bool
    {
        return !is_null($this->getLoginUtilisateurConnecte());
    }

    /**
     * @inheritDoc
     */
    public function deconnecter()
    {
        if (Cookie::existeCle("auth_token")) {
            Cookie::supprimer("auth_token");
        }
    }

    /**
     * @inheritDoc
     */
    public function getLoginUtilisateurConnecte(): ?string
    {
        if (Cookie::existeCle("auth_token")) {
            $jwt = Cookie::lire("auth_token");
            $donnes = JsonWebToken::decoder($jwt);
            return $donnes["loginUtilisateur"] ?? null;
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function estUtilisateur($login): bool
    {
        return (ConnexionUtilisateurJWT::estConnecte() &&
            ConnexionUtilisateurJWT::getLoginUtilisateurConnecte() == $login
        );
    }

    /**
     * @inheritDoc
     */
    public function estAdministrateur(): bool
    {
        $loginConnecte = ConnexionUtilisateurJWT::getLoginUtilisateurConnecte();

        // Si personne n'est connecté
        if ($loginConnecte === null)
            return false;

        /** @var Utilisateur $utilisateurConnecte */
        $utilisateurConnecte = $this->utilisateurRepository->recupererParClePrimaire($loginConnecte);

        return ($utilisateurConnecte !== null && $utilisateurConnecte->getEstAdmin());
    }

    public function getAvatarUtilisateurConnecte(): string
    {
        $login = ConnexionUtilisateurJWT::getLoginUtilisateurConnecte();
        if ($login === null) {
            return "user.png";
        }
        $utilisateurConnecte = $this->utilisateurRepository->recupererParClePrimaire($login);
        return isset($utilisateurConnecte) ? $utilisateurConnecte->getAvatar() : "user.png";
    }
}