<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Configuration\Configuration;
use App\PlusCourtChemin\Lib\ConnexionUtilisateurInterface;
use App\PlusCourtChemin\Lib\MessageFlash;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * ControleurUtilisateur est une classe qui va gérer les différents utilisateurs du site.
 */
class ControleurUtilisateur extends ControleurGenerique
{

    public function __construct(
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateurSession)
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
        return parent::afficherErreur($errorMessage, "utilisateur");
    }

    /**
     * Méthode qui affiche la liste des utilisateurs.
     *
     * @return Response
     */
    public function afficherListe(): Response
    {
        $utilisateurs = $this->utilisateurService->recupererUtilisateurs();     //appel au modèle pour gérer la BDD
        return ControleurGenerique::afficherTwig("utilisateur/listeUtilisateurs.twig", ["utilisateurs" => $utilisateurs]);
    }


//    /**
//     * Méthode qui affiche le détail d'un utilisateur.
//     *
//     * @return Response
//     */
//    public static function afficherDetail($idUser): Response
//    {
//        try {
//            $utilisateur = (new UtilisateurService())->recupererUtilisateur($idUser);
//        }catch (ServiceException $e){
//            if (strcmp($e->getCode(), "danger") == 0){
//                MessageFlash::ajouter("danger", $e->getMessage());
//            }else{
//                MessageFlash::ajouter("warning", $e->getMessage());
//            }
//            return ControleurUtilisateur::rediriger( "afficheListeUtilisateurs");
//        }
//
//        return ControleurGenerique::afficherTwig("utilisateur/detailUtilisateur.twig", ["utilisateur" => $utilisateur]);
////        return ControleurUtilisateur::afficherVue('vueGenerale.php', [
////            "utilisateur" => $utilisateur,
////            "pagetitle" => "Détail de l'utilisateur",
////            "cheminVueBody" => "utilisateur/detail.php"
////        ]);
//    }


    /**
     * Méthode qui permet de supprimer un utilisateur
     *
     * @return Response
     */
    public function supprimer($idUser): Response
    {
        try {
            $this->utilisateurService->supprimerUtilisateur($idUser);
            self::deconnecter();
        } catch (ServiceException $e) {
            if (strcmp($e->getCode(), "danger") == 0) {
                MessageFlash::ajouter("danger", $e->getMessage());
            } else {
                MessageFlash::ajouter("warning", $e->getMessage());
            }
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été supprimé !");
        return ControleurUtilisateur::rediriger("accueil");
    }

    /**
     * Méthode qui affiche le formulaire de création d'un utilisateur.
     *
     * @return Response
     */
    public function afficherFormulaireCreation(): Response
    {
        return ControleurGenerique::afficherTwig("utilisateur/inscription.twig", ["method" => Configuration::getDebug() ? "get" : "post"]);
    }


    /**
     * Méthode qui va récupérer le formulaire créé par <code>afficherFormulaireCreation()</code> et rempli par l'utilisateur
     * pour la création du compte.
     *
     * @return Response
     */
    public function creerDepuisFormulaire(): Response
    {
        try {
            $this->utilisateurService->creerUtilisateur(
                $_REQUEST['login'],
                $_REQUEST['mdp'],
                $_REQUEST['mdp2'],
                $_REQUEST['prenom'],
                $_REQUEST['nom'],
                $_REQUEST["email"],
                $_FILES['avatar']
            );
        } catch (ServiceException $e) {
            if (strcmp($e->getCode(), "danger") == 0) {
                MessageFlash::ajouter("danger", $e->getMessage());
            } else {
                MessageFlash::ajouter("warning", $e->getMessage());
            }
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été créé !");
        return ControleurUtilisateur::rediriger("accueil");
    }


    /**
     * Méthode qui affiche un formulaire avec les données de l'utilisateur pour qu'il puisse modifier ses informations.
     *
     * @return Response
     */
    public function afficherFormulaireMiseAJour($idUser): Response
    {
        try {
            $utilisateur = $this->utilisateurService->droitsModifsUtilisateurs($idUser);
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("afficheListeUtilisateurs");
        }

        $loginHTML = htmlspecialchars($idUser);
        $prenomHTML = htmlspecialchars($utilisateur->getPrenom());
        $nomHTML = htmlspecialchars($utilisateur->getNom());
        $emailHTML = htmlspecialchars($utilisateur->getEmail());

        return ControleurGenerique::afficherTwig("utilisateur/update.twig", [
            "login" => $loginHTML,
            "prenom" => $prenomHTML,
            "nom" => $nomHTML,
            "email" => $emailHTML,
            "estAdmin2" => $utilisateur->getEstAdmin(),
            "method" => Configuration::getDebug() ? "get" : "post",
            "avatar" => $utilisateur->getAvatar()]);
    }


    /**
     * Méthode qui va mettre à jour la base de données avec les nouvelles informations données par l'utilisateur.
     *
     * @return Response
     */
    public function mettreAJour(): Response
    {
        try {

            $this->utilisateurService->modifierUtilisateur(
                $_REQUEST['login'],
                $_REQUEST['prenom'],
                $_REQUEST['nom'],
                $_REQUEST['mdp'],
                $_REQUEST['mdp2'],
                $_REQUEST['mdpAncien'],
                $_REQUEST['email'],
                !isset($_REQUEST["estAdmin"]) ? false : $_REQUEST["estAdmin"],
                $_FILES['avatar']);
        } catch (ServiceException $e) {
            if (strcmp($e->getCode(), "danger") == 0) {
                MessageFlash::ajouter("danger", $e->getMessage());
                return ControleurUtilisateur::rediriger("afficheListeUtilisateurs");
            } else {
                MessageFlash::ajouter("warning", $e->getMessage());
                return ControleurUtilisateur::rediriger("afficherFormulaireMiseAJour", ["login" => $_REQUEST["login"]]);
            }
        }
        MessageFlash::ajouter("success", "L'utilisateur a bien été modifié !");
        return ControleurUtilisateur::rediriger("accueil", ["idUser" => $_REQUEST["login"]]);
    }


    /**
     * Méthode qui va afficher la page de connexion.
     *
     * @return Response
     */
    public function afficherFormulaireConnexion(): Response
    {
        return ControleurGenerique::afficherTwig("utilisateur/connexion.twig", ["method" => Configuration::getDebug() ? "get" : "post"]);
    }


    /**
     * Méthode qui va récupérer les identifiants de connexion des utilisateurs pour les connecter au site.
     *
     * @return Response
     */
    public function connecter(): Response
    {
        try {
            $login = $this->utilisateurService->droitConnexion($_REQUEST['login'], $_REQUEST['mdp']);
            $this->connexionUtilisateurSession->connecter($login);
        } catch (ServiceException $e) {
            if (strcmp($e->getCode(), "danger") == 0) {
                MessageFlash::ajouter("danger", $e->getMessage());
            } else {
                MessageFlash::ajouter("warning", $e->getMessage());
            }
            return ControleurUtilisateur::rediriger("afficherFormulaireConnexion");
        }
        MessageFlash::ajouter("success", "Connexion effectuée.");
        return ControleurUtilisateur::rediriger("accueil", ["idUser" => $_REQUEST["login"]]);
    }


    /**
     * Méthode qui va déconnecter l'utilisateur du site.
     *
     * @return Response
     */
    public function deconnecter(): Response
    {
        try {
            $this->connexionUtilisateurSession->deconnecter();
        } catch (ServiceException $e) {
            MessageFlash::ajouter("danger", $e->getMessage());
            return ControleurUtilisateur::rediriger("afficheListeUtilisateurs");
        }

        MessageFlash::ajouter("success", "L'utilisateur a bien été déconnecté.");
        return ControleurUtilisateur::rediriger("accueil");
    }

    /**
     * Méthode va demander à l'utilisateur de valider son email lors de la création de son compte.
     *
     * @return Response
     */
    public function validerEmail(): Response
    {
        try {
            $this->utilisateurService->valideEmail($_REQUEST['login'], $_REQUEST['nonce']);
        } catch (ServiceException $e) {
            if (strcmp($e->getCode(), "danger") == 0) {
                MessageFlash::ajouter("danger", $e->getMessage());
            } else {
                MessageFlash::ajouter("warning", $e->getMessage());
            }
            return ControleurUtilisateur::rediriger("afficheListeUtilisateurs");
        }
        MessageFlash::ajouter("warning", "Validation d'email réussie");
        return ControleurUtilisateur::rediriger("afficheDetailUtilisateur", ["login" => $_REQUEST["login"]]);

    }

    public function afficherFaq(): Response
    {
        return ControleurGenerique::afficherTwig("utilisateur/faq.twig");
    }


}
