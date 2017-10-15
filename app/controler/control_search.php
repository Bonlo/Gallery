<?php
function search_control()
{
    if(!empty($_POST) and isset($_POST['search']) and $_POST['search'] != null)
    {
        return Research::search();
    }
}
function erreurSearch()
{
    $mes = false;
    if(!is_null(Research::$erreur))
    {
        $mes = Research::$erreur;
    }
    return $mes;
}
