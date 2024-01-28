<?php

namespace App\PlusCourtChemin\Lib;

use App\PlusCourtChemin\Modele\DataObject\NoeudRoutier;
use App\PlusCourtChemin\Service\Exception\ServiceException;
use App\PlusCourtChemin\Service\NoeudRoutierService;
use App\PlusCourtChemin\Service\TronconRouteService;

/**
 * PlusCourtChemin est une classe qui va calculer la plus petite distance à réaliser entre deux nœuds
 */
class PlusCourtChemin
{
    private HeuristiqueStrategy $heuristiqueStrategy;

    /**
     * @var array $distances Est un tableau qui regroupe les distances en km entre les deux nœuds
     */
    protected array $distances;

    /**
     * @var array $troncons Est un tableau qui va regrouper toutes les informations des troncons par lequels on passe pour le chemin
     */
    protected array $troncons;

    protected array $geomNoeudsParcourus;

    /**
     * @var array $tronconSupplementaires Est un tableau qui stocke tous les chemins pris par l'algorithme A*
     */
    protected array $tronconSupplementaires;

    /**
     * @var array $infosTroncons
     */
    protected array $infosTroncons;

    /**
     * @var array $vitesseMoyenne
     */
    protected array $vitesseMoyenne = [118, 97, 78, 50];

    /**
     * @var \SplPriorityQueue $noeudsALaFrontiere Est un tableau qui regroupe les nœuds qui sont proches
     */
    protected \SplPriorityQueue $noeudsALaFrontiere;

    protected TronconRouteService $tronconRouteService;

    protected NoeudRoutierService $noeudRoutierService;


    /**
     * Constructeur de la classe
     *
     * @param int $noeudRoutierDepartGid
     * @param int $noeudRoutierArriveeGid
     */
    public function __construct(
        private int $noeudRoutierDepartGid,
        private int $noeudRoutierArriveeGid
    )
    {
    }

    public function setHeuristiqueStrategy($heuristiqueStrategy1): void
    {
        $this->heuristiqueStrategy = $heuristiqueStrategy1;
    }

    /**
     * @throws ServiceException
     */
    public function calculer($tronconRouteService, $noeudRoutierService, bool $modeDebug = false, bool $affichageDebug = false): array
    {
        // $this->vitesseMoyenne = [118, 97, 78, 50];


        /* File de priorité contenant les noeuds routiers qu'il reste à analyser, et initialisé avec le noeud de départ */
        $noeudsAVisiter = new \SplPriorityQueue();
        $this->tronconRouteService = $tronconRouteService;
        $this->noeudRoutierService = $noeudRoutierService;
        $noeudsAVisiter->insert($this->noeudRoutierDepartGid, 0);

        $coordoneesArrivee = $noeudRoutierService->getLongitudeLatitude($this->noeudRoutierArriveeGid);

        /* Tableau contenant les noeuds que l'on a déjà visités */
        $noeudsVisites = array();
        $this->troncons = array();
        $this->tronconSupplementaires = array();

        /* Tableau contenant l'association entre un noeud et sa distance depuis le noeud de départ */
        $this->coutDistance = [$this->noeudRoutierDepartGid => 0];

        /* Tableau contenant l'association entre un noeud et son score heuristique avec ce noeud et le noeud d'arrivé (estimation de la distance restante) */
        $this->coutTotal = [$this->noeudRoutierDepartGid => $this->heuristiqueStrategy->heuristique($this->noeudRoutierDepartGid, $this->noeudRoutierArriveeGid)];


        // Tableau contenant les noeuds précédents pour chaque noeud visité
        $predecesseurs = [$this->noeudRoutierDepartGid => null];


        $i = 0;
        /* Tant qu'il y a encore des noeuds à voir */
        while (!empty($noeudsAVisiter)) {

            /* Récupère et retire le premier dans la file */
            $noeudCourant = $noeudsAVisiter->extract();

            /* On enlève aussi la valeur du tableau permettant de vérifier les noeuds à visiter plus tard */
            //$cle = array_search($noeudCourant, $noeudsAVisiterTemp, true);
            //unset($noeudsAVisiterTemp[$cle]);

            /* si on a trouvé un plus court chemin */
            if ($noeudCourant === $this->noeudRoutierArriveeGid) {
                $array = array();
                $array[] = $this->coutDistance[$noeudCourant];

                // Récupère le noeud d'arrivée pour lequel on veut remonter le chemin
                $noeudActuel = $this->noeudRoutierArriveeGid;


                while (isset($predecesseurs[$noeudActuel])) {
                    // Récupère le troncon_route reliant le noeud précédent au noeud courant, s'il existe

                    $noeudPrecedent = $this->noeudRoutierService->recupererParClePrimaire($predecesseurs[$noeudActuel]);
                    //$voisins = $noeudPrecedent->getVoisins();

                    $troncon_gid = $this->tronconRouteService->recupererClesPrimaireParNoeuds($predecesseurs[$noeudActuel], $noeudActuel);


                    $geomNoeudActuel = $this->noeudRoutierService->getGeom($noeudActuel);

                    $this->geomNoeudsParcourus[] = $this->noeudRoutierService->getLongitudeLatitude($geomNoeudActuel);

                    $troncon = $this->tronconRouteService->recupererGeomParClesPrimaire($troncon_gid);

                    $this->infosTroncons[] = $this->tronconRouteService->recupererInfosParClesPrimaire($troncon_gid);

                    $coordonnes = $this->getLongitudeLatitudeTroncon($troncon);

                    $this->troncons[] = $coordonnes;
                    // $key = array_search($coordonnes, $this->tronconSupplementaires, true);
                    // unset($this->tronconSupplementaires[$key]);

                    $noeudActuel = $noeudPrecedent->getGid();
                }

                $array[] = $this->troncons;

                $array[] = $this->calculerTempsRoute();

                $array[] = $this->tronconSupplementaires;

                $array[] = $this->geomNoeudsParcourus;

                return $array;
            }

            /** @var NoeudRoutier $noeudRoutierCourant */
            $noeudRoutierCourant = $noeudRoutierService->recupererParClePrimaire($noeudCourant);
            $voisins = $noeudRoutierCourant->getVoisins();


            /* On ajoute le noeud courant comme visité, comme ça, il ne sera pas ré-évalué */
            $noeudsVisites[$noeudCourant] = $i;

            foreach ($voisins as $voisin) {

                $voisinGID = $voisin["noeud_routier_gid"];

                /* si on a déjà vu le noeud voisin, on l'ignore */
                if (!isset($noeudsVisites[$voisinGID])) {

                    /* On récupère la distance entre le noeud courant et le voisin actuellement dans la boucle */
                    $distanceTroncon = $voisin["longueur"];

                    /* On calcul la nouvelle distance */
                    $distanceProposee = $this->coutDistance[$noeudCourant] + $distanceTroncon;

                    /* Si il n'y a pas encore de distance entre le noeud voisin et le noeud de départ
                        ou que la distance calculé à partir de ce voisin est plus courte
                     */
                    if (!isset($this->coutDistance[$voisinGID]) || $distanceProposee < $this->coutDistance[$voisinGID]) {
                        /* On met à jour les distances et couts totaux */
                        $this->coutDistance[$voisinGID] = $distanceProposee;
                        $this->coutTotal[$voisinGID] = $this->coutDistance[$voisinGID] + $this->heuristiqueStrategy->heuristique($voisinGID, $this->noeudRoutierArriveeGid);
                        $predecesseurs[$voisinGID] = $noeudCourant;


                        if ($modeDebug) $this->tronconSupplementaires[] = $this->getLongitudeLatitudeTroncon($this->tronconRouteService->recupererGeomParClesPrimaire($voisin["troncon_gid"]));


                        // rajouter cette ligne afin de voir tous les chemins emprunté par l'algoriothme, peut être super utile pour comparer 2 heuristiques


                        /* On s'assure que le noeud voisin avec le plus faible cout sera visité */
                        $noeudsAVisiter->insert($voisinGID, -$this->coutTotal[$voisinGID]);
                    }
                }
            }
            $i++;
        }
        /* Le plus court chemin n'a pas été trouvé */
        return [-1.0];
    }

    /**
     * @throws ServiceException
     */
    public function calculerV2($tronconRouteService, $noeudRoutierService): array{
        $this->tronconSupplementaires = array();
        $this->tronconRouteService = $tronconRouteService;
        $this->noeudRoutierService = $noeudRoutierService;
        // Fonction qui sert à calculer le plus court chemin entre deux noeud routiers en utilisant la fonction astar de postgis

        /* Tableau qui va stocker : le chemin emprunter par l'algorithme atsra de postgres ainsi que le cout de chaque chemin en autre les attributs : seq / path_seq / edge / agg_cost */
        $resultatAStart = $this->tronconRouteService->calculerTrajet($this->noeudRoutierDepartGid, $this->noeudRoutierArriveeGid);

        $tabIdTronconRoute = array();
        $tabIdNoeudsRoutier = array();


        /* On récupère la dernière case du tableau de distance que nous donne la requete donc du cout total du trajet */

        $distanceFinaleTrajet = 0;

        foreach ($resultatAStart as $sequence){
            if ($sequence["edge"] != -1){
                if (!in_array($sequence["edge"], $tabIdTronconRoute)){
                    $distanceFinaleTrajet += $sequence["cost"];
                    $tabIdNoeudsRoutier[] = $sequence["node"];
                    $tabIdTronconRoute[] = $sequence["edge"];
                }
            }
        }

        foreach ($tabIdTronconRoute as $troncon_gid){
            $troncon = $this->tronconRouteService-> recupererGeomParClesPrimaire($troncon_gid);

            $this->infosTroncons[] = $this->tronconRouteService->recupererInfosParClesPrimaire($troncon_gid);

            $coordonnes = $this->getLongitudeLatitudeTroncon($troncon);

            $this->troncons[] = $coordonnes;
        }

        foreach ($tabIdNoeudsRoutier as $noeud_gid){
            $geomNoeud = $this->noeudRoutierService->getGeom($noeud_gid);

            $this->geomNoeudsParcourus[] = $this->noeudRoutierService->getLongitudeLatitude($geomNoeud);
        }

        $array = array();

        $array[] = $distanceFinaleTrajet;

        $array[] = $this->troncons;

        $array[] = $this->calculerTempsRoute();

        $array[] = $this->tronconSupplementaires;

        $array[] = $this->geomNoeudsParcourus;

        return $array;
    }



    protected function getLongitudeLatitudeTroncon(string $geom): array
    {
        $sansPoint = substr($geom, 11);

        $sansDerniereP = substr($sansPoint, 0, strlen($sansPoint) - 1);

        $tabCoordonnes = explode(",", $sansDerniereP);


        $tab = array();
        foreach ($tabCoordonnes as $coordonne) {
            $tab[] = explode(" ", $coordonne);
        }

        return $tab;
    }

    protected function calculerTempsRoute(): array
    {
        $tempTotal = 0;
        foreach ($this->infosTroncons as $troncon) {
            $longueur = floatval($troncon["longueur"]);
            $tempTotal += match ($troncon["typeroute"]) {
                TypeRoute::AUTOROUTE->value => $longueur / $this->vitesseMoyenne[0],
                TypeRoute::NATIONALE->value => $longueur / $this->vitesseMoyenne[1],
                TypeRoute::DEPARTEMENTALE->value => $longueur / $this->vitesseMoyenne[2],
                TypeRoute::NULl->value => $longueur / $this->vitesseMoyenne[3]
            };
        }
        $heure = floor($tempTotal);
        $partieDecimale = $tempTotal - $heure;
        $minutes = floor($partieDecimale * 60);
        return [$heure, $minutes];
    }


    /**
     * Méthode qui calcule le plus court chemin en suivant l'algorithme de Dijkstra
     *
     * @param bool $affichageDebug
     * @return float
     */
    /*
    public function calculer(bool $affichageDebug = false): float
    {
        $noeudRoutierRepository = new NoeudRoutierRepository();

        // Distance en km, table indexé par NoeudRoutier::gid
        $this->distances = [$this->noeudRoutierDepartGid => 0];

        $this->noeudsALaFrontiere = new \SplPriorityQueue();
        $this->noeudsALaFrontiere->insert($this->noeudRoutierDepartGid,0);

        while (!$this->noeudsALaFrontiere->isEmpty()) {
            // Enleve le nœud routier courant de la frontière
            $noeudRoutierGidCourant = $this->noeudsALaFrontiere->extract();
            //$this->noeudsVisites[$noeudRoutierGidCourant] = true;

            // Fini
            if ($noeudRoutierGidCourant === $this->noeudRoutierArriveeGid) {
                return $this->distances[$noeudRoutierGidCourant];
            }

            /** @var NoeudRoutier $noeudRoutierCourant
            $noeudRoutierCourant = $noeudRoutierRepository->recupererParClePrimaire($noeudRoutierGidCourant);
            $voisins = $noeudRoutierCourant->getVoisins();

            foreach ($voisins as $voisin) {
                $noeudVoisinGid = $voisin["noeud_routier_gid"];
                $distanceTroncon = $voisin["longueur"];
                $distanceProposee = $this->distances[$noeudRoutierGidCourant] + $distanceTroncon;

                //if(!isset($this->noeudsVisites[$noeudVoisinGid])) {
                    if (!isset($this->distances[$noeudVoisinGid]) || $distanceProposee < $this->distances[$noeudVoisinGid]) {
                        $this->distances[$noeudVoisinGid] = $distanceProposee;
                        $this->noeudsALaFrontiere->insert($noeudVoisinGid, -$distanceProposee);
                    }
              //  }
            }
        }
    }
    */


}
