<?php

class Photo
{

    const PATH = '../img/Uploaded/';

    public static $erreur;
    public static $message;

    public static $name;
    public static $fullName;
    private static $fullPath;

    private function __construct(){}

    static function upload()
    {
        return static::insert();
    }

    static function post($name, $extension, $filename)
    {
        $user_id = 0;
        if(isset($_SESSION['connected']) and $_SESSION['connected'])
        {
            $user_id = User::getId();
        }

        $req = App::query('SELECT * FROM lb_photos WHERE name="'.$name.'"');
        $donnees = $req->fetch();
        if ($donnees['name'] != $name)
        {
            App::exec('INSERT INTO lb_photos(ID, name, extension, filename, user_id) VALUES(DEFAULT, "'.$name.'", "'.$extension.'", "'.$filename.'", "'.$user_id.'")');
            return true;
        }
        else
        {
            self::$erreur = "Erreur lors du post().";
        }
    }

    static function insert()
    {
        $ALL =  array(
            'tmp_url'               =>      $_FILES['photo']['tmp_name'],
            'up_name'               =>      $_FILES['photo']['name'],
            'size'                  =>      $_FILES['photo']['size'],
            'img_size'              =>      getimagesize($_FILES['photo']['tmp_name']),
            'error'                 =>      $_FILES['photo']['error'],

            'destination'           =>      static::PATH,

            'exp_name'              =>      explode('.', $_FILES['photo']['name'])
        );

        // paramètres de check
        $extensionsValides      =       array ('jpg', 'jpeg', 'gif', 'png', 'ico');
        $maxSize                =       52428800;

        $extension              =       strtolower($ALL['exp_name'][1]);
        static::$name           =       $ALL['exp_name'][0];

        if($ALL['error'] > 0 || $ALL['size'] > $maxSize)
        {
            switch ($ALL['error']) {
                case 1:
                self::$erreur = "La taille du fichier téléchargé
                excède la valeur de upload_max_filesize,
                configurée dans le php.ini.";
                break;
                case 3:
                self::$erreur = "Le fichier n'a été que partiellement téléchargé.";
                break;
                case 4:
                self::$erreur = "Aucun fichier n'a été téléchargé.";
                break;
            }
        }
        else
        {
            static::$name      = App::slugify(static::$name);
            static::$fullName  = App::concatName(static::$name, $extension);
        }

        if(!in_array($extension, $extensionsValides))
        {
            self::$erreur = "L'extension n'est pas valide.";
        }

        /*
            Fin des checks, tout est bon.
            on déplace le fichier tmp_url
            dans sa dest.
        */
        elseif (move_uploaded_file($ALL['tmp_url'],$ALL['destination'].static::$fullName))
        {
        //Correction de l'orientation !!
            static::correctOrientation($ALL['destination'].static::$fullName);

            if(self::post(static::$name, $extension, static::$fullName) )
                {

                    if($_POST !== false)
                    {
                        echo Taxonomy::make();
                        self::$message = 'Le fichier "'.static::$name.'" a bien été téléchargé.';
                    }
                }
                else
                {
                    self::$erreur = "Un fichier portant le même nom existe déjà.";
                }
            }
        }

        static function read($array = null) //attend search()
        {
            $allNames = array();

            if($array === null or !is_array($array))
            {
                if(!is_array($array) and !is_null($array))
                {
                    self::$erreur = "Ce tag n'existe pas !";
                }
                $req = App::query('SELECT filename FROM lb_photos');
                //Stocke les champs 'filename' de toutes les entrées dans le tableau,
                //Utile pour foreach.
                while ($fetch = $req->fetch())
                {
                    $allNames[] .= $fetch['filename'];
                }
                return $allNames;
            }
            else
                return $array;
        }
        public static function getFullPath()
        {
            return static::$fullPath = static::PATH . static::$fullName;
        }
        public static function correctOrientation($path)
        {
            $filePath = $path;
            $filename = $path;
            if(function_exists('exif_read_data') && $_FILES['photo']['type'] == 'image/jpeg')

            {
                $exif = exif_read_data($filePath);

                if (!empty($exif['Orientation'])) {
                $imageResource = imagecreatefromjpeg($filePath); // provided that the image is jpeg. Use relevant function otherwise
                switch ($exif['Orientation']) {
                    case 3:
                    $image = imagerotate($imageResource, 180, 0);
                    break;
                    case 6:
                    $image = imagerotate($imageResource, -90, 0);
                    break;
                    case 8:
                    $image = imagerotate($imageResource, 90, 0);
                    break;
                    default:
                    $image = $imageResource;
                }
                return imagejpeg($image, $filename, 90);
            }
        }
    }



}
