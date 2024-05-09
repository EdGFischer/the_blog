<?php

namespace App\Models;

use MF\Model\Model;

class Post extends Model
{

    public $postId;
    public $userId;
    public $imageId;
    public $commentId;
    public $postContent;
    public $commentDate;
    public $imagePost;
    public $publishDate;


    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function registerPost()
    {
        $this->db->beginTransaction();

        try {
            $this->__set('postId', $this->insertPost());

            $this->insertPostImage();

            $this->db->commit();

            $response = array('status' => 'success', 'message' => 'Postagem realizada com sucesso!', 'data' => []);
            return json_encode($response);
        } catch (\PDOException $erro) {
            $this->db->rollBack();
            echo "ERROR USER REGISTER: ".$erro->getMessage();
        }
    }

    public function insertPost()
    {
        try {
            $query = "INSERT INTO posts(user_id, post_content) values (:user_id, :post_content)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->bindValue(':post_content', $this->postContent);
            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (\PDOException $erro) {
            echo "ERROR INSERT POST: ".$erro->getMessage();
            throw $erro;
        }
    }

    private function insertPostImage()
    {
        try {
            $uniqueFileName = time().'_'.uniqid();
            $localDirectory = "../public/data/images/".$this->userId."/";
            if (!is_dir($localDirectory)) {
                mkdir($localDirectory, 0777, true);
            }

            $ImageWithoutPrefixes = preg_replace('/^data:image\/\w+;base64,/', '', $this->imagePost);
            $imageData = base64_decode($ImageWithoutPrefixes);
            $imageExtension = 'jpeg';

            $imageName = $uniqueFileName."-".$this->postId."-".$this->userId.'.'.$imageExtension;
            $imagePath = $localDirectory.$imageName;

            if (file_put_contents($imagePath, $imageData) === false) {
                $response = array('status' => 'error', 'message' => 'Problema ao salvar a imagem!', 'data' => []);
                return json_encode($response);
            }

            $imageQuery = "INSERT INTO post_images(post_id, image_path) VALUES (:post_id, :image_path)";
            $imageStmt = $this->db->prepare($imageQuery);
            $imageStmt->bindValue(':post_id', $this->postId);
            $imageStmt->bindValue(':image_path', $imageName);
            $imageStmt->execute();

            return null;

        } catch (\PDOException $erro) {
            $response = array('status' => 'error', 'message' => 'Problema ao salvar a imagem no servidor!', 'data' => []);
            return json_encode($response);
        }
    }

    public function listPostHome($offset)
    {
        try {
            $count = 10;

            $query = "SELECT p.post_id, p.user_id, p.post_content, p.publish_date, i.image_id, i.image_path, u.username, u.name, u.user_image,
            CASE WHEN l.user_id IS NOT NULL THEN 1 ELSE 0 END AS has_liked
            FROM posts AS p
            LEFT JOIN post_images AS i ON (p.post_id = i.post_id)
            LEFT JOIN users AS u ON (p.user_id = u.user_id)
            LEFT JOIN (
                      SELECT post_id, 1 AS user_liked, user_id
                      FROM post_likes
                      WHERE user_id = :user_id
                ) AS l ON (p.post_id = l.post_id)
            WHERE p.user_id = :user_id OR p.user_id IN (
                SELECT following_id FROM followers WHERE follower_id = :user_id
            )
            ORDER BY p.publish_date DESC
            LIMIT :count OFFSET :offset";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(':user_id', $this->userId, \PDO::PARAM_INT);
            $stmt->bindValue(':count', $count, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach($posts as $key => $post) {

                $posts[$key]['sessionId'] = $_SESSION['userId'];

                $comment = new Comment($this->db);
                $comment->__set('postId', $posts[$key]['post_id']);
                $posts[$key]['comments'] = $comment->listPostComment();
            }

            return json_encode($posts);
        } catch (\PDOException $erro) {
            echo "ERROR SELECT POST: ".$erro->getMessage();
            throw $erro;
        }
    }
    
    public function listPostsUser($offset)
    {
        try {
            $count = 10;
    
            $query = "SELECT p.post_id, p.user_id, p.post_content, p.publish_date, i.image_id, i.image_path, u.username, u.name, u.user_image,
                CASE WHEN l.user_id IS NOT NULL THEN 1 ELSE 0 END AS has_liked
                FROM posts AS p
                LEFT JOIN post_images AS i ON (p.post_id = i.post_id)
                LEFT JOIN users AS u ON (p.user_id = u.user_id)
                LEFT JOIN (
                      SELECT post_id, 1 AS user_liked, user_id
                      FROM post_likes
                      WHERE user_id = :user_id
                ) AS l ON (p.post_id = l.post_id)
                WHERE u.username = :username
                ORDER BY p.publish_date DESC
                LIMIT :count OFFSET :offset";
    
            $stmt = $this->db->prepare($query);
    
            $stmt->bindValue(':username', $this->username, \PDO::PARAM_STR);
            $stmt->bindValue(':user_id', $this->userId, \PDO::PARAM_STR);
            $stmt->bindValue(':count', $count, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
    
            $userPosts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
            foreach($userPosts as $key => $post) {

                $userPosts[$key]['sessionId'] = $_SESSION['userId'];

                $comment = new Comment($this->db);
                $comment->__set('postId', $userPosts[$key]['post_id']);
                $userPosts[$key]['comments'] = $comment->listPostComment();
            }

            return json_encode($userPosts);
        } catch (\PDOException $erro) {
            echo "ERROR SELECT USER POSTS: ".$erro->getMessage();
            throw $erro;
        }
    }

    public function deletePost() {

        try {

            $this->db->beginTransaction();
            
            $comment = new Comment($this->db);
            $comment->__set('postId', $this->postId);
            $comment->deletePostComment();


            $this->deletePostImage();
            $this->deletePostLikes();
            
            $countQuery = "DELETE FROM posts WHERE post_id = :post_id";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindValue(':post_id', $this->postId);
            $countStmt->execute();

            $this->db->commit();

            return true;
        } catch (\PDOException $erro) {
            echo "ERROR DELETE POSTS: " . $erro->getMessage();
            $this->db->rollBack();
            throw $erro;
        }
    }
    private function deletePostImage()
    {
    try {
        $imageQuery = "SELECT image_path FROM post_images WHERE post_id = :post_id";
        $imageStmt = $this->db->prepare($imageQuery);
        $imageStmt->bindValue(':post_id', $this->postId);
        $imageStmt->execute();
        $imageData = $imageStmt->fetch();

        if ($imageData) {
            $imagePath = "../public/data/images/".$this->userId."/".$imageData['image_path'];

            // Excluímos o arquivo de imagem
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Excluímos a entrada do banco de dados
            $deleteQuery = "DELETE FROM post_images WHERE post_id = :post_id";
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindValue(':post_id', $this->postId);
            $deleteStmt->execute();
        }

        return null;

    } catch (\PDOException $erro) {
        echo "ERROR DELETE POSTS IMAGE: " . $erro->getMessage();
        throw $erro;
    }
}

    public function getTotalUserPosts()
    {
        try {
            $countQuery = "SELECT COUNT(*) as total_posts FROM posts WHERE user_id = :user_id";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindValue(':user_id', $this->userId);
            $countStmt->execute();
            return $countStmt->fetch(\PDO::FETCH_ASSOC)['total_posts'];
        } catch (\PDOException $erro) {
            echo "ERROR SELECT TOTAL USER POSTS: " . $erro->getMessage();
            throw $erro;
        }
    }

    public function likePost() {
        
        try {
            $query = "INSERT INTO post_likes (post_id, user_id) VALUES (:post_id, :user_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':post_id', $this->postId);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();
            
            return true;

        } catch (\PDOException $erro) {
            echo "ERROR LIKE POST: " . $erro->getMessage();
            throw $erro;
        }
    }

    public function unlikePost() {
        
        try {
            $query = "DELETE FROM post_likes WHERE post_id = :post_id AND user_id = :user_id";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':post_id', $this->postId);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();

            return true;

        } catch (\PDOException $erro) {
            echo "ERROR INLIKE POST: " . $erro->getMessage();
            throw $erro;
        }
    }

    
    public function deletePostLikes() {

        try {
            $countQuery = "DELETE FROM post_likes WHERE post_id = :post_id";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindValue(':post_id', $this->postId);
            $countStmt->execute();
            return true;
        } catch (\PDOException $erro) {
            echo "ERROR DELETE POSTS: " . $erro->getMessage();
            throw $erro;
        }
    }
    
}
