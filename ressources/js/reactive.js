let objectByName = new Map();
let registeringEffect = null;
let objetDependencies = new Map();

function applyAndRegister(effect) {
    registeringEffect = effect;
    effect();
    registeringEffect = null;
}


function reactive(passiveObject, name) {
    objetDependencies.set(passiveObject, new Map());
    const handler = {
        get(target, key) {
            if (registeringEffect !== null)
                registerEffect(target, key);
            return target[key];
        },
        set(target, key, value) {
            target[key] = value;
            trigger(target, key);
            return true;
        },
    };
    let reactiveObjet = new Proxy(passiveObject, handler);
    objectByName.set(name, reactiveObjet);
    return reactiveObjet;
}

function startReactiveDom(subDOM = document) {
    for (let elementClickable of subDOM.querySelectorAll("[data-onclick]")) {
        const [nomObjet, methode, argument] = elementClickable.dataset.onclick.split(/[.()]+/);
        elementClickable.addEventListener('click', (event) => {
            const objet = objectByName.get(nomObjet);
            objet[methode](argument);
        })
    }
    for (let rel of subDOM.querySelectorAll("[data-textfun]")) {
        const [obj, fun, arg] = rel.dataset.textfun.split(/[.()]+/);
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[fun](arg)
        });
    }
    for (let rel of subDOM.querySelectorAll("[data-textvar]")) {
        const [obj, prop] = rel.dataset.textvar.split('.');
        applyAndRegister(() => {
            rel.textContent = objectByName.get(obj)[prop]
        });
    }

    for (let rel of subDOM.querySelectorAll("[data-stylefun]")) {
        const [obj, fun, arg] = rel.dataset.stylefun.split(/[.()]+/);
        applyAndRegister(() => {
            Object.assign(rel.style, objectByName.get(obj)[fun](arg))
        });
    }

    for (let rel of subDOM.querySelectorAll("[data-htmlfun]")) {
        const [obj, fun, arg] = rel.dataset.htmlfun.split(/[.()]+/);
        applyAndRegister(() => {
            rel.innerHTML = objectByName.get(obj)[fun](arg)
            startReactiveDom(rel);
        });
    }


    /* Autocompletion */

    for (let rel of subDOM.querySelectorAll("[data-htmlvar]")) {
        const [obj, prop] = rel.dataset.htmlvar.split('.');
        rel.addEventListener("click", function (event) {

            for (let rel2 of subDOM.querySelectorAll("[data-inputfun]")) {
                const [nomObjet, methode] = rel2.dataset.inputfun.split(/[.()]+/);
                if (objectByName.get(obj) === objectByName.get(nomObjet)) {
                    rel2.value = event.target.textContent;
                    rel.innerHTML = "";
                }
            }
        });
        applyAndRegister(() => {
            rel.innerHTML = objectByName.get(obj)[prop]
        });
    }

    for (let rel of subDOM.querySelectorAll("[data-inputfun]")) {
        const [nomObjet, methode] = rel.dataset.inputfun.split(/[.()]+/);
        rel.addEventListener("input", function () {
            if (rel.value.length >= 2) {
                const objet = objectByName.get(nomObjet);
                objet[methode](rel.value);
            }
        });
    }
}


function trigger(target, key) {
    // parcours registeredEffects pour appliquer tous les effets enregistrés.
    if (!objetDependencies.get(target).has(key)) return;
    for (let effet of objetDependencies.get(target).get(key)) {
        effet();
    }
}

function registerEffect(target, key) {
    if (objetDependencies.get(target).has(key)) {
        objetDependencies.get(target).get(key).add(registeringEffect);
    } else {
        objetDependencies.get(target).set(key, new Set());
    }
}

export {applyAndRegister, reactive, startReactiveDom};

