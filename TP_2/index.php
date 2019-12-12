<?php
// On enregistre notre autoload.
function chargerClasse($classname)
{
    require $classname.'.php';
}

spl_autoload_register('chargerClasse');

session_start(); // On appelle session_start() APRÈS avoir enregistré l'autoload.

if (isset($_GET['deconnexion']))
{
    session_destroy();
    header('Location: .');
    exit();
}

$db = new PDO('mysql:host=localhost;dbname=poo_ocr', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); // On émet une alerte à chaque fois qu'une requête a échoué.

$manager = new PersonnagesManager($db);

if (isset($_SESSION['perso'])) // Si la session perso existe, on restaure l'objet.
{
    $perso = $_SESSION['perso'];
}

if (isset($_POST['creer']) && isset($_POST['nom']))         // Si on a cliqué sur créer un personnage & saisie un nom
{
    $perso = new Personnage(['nom' => $_POST['nom']]);      // On crée un nouveau personnage en récupérant le nom
    
    if (!$perso->nomValide())                               // vérif si nom valide
    {
        $message = 'Le nom choisi est invalide.';           // si non alors message d'erreur et pas de création
        unset($perso);
    }
    elseif ($manager->exists($perso->nom()))                // vérif si nom déjà existant en base
    {
        $message = 'Le nom du personnage est déjà pris.';   // si non alors message d'erreur et pas de création
        unset($perso);
    }
    else
    {
        $manager->add($perso);                              // si tout est ok création du personnage
    }
}

elseif (isset($_POST['utiliser']) && isset($_POST['nom']))  // Si on a cliqué sur utiliser un personnage & choisi un nom
{
    if ($manager->exists($_POST['nom']))                    // Si celui-ci existe.
    {
        $perso = $manager->get($_POST['nom']);              // afficher le nom
    }
    else
    {
        $message = 'Ce personnage n\'existe pas !';         // S'il n'existe pas alors message d'erreur
    }
}

elseif (isset($_GET['frapper']))                                            // Si on clic sur un personnage à frapper.
{
    if (!isset($perso))
    {
        $message = 'Merci de créer un personnage ou de vous identifier.';
    }
    
    else
    {
        if (!$manager->exists((int) $_GET['frapper']))
        {
            $message = 'Le personnage que vous voulez frapper n\'existe pas !';
        }
        
        else
        {
            $persoAFrapper = $manager->get((int) $_GET['frapper']);
            
            $retour = $perso->frapper($persoAFrapper); // On stocke dans $retour les éventuelles erreurs ou messages que renvoie la méthode frapper.
            
            switch ($retour)
            {
                case Personnage::CEST_MOI :
                    $message = 'Mais... pourquoi voulez-vous vous frapper ???';
                break;
                
                case Personnage::PERSONNAGE_FRAPPE :
                    $message = 'Le personnage a bien été frappé !';
                    
                    $manager->update($perso);
                    $manager->update($persoAFrapper);
                    
                break;
                
                case Personnage::PERSONNAGE_TUE :
                    $message = 'Vous avez tué ce personnage !';
                    
                    $manager->update($perso);
                    $manager->delete($persoAFrapper);
                    
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>TP : Mini jeu de combat</title>

<meta charset="utf-8" />
</head>
<body>
<p>Nombre de personnages créés : <?= $manager->count() ?></p>
<?php
if (isset($message)) // On a un message à afficher ?
{
    echo '<p>', $message, '</p>'; // Si oui, on l'affiche.
}

if (isset($perso)) // Si on utilise un personnage (nouveau ou pas).
{
    ?>
    <p><a href="?deconnexion=1">Déconnexion</a></p>
    
    <fieldset>
    <legend>Mes informations</legend>
    <p>
    Nom : <?= htmlspecialchars($perso->nom()) ?><br />
    Dégâts : <?= $perso->degats() ?>
    </p>
    </fieldset>
    
    <fieldset>
    <legend>Qui frapper ?</legend>
    <p>
    <?php
    $persos = $manager->getList($perso->nom());
    
    if (empty($persos))
    {
        echo 'Personne à frapper !';
    }
    
    else
    {
        foreach ($persos as $unPerso)
        {
            echo '<a href="?frapper=', $unPerso->id(), '">', htmlspecialchars($unPerso->nom()), '</a> (dégâts : ', $unPerso->degats(), ')<br />';
        }
    }
    ?>
    </p>
    </fieldset>
    <?php
}
else
{
    ?>
    <form action="" method="post">
    <p>
    Nom : <input type="text" name="nom" maxlength="50" />
    <input type="submit" value="Créer ce personnage" name="creer" />
    <input type="submit" value="Utiliser ce personnage" name="utiliser" />
    </p>
    </form>
    <?php
}
?>
</body>
</html>
<?php
if (isset($perso)) // Si on a créé un personnage, on le stocke dans une variable session afin d'économiser une requête SQL.
{
    $_SESSION['perso'] = $perso;
}