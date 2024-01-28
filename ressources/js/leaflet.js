const inputCalcul = document.getElementById("calculer");
const inputDepart = document.getElementById("nomCommuneDepart_id");
const inputArrivee = document.getElementById("nomCommuneArrivee_id");
const debug = document.getElementById("debug");
const cheat = document.getElementById("cheat");
const label = document.getElementById("labelDebug");
const wrapper = document.getElementById('wrapper');
const boutonFav = document.getElementById('favoris');
const traficRoutier = document.getElementById("trafic");
const boutonswip = document.getElementById('mon-bouton');
const nbVillesSupplementaires = document.getElementById('nombreVilles');
const localisation = document.getElementById('localisation');
const baseURL = "http://localhost/plus-court-chemin-code-de-base/web";
const APIKeyTrafic = "GrDtzpYjSfRFJG4dz4Qyn7s16jhdCcTj"; //"SgSszDSLRGDRiNJ9yUsdfeo3Wfrb83Xv";
const messageFlash = document.getElementById('messagesFlash');
const modifItineraire = document.getElementById('modifItineraire');
let debugValue = false;
let modeCheat = false;
let afficherTraficRoutier = false;

let map;
let additionnal;
let trajet = null;
let dataForManyTravel = [];
let nombreRequetesAFaire = 0;
let marqueurLocalisation = null;
let coords;

/* Stockage des éléments sur la carte */
let markers = [];
let polylines = [];

/**
 * Créer la carte et centre le point de vue sur la France
 */
window.onload = () => {
    map = L.map('map').setView([46.41, 2], 6);

    map.removeControl(map.zoomControl);

    let zoom = L.control.zoom();
    zoom.addTo(map);
    zoom.setPosition('bottomleft');

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

}

/**
 * Appel dynamique au calcul du plus court chemin et met à jour l'interface
 */
inputCalcul.addEventListener("click", () => {

    let heuristique = document.getElementById("selectH").value;

    let noeudDepart = inputDepart.value;
    let noeudArrivee = inputArrivee.value;

    if (Number(nbVilles.value) === 0) {
        if (noeudDepart !== '' && noeudArrivee !== '') {
            calculTrajet(noeudDepart, noeudArrivee, heuristique);
        } else {
            messageFlash.insertAdjacentHTML("beforeend", "<div class=\"alert alert-warning\">Il faut saisir toutes les villes</div>");
            enleverMessageFlash();
        }
    } else {
        let villesInput = [];
        let nomsBien = true;
        for (let i = 0; i < Number(nbVilles.value); i++) {
            villesInput.push(document.getElementById(`nomCommune_${i}_id`));
        }
        for (let villeInput of villesInput) {
            if (villeInput.value === '') {
                nomsBien = false;
                break;
            }
        }
        if (nomsBien && noeudDepart !== '' && noeudArrivee !== '') {

            initialiseDataForManyTravel();
            dataForManyTravel["nomCommunes"].push(noeudDepart);
            verifyAndRemoveLayers();

            nombreRequetesAFaire = 1 + villesInput.length;


            calculTrajet(noeudDepart, noeudArrivee, heuristique, true);


            let villePrecedente = noeudArrivee;
            for (let villeInput of villesInput) {
                calculTrajet(villePrecedente, villeInput.value, heuristique, true);
                villePrecedente = villeInput.value;
            }
        } else {
            messageFlash.insertAdjacentHTML("beforeend", "<div class=\"alert alert-warning\">Il faut saisir toutes les villes</div>");
            enleverMessageFlash();
        }
    }

});

async function calculTrajet(noeudDepart, noeudArrivee, heuristique, plusieursTrajets = false) {
    let url = `${baseURL}/api/plusCourtChemin`;
    let currentPosition = false;

    afficheSpinChargement();

    if (noeudDepart === 'Ma position') {
        let url = `${baseURL}/api/noeudRoutier`;
        currentPosition = true;

        await fetch(url, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: `{
                "longitude": "${coords[1]}",
                "latitude": "${coords[0]}"
                }`
        }).then(response => response.json())
            .then(response => noeudDepart = JSON.parse(response))
    }

    await fetch(url, {
        method: "POST",
        headers: {
            'Content-Type': 'raw'
        },
        body: `{
            "currentPosition":"${currentPosition}",
            "heuristique":"${heuristique}",
            "nomCommuneDepart":"${noeudDepart}",
            "nomCommuneArrivee": "${noeudArrivee}",
            "modeDebug": "${debugValue}",
            "modeCheat": "${modeCheat}"
        }`
    }).then(response => response.json())
        .then(response => JSON.parse(response))
        .then(response => {
            let infosTrajet = response["trajet"];
            let debugChemin = infosTrajet["debugChemin"];

            trajet = response;

            if (plusieursTrajets) {
                nombreRequetesAFaire--;
                afficheUnTrajet(trajet);
            } else {
                afficheTrajet(trajet);
            }
            console.log(debugChemin);
            manageDebugPath(debugChemin, debug);
        });

}

function enleverMessageFlash() {
    setTimeout(() => {
        while (messageFlash.firstChild !== null) {
            messageFlash.removeChild(messageFlash.firstChild);
        }
    }, 2000)
}

function afficheSpinChargement(){
    if(!document.getElementById('load'))
         modifItineraire.insertAdjacentHTML('beforeend', '<div id="load" class="loader"></div>');
}

function retireSpinChargement(){
    if(document.getElementById('load'))
         document.getElementById('load').remove();
}

/**
 * Permet d'ajouter un trajet aux favoris de l'utilisateur
 */
boutonFav.addEventListener('click', () => {

    let url = `${baseURL}/api/ajoutTrajet`;

    if (trajet != null) {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8'
            },
            body: JSON.stringify(trajet)
        }).then(response => response.json());

        closeWindowIfVisible();
    }

});

/**
 * Ferme la fenetre des trajets utilisateurs si elle est ouverte lorsque
 * l'utilisateur clique sur le bouton d'ajout
 */
function closeWindowIfVisible() {
    if (div.classList.contains('visible')) {
        bouton.textContent = "▼";
        div.classList.remove('visible');
        div.innerHTML = "";
    }
}


/**
 * Affiche les trajets de l'utilisateur
 */
if (boutonswip !== null) {
    boutonswip.addEventListener('click', () => {

        let div = document.getElementById('ma-div');
        if (!div.classList.contains('visible')) {

            let url = `${baseURL}/api/Trajets`;
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            }).then(response => response.json())
                .then(response => {
                    let i = 0;
                    for (let route of response) {
                        let parsed = JSON.parse(route[0]);
                        div.insertAdjacentHTML('beforeend', templateTrajetFavoris(i, parsed));
                        let way = document.getElementById(`${i}`);

                        way.addEventListener("click", () => {
                            trajet = parsed;
                            afficheTrajet(parsed);
                        });

                        i++;
                    }
                    addListenerDeleteButtons();
                });
        } else {
            div.innerHTML = "";
        }
    });
}


/**
 * retourne la template de l'affichage des trajets favoris de l'utilisateur
 *
 * @param index l'indice du trajet de l'utilisateur
 * @param tabTrajet les données relatives au trajet
 * @returns HTML element
 */
function templateTrajetFavoris(index, tabTrajet) {
    return `
    <div class="containerTrajet">
        <div id="${index}" class="trajetFavoris">${tabTrajet['nomCommuneDepart']} -- ${tabTrajet['nomCommuneArrivee']}</div>
        <button class="delete-trajet color-9" data-id-trajetFav="${tabTrajet['nomCommuneDepart']}+${tabTrajet['nomCommuneArrivee']}">Supprimer</button>
    </div>
    `;
}

/**
 * Supprime le trajet favoris choisi par l'utilisateur
 *
 * @param {HTMLElement} button La balise <button> cliquée
 */
function supprimerTrajet(button) {
    let idTrajet = button.dataset.idTrajetfav;
    let url = `${baseURL}/api/Trajets/${idTrajet}`;

    fetch(url, {method: "DELETE"})
        .then(response => {
            if (response.status === 200) {
                // Plus proche ancêtre <div class="feedy">
                let divTrajet = button.closest("div.containerTrajet");
                divTrajet.remove();
            }
        });
}


/**
 * Ajoute le listener de clique à tous les boutons de suppression
 */
function addListenerDeleteButtons() {
    let buttons = document.querySelectorAll(".delete-trajet");
    for (let button of buttons) {
        button.addEventListener("click", () => {
            supprimerTrajet(button);
        });
    }
}

function initialiseDataForManyTravel() {
    dataForManyTravel["nomCommunes"] = [];
    dataForManyTravel["distance"] = 0;
    dataForManyTravel["heures"] = 0;
    dataForManyTravel["minutes"] = 0;
}

function afficheUnTrajet(data) {
    dataForManyTravel["nomCommunes"].push(data['nomCommuneArrivee']);
    dataForManyTravel["distance"] += data['trajet']['distance'];
    dataForManyTravel["heures"] += data['trajet']['temps']['heures'];
    dataForManyTravel["minutes"] += data['trajet']['temps']['minutes'];


    display_Marker(data["geomNoeuds"]);
    let troncons = data["trajet"]["troncons"].map(array2D => array2D.map(array1D => array1D.map(parseFloat).reverse()));

    display_Road(troncons, data["geomNoeudsParcourus"]);

    if (nombreRequetesAFaire === 0) {
        if (dataForManyTravel["minutes"] >= 60){
            dataForManyTravel["heures"] += Math.floor(dataForManyTravel["minutes"]/60);
            dataForManyTravel["minutes"] = dataForManyTravel["minutes"]%60;
        }
        ajouteFenetreInfosTrajet(dataForManyTravel, true);
        afficheMeteoVilles();
        retireSpinChargement();
    }
}


/**
 * Affiche toutes les informations du trajet sur la carte
 *
 * @param data les données du trajet
 */
function afficheTrajet(data) {
    verifyAndRemoveLayers();
    ajouteFenetreInfosTrajet(data);
    display_Marker(data["geomNoeuds"]);
    afficheMeteoVilles();
    let troncons = data["trajet"]["troncons"].map(array2D => array2D.map(array1D => array1D.map(parseFloat).reverse()));
    display_Road(troncons, data["geomNoeudsParcourus"]);
    retireSpinChargement();
}


/**
 * Ajoute la fenetre d'information du trajet calculé
 */
function ajouteFenetreInfosTrajet(data, manyTravels = false) {
    if (manyTravels) {
        wrapper.insertAdjacentHTML('afterend', templateTimeManyTravel(data));
    } else {
        wrapper.insertAdjacentHTML('afterend', templateTime(data));
    }
}

function templateTimeManyTravel(data) {
    let communes = "";
    for (let nomCommune of data["nomCommunes"]) {
        communes += `${nomCommune} - `;
    }
    communes = communes.substring(0, communes.length - 2);
    let firstPart = `
    <div id="resultat">
         <div id="trajet">
               <div id="trajet2">${communes}</div>
                <p id="distance">${data['distance']}km</p>
          </div>
          <div id="temps">`;

    let time;
    if (data['heures'] === 0) {
        time = data['minutes'] + " min";
    } else {
        time = data['heures'] + " h" + data['minutes'] + " min";
    }

    firstPart += time;

    firstPart += `
        </div>
            </div>
    `;

    return firstPart;
}

/**
 * Template de l'affichage de la distance, temps de trajet, villes du chemin calculé
 *
 * @returns {string}
 */
function templateTime(data) {
    let firstPart = `
    <div id="resultat">
         <div id="trajet">
               <div id="trajet2">${data['nomCommuneDepart']} - ${data['nomCommuneArrivee']}</div>
                <p id="distance">${data['trajet']['distance']}km</p>
          </div>
          <div id="temps">`;

    let time;
    if (data['trajet']['temps']['heures'] === 0) {
        time = data['trajet']['temps']['minutes'] + " min";
    } else {
        time = data['trajet']['temps']['heures'] + " h" + data['trajet']['temps']['minutes'] + " min";
    }

    firstPart += time;

    firstPart += `
        </div>
            </div>
    `;

    return firstPart;

}

/**
 * Vérifie qu'il y a des éléments sur la carte et les supprime
 */
function verifyAndRemoveLayers() {
    if (markers.length === 0 && polylines.length === 0) {
        return;
    }
    if (markers.length !== 0) {
        markers.forEach(marker => map.removeLayer(marker));
    }
    if (polylines.length !== 0) {
        polylines.forEach(polyline => map.removeLayer(polyline));
        let res = document.getElementById('resultat');
        if(res != null) res.remove();
    }

    marqueurLocalisation = null;
    markers = [];
    polylines = [];
}

debug.addEventListener("change", () => {
    if (cheat.checked){
        if (debug.checked){
            messageFlash.insertAdjacentHTML("beforeend", '<div class="alert alert-warning">Le mode debug ne fonctionne pas avec la méthode spéciale</div>');
            enleverMessageFlash();
        }
        debugValue = false;
    }else{
        debugValue = debug.checked;
    }
});

cheat.addEventListener("change", () =>{
    if (debug.checked) {
        if (cheat.checked) {
            messageFlash.insertAdjacentHTML("beforeend", '<div class="alert alert-warning">Le mode debug ne fonctionne pas avec la méthode spéciale</div>');
            enleverMessageFlash();
        }
        modeCheat = false;
    }else{
        modeCheat = cheat.checked;
    }
})

traficRoutier.addEventListener("change", ()=>{
    afficherTraficRoutier = traficRoutier.checked;
})


/**
 * Récupère les données des troncons de route
 */

/*
window.addEventListener("load", () => {
    let donnees = document.getElementById("data");
    let troncons = document.getElementById("troncons");
    let debugPath = document.getElementById("debugPath");

    if (donnees != null && troncons != null){
        display_Marker(JSON.parse(donnees.value));
        let jsonTab = JSON.parse(troncons.value);

        jsonTab = jsonTab.map(value =>
            value.map(value1 =>
                value1.map(value2 => parseFloat(value2)).reverse()
            ));
        display_Road(jsonTab);


        if(debugPath != null) {
            manageDebugPath(debugPath, debug);
        }
    }
});
 */

/**
 * Gère l'affichage des chemins supplémentaires pris par l'algorithme A*.
 *
 * @param debugPath le tableau en 3 dimensions des coordonnées
 * @param debug l'input checkbox
 */
function manageDebugPath(debugPath, debug) {
    if (debugPath != null) {
        debug.addEventListener("change", () => {
            //let roadJson = JSON.parse(debugPath.value);
            let roadJson = debugPath.map(array2D => array2D.map(array1D => array1D.map(parseFloat).reverse()));
            if (debug.checked) {
                diplay_additional_road(roadJson);
            } else {
                remove_additional_road(roadJson);
            }
        });
    }
}

/**
 * Supprime l'élement polyline de la carte
 */
function remove_additional_road() {
    map.removeLayer(additionnal);
    polylines.unshift(additionnal);
}

/**
 * Affiche le chemin supplémentaire sur la carte
 *
 * @param geom les coordonnées de toutes les lignes constituant le chemin supplémentaire pris par l'algorithme
 */
function diplay_additional_road(geom) {
    additionnal = L.polyline(geom, {color: 'red'});
    polylines.push(additionnal);
    additionnal.addTo(map);
    map.fitBounds(additionnal.getBounds());
}

/**
 * Affiche le plus court chemin sur la carte
 *
 * @param geom les coordonnées de toutes les lignes constituant le plus court chemin
 */

/*
function display_Road(geom, noeudsParcourus){
    let l = L.polyline(geom, {color: 'red'});
    polylines.push(l);
    l.addTo(map);
    map.fitBounds(l.getBounds());
}
 */

/**
 *
 * @param geom
 * @param noeudsParcourus
 */
function display_Road(geom, noeudsParcourus) {
    let i = 0;
    let l = L.polyline(geom, {color: 'red'});
    if (afficherTraficRoutier){
        for (let troncon of geom) {
            let geomNoeud = noeudsParcourus[i];
            fetch(`https://api.tomtom.com/traffic/services/4/flowSegmentData/absolute/10/json?key=${APIKeyTrafic}&point=${geomNoeud[1]},${geomNoeud[0]}`)
                .then(response => response.json())
                .then(response => {
                    let facteurTrafic = response.flowSegmentData.currentSpeed / response.flowSegmentData.freeFlowSpeed;
                    if (facteurTrafic >= 0.8) {
                        let l = L.polyline(troncon, {color: 'green'});
                        polylines.push(l);
                        l.addTo(map);
                    } else if (facteurTrafic >= 0.5) {
                        let l = L.polyline(troncon, {color: 'orange'});
                        polylines.push(l);
                        l.addTo(map);
                    } else {
                        let l = L.polyline(troncon, {color: 'red'});
                        polylines.push(l);
                        l.addTo(map);
                    }
                });
            i++;
        }

    }else{
        polylines.push(l);
        l.addTo(map);
    }
    map.fitBounds(l.getBounds());
}

/**
 * Affiche les marqueurs sur la carte
 *
 * @param geom les coordonnées des noeuds de départ et d'arrivée
 */

function display_Marker(geom) {
    let m1 = L.marker([parseFloat(geom["depart"][1]), parseFloat(geom["depart"][0])]);
    markers.push(m1);
    m1.addTo(map);
    let m2 = L.marker([parseFloat(geom["arrivee"][1]), parseFloat(geom["arrivee"][0])]);
    markers.push(m2);
    m2.addTo(map);
    // afficheMeteoVilles(geom);
}

/**
 * bind des popup aux marqueurs des villes pour afficher leurs météo respective
 *
 */
function afficheMeteoVilles() {
    for (let marqueur of markers) {
        const {lat, lng} = marqueur.getLatLng();
        let villeMeteo = fetch(`https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lng}&appid=bcd4b07af27636e64ae6a73e9534ec12&lang=fr`)
            .then(response => response.json())
            .then(req => {
                marqueur.bindPopup("Météo " + req.name + " : " + req.weather[0].description).openPopup();
            });
    }
}


/**
 * Appelle la fonction qui envoie la requete et récupère le résultat pour créer des marqueurs sur
 * la carte
 */
function display_Marker2() {
    charger_Marker(inputDepart.value, inputArrivee.value)
        .then((response) => {
            L.marker([parseFloat(response["depart"][1]), parseFloat(response["depart"][0])]).addTo(map);
            L.marker([parseFloat(response["arrivee"][1]), parseFloat(response["arrivee"][0])]).addTo(map);
        });
}

/**
 * Envoie une requete au fichier recherche.php et retourne au format JSON
 * un tableau contenant la longitude et la latitude de @param noeudDepart et @param noeudArrivee
 *
 * template de la réponse : {"depart": [longitude, latitude], "arrivee: [longitude, latitude]}
 *
 * @param noeudDepart
 * @param noeudArrivee
 * @returns {Promise<any>}
 */
async function charger_Marker(noeudDepart, noeudArrivee) {
    let URL = `../ressources/php/Recherche.php`;
    return (await fetch(URL, {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `nomCommuneDepart=${noeudDepart}&nomCommuneArrivee=${noeudArrivee}`
    })).json();
}

/**
 * Récupère la position géographique de la machine actuelle (qui a cliqué sur le bouton)
 */
function getLocation() {
    if (marqueurLocalisation != null) return;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
        inputDepart.value = 'Ma position';
    } else {
        console.log("localisation non pris en charge");
    }
}


/**
 * Affiche la position de l'utilisateur sur la carte sous la forme d'un marqueur
 *
 * @param position la position de l'utilisateur
 */
function showPosition(position) {
    marqueurLocalisation = L.marker([position.coords.latitude, position.coords.longitude]);
    markers.push(marqueurLocalisation);
    marqueurLocalisation.addTo(map);
    coords = [position.coords.latitude, position.coords.longitude];
}

localisation.addEventListener('click', getLocation);






