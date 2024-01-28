<?php

namespace App\PlusCourtChemin\Modele\Repository;

use App\PlusCourtChemin\Modele\DataObject\Utilisateur;

/**
 * Cette classe contient toutes les méthodes gérant la persistance des données des utilisateurs.
 */
class UtilisateurRepository extends AbstractRepository
{

    /**
     * @return Utilisateur[]
     */
    public function getUtilisateurs(): array
    {
        $pdoStatement = parent::getConnexionBaseDeDonnees()->getPdo()->query("SELECT * FROM utilisateur");

        $utilisateurs = [];
        foreach ($pdoStatement as $utilisateurFormatTableau) {
            $utilisateurs[] = (new UtilisateurRepository(parent::getConnexionBaseDeDonnees()))->construireDepuisTableau($utilisateurFormatTableau);
        }

        return $utilisateurs;
    }

    /** Construit un objet Utilisateur à partir d'un tableau donné en paramètre.
     * @param array $utilisateurTableau
     * @return Utilisateur
     */
    public function construireDepuisTableau(array $utilisateurTableau): Utilisateur
    {
        return new Utilisateur(
            $utilisateurTableau["login"],
            $utilisateurTableau["nom"],
            $utilisateurTableau["prenom"],
            $utilisateurTableau["mdp"],
            $utilisateurTableau["est_admin"],
            $utilisateurTableau["email"],
            $utilisateurTableau["email_a_valider"],
            $utilisateurTableau["nonce"],
            $utilisateurTableau["avatar"]
        );
    }

    /** Retourne le nom de la table contenant les données des utilisateurs.
     * @return string
     */
    public function getNomTable(): string
    {
        return 'utilisateur';
    }

    /** Retourne la clé primaire de la table utilisateur.
     * @return string
     */
    protected function getNomClePrimaire(): string
    {
        return 'login';
    }

    /** Retourne le nom de tous les attributs de la table utilisateur.
     * @return string[] le tableau contenant tous les noms des attributs
     */
    protected function getNomsColonnes(): array
    {
        return ["login", "nom", "prenom", "mdp", "est_admin", "email", "email_a_valider", "nonce", "avatar"];
    }
}