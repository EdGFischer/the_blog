<?php

namespace App\Models;

use MF\Model\Model;

class FollowerManager extends Model
{
    public $userId;
    public $followerId;
    public $followingId;

    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function followUser() {
        try {
            $query = "INSERT INTO followers (follower_id, following_id) VALUES (:follower_id, :following_id)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':follower_id', $this->followerId);
            $stmt->bindValue(':following_id', $this->followingId);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Erro ao seguir usuário: " . $e->getMessage();
        }
    }

    public function unfollowUser() {
        try {
            $query = "DELETE FROM followers WHERE follower_id = :follower_id AND following_id = :following_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':follower_id', $this->followerId);
            $stmt->bindValue(':following_id', $this->followingId);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Erro ao deixar de seguir usuário: " . $e->getMessage();
        }
    }

    public function getFollowers() {
        try {
            $query = "SELECT u.username, u.user_id, u.name, u.user_image FROM users u
                      JOIN followers f ON u.user_id = f.follower_id
                      WHERE f.following_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo "Erro ao obter seguidores: " . $e->getMessage();
            return [];
        }
    }

    public function getFollowing() {
        try {
            $query = "SELECT u.username, u.user_id, u.name, u.user_image FROM users u
                      JOIN followers f ON u.user_id = f.following_id
                      WHERE f.follower_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo "Erro ao obter quem o usuário está seguindo: " . $e->getMessage();
            return [];
        }
    }
    public function getTotalFollowersCount() {
        try {
            $query = "SELECT COUNT(*) FROM followers WHERE following_id = :userId";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':userId', $this->userId, \PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            return $count;
        } catch (\PDOException $e) {
            echo "Erro ao obter a contagem total de seguidores: " . $e->getMessage();
            return false;
        }
    }
    
    public function getTotalFollowingCount() {
        try {
            $query = "SELECT COUNT(*) FROM followers WHERE follower_id = :userId";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':userId', $this->userId, \PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->fetchColumn();
    
            return $count;
        } catch (\PDOException $e) {
            echo "Erro ao obter a contagem total de usuários seguidos: " . $e->getMessage();
            return false;
        }
    }

    public function isFollowing() {
        try {
            $query = "SELECT COUNT(*) FROM followers WHERE follower_id = :follower_id AND following_id = :following_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':follower_id', $this->followerId);
            $stmt->bindValue(':following_id', $this->followingId);
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            return ($count > 0);
        } catch (\PDOException $e) {
            echo "Erro ao verificar se um usuário está seguindo o outro: " . $e->getMessage();
            return false;
        }
    }
}