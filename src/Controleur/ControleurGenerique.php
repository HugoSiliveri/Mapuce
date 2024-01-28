<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Lib\Conteneur;
use App\PlusCourtChemin\Lib\MessageFlash;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * ControleurGenerique est une classe qui regroupe les méthodes communes aux différents controllers. Toutes les méthodes
 * de cette classe sont statiques et accessibles uniquement par les autres controllers.
 */
class ControleurGenerique
{

    /**
     * Affiche la vue grâce au chemin avec des paramètres supplémentaires et optionnels.
     *
     * @param string $cheminVue Chemin de la vue qui sera affichée.
     * @param array $parametres Liste des paramètres qui seront envoyés dans la vue
     * @return Response
     */
    protected static function afficherVue(string $cheminVue, array $parametres = []): Response
    {
        extract($parametres);
        $messagesFlash = MessageFlash::lireTousMessages();
        ob_start();
        require __DIR__ . "/../vue/$cheminVue";
        $corpsReponse = ob_get_clean();
        return new Response($corpsReponse);
    }

    /**
     * Méthode qui permet de rediriger l'utilisateur vers une différente page
     *
     * @param string $route
     * @param array $option
     * @return RedirectResponse
     */
    protected static function rediriger(string $route = "", array $option = []): Response
    {
        $generateurUrl = Conteneur::recupererService("generateurUrl");
        $url = $generateurUrl->generate($route, $option);
        $urlFinal = "Location: " . $url;
        header($urlFinal);
        return new RedirectResponse($url);
    }

    /**
     * Méthode qui affiche un message d'erreur pour un controller et qui affiche la vue erreur.php
     *
     * @param $errorMessage
     * @param $statusCode
     * @return Response
     */
    public static function afficherErreur($errorMessage = "", $statusCode = 400): Response
    {
//        $reponse = ControleurGenerique::afficherVue('vueGenerale.php', [
//            "pagetitle" => "Problème",
//            "cheminVueBody" => "erreur.php",
//            "errorMessage" => $errorMessage
//        ]);
//
//        $reponse->setStatusCode($statusCode);
        return ControleurGenerique::afficherTwig("erreur.twig", ["errorMessage" => $errorMessage]);
    }

    protected static function afficherTwig(string $cheminVue, array $parametres = []): Response
    {
        /** @var Environment $twig */
        $twig = Conteneur::recupererService("twig");
        return new Response($twig->render($cheminVue, $parametres));
    }

}