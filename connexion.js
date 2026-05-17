//fonction qui affiche ou masque le mdp
function visibilitemdp(id, icon){
    let password = document.getElementById(id);
    if(password.type == "password"){
        password.setAttribute("type", "text"); //affiche le mdp
        icon.src = "images/oeilbarre.png"; //œil barré
        setTimeout(function(){
            password.setAttribute("type", "password"); //masque le mdp au bout de 30s
            icon.src = "images/oeil.png"; //œil normal
        }, 60000);
    }
    else{
        password.setAttribute("type", "password"); //masque le mdp
        icon.src = "images/oeil.png"; //œil normal
    }
}

//fonction qui compte le nb carctères saisi dans un champ
function compteur(idChamp, idCompteur, max){
    let texte = document.getElementById(idChamp).value;
    document.getElementById(idCompteur).innerHTML = texte.length + " / " + max + " caractères";
}

//fonction qui vérifie les champs côté client dans le formulaire d'inscription 
function verificationInscription(){
    let valide = true;
  
    //civilité
    let civiliteMme = document.getElementById("civilite_mme").checked;
    let civiliteM = document.getElementById("civilite_m").checked;
    if(!civiliteMme && !civiliteM){
        document.getElementById("erreurcivilite").innerHTML = "Veuillez choisir une civilité";
        valide = false;
    }
    else{
        document.getElementById("erreurcivilite").innerHTML = "";
    }

    //prenom
    let prenom = document.getElementById("prenom").value.trim();
    if(prenom == ""){
        document.getElementById("erreurprenom").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(prenom.length < 2){
        document.getElementById("erreurprenom").innerHTML = "Le prénom doit contenir au moins 2 caractères";
        valide = false;
    }
    else if(!/^[a-zA-ZÀ-ÿ\- ]+$/.test(prenom)){
        document.getElementById("erreurprenom").innerHTML = "Le prénom doit contenir uniquement des lettres";
        valide = false;
    }
    else{
        document.getElementById("erreurprenom").innerHTML = "";
    }

    //nom
    let nom = document.getElementById("nom").value.trim();
    if(nom == ""){
        document.getElementById("erreurnom").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(nom.length < 2){
        document.getElementById("erreurnom").innerHTML = "Le nom doit contenir au moins 2 caractères";
        valide = false;
    }
    else if(!/^[a-zA-ZÀ-ÿ\- ]+$/.test(nom)){
        document.getElementById("erreurnom").innerHTML = "Le nom doit contenir uniquement des lettres";
        valide = false;
    }
    else{
        document.getElementById("erreurnom").innerHTML = "";
    }

    //date
    let anniv = document.getElementById("anniv").value;
    let aujourdhui = new Date().toISOString().split("T")[0];
    if(anniv == ""){
        document.getElementById("erreuranniv").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(anniv > aujourdhui){
        document.getElementById("erreuranniv").innerHTML = "Date invalide";
        valide = false;
    }
    else{
        document.getElementById("erreuranniv").innerHTML = "";
    }

    //téléphone
    let tel = document.getElementById("tel").value;
    let telsansespace = tel.replaceAll(" ", "");
    if(tel == ""){
        document.getElementById("erreurtel").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(!/^[0-9]+$/.test(telsansespace)){
        document.getElementById("erreurtel").innerHTML = "Le numéro doit contenir uniquement des chiffres et des espaces";
        valide = false;
    }
    else if(telsansespace.length != 10){
        document.getElementById("erreurtel").innerHTML = "Le numéro doit contenir 10 chiffres";
        valide = false;
    }
    else if(telsansespace[0] != "0"){
        document.getElementById("erreurtel").innerHTML = "Le numéro doit commencer par 0";
        valide = false;
    }
    else{
        document.getElementById("erreurtel").innerHTML = "";
    }

    //email
    let mail = document.getElementById("mail").value.trim();
    if(mail == ""){
        document.getElementById("erreurmail").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)){
        document.getElementById("erreurmail").innerHTML = "Adresse e-mail invalide";
        valide = false;
    }
    else{
        document.getElementById("erreurmail").innerHTML = "";
    }

    //mdp
    let mdp = document.getElementById("mdp").value;
    let mdpconfirme = document.getElementById("mdpconfirme").value;
    if(mdp == ""){
        document.getElementById("erreurmdp").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(mdp.length < 8){
        document.getElementById("erreurmdp").innerHTML = "Le mot de passe doit contenir au moins 8 caractères";
        valide = false;
    }
    else{
        document.getElementById("erreurmdp").innerHTML = "";
    }

    //mdpconfirme
    if(mdpconfirme == ""){
        document.getElementById("erreurmdpconfirme").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(mdp != mdpconfirme){
        document.getElementById("erreurmdpconfirme").innerHTML = "Les mots de passe ne correspondent pas";
        document.getElementById("erreurmdp").innerHTML = "Les mots de passe ne correspondent pas";
        valide = false;
    }
    else{
        document.getElementById("erreurmdpconfirme").innerHTML = "";
    }

    //cgu
    let cgu = document.getElementById("cgu").checked;
    if(!cgu){
        document.getElementById("erreurcgu").innerHTML = "Veuillez accepter les conditions générales";
        valide = false;
    }
    else{
        document.getElementById("erreurcgu").innerHTML = "";
    }

    //si tout est bon
    return valide;
}

//fonction qui vérifie les champs côté client dans le formulaire de connexion
function verificationConnexion(){
    let valide = true;

    //email
    let mail = document.getElementById("mail").value.trim();
    if(mail == ""){
        document.getElementById("erreurmail").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)){
        document.getElementById("erreurmail").innerHTML = "Adresse e-mail invalide";
        valide = false;
    }
    else{
        document.getElementById("erreurmail").innerHTML = "";
    }

    //mdp
    let mdp = document.getElementById("mdp").value;
    if(mdp == ""){
        document.getElementById("erreurmdp").innerHTML = "Veuillez renseigner ce champ";
        valide = false;
    }
    else if(mdp.length < 8){
        document.getElementById("erreurmdp").innerHTML = "Le mot de passe doit contenir au moins 8 caractères";
        valide = false;
    }
    else{
        document.getElementById("erreurmdp").innerHTML = "";
    }

    //si tout est bon
    return valide;

}
