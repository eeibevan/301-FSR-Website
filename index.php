<?php
require_once 'persistence/mysql.php';

function isLoggedIn() {
    return isset($_SESSION['userId']);
}

function isFaculty() {
    if (!isLoggedIn())
        return false;
    $user = getUserById($_SESSION['userId']);
    if ($user == null)
        return false;
    return $user->role === 'faculty';
}

function isFsr() {
    if (!isLoggedIn())
        return false;
    $user = getUserById($_SESSION['userId']);
    if ($user == null)
        return false;
    return $user->role === 'fsr';
}

if (isset($_GET['path']))
    $path = $_GET['path'];
else if (isset($_POST['path']))
    $path = $_POST['path'];
else
    $path = '/home';

session_start();

switch ($path) {
    case '/home':
        require_once 'home.php';
        break;
    case '/contact':
        require_once 'contact.php';
        break;
    case '/login':
        require_once 'login.php';
        break;
    case '/api/login':
        $username = $_POST['username'];
        $plainText = $_POST['password'];

        if (!isset($username)) {
            header(' ', true, 400);
            $resp['message'] = 'No Email Provided';
            echo json_encode($resp);
            die();
        }
        if (!isset($plainText)) {
            header(' ', true, 400);
            $resp['message'] = 'No Password Provided';
            echo json_encode($resp);
            die();
        }

        $user = getUserByUsername($username);
        if ($user == null || $user->id == null || !verifyPassword($plainText, $user->salt, $user->hashedPass)) {
            header(' ', true, 400);
            $resp['message'] = 'Email/Password Not Valid';
            echo json_encode($resp);
            die();
        }

        // Flags Valid Session
        $_SESSION['userId'] = $user->id;
        $resp['message'] = 'success';
        $resp['userId'] = $user->id;
        echo json_encode($resp);
        die();
        break;
    case '/logout':
        $_SESSION = array();
        session_destroy();
        require_once 'home.php';
        break;
    case '/api/requests':
        if (!isset($_SESSION['userId'])) {
            header(' ', true, 401);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }

        if (isset($_GET['id'])) {
            echo json_encode(getRequestById($_GET['id']));
            die();
        }

        if (isFsr()) {
            if(isset($_GET['status']))
                echo json_encode(getRequestsByStatus($_GET['status']));
            else
                echo json_encode(getAllRequests());
            die();
        }

        if(isset($_GET['status'])) {
            echo json_encode(getRequestsForUserByStatus($_SESSION['userId'], $_GET['status']));
        } else {
            echo json_encode(getRequestsForUser($_SESSION['userId']));
        }
        break;
    case '/api/requests/update':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }

        if (!isset($_POST['id'])) {
            header(' ', true, 404);
            $resp['message'] = 'Not Found';
            echo json_encode($resp);
            die();
        }

        $request = getRequestById($_POST['id']);
        if ($request == null) {
            header(' ', true, 404);
            $resp['message'] = 'Not Found';
            echo json_encode($resp);
            die();
        }

        if (!isFsr() && $request->id !== $_SESSION['userId']) {
            header(' ', true, 403);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }

        if (isset($_POST['driveClass']))
            $request->class = $_POST['driveClass'];

        if (isset($_POST['drives']))
            $request->drives = $_POST['drives'];

        if (isset($_POST['operatingSystem']))
            $request->operatingSystem = $_POST['operatingSystem'];

        if (isset($_POST['description']))
            $request->other = $_POST['description'];

        if (isset($_POST['status']))
            $request->status = $_POST['status'];

        $response['id'] = persistRequest($request);

        echo json_encode($response);
        die();
        break;
    case '/api/requests/create':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }

        $request = new \Request();
        $request->userId = $_SESSION['userId'];
        $request->class = $_POST['driveClass'];
        $request->drives = $_POST['drives'];
        $request->operatingSystem = $_POST['operatingSystem'];
        $request->other = $_POST['description'];
        $request->status = 'Open';

        $response['id'] = persistRequest($request);

        echo json_encode($response);
        header(' ', true, 201);
        break;
    case '/api/requests/delete':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }
        $request = getRequestById($_POST['id']);
        if ($request == null || $request->id == null) {
            header(' ', true, 401);
            die();
        }

        if (!isFsr() && $request->userId !== $_SESSION['userId']) {
            header(' ', true, 403);
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die();
        }

        deleteRequest($request->id);
        $resp['id'] = $_POST['id'];
        echo json_encode($resp);
        break;
    case '/api/user/add':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            $resp['message'] = "Not Logged In";
            json_encode($resp);
            die();
        } else if (!isFsr()) {
            header(' ', true, 403);
            $resp['message'] = "Not Authorised";
            json_encode($resp);
            die();
        }

        $resp['id'] = createUser($_POST['username'], $_POST['password'], $_POST['role'], 1);
        header(' ', true, 201);
        echo json_encode($resp);
        break;
    case '/api/user/update':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            $resp['message'] = "Not Logged In";
            json_encode($resp);
            die();
        } else if (!isFsr()) {
            header(' ', true, 403);
            $resp['message'] = "Not Authorised";
            json_encode($resp);
            die();
        }
        if (!isset($_POST['id'])) {
            header(' ', true, 404);
            $resp['message'] = "No ID Provided";
            json_encode($resp);
            die();
        }

        $user = getUserById($_POST['id']);
        if ($user == null) {
            header(' ', true, 404);
            $resp['message'] = "User Not Found";
            echo json_encode($resp);
            die();
        }

        if (isset($_POST['username']))
            $user->username = $_POST['username'];

        if (isset($_POST['password']))
            $user->hashedPass = _generatePassword($_POST['password'], $user->salt);

        if (isset($_POST['role']))
            $user->role = $_POST['role'];

        $response['id'] = persistUser($user);
        echo json_encode($response);
        break;
    case '/api/users':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }

        if (!isFsr()) {
            header(' ', true, 403);
            die();
        }

        if (isset($_GET['id'])) {
            echo json_encode(getUserById($_GET['id']));
            die();
        }

        echo json_encode(getAllUsers());
        die();
        break;
    case '/api/user/changePass':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        $currentUser = getUserById($_SESSION['userId']);
        if ($currentUser == null || $currentUser->id == null) {
            header(' ', true, 404);
            die();
        }
        if (!isset($_POST['password']) || strlen($_POST['password']) == 0) {
            header(' ', true, 400);
            $resp['message'] = 'Password Required';
            json_encode($resp);
            die();
        }

        $currentUser->hashedPass = _generatePassword($_POST['password'], $currentUser->salt);
        $resp['id'] = persistUser($currentUser);
        echo json_encode($resp);
        break;
    case '/user':
        if (!isset($_GET['id'])) {
            header(' ', true, 404);
            die();
        }
        $user = getUserById($_GET['id']);
        if ($user == null) {
            header(' ', true, 404);
            die();
        }
        if (!isFsr() && $user->id !== $_SESSION['userId']) {
            header(' ', true, 403);
            die();
        }
        require_once 'userForm.php';
        break;
    case '/user/all':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }

        if (!isFsr()) {
            header(' ', true, 403);
            die();
        }
        require_once 'allUsers.php';
        break;
    case '/userForm':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }

        if (!isFsr()) {
            header(' ', true, 403);
            die();
        }

        require_once 'userForm.php';
        break;
    case '/changePass':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        require_once 'changeMyPass.php';
        break;
    case '/requestForm':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        require_once 'requestForm.php';
        break;
    case '/request/edit':
        $request = getRequestById($_GET['id']);
        if ($request == null) {
            header(' ', true, 404);
            die();
        }
        if (!isFsr() && $request->userId !== $_SESSION['userId']) {
            header(' ', true, 403);
            die();
        }
        require_once 'requestForm.php';
        break;
    case '/request':
        if (!isset($_GET['id'])) {
            header(' ', true, 404);
            die();
        }
        $request = getRequestById($_GET['id']);
        if ($request == null || $request->id == null) {
            header(' ', true, 404);
            die();
        }
        if (!isFsr() && $request->userId !== $_SESSION['userId']) {
            header(' ', true, 403);
            die();
        }
        require_once 'viewDriveRequest.php';
        break;
    case '/schedule':
        require_once 'schedule.php';
        break;
    case '/viewDriveRequests':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        require_once 'myRequests.php';
        break;
    default:
        require_once 'home.php';
        break;
}

function createUser($username, $password, $role, $isActive) {
    $user = new \User();
    $user->username = $username;

    $salt = openssl_random_pseudo_bytes(32);
    $user->salt = $salt;
    $hashedPass = _generatePassword($password, $salt);
    $user->hashedPass = $hashedPass;

    $user->isActive = $isActive;
    $user->role = $role;

    return persistUser($user);
}

