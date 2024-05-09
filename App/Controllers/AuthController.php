<?php

namespace App\Controllers;

//Os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

class AuthController extends Action
{


    public function authenticate()
    {
        $usuario = Container::getModel('User');

        $data = json_decode(file_get_contents('php://input'), true);
        $usuario->__set('username', $data['username']);
        $usuario->__set('password', $data['password']);

        $return = $usuario->authenticate();
        $conference = json_decode($return);

        if ($conference->status === 'success') {
            session_start();
            $_SESSION['userId'] = $usuario->__get('userId');
            $_SESSION['username'] = $usuario->__get('username');
            echo $return;
        } else {
            echo $return;
        }
    }

    public function logOff()
    {
        session_start();
        session_destroy();
        header('Location: /');
    }
}

