<?php

namespace App\PlusCourtChemin\Modele\HTTP;

use App\PlusCourtChemin\Configuration\Configuration;
use Exception;

/**
 * Cette classe permet de gérer les sessions du site web.
 */
class Session
{
    private static ?Session $instance = null;

    /** Constructeur de Session. Démarre une session
     * @throws Exception lorsque la session a échoué à démarrer
     */
    private function __construct()
    {
        if (session_start() === false) {
            throw new Exception("La session n'a pas réussi à démarrer.");
        }
    }

    /** Calcule la durée entre la dernière activité de l'utilsateur sur le site et le temps actuel.
     * Si celle-ci dépasse la durée d'expiration de la session, la méthode supprime celle-ci.
     * @param int $dureeExpiration
     * @return void
     */
    public function verifierDerniereActivite(int $dureeExpiration)
    {
        if ($dureeExpiration == 0)
            return;

        if (isset($_SESSION['derniereActivite']) && ((time() - $_SESSION['derniereActivite']) > $dureeExpiration)) {
            $delai = time() - $_SESSION['derniereActivite'];
            session_unset();     // unset $_SESSION variable for the run-time
            // MessageFlash::ajouter("info", "Déconnexion : inactif depuis $delai sec au lieu $dureeExpiration");
        }

        if (isset($_SESSION['derniereActivite'])) {
            $delai = (time() - $_SESSION['derniereActivite']);
            // MessageFlash::ajouter("info", "Dernière activité il y a $delai sec.");
        }

        $_SESSION['derniereActivite'] = time(); // update last activity time stamp
    }

    /** Crée une instance de la classe Session et la retourne. Si une instance est déjà créée, la méthode la retourne
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (is_null(Session::$instance)) {
            Session::$instance = new Session();
            $dureeExpiration = Configuration::getDureeExpirationSession();
            Session::$instance->verifierDerniereActivite($dureeExpiration);
        }
        return Session::$instance;
    }

    /** Vérifie si la clé de la session en paramètre existe
     * @param $cle
     * @return bool vrai si la clé existe, faux sinon
     */
    public function existeCle($cle): bool
    {
        return isset($_SESSION[$cle]);
    }

    /** Enregistre une session.
     * @param string $name le nom de la session
     * @param mixed $value la valeur de la session
     * @return void
     */
    public function enregistrer(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    /** Lit la session et la retourne.
     * @param string $name le nom de la session
     * @return mixed le contenu obtenu lors de la lecture
     */
    public function lire(string $name): mixed
    {
        return $_SESSION[$name];
    }

    /** Supprime la session
     * @param $cle
     * @return void
     */
    public function supprimer($name): void
    {
        unset($_SESSION[$name]);
    }

    /** Détruit la session.
     * À la différence de la méthode supprimer, cette méthode supprime aussi les données de la session du disque dur du serveur.
     * @return void
     */
    public function detruire(): void
    {
        session_unset();     // unset $_SESSION variable for the run-time
        session_destroy();   // destroy session data in storage
        Cookie::supprimer(session_name()); // deletes the session cookie
        // Il faudra reconstruire la session au prochain appel de getInstance()
        $instance = null;
    }
}
