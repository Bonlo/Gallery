<?php

class Taxonomy {

    public static $tag = [];
    private static $term;
    private static $TABLE_taxons = [];
    private static $TABLE_terms = [];

    private function __construct(){}

    public static function make()
    {

        static::insert_tag();

        static::insert_relation();

    }
    /*
    *
    *   Setters
    *
    ***************/
    private static function set_tag()
    {
        $tags = explode(" ", $_POST['tag']);

        foreach ($tags as $key => $value)
        {
            static::$tag[$value] =  [
                "tag"  => $value,
                "slug" => App::slugify($value)
            ];
        }
        return static::$tag;
    }
    private static function set_TABLE_taxons()
    {
        $query_taxons = App::query('SELECT * FROM lb_taxonomie');
        $taxons = [];

        while ($fetch = $query_taxons->fetch())
        {
            $taxons[$fetch['name']] = $fetch['taxon_id'];
        }
        static::$TABLE_taxons = $taxons;
    }
    private static function set_TABLE_terms()
    {
        $query_terms = App::query('SELECT * FROM lb_terms');
        $tags = [];
        while ($fetch = $query_terms->fetch())
        {
            $tags[$fetch['name']] = $fetch['taxon_id'];
        }
        static::$TABLE_terms = $tags;
    }

    /*
    *
    *   Getters
    *
    ***************/

    public static function get_term()
    {
        return static::$term = $_POST['color'];
    }

    /*
    *
    *   Insert
    *
    ***************/
    public static function insert_tag()
    {
        if(!empty($_POST['tag']))
        {
            static::set_tag();

            foreach (static::$tag as $index => $array)
                {

                    $req1 = App::query('SELECT * FROM lb_terms WHERE taxon_id=6 and name="'.$array['tag'].'"');
                    $donnees = $req1->fetch();

                    if ($donnees['name'] != $array['tag'])
                    {
                        $req = App::prepare('INSERT INTO lb_terms(name, taxon_id) VALUES(:name, :taxon_id)');
                        $req->execute(array(

                            'name'      => $array['tag'],
                            'taxon_id'  => 6
                        ));
                    }
                }
            }
            else
                return false;
        }
        private static function insert_relation()
        {

            $req = App::prepare('INSERT INTO lb_tax_relation(photo_id, term_id) VALUES(:photo_id ,:term_id)');
            $current_photo_id = App::query('SELECT ID FROM lb_photos WHERE name = "'.Photo::$name.'"')->fetch(PDO::FETCH_ASSOC)['ID'];
            $req->bindParam(':photo_id', $current_photo_id);

            if(!empty(static::$tag))
                {
                    foreach (static::$tag as $index => $array)
                        {
                            $current_tag_id   = App::query('SELECT term_id FROM lb_terms WHERE name = "'.$array['tag'].'"')->fetch(PDO::FETCH_ASSOC)['term_id'];
                //Première entrée
                            $req->bindParam(':term_id' , $current_tag_id);
                            $req->execute();


                            static::get_term();
                            $current_term_id = App::query('SELECT term_id FROM lb_terms WHERE name = "'.static::$term.'"')->fetch(PDO::FETCH_ASSOC)['term_id'];

                            $verif = App::query('SELECT * FROM lb_tax_relation WHERE photo_id="'.$current_photo_id.'" and term_id="'.$current_term_id.'"')->fetch(PDO::FETCH_ASSOC);

                            if($verif == false)
                            {
                    //Deuxième entrée avec photo_id identique
                                $req->bindParam(':term_id' , $current_term_id);
                                $req->execute();;
                            }
                        }

            //

                    }
                    else
                    {
                        static::get_term();
                        $current_term_id = App::query('SELECT term_id FROM lb_terms WHERE name = "'.static::$term.'"')->fetch(PDO::FETCH_ASSOC)['term_id'];

                        $verif = App::query('SELECT * FROM lb_tax_relation WHERE photo_id="'.$current_photo_id.'" and term_id="'.$current_term_id.'"')->fetch(PDO::FETCH_ASSOC);

                        if($verif == false)
                        {
                //Deuxième entrée avec photo_id identique
                            $req->bindParam(':term_id' , $current_term_id);
                            $req->execute();;
                        }

                    }
                }



            }
