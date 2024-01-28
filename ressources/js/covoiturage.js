let covoiturage = document.getElementById("covoiturage");

function getData(url) {
    return new Promise(function (resolve, reject) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                // la requête est terminée avec succès
                const data = JSON.parse(xhr.responseText);
                resolve(data);  // la promesse est résolue
            } else {
                // erreur pendant la requête
                reject(`${xhr.status}: ${xhr.responseText}`);   // promesse rejetée
            }
        }
        xhr.send();
    });
}

function getData2(url, query) {
    return new Promise(function (resolve, reject) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () { //Appelle une fonction au changement d'état.
            if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                const data = JSON.parse(xhr.responseText);
                resolve(data);
            } else {
                reject(`${xhr.status}: ${xhr.responseText}`);
            }
        }
        xhr.send(query);
    });
}

function requeteCovoiturage3() {
    let url = "https://public.opendatasoft.com/api/records/1.0/search/";
    let query = "dataset=aires-covoiturage&q=&facet=ville&facet=type_de_parking&facet=source&facet=pmr&facet=transport_public&facet=prix&facet=ouverture&facet=lumiere&facet=velo&facet=couv4gbytel&facet=couv4gsfr&facet=couv4gorange&facet=couv4gfree&facet=nom_epci&facet=nom_reg&facet=nom_dep";
    getData2(url, query)
        .then(data => {
            for (let record of data.records) {
                getData(record.fields.ville).then(data => {
                    //covoiturage.insertAdjacentHTML("beforeend",`<p>${data}</p>`)
                    console.log(data);
                });
            }
        });
}

function requeteCovoiturage() {
    getData("https://public.opendatasoft.com/api/records/1.0/search/?dataset=aires-covoiturage&q=&facet=ville&facet=type_de_parking&facet=source&facet=pmr&facet=transport_public&facet=prix&facet=ouverture&facet=lumiere&facet=velo&facet=couv4gbytel&facet=couv4gsfr&facet=couv4gorange&facet=couv4gfree&facet=nom_epci&facet=nom_reg&facet=nom_dep")
        .then(data => {
            console.log(data);
            console.log(data.records);
            for (let record of data.records) {
                console.log(record.fields.ville);
                //covoiturage.insertAdjacentHTML("beforeend",`<p>${data}</p>`);
            }
        });
}

function requeteCovoiturage2() {
    fetch("https://public.opendatasoft.com/api/records/1.0/search/?dataset=aires-covoiturage&q=&facet=ville&facet=type_de_parking&facet=source&facet=pmr&facet=transport_public&facet=prix&facet=ouverture&facet=lumiere&facet=velo&facet=couv4gbytel&facet=couv4gsfr&facet=couv4gorange&facet=couv4gfree&facet=nom_epci&facet=nom_reg&facet=nom_dep")
        .then(response => response.json())
        .then(data => {
            for (let record of data.records) {
                fetch(record.fields.ville).then(response => response.json())
                    .then(data => console.log(data));
            }
        });
}