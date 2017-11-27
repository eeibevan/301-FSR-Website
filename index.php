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
            http_response_code(400);
            $resp['message'] = 'No Email Provided';
            echo json_encode($resp);
            die();
        }

        $user = getUserByUsername($username);
        if ($user == null || !verifyPassword($plainText, $user->salt, $user->hashedPass)) {
            http_response_code(400);
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
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(401);
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
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(401);
        }

        if (!isset($_POST['id'])) {
            $resp['message'] = 'Not Found';
            echo json_encode($resp);
            die(404);
        }

        $request = getRequestById($_POST['id']);
        if ($request == null) {
            $resp['message'] = 'Not Found';
            echo json_encode($resp);
            die(404);
        }

        if (!isFsr() && $request->id !== $_SESSION['userId']) {
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(403);
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
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(401);
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
        die(201);
        break;
    case '/api/requests/delete':
        if (!isLoggedIn()) {
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(401);
        }
        $request = getRequestById($_POST['id']);
        if ($request == null || $request->id == null)
            die(404);

        if (!isFsr() && $request->userId !== $_SESSION['userId']) {
            $resp['message'] = 'Unauthorized';
            echo json_encode($resp);
            die(403);
        }

        deleteRequest($request->id);
        $resp['id'] = $_POST['id'];
        echo json_encode($resp);
        break;
    case '/api/user/add':
        if (!isLoggedIn()) {
            $resp['message'] = "Not Logged In";
            json_encode($resp);
            die(401);
        } else if (!isFsr()) {
            $resp['message'] = "Not Authorised";
            json_encode($resp);
            die(403);
        }

        $resp['id'] = createUser($_POST['username'], $_POST['password'], $_POST['role'], 1);
        echo json_encode($resp);
        break;
    case '/api/user/update':
        if (!isLoggedIn()) {
            $resp['message'] = "Not Logged In";
            json_encode($resp);
            die(401);
        } else if (!isFsr()) {
            $resp['message'] = "Not Authorised";
            json_encode($resp);
            die(403);
        }
        if (!isset($_POST['id'])) {
            $resp['message'] = "No ID Provided";
            json_encode($resp);
            die(404);
        }

        $user = getUserById($_POST['id']);
        if ($user == null) {
            $resp['message'] = "User Not Found";
            echo json_encode($resp);
            die(404);
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
        if (!isLoggedIn())
            die(401);

        if (!isFsr())
            die(403);

        if (isset($_GET['id'])) {
            echo json_encode(getUserById($_GET['id']));
            die();
        }

        $test1 = getAllUsers();
        $test = json_encode(getAllUsers());
        echo json_encode(getAllUsers());
        die();
        break;
    case '/api/user/changePass':
        if (!isLoggedIn())
            die(401);
        $currentUser = getUserById($_SESSION['userId']);
        if ($currentUser == null || $currentUser->id == null)
            die(404);
        if (!isset($_POST['password']) || strlen($_POST['password']) == 0) {
            $resp['message'] = 'Password Required';
            json_encode($resp);
            die(400);
        }

        $currentUser->hashedPass = _generatePassword($_POST['password'], $currentUser->salt);
        $resp['id'] = persistUser($currentUser);
        echo json_encode($resp);
        break;
    case '/user':
        if (!isset($_GET['id']))
            die(404);
        $user = getUserById($_GET['id']);
        if ($user == null)
            die(404);
        if (!isFsr() && $user->id !== $_SESSION['userId'])
            die(403);

        require_once 'userForm.php';
        break;
    case '/user/all':
        if (!isLoggedIn())
            die(401);

        if (!isFsr())
            die(403);
        require_once 'allUsers.php';
        break;
    case '/userForm':
        if (!isLoggedIn())
            die(401);

        if (!isFsr())
            die(403);

        require_once 'userForm.php';
        break;
    case '/changePass':
        if (!isLoggedIn())
            die(401);
        require_once 'changeMyPass.php';
        break;
    case '/requestForm':
        require_once 'requestForm.php';
        break;
    case '/request/edit':
        $request = getRequestById($_GET['id']);
        if ($request == null)
            die(404);
        if (!isFsr() && $request->userId !== $_SESSION['userId'])
            die(403);
        require_once 'requestForm.php';
        break;
    case '/request':
        if (!isset($_GET['id']))
            die(404);
        $request = getRequestById($_GET['id']);
        if ($request == null || $request->id == null)
            die(404);
        if (!isFsr() && $request->userId !== $_SESSION['userId'])
            die(403);
        require_once 'viewDriveRequest.php';
        break;
    case '/schedule':
        require_once 'schedule.php';
        break;
    case '/viewDriveRequests':
        if (!isLoggedIn())
            die(401);
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
