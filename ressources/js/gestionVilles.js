const boutonAjoutVille = document.getElementById("ajouterVille");
const boutonRetirerVille = document.getElementById("RetirerVille");
const nbVilles = document.getElementById("nombreVilles");
const fieldSet = document.getElementById("villes");

let ajoutApres = document.getElementById("autocompletionVilleArrivee");

let id = 0;

boutonAjoutVille.addEventListener("click", () => {
    if (Number(nbVilles.value) < 5) {
        nbVilles.value++;
        ajoutApres.insertAdjacentHTML("afterend",
            `<p id="commune_${id}" class="fieldsetP">
              <label for="nomCommune_${id}_id"></label>
              <input class="fieldsetInputPCC" type="text" value="" placeholder="Commune supplémentaire ${id}"
              name="nomCommune_${id}"
              id="nomCommune_${id}_id" required> </p>`);
        ajoutApres = document.getElementById(`commune_${id}`);
        id++;
    }
});

boutonRetirerVille.addEventListener("click", () => {
    if (Number(nbVilles.value) > 0) {
        nbVilles.value--;
        fieldSet.removeChild(ajoutApres);
        id -= 2;
        if (id < 0) {
            ajoutApres = document.getElementById("autocompletionVilleArrivee");
            id = 0;
        } else {
            ajoutApres = document.getElementById(`commune_${id}`);
            id++;
        }
    }
})
