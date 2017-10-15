<?php

//


function uploadControl()
{
    if (!empty($_FILES) and $_FILES['photo']['error'] !== 4)
    {
        Photo::Upload();
    }
}


function erreurPhoto()
{
    $mes = false;
    if(!is_null(Photo::$erreur))
    {
        $mes = Photo::$erreur;
    }
    return $mes;
}
function messagePhoto()
{
    $mes = false;
    if(!is_null(Photo::$message))
    {

        $mes = Photo::$message;
    }
    return $mes;
}
