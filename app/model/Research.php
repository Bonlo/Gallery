<?php

class Research
{
    public static $erreur;
    public static $searched;

    public static function search()
    {

        $searched = $_POST['search'];
        $searched = strtolower($searched);

        $array_searched = explode(" ", $searched);
        $intersection = array_intersect($array_searched, App::arrayTerms("name"));
        if(!empty($intersection))
        {
            foreach (App::arrayTerms() as $keyTerm => $multiArrayTerms)
            {
                foreach ($multiArrayTerms as $column => $row)
                {
                    foreach ($intersection as $searched)
                    {
                        if($column == "name" and $row == $searched)
                        {


                            foreach (App::arrayRelation() as $keyRel => $relation)
                            {
                                foreach ($relation as $row => $term_id)
                                {
                                    $searchedTermId = App::arrayTerms()[$keyTerm]['term_id'];


                                    if($row == "term_id" and $term_id == $searchedTermId)
                                    {
                                        $findedPhoto[] = App::arrayRelation()[$keyRel]['photo_id'];
                                    }
                                }
                            }

                        }
                    }
                }
            }
            if(isset($findedPhoto))
            {

                $cleanFindedPhotos = array_unique($findedPhoto);
                sort($cleanFindedPhotos);


                foreach (App::arrayPhotos() as $keyPhoto => $photo)
                {
                    foreach ($cleanFindedPhotos as $key => $searchedPhoto)
                    {
                        if($photo['ID'] == $searchedPhoto)
                        {
                            $findedUrls[] = App::arrayPhotos()[$keyPhoto]['filename'];
                        }
                    }
                }
            }
            return $findedUrls;
        }
        else {
            Photo::$erreur = "Ce tag n'existe pas";
        }

    }

}
