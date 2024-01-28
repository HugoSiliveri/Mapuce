<?php

namespace App\PlusCourtChemin\Modele\HTTP;

/**
 * Cette classe permet de gérer les cookies du site web.
 */
class Cookie
{

    /** Vérifie si la clé du cookie en paramètre existe
     * @param $cle
     * @return bool vrai si la clé existe, faux sinon
     */
    public static function existeCle($cle): bool
    {
        return isset($_COOKIE[$cle]);
    }

    /** Enregistre un cookie
     * @param string $cle la clé du cookie
     * @param mixed $valeur la valeur du cookie
     * @param int|null $dureeExpiration la durée d'expiration du cookie, initialisée à null
     * @return void
     */
    public static function enregistrer(string $cle, mixed $valeur, ?int $dureeExpiration = null): void
    {
        $valeurJSON = serialize($valeur);
        if ($dureeExpiration === null)
            setcookie($cle, $valeurJSON, 0);
        else
            setcookie($cle, $valeurJSON, time() + $dureeExpiration);
    }

    /** Lit le cookie et le retourne
     * @param string $cle la clé du cookie
     * @return mixed le contenu obtenu lors de la lecture
     */
    public static function lire(string $cle): mixed
    {
        return unserialize($_COOKIE[$cle]);
    }

    /** Supprime le cookie
     * @param $cle
     * @return void
     */
    public static function supprimer($cle): void
    {
        unset($_COOKIE[$cle]);
        setcookie($cle, "", 1);
    }
}
