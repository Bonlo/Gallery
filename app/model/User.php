<?php

class User
{
    public static $erreur;
    public static $message;

    private static $login;
    private static $password;
    private static $email;

    private static $connected = false;

    private function __construct(){}

    public static function addUser()
    {
        if(static::setLogin() and static::setPassword() and static::setEmail() and static::testUser() )
        {
            $prep = App::prepare('INSERT INTO lb_users(login, password, email) VALUES (?, ?, ?)');
            $prep->execute(array(self::$login, self::$password, self::$email));

            self::$message = "Bienvenue ! :)";

            $_SESSION['connected'] = true;
            $_SESSION['login'] = self::$login;
        }
    }
    public static function logIn()
    {
        $password = hash('sha512', $_POST['password']);
        $login = $_POST['login'];

        $sql = "SELECT id FROM lb_users WHERE login='$login' AND password='$password'";

        $req = App::query($sql);
        if($req->rowCount())
        {
            $_SESSION['connected'] = true;
            $_SESSION['login'] = $login;

            self::$message = "Vous êtes maintenant connecté";
        }
        else
            self::$erreur = "Mauvais identifiant ou mot de passe";
    }
    public static function isConnected()
    {
        return self::$connected;
    }

    public static function getLogin()
    {
        return self::$login;
    }
    public static function getId()
    {
        $login = $_SESSION['login'];
        $requete = App::query("SELECT ID FROM lb_users WHERE login ='$login'");
        $reponse = $requete->fetch();
        $id = $reponse['ID'];
        return $id;
    }

    private static function setLogin()
    {
        if($new_login = htmlspecialchars($_POST['new_login']))
        {
            self::$login = $new_login;
            return true;
        }
        else
        {
            self::$erreur = "L'identifiant est invalide !";
        }
    }
    private static function setPassword()
    {
        if($_POST['new_password'] == $_POST['password_rep'])
        {
            self::$password = hash('sha512', $_POST['new_password']);
            return true;
        }
        else
        {
            self::$erreur = "Les mots de passe ne correspondent pas !";
        }
    }
    private static function setEmail()
    {
        if($new_email = filter_var($_POST['new_email'], FILTER_VALIDATE_EMAIL))
        {
            self::$email = $new_email;
            return true;
        }
        else
        {
            self::$erreur = "L'adresse mail est invalide !";
        }
    }
    private static function testUser()
    {
        $reponse = App::query('SELECT login FROM lb_users WHERE login = "' . self::$login . '" ');
        $login = $reponse->fetch();

        $reponse = App::query('SELECT email FROM lb_users WHERE email = "' . self::$email . '" ');
        $mail = $reponse->fetch();


        if (strtolower($_POST['new_login']) == strtolower($login['login']))
        {
            self::$erreur = "Ce nom d'utilisateur est déjà utilisé.";
            return false;
        }
        elseif (strtolower($_POST['new_email']) == strtolower($mail['email']))
        {
            self::$erreur = "Cette adresse mail est déjà utilisée.";
            return false;
        }
        else
            return true;
    }
}
