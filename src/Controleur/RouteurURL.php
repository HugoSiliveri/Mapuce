<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Configuration\ConfigurationBDDPostgreSQL;
use App\PlusCourtChemin\Lib\ConnexionUtilisateurJWT;
use App\PlusCourtChemin\Lib\ConnexionUtilisateurSession;
use App\PlusCourtChemin\Lib\Conteneur;
use App\PlusCourtChemin\Lib\HeuristiqueStrategy;
use App\PlusCourtChemin\Lib\MessageFlash;
use App\PlusCourtChemin\Modele\Repository\ConnexionBaseDeDonnees;
use App\PlusCourtChemin\Modele\Repository\NoeudCommuneRepository;
use App\PlusCourtChemin\Modele\Repository\NoeudRoutierRepository;
use App\PlusCourtChemin\Modele\Repository\TronconRouteRepository;
use App\PlusCourtChemin\Modele\Repository\UtilisateurRepository;
use App\PlusCourtChemin\Service\NoeudCommuneService;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;
use App\PlusCourtChemin\Service\UtilisateurService;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class RouteurURL
{
    public static function traiterRequete(Request $requete): Response
    {

        $conteneur = new ContainerBuilder();

        $conteneur->register('config_bdd', ConfigurationBDDPostgreSQL::class);


        $connexionBaseService = $conteneur->register('connexion_base', ConnexionBaseDeDonnees::class);
        $connexionBaseService->setArguments([new Reference('config_bdd')]);

        $utilisateurRepository = $conteneur->register('utilisateur_repository', UtilisateurRepository::class);
        $utilisateurRepository->setArguments([new Reference('connexion_base')]);

        $noeudCommuneRepository = $conteneur->register('noeud_commune_repository', NoeudCommuneRepository::class);
        $noeudCommuneRepository->setArguments([new Reference('connexion_base')]);

        $noeudRoutierRepository = $conteneur->register('noeud_routier_repository', NoeudRoutierRepository::class);
        $noeudRoutierRepository->setArguments([new Reference('connexion_base')]);

        $tronconRouteRepository = $conteneur->register('troncon_route_repository', TronconRouteRepository::class);
        $tronconRouteRepository->setArguments([new Reference('connexion_base')]);

        $utilisateurService = $conteneur->register('utilisateur_service', UtilisateurService::class);
        $utilisateurService->setArguments([new Reference('utilisateur_repository'), new Reference('connexion_utilisateur_session')]);

        $noeudCommuneService = $conteneur->register('noeud_commune_service', NoeudCommuneService::class);
        $noeudCommuneService->setArguments([new Reference('noeud_commune_repository')]);

        $noeudRoutierService = $conteneur->register('noeud_routier_service', NoeudRoutierService::class);
        $noeudRoutierService->setArguments([new Reference('noeud_routier_repository')]);

        $tronconRouteService = $conteneur->register('troncon_route_service', TronconRouteService::class);
        $tronconRouteService->setArguments([new Reference('troncon_route_repository')]);

        $connexionUtilisateur = $conteneur->register('connexion_utilisateur_session', ConnexionUtilisateurSession::class);
        $connexionUtilisateur->setArguments([new Reference('utilisateur_repository')]);

        $connexionUtilisateurJWT = $conteneur->register('connexion_utilisateur_jwt', ConnexionUtilisateurJWT::class);
        $connexionUtilisateurJWT->setArguments([new Reference('utilisateur_repository')]);

        $controleurUtilisateur = $conteneur->register('controleur_utilisateur', ControleurUtilisateur::class);
        $controleurUtilisateur->setArguments([new Reference('utilisateur_service'), new Reference('connexion_utilisateur_session')]);

        $controleurNoeudCommune = $conteneur->register('controleur_noeud_commune', ControleurNoeudCommune::class);
        $controleurNoeudCommune->setArguments([new Reference('noeud_commune_service'), new Reference('noeud_routier_service'), new Reference('troncon_route_service')]);

        $heuristiqueStrategy = $conteneur->register('heuristique_startegy', HeuristiqueStrategy::class);
        $heuristiqueStrategy->setArguments([new Reference('noeud_routier_service')]);

        $controleurNoeudCommuneAPI = $conteneur->register('controleur_noeud_commune_api', ControleurNoeudCommuneAPI::class);
        $controleurNoeudCommuneAPI->setArguments([new Reference('noeud_commune_service'), new Reference('noeud_routier_service'), new Reference('troncon_route_service')]);

        $controleurUtilisateurAPI = $conteneur->register('controleur_utilisateur_api', ControleurUtilisateurAPI::class);
        $controleurUtilisateurAPI->setArguments([new Reference('utilisateur_service'), new Reference('connexion_utilisateur_jwt')]);


        /* Instantiation d'une collection de routes */
        $routes = new RouteCollection();

        /* Route par défaut qui affiche la liste des noeuds commune */
        $routeParDefaut = new Route("/", [
            "_controller" => ["controleur_noeud_commune", "plusCourtChemin"]
        ]);

        $routeAfficheListeNoeuds = new Route("/communes", [
            "_controller" => ["controleur_noeud_commune", "afficherListe"]
        ]);

        /* Route affichant le détail d'un noeud routier */
        $routeAfficheDetailNoeud = new Route("/communes/{gid}", [
            "_controller" => ["controleur_noeud_commune", "afficherDetail"]
        ]);
        $routeAfficheDetailNoeud->setMethods(["GET"]);

        /* Route affichant la vue du plus court chemin entre deux villes */
        $routePlusCourtChemin = new Route("/plusCourtChemin", [
            "_controller" => ["controleur_noeud_commune", "plusCourtChemin"]
        ]);
        $routePlusCourtChemin->setMethods(["GET", "POST"]);

        /* Route affichant le détail d'un utilisateur */
        $routeDetailUtilisateur = new Route("/detailUtilisateur/{idUser}", [
            "_controller" => ["controleur_utilisateur", "afficherDetail"]
        ]);
        $routeDetailUtilisateur->setMethods(["GET"]);

        /* Route affichant la liste des utilisateurs */
        $routeListeUtilisateur = new Route("/utilisateurs", [
            "_controller" => ["controleur_utilisateur", "afficherListe"]
        ]);
        $routeListeUtilisateur->setMethods(["GET"]);

        /* Route permettant la suppresion de l'utilisateur */
        $routeSupprimerUtilisateur = new Route("/supprimerUtilisateur/{idUser}", [
            "_controller" => ["controleur_utilisateur", "supprimer"]
        ]);
        $routeSupprimerUtilisateur->setMethods(["GET"]);

        /* Route affichant le formulaire de création à partir de la page d'acceuil */
        $routeInscriptionGET = new Route("/inscription", [
            "_controller" => ["controleur_utilisateur", "afficherFormulaireCreation"]
        ]);
        $routeInscriptionGET->setMethods(["GET"]);

        /* Route amenant à créer un utilisateur à partir du formulaire */
        $routeInscriptionPOST = new Route("/inscription", [
            "_controller" => ["controleur_utilisateur", "creerDepuisFormulaire"]
        ]);
        $routeInscriptionPOST->setMethods(["POST"]);

        /* Route affichant le formulaire de mise à jour d'un utilisateur */
        $routeMiseAjourUtilisateurGET = new Route("/miseAJour/{idUser}", [
            "_controller" => ["controleur_utilisateur", "afficherFormulaireMiseAJour"]
        ]);
        $routeMiseAjourUtilisateurGET->setMethods(["GET"]);

        /* Route permettant d'appeler l'action mise à jour du controleur utilisateur */
        $routeMiseAjourUtilisateurPOST = new Route("/miseAJour", [
            "_controller" => ["controleur_utilisateur", "mettreAJour"]
        ]);
        $routeMiseAjourUtilisateurPOST->setMethods(["POST"]);

        /* Route permettant d'afficher le formulaire de connexion */
        $routeConnexionGET = new Route("/connexion", [
            "_controller" => ["controleur_utilisateur", "afficherFormulaireConnexion"]
        ]);
        $routeConnexionGET->setMethods(["GET"]);

        /* Route permettant d'appeler l'action connecter du controleur utilisateur */
        $routeConnexionPOST = new Route("/connexion", [
            "_controller" => ["controleur_utilisateur", "connecter"]
        ]);
        $routeConnexionPOST->setMethods(["POST"]);

        /* Route permettant d'appeler l'action deconnecter du controleur utilisateur */
        $routeDeconnexion = new Route("/deconnexion", [
            "_controller" => ["controleur_utilisateur", "deconnecter"]
        ]);
        $routeDeconnexion->setMethods(["GET"]);


        $routeConnexionAPI = new Route("/api/connexion", [
            "_controller" => ["controleur_utilisateur_api", "connecter"]
        ]);
        $routeConnexionAPI->setMethods(["POST"]);

        $routeListeUtilisateurAPI = new Route("/api/utilisateurs", [
            "_controller" => ["controleur_utilisateur_api", "afficherListe"]
        ]);
        $routeListeUtilisateurAPI->setMethods(["GET"]);

        $routeDetailNoeudAPI = new Route("/api/communes/{gid}", [
            "_controller" => ["controleur_noeud_commune_api", "afficherDetail"]
        ]);
        $routeDetailNoeudAPI->setMethods(["GET"]);

        $routeListeCommunesAPI = new Route("/api/communes", [
            "_controller" => ["controleur_noeud_commune_api", "afficherListe"]
        ]);
        $routeListeCommunesAPI->setMethods(["GET"]);

        $routePlusCourtCheminAPI = new Route("/api/plusCourtChemin", [
            "_controller" => ["controleur_noeud_commune_api", "plusCourtChemin"]
        ]);
        $routePlusCourtCheminAPI->setMethods(["POST"]);

        $routeFAQ = new Route("/faq", [
            "_controller" => ["controleur_utilisateur", "afficherFaq"]
        ]);
        $routeFAQ->setMethods(["GET"]);

        $routeAjoutTrajetAPI = new Route("/api/ajoutTrajet", [
            "_controller" => ["controleur_utilisateur_api", "ajouterTrajet"]
        ]);
        $routeAjoutTrajetAPI->setMethods(["POST"]);

        $routeAfficherTrajetAPI = new Route("/api/Trajets", [
            "_controller" => ["controleur_utilisateur_api", "afficherTrajets"]
        ]);
        $routeAfficherTrajetAPI->setMethods(["GET"]);

        $routeSupprimerTrajet = new Route("/api/Trajets/{idTrajet}", [
            "_controller" => ["controleur_utilisateur_api", "supprimerTrajet"]
        ]);
        $routeSupprimerTrajet->setMethods(["DELETE"]);

        $routeGetGidProche = new Route("/api/noeudRoutier", [
            "_controller" => ["controleur_noeud_commune_api", "getGidFromCurrentPosition"]
        ]);
        $routeGetGidProche->setMethods(["POST"]);


        /* Ajoute les routes dans la collection et leur associe un nom */
        $routes->add("accueil", $routeParDefaut);
        $routes->add("afficheDetailsNoeud", $routeAfficheDetailNoeud);
        $routes->add("plusCourtCheminGET", $routePlusCourtChemin);
        $routes->add("afficheDetailUtilisateur", $routeDetailUtilisateur);
        $routes->add("afficheListeUtilisateurs", $routeListeUtilisateur);
        $routes->add("supprimeUtilisateur", $routeSupprimerUtilisateur);
        $routes->add("afficherFormulaireCreation", $routeInscriptionGET);
        $routes->add("inscription", $routeInscriptionPOST);
        $routes->add("afficherFormulaireMiseAJour", $routeMiseAjourUtilisateurGET);
        $routes->add("miseAJour", $routeMiseAjourUtilisateurPOST);
        $routes->add("afficherFormulaireConnexion", $routeConnexionGET);
        $routes->add("connecter", $routeConnexionPOST);
        $routes->add("deconnexion", $routeDeconnexion);
        $routes->add("plusCourtCheminPOST", $routePlusCourtChemin);
        $routes->add("afficheListeCommunes", $routeAfficheListeNoeuds);
        $routes->add("afficherFAQ", $routeFAQ);

        $routes->add("connexionAPI", $routeConnexionAPI);
        $routes->add("afficheListeUtilisateursAPI", $routeListeUtilisateurAPI);
        $routes->add("afficheDetailCommuneAPI", $routeDetailNoeudAPI);
        $routes->add("afficheListeCommunesAPI", $routeListeCommunesAPI);
        $routes->add("plusCourtChemin", $routePlusCourtCheminAPI);
        $routes->add("ajoutTrajet", $routeAjoutTrajetAPI);
        $routes->add("afficherTrajetsAPI", $routeAfficherTrajetAPI);
        $routes->add("supprimerTrajetAPI", $routeSupprimerTrajet);
        $routes->add("getGidProche", $routeGetGidProche);


        $contexteRequete = (new RequestContext())->fromRequest($requete);

        $associateurUrl = new UrlMatcher($routes, $contexteRequete);

        $assistantUrl = new UrlHelper(new RequestStack(), $contexteRequete);
        //$assistantUrl->getAbsoluteUrl("assets/css/styles.css");
        // Renvoie l'URL .../web/assets/css/styles.css, peu importe l'URL courante


        $generateurUrl = new UrlGenerator($routes, $contexteRequete);

        Conteneur::ajouterService("assistantUrl", $assistantUrl);
        Conteneur::ajouterService("generateurUrl", $generateurUrl);

        $twigLoader = new FilesystemLoader(__DIR__ . '/../vue/');
        $twig = new Environment(
            $twigLoader,
            [
                'autoescape' => 'html',
                'strict_variables' => true
            ]
        );

        $estUtilisateur = ConnexionUtilisateurSession::class;

        /* Ajout de méthodes callables pour l'appel à une route et aux assets*/
        $callable = [$generateurUrl, "generate"];
        $callable2 = [$assistantUrl, "getAbsoluteUrl"];
        $callable3 = [$estUtilisateur, "estUtilisateur"];

        /* On ajoute les fonctions correspondante à twig */
        $twig->addFunction(new TwigFunction("route", $callable));
        $twig->addFunction(new TwigFunction("asset", $callable2));
        $twig->addFunction(new TwigFunction("estUtilisateur", $callable3));
        $twig->addFunction(new TwigFunction("jsonEncode", function ($string) {
            return json_encode($string);
        }));

        /* Ajout de variables globales */
        $twig->addGlobal('messagesFlash', new MessageFlash());
        $twig->addGlobal("connexionUtilisateur", new ConnexionUtilisateurSession(new UtilisateurRepository(new ConnexionBaseDeDonnees(new ConfigurationBDDPostgreSQL()))));


        Conteneur::ajouterService("twig", $twig);

        try {
            /**
             * @throws NoConfigurationException  If no routing configuration could be found
             * @throws ResourceNotFoundException If the resource could not be found
             * @throws MethodNotAllowedException If the resource was found but the request method is not allowed
             */
            $donneesRoute = $associateurUrl->match($requete->getPathInfo());
            $requete->attributes->add($donneesRoute);

            $resolveurDeControleur = new ContainerControllerResolver($conteneur);

            /**
             * @throws \LogicException If a controller was found based on the request but it is not callable
             */
            $controleur = $resolveurDeControleur->getController($requete);

            /* Instanciation d'un résolveur d'argument, sert à récupérer l'argument placé en paramètre d'une action de controleur */
            $resolveurDArguments = new ArgumentResolver();

            /**
             * @throws \RuntimeException When no value could be provided for a required argument
             */
            $arguments = $resolveurDArguments->getArguments($requete, $controleur);

            /* Appelle le callback avec ses arguments */
            $reponse = call_user_func_array($controleur, $arguments);

        } catch (ResourceNotFoundException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 404);
        } catch (MethodNotAllowedException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage(), 405);
        } catch (BadRequestException $exception) {
            $reponse = ControleurGenerique::afficherErreur($exception->getMessage());
        }
        return $reponse;
    }
}