<?php

namespace App\Models;

use MF\Model\Model;

class User extends Model
{

    public $userId;
    public $username;
    public $name;
    public $birthDate;
    public $email;
    public $password;
    public $userImage;
    public $register_date;

    public function __get($atributo)
    {
        return $this->$atributo;
    }

    public function __set($atributo, $valor)
    {
        $this->$atributo = $valor;
    }

    public function registerUser()
    {
        try {
            $validationResponse = $this->validateData();

            if ($validationResponse !== null) {
                return $validationResponse;
            }

            if ($this->verifyEmail() > 0) {
                $response = array('status' => 'error', 'message' => 'E-mail já cadastrado', 'data' => ['email', 'confirmEmail']);
                return json_encode($response);
            }

            if ($this->verifyUsername() > 0) {
                $response = array('status' => 'error', 'message' => 'Nome de usuário já cadastrado', 'data' => ['email', 'confirmEmail']);
                return json_encode($response);
            }

            $query = "INSERT INTO users(username, name, email, password) values (:username, :name, :email, :password)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', strtolower($this->username));
            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':password', md5($this->password));
            $stmt->execute();

            $response = array('status' => 'success', 'message' => 'Usuário registrado com sucesso!', 'data' => []);

            return json_encode($response);
        } catch (\PDOException $erro) {
            echo "ERROR USER REGISTER: ".$erro->getMessage();
        }
    }

    public function updateUsername() {
        try {

            if (empty($this->username) || strlen($this->username) <= 3 || strlen($this->username) >= 30) {
                $response = array('status' => 'error', 'message' => 'O nome de usuário deve ter entre 3 a 30 caracteres', 'data' => ['username']);
                return json_encode($response);

            }

            if ($this->verifyUsername() > 0) {
                $response = array('status' => 'error', 'message' => 'Nome de usuário já cadastrado', 'data' => ['email', 'confirmEmail']);
                return json_encode($response);
            }

            $query = "UPDATE users SET username = :username WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', strtolower($this->username));
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();

            $response = array('status' => 'success', 'message' => 'Usuário atualizado com sucesso!', 'data' => []);

            return json_encode($response);
        } catch (\PDOException $erro) {
            echo "ERROR USER REGISTER: ".$erro->getMessage();
        }
    }

    public function updateUser() {
        try {

            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $response = array('status' => 'error', 'message' => 'Email inválido', 'data' => ['email']);
                return json_encode($response);
            }

            if (empty($this->name)) {
                $response = array('status' => 'error', 'message' => 'Preencha um nome', 'data' => ['name']);
                return json_encode($response);
            }

            $query = "UPDATE users SET name = :name, birth_date = :birth_date, email = :email WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':name', $this->name);
            $stmt->bindValue(':birth_date', $this->birthDate);
            $stmt->bindValue(':email', $this->email);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();

            $response = array('status' => 'success', 'message' => 'Usuário atualizado com sucesso!', 'data' => []);

            return json_encode($response);
        } catch (\PDOException $erro) {
            echo "ERROR USER REGISTER: ".$erro->getMessage();
        }
    }

    public function getUserData() {
        try {
            $query = "SELECT user_id, username, name, email, birth_date, user_image FROM users WHERE username LIKE :username";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', $this->username);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $result;
        } catch (\PDOException $erro) {
            echo "ERROR LIST USER: ".$erro->getMessage();
        }
    }

    public function listUsers()
    {
        try {
            $query = "SELECT user_id, username, name FROM users WHERE username LIKE :username OR name LIKE :name ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', '%'.$this->username.'%');
            $stmt->bindValue(':name', '%'.$this->name.'%');
            $stmt->execute();
            
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $jsonResult = json_encode($results);

            return $jsonResult;
        } catch (\PDOException $erro) {
            echo "ERROR LIST USER: ".$erro->getMessage();
        }
    }

    public function newUserImage()
    {
        $this->db->beginTransaction();

        try {
            $this->__set('userImage', $this->insertUserImage());
            
            $query = "UPDATE users SET user_image = :user_image WHERE user_id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':user_image', $this->userImage);
            $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();

            $this->db->commit();

            $response = array('status' => 'success', 'message' => 'Imagem salva com sucesso!', 'data' => []);
            return json_encode($response);
        } catch (\PDOException $erro) {
            $this->db->rollBack();
            echo "ERROR USER REGISTER: ".$erro->getMessage();
        }
    }

    private function insertUserImage()
    {
        try {
            $uniqueFileName = time().'_'.uniqid();
            $localDirectory = "../public/data/images/".$this->userId."/profile/";
            if (!is_dir($localDirectory)) {
                mkdir($localDirectory, 0777, true);
            }

            $ImageWithoutPrefixes = preg_replace('/^data:image\/\w+;base64,/', '', $this->userImage);
            $imageData = base64_decode($ImageWithoutPrefixes);
            $imageExtension = 'jpeg';

            $imageName = $uniqueFileName."-".$this->userId.'.'.$imageExtension;
            $imagePath = $localDirectory.$imageName;

            if (file_put_contents($imagePath, $imageData) === false) {
                $response = array('status' => 'error', 'message' => 'Problema ao salvar a imagem!', 'data' => []);
                return json_encode($response);
            }

            return $imageName;

        } catch (\PDOException $erro) {
            $response = array('status' => 'error', 'message' => 'Problema ao salvar a imagem no servidor!', 'data' => []);
            return json_encode($response);
        }
    }

    public function verifyEmail()
    {
        try {
            $query = "SELECT COUNT('user_id') FROM users WHERE LOWER(email) = LOWER(:email)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':email', strtolower($this->email));
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\PDOException $erro) {
            echo "ERROR VERIFY E-MAIL TO REGISTER USER: ".$erro->getMessage();
        }
    }

    public function verifyUsername()
    {
        try {
            $sqlUserId =  empty($this->userId) ? "" :  " AND user_id != :user_id";
            $query = "SELECT COUNT('user_id') FROM users WHERE LOWER(username) = LOWER(:username)" . $sqlUserId;
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', strtolower($this->username));
            empty($this->userId) ? "" : $stmt->bindValue(':user_id', $this->userId);
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (\PDOException $erro) {
            echo "ERROR VERIFY USERNAME TO REGISTER USER: ".$erro->getMessage();
        }
    }

    public function validateData()
    {
        $response = array('status' => '', 'message' => [], 'data' => []);

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $response['status'] = 'error';
            $response['message'] = 'Email inválido';
            array_push($response['data'], 'email', 'confirmEmail');
        }

        if ($this->email !== $this->confirmEmail) {
            $response['status'] = 'error';
            $response['message'] = 'Os emails não correspondem';
            array_push($response['data'], 'confirmEmail');
        }

        if (empty($this->password)) {
            $response['status'] = 'error';
            $response['message'] = 'Preencha uma senha';
            array_push($response['data'], 'confirmPassword', 'password');
        }

        if ($this->password !== $this->confirmPassword) {
            $response['status'] = 'error';
            $response['message'] = 'As senhas não correspondem';
            array_push($response['data'], 'confirmPassword', 'password');
        }

        if (empty($this->name)) {
            $response['status'] = 'error';
            $response['message'] = 'Preencha um nome';
            array_push($response['data'], 'name');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->username) || strpos($this->username, ' ') !== false) {
            $response['status'] = 'error';
            $response['message'] = 'O nome de usuário não deve conter caracteres especiais';
            array_push($response['data'], 'username');
        }

        if (empty($this->username) || strlen($this->username) <= 3 || strlen($this->username) >= 30) {
            $response['status'] = 'error';
            $response['message'] = 'O nome de usuário deve ter entre 3 a 30 caracteres';
            array_push($response['data'], 'username');
        }

        if (count($response['data']) == 0) {
            return null;
        } else {
            return json_encode($response);
        }

    }

    public function authenticate()
    {

        $query = "SELECT user_id, username, name, email FROM users WHERE username = :username AND password = :password";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':username', $this->username);
        $stmt->bindValue(':password', md5($this->password));
        $stmt->execute();

        $usuario = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!empty($usuario['user_id']) && !empty($usuario['username'])) {
            $this->__set('userId', $usuario['user_id']);
            $this->__set('username', $usuario['username']);

            $response = array('status' => 'success', 'message' => 'Login realizado com sucesso!');
            return json_encode($response);
        } else {
            $returnData = [];
            $returnMessage = '';
            if (empty($this->username) || $this->verifyUsername() == 0) {
                array_push($returnData, 'username', 'password');
                $returnMessage = 'Nome de usuário não encontrado';
            } else {
                array_push($returnData, 'password');
                $returnMessage = 'Senha não confere';
            }

            $response = array('status' => 'error', 'message' => $returnMessage, 'data' => $returnData);
            return json_encode($response);
        }
    }
}
