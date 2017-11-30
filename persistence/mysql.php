<?php

class User {
    public $id;
    public $username;
    public $hashedPass;
    public $salt;
    public $role;
    public $isActive;
}

class Request {
    public $id;
    public $userId;
    public $created;
    public $class;
    public $drives;
    public $operatingSystem;
    public $other;
    public $status;
}

function _getConnection() {
    $dsn = 'mysql:host=localhost;dbname=fsr';
    $username = 'root';
    $password = 'root';

    return new \PDO($dsn, $username, $password);
}

function _generatePassword($plaintext_pass, $salt) {
    return hash('sha512', $salt . $plaintext_pass);
}


function verifyPassword($plaintext, $salt, $hashed) {
    return hash('sha512', $salt . $plaintext) == $hashed;
}

function persistUser($user) {
    if (!isset($user->id) || $user->id == null)
        return _insertUser($user);
    else
        return _updateUser($user);
}

function _insertUser($user) {
    $db = _getConnection();
    $query = "INSERT INTO `users2ElectricBoogaloo` (username, password, salt, is_active, role) VALUES (:Username, :Pass, :Salt, :Is_Active, :Role)";
    $statement = $db->prepare($query);

    $statement->bindValue(':Username', $user->username);
    $statement->bindValue(':Pass', $user->hashedPass);
    $statement->bindValue(':Salt', $user->salt);
    $statement->bindValue(':Is_Active', $user->isActive);
    $statement->bindValue(':Role', $user->role);

    $success = $statement->execute();
    $statement->closeCursor();
    return $db->lastInsertId();
}

function _updateUser($user) {
    $db = _getConnection();
    $query = "UPDATE `users2ElectricBoogaloo` SET `username`=:Username, `password`=:Pass, `salt`=:Salt, `is_active`=:Is_Active, `role`=:Role WHERE `id`=:Id";
    $statement = $db->prepare($query);

    $statement->bindValue(':Username', $user->username);
    $statement->bindValue(':Pass', $user->hashedPass);
    $statement->bindValue(':Salt', $user->salt);
    $statement->bindValue(':Is_Active', $user->isActive);
    $statement->bindValue(':Role', $user->role);
    $statement->bindValue(':Id', $user->id);

    $success = $statement->execute();
    $statement->closeCursor();

    return $user->id;
}

function _userFromRow($result) {
    $user = new \User();

    $user->id = $result['id'];
    $user->username = $result['username'];
    $user->hashedPass = $result['password'];
    $user->salt = $result['salt'];

    if ($result['is_active'] > 0)
        $user->isActive = true;
    else
        $user->isActive = false;

    $user->role = $result['role'];

    return $user;
}

function getUserById($id) {
    $db  = _getConnection();
    $query = "SELECT `id`, `username`, `password`, `salt`, `is_active`, `role` FROM `users2ElectricBoogaloo` WHERE id=:Id LIMIT 1";

    $statement = $db->prepare($query);
    $statement->bindValue(':Id', $id);
    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    if ($result) {
        return _userFromRow($result);
    } else {
        return null;
    }
}

function getReducedUserById($id) {
    $db  = _getConnection();
    $query = "SELECT SELECT `id`, `username`, `is_active`, `role` FROM `users2ElectricBoogaloo` WHERE id=:Id LIMIT 1";

    $statement = $db->prepare($query);
    $statement->bindValue(':Id', $id);
    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    if (count($result) > 0) {
        return _userFromRow($result);
    } else {
        return null;
    }
}

function getUserByUsername($username) {
    $db  = _getConnection();
    $query = "SELECT `id`, `username`, `password`, `salt`, `is_active`, `role` FROM `users2ElectricBoogaloo` WHERE `username`=:Username LIMIT 1";

    $statement = $db->prepare($query);
    $statement->bindValue(':Username', $username);
    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    if ($result) {
        return _userFromRow($result);
    } else {
        return null;
    }
}

function deleteUser($id) {
    $db = _getConnection();
    $statement = $db->prepare("DELETE FROM `users2ElectricBoogaloo` WHERE `id`=:Id");
    $statement->bindValue(":Id", $id);
    $statement->execute();
    $statement->closeCursor();
}

function getAllUsers() {
    $db  = _getConnection();
    $query = "SELECT `id`, `username`, `is_active`, `role` FROM `users2ElectricBoogaloo`";

    $statement = $db->prepare($query);
    $statement->execute();
    $results = $statement->fetchAll();
    $statement->closeCursor();

    $parsedResults = array();
    foreach ($results as $result) {
        array_push($parsedResults, _userFromRow($result));
    }
    return $parsedResults;
}

function _requestFromRow($row) {
    $request = new \Request();

    $request->id = $row['id'];
    $request->userId = $row['user_id'];
    $request->created = $row['created'];
    $request->class = $row['class'];
    $request->drives = $row['drives'];
    $request->operatingSystem = $row['operating_system'];
    $request->other = $row['other'];
    $request->status = $row['status'];

    return $request;
}

function getRequestById($id) {
    $db  = _getConnection();
    $query = "SELECT `id`, `user_id`, `created`, `class`, `drives`, `operating_system`, `other`, `status` FROM `request` WHERE `id`=:Id";

    $statement = $db->prepare($query);
    $statement->bindValue(':Id', $id);
    $statement->execute();
    $result = $statement->fetch();
    $statement->closeCursor();

    if (count($result) > 0) {
        return _requestFromRow($result);
    } else {
        return null;
    }
}

function getRequestsForUser($userId) {
    $db  = _getConnection();
    $query = "SELECT `id`, `user_id`, `created`, `class`, `drives`, `operating_system`, `other`, `status` FROM `request` WHERE `user_id`=:UserId ORDER BY `created` DESC ";

    $statement = $db->prepare($query);
    $statement->bindValue(':UserId', $userId);
    $statement->execute();

    $results = $statement->fetchAll();
    $statement->closeCursor();

    $parsedResults = array();
    foreach ($results as $result) {
        array_push($parsedResults, _requestFromRow($result));
    }

    return $parsedResults;
}

function getRequestsForUserByStatus($userId, $status) {
    $db  = _getConnection();
    $query = "SELECT `id`, `user_id`, `created`, `class`, `drives`, `operating_system`, `other`, `status` FROM `request` WHERE `user_id`=:UserId AND `status`=:Status ORDER BY `created` DESC";

    $statement = $db->prepare($query);
    $statement->bindValue(':UserId', $userId);
    $statement->bindValue(':Status', $status);
    $statement->execute();

    $results = $statement->fetchAll();
    $statement->closeCursor();

    $parsedResults = array();
    foreach ($results as $result) {
        array_push($parsedResults, _requestFromRow($result));
    }

    return $parsedResults;
}

function getRequestsByStatus($status) {
    $db  = _getConnection();
    $query = "SELECT `id`, `user_id`, `created`, `class`, `drives`, `operating_system`, `other`, `status` FROM `request` WHERE `status`=:Status ORDER BY `created` DESC";

    $statement = $db->prepare($query);
    $statement->bindValue(':Status', $status);
    $statement->execute();

    $results = $statement->fetchAll();
    $statement->closeCursor();

    $parsedResults = array();
    foreach ($results as $result) {
        array_push($parsedResults, _requestFromRow($result));
    }

    return $parsedResults;
}

function getAllRequests() {
    $db  = _getConnection();
    $query = "SELECT `id`, `user_id`, `created`, `class`, `drives`, `operating_system`, `other`, `status` FROM `request` ORDER BY `created` DESC";

    $statement = $db->prepare($query);
    $statement->execute();

    $results = $statement->fetchAll();
    $statement->closeCursor();

    $parsedResults = array();
    foreach ($results as $result) {
        array_push($parsedResults, _requestFromRow($result));
    }

    return $parsedResults;
}

function _insertRequest($request) {
    $db = _getConnection();
    $query = "INSERT INTO `request` (user_id, class, drives, operating_system, other, status, created) VALUES (:UserId, :Class, :Drives, :OperatingSystem, :Other, :Status, NOW())";
    $statement = $db->prepare($query);

    $statement->bindValue(':UserId', $request->userId);
    $statement->bindValue(':Class', $request->class);
    $statement->bindValue(':Drives', $request->drives);
    $statement->bindValue(':OperatingSystem', $request->operatingSystem);
    $statement->bindValue(':Other', $request->other);
    $statement->bindValue(':Status', $request->status);

    $success = $statement->execute();
    $statement->closeCursor();
    return $db->lastInsertId();
}

function _updateRequest($request) {
    $db = _getConnection();
    $query = "UPDATE `request` SET `class`=:Class, `drives`=:Drives, `operating_system`=:OperatingSystem, `other`=:Other ,`status`=:Status WHERE `id`=:Id";

    $statement = $db->prepare($query);
    $statement->bindValue(':Class', $request->class);
    $statement->bindValue(':Drives', $request->drives);
    $statement->bindValue(':OperatingSystem', $request->operatingSystem);
    $statement->bindValue(':Other', $request->other);
    $statement->bindValue(':Status', $request->status);
    $statement->bindValue(':Id', $request->id);

    $success = $statement->execute();
    $statement->closeCursor();

    return $request->id;
}

function persistRequest($request) {
    if (!isset($request->id) || $request->id == null) {
        return _insertRequest($request);
    } else {
        return _updateRequest($request);
    }
}

function deleteRequest($id) {
    $db  = _getConnection();
    $query = "DELETE FROM `request` WHERE `id`=:Id";

    $statement = $db->prepare($query);
    $statement->bindValue(':Id', $id);
    $success =$statement->execute();
    $statement->closeCursor();
}
