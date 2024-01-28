import {applyAndRegister, reactive, startReactiveDom} from "./reactive.js";

let autocompDepart = reactive({
    suggestions: [],
    suggestions_str: "",

    videVilles: function () {
        this.suggestions = [];
    },
    afficheVilles: function () {
        this.suggestions_str = ``;
        for (let ville of this.suggestions) {
            this.suggestions_str += `<p>${ville}</p>`;
        }
        this.videVilles();
        return this.suggestions_str;
    },
    callbackVille: function (req) {
        let tabVilles = JSON.parse(req.responseText);
        for (let ville of tabVilles) {
            this.suggestions.push(ville.nom_comm);
        }
        this.afficheVilles();
    },
    requeteAJAX: function (stringVille) {
        let ville = encodeURI(stringVille);
        let url = `../ressources/php/requeteVilleDepart.php?nomCommuneDepart=${ville}`;
        let requete = new XMLHttpRequest();
        requete.open("GET", url, true);
        requete.addEventListener("load", function () {
            autocompDepart.callbackVille(requete);
        });
        requete.send(null);
    }
}, "autocompDepart");


applyAndRegister(() => {
    autocompDepart.afficheVilles();
});


let autocompArrivee = reactive({
    suggestions: [],
    suggestions_str: "",

    videVilles: function () {
        this.suggestions = [];
    },
    afficheVilles: function () {
        this.suggestions_str = ``;
        for (let ville of this.suggestions) {
            this.suggestions_str += `<p>${ville}</p>`;
        }
        this.videVilles();
        return this.suggestions_str;
    },
    callbackVille: function (req) {
        let tabVilles = JSON.parse(req.responseText);
        for (let ville of tabVilles) {
            this.suggestions.push(ville.nom_comm);
        }
        this.afficheVilles();
    },
    requeteAJAX: function (stringVille) {
        let ville = encodeURI(stringVille);
        let url = `../ressources/php/requeteVilleDepart.php?nomCommuneDepart=${ville}`;
        let requete = new XMLHttpRequest();
        requete.open("GET", url, true);
        requete.addEventListener("load", function () {
            autocompArrivee.callbackVille(requete);
        });
        requete.send(null);
    }
}, "autocompArrivee");


applyAndRegister(() => {
    autocompArrivee.afficheVilles();
});

startReactiveDom();
