<?php

namespace App\PlusCourtChemin\Controleur;

use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudCommuneServiceInterface;
use App\PlusCourtChemin\Service\NoeudRoutierServiceInterface;
use App\PlusCourtChemin\Service\TronconRouteServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControleurNoeudCommuneAPI
{
    public function __construct(
        private readonly NoeudCommuneServiceInterface $noeudCommuneService,
        private readonly NoeudRoutierServiceInterface $noeudRoutierService,
        private readonly TronconRouteServiceInterface $tronconRouteService)
    {
    }

    public function afficherListe(): Response
    {
        try {
            $noeudsCommunes = $this->noeudCommuneService->recupererNoeudsCommune();
            $tab = [];
            $i = 0;
            foreach ($noeudsCommunes as $noeud) {
                $tab[$i] = $noeud->jsonSerialize();
                $i++;
            }
            return new JsonResponse($tab, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function afficherDetail($gid): Response
    {
        try {
            $noeudCommune = $this->noeudCommuneService->recupererNoeudCommune($gid);
            return new JsonResponse($noeudCommune->jsonSerialize(), Response::HTTP_OK);
        } catch (ServiceException $exception) {
            ;
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function plusCourtChemin(Request $request): Response
    {
        try {
            $bodyJSON = json_decode($request->getContent());
            $elements = [
                "currentPosition" => $bodyJSON->{'currentPosition'},
                "nomCommuneDepart" => $bodyJSON->{'nomCommuneDepart'},
                "nomCommuneArrivee" => $bodyJSON->{'nomCommuneArrivee'},
                "heuristique" => $bodyJSON->{'heuristique'},
                "modeDebug" => $bodyJSON->{'modeDebug'},
                "modeCheat" => $bodyJSON->{'modeCheat'}
            ];
            $parametres = $this->noeudCommuneService->plusCourtChemin($elements, $this->noeudRoutierService, $this->tronconRouteService);

            $geomNoeudDepart = $this->noeudRoutierService->getGeom($parametres['gidNoeudDepart']);
            $geomNoeudArrivee = $this->noeudRoutierService->getGeom($parametres['gidNoeudArrivee']);

            $tabGeomDepart = $this->noeudRoutierService->getLongitudeLatitude($geomNoeudDepart);
            $tabGeomArrivee = $this->noeudRoutierService->getLongitudeLatitude($geomNoeudArrivee);

            $result = [
                "nomCommuneDepart" => $parametres['nomCommuneDepart'],
                "nomCommuneArrivee" => $parametres['nomCommuneArrivee'],
                "heuristique" => $elements['heuristique'],
                "geomNoeudsParcourus" => $parametres['geomNoeudsParcourus'],
                "geomNoeuds" => [
                    "depart" => $tabGeomDepart,
                    "arrivee" => $tabGeomArrivee
                ],
                "trajet" => [
                    "distance" => $parametres["distance"],
                    "temps" => [
                        "heures" => $parametres["heures"],
                        "minutes" => $parametres["minutes"]
                    ],
                    "troncons" => $parametres["troncons"],
                    "debugChemin" => $parametres["debugChemin"]
                ]
            ];
            return new JsonResponse(json_encode($result), Response::HTTP_OK);
        } catch (ServiceException $exception) {
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    public function getGidFromCurrentPosition(Request $request): Response
    {
        $bodyJson = json_decode($request->getContent());
        $longitude = $bodyJson->{'longitude'};
        $latitude = $bodyJson->{'latitude'};
        try {
            $gid = $this->noeudRoutierService->getGidCurrentPosition($longitude, $latitude);
            return new JsonResponse($gid, Response::HTTP_OK);
        } catch (ServiceException $exception) {
            ;
            return new JsonResponse(["error" => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }
}