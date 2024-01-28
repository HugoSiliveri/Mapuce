let autocompVilleDepart = document.getElementById("autocompletionVilleDepart");
let autocompVilleArrivee = document.getElementById("autocompletionVilleArrivee");
//autocompVilleDepart.style.borderWidth = "0px";
let inputVilleDepart = document.getElementById("nomCommuneDepart_id");
let inputVilleArrivee = document.getElementById("nomCommuneArrivee_id");

if (inputVilleDepart !== null) {
    inputVilleDepart.addEventListener("input", function () {
        if (inputVilleDepart.value.length >= 2) {
            requeteAJAXDepart(inputVilleDepart.value);
        }
    });
}

if (autocompVilleDepart !== null) {
    autocompVilleDepart.addEventListener("click", function (event) {
        inputVilleDepart.value = event.target.textContent;
        videVilles();
    });
}

if (inputVilleArrivee !== null) {
    inputVilleArrivee.addEventListener("input", function () {
        if (inputVilleArrivee.value.length >= 2) {
            requeteAJAXArrivee(inputVilleArrivee.value);
        }
    });
}

if (autocompVilleArrivee !== null) {
    autocompVilleArrivee.addEventListener("click", function (event) {
        inputVilleArrivee.value = event.target.textContent;
        videVilles();
    });
}


function afficheVillesDepart(tableau) {
    videVilles();
    for (let ville of tableau) {
        autocompVilleDepart.insertAdjacentHTML("beforeend", `<p>${ville}</p>`);
    }
}

function afficheVillesArrivee(tableau) {
    videVilles();
    for (let ville of tableau) {
        autocompVilleArrivee.insertAdjacentHTML("beforeend", `<p>${ville}</p>`);
    }
}

function videVilles() {
    autocompVilleDepart.innerHTML = "";
    autocompVilleArrivee.innerHTML = "";
}


function requeteAJAXDepart(stringVille) {
    let ville = encodeURI(stringVille);
    let url = `../ressources/php/requeteVilleDepart.php?nomCommuneDepart=${ville}`;
    let requete = new XMLHttpRequest();
    requete.open("GET", url, true);
    requete.addEventListener("load", function () {
        callbackDepart(requete);
    });
    requete.send(null);
}

function requeteAJAXArrivee(stringVille) {
    let ville = encodeURI(stringVille);
    let url = `../ressources/php/requeteVilleArrivee.php?nomCommuneArrivee=${ville}`;
    let requete = new XMLHttpRequest();
    requete.open("GET", url, true);
    requete.addEventListener("load", function () {
        callbackArrivee(requete);
    });
    requete.send(null);
}

function callbackDepart(req) {
    let tabVilles = JSON.parse(req.responseText);
    let tabNoms = [];
    for (let ville of tabVilles) {
        tabNoms.push(ville.nom_comm);
    }
    afficheVillesDepart(tabNoms);
}

function callbackArrivee(req) {
    let tabVilles = JSON.parse(req.responseText);
    let tabNoms = [];
    for (let ville of tabVilles) {
        tabNoms.push(ville.nom_comm);
    }
    afficheVillesArrivee(tabNoms);
}