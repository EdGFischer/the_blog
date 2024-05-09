<?php

namespace App\Controllers;

//Os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

//Os models
use App\Models\Produto;
use App\Models\Info;

class IndexController extends Action
{


    public function index()
    {

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['userId']) || $_SESSION['userId'] == '' || !isset($_SESSION['username']) || $_SESSION['username'] == '') {
            $this->render('index', 'layout');
        } else {
            header('Location: /home');
        }
    }

    public function register()
    {

        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['userId']) || $_SESSION['userId'] == '' || !isset($_SESSION['username']) || $_SESSION['username'] == '') {
            $this->render('register', 'layout');
        } else {
            header('Location: /home');
        }

    }

    public function newRegister()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $user = Container::getModel('user');

        $user->__set('username', $data['username']);
        $user->__set('name', $data['name']);
        $user->__set('password', $data['password']);
        $user->__set('confirmPassword', $data['confirmPassword']);
        $user->__set('email', $data['email']);
        $user->__set('confirmEmail', $data['confirmEmail']);

        echo $user->registerUser();
    }
}
