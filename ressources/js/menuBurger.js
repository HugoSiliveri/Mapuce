const menu = document.getElementById("container");
const sous_menu = document.getElementById("sous_menu");
const infos = document.querySelectorAll(".infos");
const boutonContainers = document.querySelectorAll(".boutonContainer");


function derouler() {
    menu.classList.toggle("change");
    sous_menu.style.display = "flex";
    for (const info of infos) {
        info.style.display = "flex";
    }
}

function rembaler() {
    menu.classList.toggle("change");
    sous_menu.style.display = "none";
    for (const info of infos) {
        info.style.display = "none";
    }
}

menu.addEventListener("click", function () {
    if (sous_menu.style.display === "flex") {
        rembaler();
    } else {
        derouler();
    }
});


// Permet de rendre les balise cliquables
for (const boutonContainer of boutonContainers) {
    boutonContainer.addEventListener("click", function () {
        let id = boutonContainer.id;

        let layerClass = "." + id + "-layer";

        let layers = document.querySelectorAll(layerClass);

        for (let layer of layers) {
            layer.classList.toggle("active");
        }
        setTimeout(() => {
            window.location.href = boutonContainer.querySelector('data').getAttribute("value");
        }, 700);
    });
}