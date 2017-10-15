<?php

function connectUserControl()
{
    if( isset($_POST) and
        isset($_POST['login']) and
        isset($_POST['password']) )
    {
        echo User::logIn();
    }
}


function addUserControl()
{
    if( isset($_POST['new_login']) and
        isset($_POST['new_password']) and
        isset($_POST['password_rep']) and
        isset($_POST['new_email']) )
    {
        User::addUser();
    }
}
function erreurUser()
{

    $mes = false;
    if(!is_null(User::$erreur))
    {
        $mes = User::$erreur;
    }
    return $mes;
}
function messageUser()
{
    $mes = false;
    if(!is_null(User::$message))
    {
        $mes = User::$message;
    }
    return $mes;
}


function showEmail()
{
    $req = App::query('SELECT email FROM lb_users WHERE login="'.$_SESSION["login"].'"');
    $fetch = $req->fetch(PDO::FETCH_ASSOC);
    echo $fetch["email"];

}
