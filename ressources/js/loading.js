// window.addEventListener("load", function () {
//    document.getElementById("main").classList.remove("isLoading");
// });
// window.onload = function() {
//    document.getElementById("loader").style.display = "none";
// };
// window.onload = function() {
//    let loader = document.getElementById("loader");
//    loader.classList.remove("show");
// };

// function showLoader() {
//    document.getElementById("loader").classList.add("show");
// }
//
// // Fonction pour masquer l'écran de chargement
// function hideLoader() {
//    document.getElementById("loader").classList.remove("show");
// }
//
// window.onload = function () {
//    document.getElementById("formPlusCourtChemin").addEventListener("submit", function (event) {
//       event.preventDefault();
//       showLoader();
//       let data = {
//         nomCommuneDepart: document.getElementById("nomCommuneDepart_id").value,
//         nomCommuneArrivee: document.getElementById("nomCommuneArrivee_id").value
//       };
//       let xhr = new XMLHttpRequest();
//       xhr.open("POST", "http://localhost/SAE2/plus-court-chemin-code-de-base/web/plusCourtChemin", true);
//       xhr.setRequestHeader("Content-Type", "application/json");
//       xhr.onreadystatechange = function() {
//          if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
//             // Traitement de la réponse du serveur
//             hideLoader();
//             console.log("oui");
//          }
//       };
//       xhr.send(JSON.stringify(data));
//    });
// }

$(document).ready(function () {
    $("#calculer").click(function () {
        $("#loader").fadeIn(300);
        console.log("oui");
    });

    $(document).ajaxStop(function () {
        $("#loader").fadeOut(300);
        console.log("non");
    });
});

