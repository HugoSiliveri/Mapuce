<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Lib\MessageFlash;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneServiceInterface;
use App\PlusCourtChemin\Service\NoeudRoutierServiceInterface;
use App\PlusCourtChemin\Service\TronconRouteServiceInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * ControleurNoeudCommune est une classe qui va gérer le parcours entre les communes de France.
 */
class ControleurNoeudCommune extends ControleurGenerique
{
    public function __construct(
        private readonly NoeudCommuneServiceInterface $noeudCommuneService,
        private readonly NoeudRoutierServiceInterface $noeudRoutierService,
        private readonly TronconRouteServiceInterface $tronconRouteService)
    {
    }

    /**
     * Méthode qui appelle <code>afficherErreur()</code> de ControleurGenerique. Elle renvoie un message d'erreur
     * pour ce controller.
     *
     * @param string $errorMessage
     * @param string $controleur
     * @return Response
     */
    public static function afficherErreur($errorMessage = "", $controleur = ""): Response
    {
        return parent::afficherErreur($errorMessage, "noeudCommune");
    }


    /**
     * Méthode qui affiche la liste des nœuds routiers.
     *
     * @return Response
     */
    public function afficherListe(): Response
    {
        $noeudsCommunes = $this->noeudCommuneService->recupererNoeudsCommune();
        return ControleurGenerique::afficherTwig("noeudCommune/listeNoeudsCommunes.twig", ["noeudsCommunes" => $noeudsCommunes]);
    }


    /**
     * Méthode qui affiche le détail d'un nœud routier
     *
     * @return Response
     */
    public function afficherDetail($gid): Response
    {
        try {
            $noeudCommune = $this->noeudCommuneService->recupererNoeudCommune($gid);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurNoeudCommune::rediriger("afficheListe");
        }

        return ControleurGenerique::afficherTwig("noeudCommune/detailNoeudCommune.twig", ["noeudCommune" => $noeudCommune]);
    }


    /**
     * Méthode qui calcule le plus court chemin entre deux communes.
     *
     * @return Response
     */
    public function plusCourtChemin(): Response
    {
        try {
            $parametres = $this->noeudCommuneService->plusCourtChemin($_POST, $this->noeudRoutierService, $this->tronconRouteService);
        } catch (ServiceException $exception) {
            MessageFlash::ajouter("danger", $exception->getMessage());
            return ControleurGenerique::rediriger("plusCourtCheminGET");
        }
        return ControleurGenerique::afficherTwig("noeudCommune/plusCourtChemin.twig", $parametres);
    }
}
