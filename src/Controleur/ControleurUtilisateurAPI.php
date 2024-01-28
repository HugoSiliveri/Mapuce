<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Lib\ConnexionUtilisateurInterface;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\UtilisateurServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControleurUtilisateurAPI
{
    public function __construct(
        private readonly UtilisateurServiceInterface   $utilisateurService,
        private readonly ConnexionUtilisateurInterface $connexionUtilisateur
    )
    {
    }


    /**
     * Méthode qui affiche la liste des utilisateurs.
     *
     * @return Response
     */
    public function afficherListe(): Response
    {
        try {
            $utilisateurs = $this->utilisateurService->recupererUtilisateurs();
            $tab = [];
            $i = 0;
            foreach ($utilisateurs as $utilisateur) {
                $tab[$i] = $utilisateur->jsonSerialize();
                $i++;
            }
            return new JsonResponse($tab, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function connecter(Request $request): Response
    {
        try {
            $bodyJSON = json_decode($request->getContent());
            $login = $bodyJSON->{'login'};
            $password = $bodyJSON->{'password'};
            // depuis le corps de requête au format JSON
            $loginUser = $this->utilisateurService->droitConnexion($login, $password);
            // pour connecter l'utilisateur avec son identifiant
            $this->connexionUtilisateur->connecter($loginUser);
            return new JsonResponse();
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (\JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function ajouterTrajet(Request $request): Response
    {
        try {
            $bodyJSON = json_decode($request->getContent());
            $data = $bodyJSON;
            //var_dump($bodyJSON);
            $ajoutTrajet = $this->utilisateurService->ajouterTrajetUtilisateur($request->getContent());

            return new JsonResponse($ajoutTrajet, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        } catch (\JsonException $exception) {
            return new JsonResponse(
                ["error" => "Corps de la requête mal formé"],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * @return Response
     */
    public function afficherTrajets(): Response
    {
        try {
            $trajets = $this->utilisateurService->recupererTrajetsUtilisateur();

            return new JsonResponse($trajets, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param string $idTrajet
     * @return Response
     */
    public function supprimerTrajet(string $idTrajet): Response
    {
        try {
            // $login = $this->connexionUtilisateur->getLoginUtilisateurConnecte();
            $this->utilisateurService->supprimerTrajetUtilisateur($idTrajet);
            return new JsonResponse($idTrajet, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], $exception->getCode());
        }

    }

}