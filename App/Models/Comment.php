<?php

namespace App\Models;

use MF\Model\Model;

class Comment extends Model
{
    public $commentId;
    public $postId;
    public $userId;
    public $commentContent;
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

    public function __construct($db){
        parent::__construct($db);
    }
    public function insertComment()
    {
        try {
            $query = "INSERT INTO post_comments (post_id, user_id, comment_content) VALUES (:post_id, :user_id, :comment_content)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':post_id', $this->postId);
            $stmt->bindParam(':user_id', $this->userId);
            $stmt->bindParam(':comment_content', $this->commentContent);
            $stmt->execute();

            return true;
        } catch (\PDOException $erro) {
            echo "ERROR INSERT COMMENT: " . $erro->getMessage();
            throw $erro;
        }
    }

    public function listPostComment()
    {
        try {

            $query = "SELECT c.comment_id, c.user_id, c.comment_content, c.comment_date, u.username, u.name, u.user_image
            FROM post_comments AS c
            LEFT JOIN users AS u ON (c.user_id = u.user_id)
            WHERE c.post_id = :post_id
            ORDER BY c.comment_date DESC";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':post_id', $this->postId, \PDO::PARAM_INT);
            $stmt->execute();

            $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($comments)) {
                return []; 
            }

            return $comments;
        } catch (\PDOException $erro) {
            echo "ERROR SELECT COMMENTS: " . $erro->getMessage();
            throw $erro;
        }
    }

    public function deletePostComment() {

        try {
            $countQuery = "DELETE FROM post_comments WHERE post_id = :post_id";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->bindValue(':post_id', $this->postId);
            $countStmt->execute();
            return true;
        } catch (\PDOException $erro) {
            echo "ERROR DELETE POSTS: " . $erro->getMessage();
            throw $erro;
        }
    }

    public function deleteComment()
    {
        try {
            $query = "DELETE FROM post_comments WHERE comment_id = :comment_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':comment_id', $this->commentId);
            $stmt->execute();

            return true;
        } catch (\PDOException $erro) {
            echo "ERROR DELETE COMMENT: " . $erro->getMessage();
            throw $erro;
        }
    }

}