<?php

namespace App;

use MF\Init\Bootstrap;

class Route extends Bootstrap
{

    protected function initRoutes()
    {

        /* Index Routes*/
        $routes['index'] = array(
            'route' => '/',
            'controller' => 'IndexController',
            'action' => 'index'
        );

        $routes['register'] = array(
            'route' => '/register',
            'controller' => 'IndexController',
            'action' => 'register'
        );

        $routes['newRegister'] = array(
            'route' => '/newRegister',
            'controller' => 'IndexController',
            'action' => 'newRegister'
        );

        /* Authorization Routes*/
        $routes['authenticate'] = array(
            'route' => '/authenticate',
            'controller' => 'AuthController',
            'action' => 'authenticate'
        );

        
        /* App - Navbar */
        $routes['searchUsers'] = array(
            'route' => '/searchUsers',
            'controller' => 'AppController',
            'action' => 'searchUsers'
        );
        
        $routes['logOff'] = array(
            'route' => '/logOff',
            'controller' => 'AuthController',
            'action' => 'logOff'
        );

        /* App */
        $routes['home'] = array(
            'route' => '/home',
            'controller' => 'AppController',
            'action' => 'home'
        );
        
        $routes['profile'] = array(
            'route' => '/profile/([\w\s_]+)',
            'controller' => 'AppController',
            'action' => 'profile'
        );
        
        $routes['newPost'] = array(
            'route' => '/newPost',
            'controller' => 'AppController',
            'action' => 'newPost'
        );

        $routes['loadPostsHome'] = array(
            'route' => '/loadPostsHome',
            'controller' => 'AppController',
            'action' => 'loadPostsHome'
        );

        $routes['loadPostsUser'] = array(
            'route' => '/loadPostsUser',
            'controller' => 'AppController',
            'action' => 'loadPostsUser'
        );

        $routes['deletePost'] = array(
            'route' => '/deletePost',
            'controller' => 'AppController',
            'action' => 'deletePost'
        );

        $routes['editProfile'] = array(
            'route' => '/editProfile',
            'controller' => 'AppController',
            'action' => 'editProfile'
        );

        $routes['updateUsername'] = array(
            'route' => '/updateUsername',
            'controller' => 'AppController',
            'action' => 'updateUsername'
        );

        $routes['updateUser'] = array(
            'route' => '/updateUser',
            'controller' => 'AppController',
            'action' => 'updateUser'
        );

        $routes['newUserImage'] = array(
            'route' => '/newUserImage',
            'controller' => 'AppController',
            'action' => 'newUserImage'
        );
        
        $routes['followUser'] = array(
            'route' => '/followUser',
            'controller' => 'AppController',
            'action' => 'followUser'
        );
        
        $routes['unfollowUser'] = array(
            'route' => '/unfollowUser',
            'controller' => 'AppController',
            'action' => 'unfollowUser'
        );

        $routes['likePost'] = array(
            'route' => '/likePost',
            'controller' => 'AppController',
            'action' => 'likePost'
        );
        
        $routes['unlikePost'] = array(
            'route' => '/unlikePost',
            'controller' => 'AppController',
            'action' => 'unlikePost'
        );

        $routes['sendComment'] = array(
            'route' => '/sendComment',
            'controller' => 'AppController',
            'action' => 'sendComment'
        );

        $routes['listPostComment'] = array(
            'route' => '/listPostComment',
            'controller' => 'AppController',
            'action' => 'listPostComment'
        );

        $routes['deleteComment'] = array(
            'route' => '/deleteComment',
            'controller' => 'AppController',
            'action' => 'deleteComment'
        );

        $this->setRoutes($routes);

    }


}

?>