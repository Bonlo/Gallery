<?php
/*

Gère la connexion à la bdd en singleton;
Supporte les méthodes PDO sans en instancier directement un objet;
Contient les functions de travail les plus utilisées.


*/

class App {
        /**
         * Instance de la classe App
         * @access private
         * @var connexion
         * @see getInstance
         */
        public static $instance;

        /**
         * Type de la base de donnée.
         * @access private
         * @var string
         * @see __construct
         */
        private $type = "mysql";

        /**
         * Adresse du serveur hôte.
         * @access private
         * @var string
         * @see __construct
         */
        private $host = "Your host";

        /**
         * Nom d'utilisateur pour la connexion à la base de données
         * @access private
         * @var string
         * @see __construct
         */
        private $username = "Your username";

        /**
         * Mot de passe pour la connexion à la base de donnée
         * @access private
         * @var string
         * @see __construct
         */
        private $password = "Your Password";

        /**
         * Nom de la base de donnée.
         * @access private
         * @var string
         * @see __construct
         */
        private $dbname = "Your DataBaseName";


        private $bdd;


        /**
         * Lance la connexion à la base de donnée en le mettant
         * dans un objet PDO qui est stocké dans la variable $bdd
         * @access private
         */




        private function __construct()
        {
            try{
                    $this->bdd = new PDO(
                    $this->type.':host='.$this->host.'; dbname='.$this->dbname,
                    $this->username,
                    $this->password,
                    array(PDO::ATTR_PERSISTENT => true)
                    );

                $req = "SET NAMES UTF8";
                $result = $this->bdd->prepare($req);
                $result->execute();
            }
            catch(PDOException $e){
                echo "<div>Erreur !: ".$e->getMessage()."</div>";
                die();
            }
        }

        /**
         * Singleton !!!!.
         * @return $instance de self
         */
        public static function getInstance()
        {
            if (!self::$instance instanceof self)
            {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /*
        *Récupère l'objet pdo
        *@return instance de pdo utilisée par self
        */
        public function getBdd()
        {
            return $this->bdd;
        }
        /*(!!)
        *Suite de raccourci pour parvenir à un
        *query/exec/prepare du type "App::query()"
        */
        private static function getPdo()
        {
            return self::getInstance()->getBdd();
        }

        public static function query($query)
        {
            return self::getPdo()->query($query);
        }
        public static function exec($exec)
        {
            return self::getPdo()->exec($exec);
        }
        public static function prepare($prep)
        {
            return self::getPdo()->prepare($prep);
        }
        public static function execute($execute = array())
        {
            return self::getPdo()->execute($execute);
        }

        //!!


        /*
        * Fonctions d'opérations sur string
        *
        */
        static function slugify($str)
        {
            $str = strtr($str, array(
                'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
                'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
                'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
                'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
                'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
                'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y',
                'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', 'Œ'=>'OE', 'œ'=>'oe',
            ));
            $str = preg_replace('#[^0-9a-z]+#i', '_', $str);
            $str = strtolower($str);
            $str = trim($str, '-');
            return $str;
        }



        static function concatName($name, $extension)
        {
            $concat = $name.'.'.$extension;
            return $concat;
        }

        /*
        *Récupération des tables sous forme de tableau bidimensionnel,
        *Avec une option pour récupérer une seule colonne. Cette option
        *sera utile pour array_search()
        */


        /*** TABLEAU PHOTOS ***/
    //return array[photo_id] [clé => valeur] de la table + exif
    static function arrayPhotos($colonne_return = null)
    {
        $query = App::query("SELECT * FROM lb_photos");
        while($fetch = $query->fetch(PDO::FETCH_ASSOC))
        {
            //Variable de chemin courant, plus fun que tout ce long machin
            $photo_path = "img/Uploaded/".$fetch['filename'];
            //On rempli le tableau de table avec tout ce que contient la table
            $temp = $fetch;
            //Si l'image est jpeg alors il y a des exif, donc on les ajoute à chaque photo
            if(exif_imagetype($photo_path) == IMAGETYPE_JPEG)
            {
                $exif = exif_read_data($photo_path);

                foreach ($exif as $key => $value)
                {
                    if(!is_array($value)    and    ($key == 'ExposureTime'
                                            or      $key == 'FNumber'
                                            or      $key == 'ExposureProgram'
                                            or      $key == 'ISOSpeedRatings'))
                    {
                        if ($key == 'ExposureTime')     $key = 'vitesse';
                        if ($key == 'FNumber')          $key = 'ouverture';
                        if ($key == 'ExposureProgram')  $key = 'mode';
                        if ($key == 'ISOSpeedRatings')  $key = 'iso';
                        $exifs[$key] = $value;
                    }
                }
                //On merge le fetch temporaire et les exifs
                //si l'opération précédente a eu lieu
                if (isset($exifs))
                array_merge($fetch, $exifs);
            }
            //And voilà
            $lb_photos[] = $fetch;
        }
        //OPTION utilisant le paramètre pour return seulement la colonne demandée
        if($colonne_return !== null)
        {
            //on recherche tout de suite si la colonne existe.
            $colonne_exist = false;
            foreach ($lb_photos as $photo)
            {
                if(isset($photo[$colonne_return]))
                    $colonne_exist = true;
                else
                    $colonne_exist = false;
            }

            if($colonne_exist)
            {
                $variable_dynamique = "lb_photos"."_".$colonne_return;
                foreach ($lb_photos as $value)
                {
                    ${$variable_dynamique}[] = $value[$colonne_return];
                }
                return $$variable_dynamique;
            }
            else
            {
                echo "<br><br>Paramètre == null<br><br>";
                return $lb_photos;
            }
        }
        return $lb_photos;
    }
    /*** TABLEAU TAX RELATION ***/
    //return array[ID] [photo_id => term_id] de la table + exif
    static function arrayRelation($colonne_return = null)
    {
        $query = App::query("SELECT * FROM lb_tax_relation");
        while($fetch = $query->fetch(PDO::FETCH_ASSOC))
        {
            $lb_tax_relation[] = $fetch;
        }
        if($colonne_return !== null)
        {
            $colonne_exist = false;
            foreach ($lb_tax_relation as $relation)
            {
                if(isset($relation[$colonne_return]))
                    $colonne_exist = true;
                else
                    $colonne_exist = false;
            }

            if($colonne_exist)
            {
                $variable_dynamique = "lb_tax_relation"."_".$colonne_return;
                foreach ($lb_tax_relation as $value)
                {
                    ${$variable_dynamique}[] = $value[$colonne_return];
                }
                return $$variable_dynamique;
            }
            else
            {
                echo "<br><br>Paramètre == null<br><br>";
                return $lb_tax_relation;
            }
        }
        return $lb_tax_relation;
    }
    /*** TABLEAU TERMS ***/
    //return array[term_id] [name => taxon_id]
    static function arrayTerms($colonne_return = null)
    {
        $query = App::query("SELECT * FROM lb_terms");

        while($fetch = $query->fetch(PDO::FETCH_ASSOC))
        {
            $lb_terms[] = $fetch;
        }

        if($colonne_return !== null)
        {

            $colonne_exist = false;
            foreach ($lb_terms as $term)
            {
                if(isset($term[$colonne_return]))
                    $colonne_exist = true;
                else
                    $colonne_exist = false;
            }

            if($colonne_exist)
            {
                $variable_dynamique = "lb_terms"."_".$colonne_return;
                foreach ($lb_terms as $value)
                {
                    //se référer à la doc pour cette syntaxe
                    ${$variable_dynamique}[] = $value[$colonne_return];
                }
                return $$variable_dynamique;
            }
            else
            {
                echo "<br><br>Paramètre == null<br><br>";
                return $lb_terms;
            }
        }
        return $lb_terms;
    }
    /*** TABLEAU TAXONOMY ***/
    //return array[taxon_id => name]
    static function arrayTaxon($colonne_return = null)
    {
        $query = App::query("SELECT * FROM lb_taxonomie");
        while($fetch = $query->fetch(PDO::FETCH_ASSOC))
        {
            $lb_taxonomie[] = $fetch;
        }
        if($colonne_return !== null)
        {
            $colonne_exist = false;
            foreach ($lb_taxonomie as $taxon)
            {
                if(isset($taxon[$colonne_return]))
                    $colonne_exist = true;
                else
                    $colonne_exist = false;
            }

            if($colonne_exist)
            {
                $variable_dynamique = "lb_taxonomie"."_".$colonne_return;
                foreach ($lb_taxonomie as $value)
                {
                    ${$variable_dynamique}[] = $value[$colonne_return];
                }
                return $$variable_dynamique;
            }
            else
            {
                echo "<br><br>Paramètre == null<br><br>";
                return $lb_taxonomie;
            }
        }
        return $lb_taxonomie;
    }
    /*** TABLEAU USERS ***/
    static function arrayUsers($colonne_return = null)
    {
        $query = App::query("SELECT * FROM lb_users");
        while($fetch = $query->fetch(PDO::FETCH_ASSOC))
        {
            $lb_users[] = $fetch;
        }
        if($colonne_return !== null)
        {
            $colonne_exist = false;
            foreach ($lb_users as $user)
            {
                if(isset($user[$colonne_return]))
                    $colonne_exist = true;
                else
                    $colonne_exist = false;
            }

            if($colonne_exist)
            {
                $variable_dynamique = "lb_users_".$colonne_return;
                foreach ($lb_users as $value)
                {
                    ${$variable_dynamique}[] = $value[$colonne_return];
                }
                return $$variable_dynamique;
            }
            else
            {
                echo "<br><br>Paramètre == null<br><br>";
                return $lb_users;
            }
        }
        return $lb_users;
    }
}
