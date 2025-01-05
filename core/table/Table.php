<?php

namespace core\Table;

use core\database\Database;


class Table
{
    protected $_dbinstance;

    protected $table;

    public function __construct(Database $database)
    {
        $this->_dbinstance = $database;
    }

    public function extract($key, $value)
    {
        $records = $this->all();
         $return = [];
        foreach ($records as $v)
        {
            $return[$v->$key] = $v->$value;
        }
        return $return;
    }

    public function all()
    {
        return $this->query("SELECT * FROM " . $this->table." ORDER BY id DESC");
    }
    public function allBat()
    {
        return $this->query("SELECT * FROM batiment where ref_proprietaire =".$_SESSION['auth']."");
    }
    public function allMedias()
    {
    return $this->query("SELECT * FROM medias ORDER BY id_media DESC");
    }
    public function PlayMedia($id)
    {
    return $this->query("SELECT * FROM medias WHERE id_media=$id");
    }
    public function allLocataires()
    {
        return $this->query("SELECT * FROM locataire where 	ref_bailleur =".$_SESSION['auth']."");
    }
    public function allDepense()
    {
        return $this->query("SELECT porte.designation as portes,batiment.designation as bat,libelle,depense.montant as charge,depense.created_at as jour,gerant FROM porte INNER JOIN batiment on porte.ref_bat = batiment.id INNER JOIN depense on porte.id = depense.ref_porte WHERE depense.gerant=".$_SESSION['auth']."");
    }
    public function allPorte()
    {
        return $this->query("SELECT porte.designation as portes,batiment.designation as bat,porte.id as code,prix_mois FROM porte INNER JOIN batiment on porte.ref_bat = batiment.id WHERE batiment.ref_proprietaire=".$_SESSION['auth']." ");
    }
    public function allPaie()
    {
        return $this->query("SELECT porte.designation as portes,porte.prix_mois as prix,porte.niveau_bat,batiment.designation as bat,mouvement.created_at as datepaie,mouvement.montant as paie,mouvement.debut as date1,mouvement.fin as date2,mouvement.type_mvt as typepaie,mouvement.gerant as agent FROM mouvement INNER JOIN porte on mouvement.ref_porte = porte.id INNER JOIN batiment on porte.ref_bat = batiment.id  WHERE batiment.ref_proprietaire=".$_SESSION['auth']." ");
    }
    public function allPaieFiltre($date1,$date2)
    {
        // var_dump($date1);die();
        return $this->query("SELECT porte.designation as portes,porte.prix_mois as prix,porte.niveau_bat,batiment.designation as bat,mouvement.created_at as datepaie,mouvement.montant as paie,mouvement.debut as date1,mouvement.fin as date2,mouvement.type_mvt as typepaie,mouvement.gerant as agent FROM mouvement INNER JOIN porte on mouvement.ref_porte = porte.id INNER JOIN batiment on porte.ref_bat = batiment.id  WHERE date_Format(mouvement.created_at, '%Y-%m-%d') BETWEEN (date_Format($date1, '%Y-%m-%d') AND date_Format($date2, '%Y-%m-%d'))  AND batiment.ref_proprietaire=".$_SESSION['auth']." ");
        // var_dump($this);die();
    }
    public function allLoca($tel, $champ = 'ref_bailleur')
    {
        return $this->query("SELECT * FROM " . $this->table." WHERE ". $champ ." = ? ", [$tel]);
    }
    public function allGaratie()
    {
        return $this->query("SELECT batiment.designation as bat,porte.designation as Portes,porte.prix_mois,porte.id as code,garatie.designation,garatie.montant,garatie.nbmois,garatie.date_debut,garatie.date_fin,garatie.etat,utilisateur.tel as proprie FROM `batiment` INNER JOIN porte on batiment.id = porte.ref_bat INNER JOIN garatie on porte.id = garatie.ref_porte INNER JOIN utilisateur on batiment.ref_proprietaire = utilisateur.tel where utilisateur.tel =".$_SESSION['auth']." ");
    }
    public function sommeGara($id, $champ = 'etat')
    {
        return $this->query("SELECT SUM(montant)  FROM " . $this->table ." WHERE ". $champ ." = ? ", [$id]);

    }
    public function sommePaie()
    {
    
        return $this->query("SELECT SUM(montant) as total  FROM mouvement WHERE gerant=".$_SESSION['auth']." AND created_at>=CURRENT_DATE()");

    }
    public function sommeGarantie()
    {
        return $this->query("SELECT SUM(garatie.montant) as total FROM `batiment` INNER JOIN porte on batiment.id = porte.ref_bat INNER JOIN garatie on porte.id = garatie.ref_porte INNER JOIN utilisateur on batiment.ref_proprietaire = utilisateur.tel where utilisateur.tel =".$_SESSION['auth']." AND  garatie.etat='En cours' ");

    }
    public function sommeDepense()
    {
        return $this->query("SELECT SUM(depense.montant) as total FROM porte INNER JOIN batiment on porte.ref_bat = batiment.id INNER JOIN depense on porte.id = depense.ref_porte WHERE depense.gerant=".$_SESSION['auth']."");
    }
    public function findByRef_bat($id, $champ = 'id')
    {
       return $this->query("SELECT * FROM ". $this->table ." WHERE ". $champ ." = ? ", [$id]);
    }
    public function allBatFilter($champ,$stat='Disponnible')
    {
       return $this->query("SELECT porte.designation as portes,batiment.designation as bat,porte.id as code,prix_mois,porte.statut as st  FROM porte INNER JOIN batiment on porte.ref_bat = batiment.id WHERE batiment.ref_proprietaire=".$_SESSION['auth']." AND ". $champ ." = ? ", [$stat]," ");
    }
    public function findById($id, $champ = 'id')
    {
       return $this->query("SELECT * FROM ". $this->table ." WHERE ". $champ ." = ? ", [$id], get_called_class(), true);
    }
    public function findByEmail($email, $champ = 'email')
    {
       return $this->query("SELECT * FROM ". $this->table ." WHERE ". $champ ." = ? ", [$email], get_called_class(), true);
    }
    public function findByCategorie($categorie, $champ = 'categorie')
    {
       return $this->query("SELECT * FROM ". $this->table ." WHERE ". $champ ." = ? ", [$categorie], get_called_class(), true);
    }
    public function findByTel($ref_client, $champ = 'ref_client')
    {
       return $this->query("SELECT * FROM ". $this->table ." WHERE ". $champ ." = ? ", [$ref_client], get_called_class(), true);
    }
    public function delete($id)
    {
        return $this->query(" DELETE FROM " . $this->table ." WHERE id = ?", [$id], true);
    }

    public function update($id, $fields, $champ = 'id')
    {
       $sql_parts = [];
       $attributes = [];
       foreach ($fields as $key => $v)
       {
           $sql_parts[] = "$key = ?";
           $attributes[] = $v;
       }
       $sql_part = implode(',',$sql_parts);
       $attributes[] = $id;
       return $this->query("UPDATE ". $this->table ." SET $sql_part WHERE $champ = ?", $attributes, true);
    }

    public function save($fields)
    {
        $sql_parts = [];
        $attributes = [];
        foreach ($fields as $key => $v)
        {
            $sql_parts[] = "$key = ?";
            $attributes[] = $v;
        }
        $sql_part = implode(',',$sql_parts);

        return $this->query("INSERT INTO ". $this->table ." SET $sql_part ", $attributes , true);
    }

    public function query($statement, $attributes = null, $one = false)
    {
        if($attributes)
        {
            return $this->_dbinstance->prepare(
                $statement, 
                $attributes,
                str_replace('Table', 'Entity', get_class($this)),
                $one);
        }else {
            return $this->_dbinstance->query(
                $statement,
                str_replace('Table', 'Entity', get_class($this)),
                $one
            );
        }
    }
}