<?php

namespace App\Controllers;

//Os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;

//Os models
use App\Models\Produto;
use App\Models\Info;

class AppController extends Action
{
    public function home()
    {

        $this->validateAuthentication();

        $user = Container::getModel('user');
        $user->__set('username', $_SESSION['username']);
        $this->view->userData = $user->getUserData();
        
        $followerManager = Container::getModel('followerManager');
        $followerManager->__set('userId', $_SESSION['userId']);
        $this->view->followers = $followerManager->getTotalFollowersCount();
        $this->view->following = $followerManager->getTotalFollowingCount();

        $post = Container::getModel('post');
        $post->__set('userId', $_SESSION['userId']);
        $this->view->totalPosts = $post->getTotalUserPosts();


        $this->render('home', 'layout');
    }

    public function profile()
    {
        $this->validateAuthentication();

        $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $urlSegments = explode('/', trim($urlPath, '/'));
        $usernameFromUrl = end($urlSegments);

        $user = Container::getModel('user');
        $user->__set('username', $usernameFromUrl);
        $this->view->profileData = $user->getUserData();
        
        $post = Container::getModel('post');
        $post->__set('userId', $_SESSION['userId']);
        $this->view->totalPosts = $post->getTotalUserPosts();
        
        $followerManager = Container::getModel('followerManager');
        $followerManager->__set('userId',  $this->view->profileData['user_id']);
        $this->view->followers = $followerManager->getTotalFollowersCount();
        $this->view->following = $followerManager->getTotalFollowingCount();
        $this->view->followersList = $followerManager->getFollowers();
        $this->view->followingList = $followerManager->getFollowing();

        if($usernameFromUrl != $_SESSION['username']) {
            $followerManager->__set('followerId',   $_SESSION['userId']);
            $followerManager->__set('followingId', $this->view->profileData['user_id']);
            $this->view->isFollowing = $followerManager->isFollowing();
        }  else {
            $this->view->buttonFollow = FALSE;
        }      

        $this->render('profile', 'layout');
    }

    public function editProfile()
    {

        $this->validateAuthentication();
        $user = Container::getModel('user');
        $user->__set('username', $_SESSION['username']);
        $this->view->userData = $user->getUserData();
        $this->render('editProfile', 'layout');
    }

    public function searchUsers()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $user = Container::getModel('user');
        $user->__set('name', $data['name']);
        $user->__set('username', $data['username']);

        echo $user->listUsers();
    }
    public function newPost()
    {
        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('post');
        $post->__set('userId', $_SESSION['userId']);
        $post->__set('postContent', $data['postContent']);
        $post->__set('imagePost', $data['imagePost']);

        echo $post->registerPost();
    }

    public function loadPostsHome()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('post');
        $post->__set('userId', $_SESSION['userId']);
        echo $post->listPostHome($data['count']);
    }

    public function loadPostsUser()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('post');
        $post->__set('username', $data['username']);
        $post->__set('userId', $_SESSION['userId']);
        echo $post->listPostsUser($data['count']);
    }
    
    public function deletePost()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('post');
        $post->__set('postId', $data['postId']);
        $post->__set('userId', $_SESSION['userId']);
        echo $post->deletePost();
    }

    public function updateUsername()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $user = Container::getModel('user');
        $user->__set('userId', $_SESSION['userId']);
        $user->__set('username', $data['username']);
        $return =  $user->updateUsername();

        $conference = json_decode($return);

        if ($conference->status === 'success') {
            $_SESSION['username'] = $user->__get('username');
            echo $return;
        } else {
            echo $return;
        }
    }

    public function updateUser()
    {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $user = Container::getModel('user');
        $user->__set('userId', $_SESSION['userId']);
        $user->__set('name', $data['name']);
        $user->__set('birthDate', $data['birthDate']);
        $user->__set('email', $data['email']);
        echo $user->updateUser();
    }

    public function newUserImage() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('user');
        $post->__set('userId', $_SESSION['userId']);
        $post->__set('userImage', $data['userImage']);

        echo $post->newUserImage();

    }

    public function followUser() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('FollowerManager');
        $post->__set('followerId', $_SESSION['userId']);
        $post->__set('followingId', $data['userId']);

        echo $post->followUser();
    }

    public function unfollowUser() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('FollowerManager');
        $post->__set('followerId', $_SESSION['userId']);
        $post->__set('followingId', $data['userId']);

        echo $post->unfollowUser();
    }

    
    public function likePost() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('Post');
        $post->__set('userId', $_SESSION['userId']);
        $post->__set('postId', $data['postId']);

        echo $post->likePost();
    }
    
    public function unlikePost() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $post = Container::getModel('Post');
        $post->__set('userId', $_SESSION['userId']);
        $post->__set('postId', $data['postId']);

        echo $post->unlikePost();
    }
    
    public function sendComment() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $comment = Container::getModel('Comment');
        $comment->__set('userId', $_SESSION['userId']);
        $comment->__set('postId', $data['postId']);
        $comment->__set('commentContent', $data['commentContent']);

        echo $comment->insertComment();

    }

    public function listPostComment() {

        $this->validateAuthentication();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $comment = Container::getModel('Comment');
        $comment->__set('postId', $data['postId']);

        $return = array(
            "comments" => $comment->listPostComment(),
            "sessionId" => $_SESSION['userId']
        );

        echo json_encode($return);

    }

    public function deleteComment() {

        $this->validateAuthentication();

        $data = json_decode(file_get_contents('php://input'), true);
        $comment = Container::getModel('Comment');
        $comment->__set('commentId', $data['commentId']);

        echo json_encode($comment->deleteComment());

    }

    public function validateAuthentication()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (!isset($_SESSION['userId']) || $_SESSION['userId'] == '' || !isset($_SESSION['username']) || $_SESSION['username'] == '') {

            header('Location: /?login=noAuthentication');
        }
    }
}
