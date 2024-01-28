<?php

namespace App\PlusCourtChemin\Lib;


/**
 * ConnexionUtilisateurSession est une classe qui s'occupe de créer et gérer une instance d'un utilisateur sur le site.
 */
interface ConnexionUtilisateurInterface
{
    /**
     * Méthode qui va enregistrer l'utilisateur dans une session
     *
     * @param string $loginUtilisateur
     * @return void
     */
    public function connecter(string $loginUtilisateur): void;

    /**
     * Méthode qui va renvoyer un booléen sur la connexion de l'utilisateur.
     *
     * @return bool <code>true</code> si l'utilisateur est déjà connecté, <code>false</code> sinon
     */
    public function estConnecte(): bool;

    /**
     * Méthode qui va déconnecter l'utilisateur de la session
     *
     * @return void
     */
    public function deconnecter();

    /**
     * Méthode qui va retourner le login de l'utilisateur connecté à cette session. Si l'utilisateur n'est pas
     * connecté, il va renvoyer <code>null</code>
     *
     * @return string|null
     */
    public function getLoginUtilisateurConnecte(): ?string;

    /**
     * Méthode qui va voir si le <code>$login</code> est un utilisateur
     *
     * @param $login
     * @return bool <code>true</code> si <code>$login</code> est un utilisateur, <code>false</code> sinon.
     */
    public function estUtilisateur($login): bool;

    /**
     * Méthode qui va renvoyer un booléen sur le status d'administrateur de l'utilisateur connecté.
     *
     * @return bool <code>true</code> si l'utilisateur est un administrateur, <code>false</code> sinon.
     */
    public function estAdministrateur(): bool;

    public function getAvatarUtilisateurConnecte(): string;
}