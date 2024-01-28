<?php

namespace App\PlusCourtChemin\Lib;

/**
 * MotDePasse est une classe qui va s'occuper de la sécurisation des mots de passes des utilisateurs.
 */
class MotDePasse
{

    /**
     * @var string $poivre Est un morceau de chaine qui va permettre de transformer le mot de passe
     */
    // Exécutez genererChaineAleatoire() et stockez sa sortie dans le poivre
    private static string $poivre = "";

    /**
     * Méthode qui va transformer un mot de passe identifiable en une chaine de caractères et qui va la renvoyer.
     * (mot de passe haché)
     *
     * @param string $mdpClair
     * @return string
     */
    public static function hacher(string $mdpClair): string
    {
        $mdpPoivre = hash_hmac("sha256", $mdpClair, MotDePasse::$poivre);
        $mdpHache = password_hash($mdpPoivre, PASSWORD_DEFAULT);
        return $mdpHache;
    }

    /**
     * Méthode qui va comparer un mot de passe visible avec un mot de passe haché (le mot de passe haché sera retransformé
     * revenir en un mot de passe visible)
     *
     * @param string $mdpClair
     * @param string $mdpHache
     * @return bool <code>true</code> si le mot de passe haché correspond au mot de passe visible, <code>false</code> sinon
     */
    public static function verifier(string $mdpClair, string $mdpHache): bool
    {
        $mdpPoivre = hash_hmac("sha256", $mdpClair, MotDePasse::$poivre);
        return password_verify($mdpPoivre, $mdpHache);
    }

    /**
     * Méthode qui va générer une chaine aléatoire qui sera utilisée pour le hachage
     *
     * @param int $nbCaracteres
     * @return string
     * @throws \Exception
     */
    public static function genererChaineAleatoire(int $nbCaracteres = 22): string
    {
        // 22 caractères par défaut pour avoir au moins 128 bits aléatoires
        // 1 caractère = 6 bits car 64=2^6 caractères en base_64
        // et 128 <= 22*6 = 132
        $octetsAleatoires = random_bytes(ceil($nbCaracteres * 6 / 8));
        return substr(base64_encode($octetsAleatoires), 0, $nbCaracteres);
    }
}

// Pour créer votre poivre (une seule fois)
// var_dump(MotDePasse::genererChaineAleatoire());