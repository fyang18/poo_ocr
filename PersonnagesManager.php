<?php
class PersonnagesManager
{
    private $_db; // Instance de PDO
    
    public function __construct($db)
    {
        $this->setDb($db);
    }
    
    public function add(Personnage $perso)
    {
        // Préparation de la requête d'insertion.
        $q = $this->_db->prepare('INSERT INTO personnages(nom) VALUES(:nom)');

        // Assignation des valeurs pour le nom du personnage.
        $q->bindValue(':nom', $perso->nom());

        // Exécution de la requête.
        $q->execute();

        
        // Hydratation du personnage passé en paramètre avec assignation de son identifiant et des dégâts initiaux (= 0).  
        $perso->hydrate([
          'id' => $this->_db->lastInsertId(),
          'degats' => 0,
        ]);
    }
    
    public function count()
    {
        // Exécute une requête COUNT() et retourne le nombre de résultats retourné.
        return $this->_db->query('SELECT COUNT(*) FROM personnages')->fetchColumn();
    }
    
    public function delete(Personnage $perso)
    {
        // Exécute une requête de type DELETE.
        $this->_db->exec('DELETE FROM personnages WHERE id = '.$perso->id());
    }
    
    public function exists($info)
    {
        // Si le paramètre est un entier, c'est qu'on a fourni un identifiant.
        // On exécute alors une requête COUNT() avec une clause WHERE, et on retourne un boolean.
        
        // Sinon c'est qu'on a passé un nom.
        // Exécution d'une requête COUNT() avec une clause WHERE, et retourne un boolean.
    }
    
    public function get($info)
    {
        // Si le paramètre est un entier, on veut récupérer le personnage avec son identifiant.
        // Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
        
        // Sinon, on veut récupérer le personnage avec son nom.
        // Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
    }
    
    public function getList($nom)
    {
        // Retourne la liste des personnages dont le nom n'est pas $nom.
        // Le résultat sera un tableau d'instances de Personnage.
    }
    
    public function update(Personnage $perso)
    {
        // Prépare une requête de type UPDATE.
        // Assignation des valeurs à la requête.
        // Exécution de la requête.
    }
    
    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}