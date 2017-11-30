<?php
require_once 'persistence/mysql.php';

function isLoggedIn() {
    return isset($_SESSION['userId']);
}

function isFaculty() {
    if (!isLoggedIn())
        return false;
    $user = getUserById($_SESSION['userId']);
    if (is_null($user))
        return false;
    return $user->role === 'faculty';
}

function isFsr() {
    if (!isLoggedIn())
        return false;
    $user = getUserById($_SESSION['userId']);
    if (is_null($user))
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
    /**
     * Shows Home Page
     */
    case '/home':
        require_once 'home.php';
        break;

    /**
     * Shows Contact Page
     */
    case '/contact':
        require_once 'contact.php';
        break;
    /**
     * Shows Login Page
     */
    case '/login':
        require_once 'login.php';
        break;
    /**
     * API Call For Logging In A User
     *
     * @method: POST
     *
     * @parameter username {String}
     *  Email/Username of The User To Login
     *
     * @parameter password {String}
     *  Password of The User To Login
     *
     * @return
     *  400: No Email/Password, Invalid Email/Password, User Not Found
     *  200: User Found & Session Started
     *      json: { userId:(the User's ID) }
     */
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
        if (is_null($user)|| $user->id == null || !verifyPassword($plainText, $user->salt, $user->hashedPass)) {
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
    /**
     * Ends The Current Session And Dumps The User At The Homepage
     */
    case '/logout':
        $_SESSION = array();
        session_destroy();
        require_once 'home.php';
        break;
    /**
     * API Method For Retrieving Requests
     * If Called From A Faculty Account, Only Retrieves Their Drives
     * If Called From A FSR Account, Retrieves All Drives
     *
     * @method GET
     *
     * @parameter [id] {int}
     *  ID of A Request To Retrieve
     *
     * @parameter [status] {string}
     *  Type of Status (Open/Closed) To Retrieve Requests From
     *
     * @return
     *  200:
     *      If id Is Not Set, Then An Array of Requests
     *      If status Is Set, Then Only Requests of That Status
     *      If Called From A Faculty Account, Only Retrieves Their Drives
     *      If Called From A FSR Account, Retrieves All Drives
     *  401:
     *      User Is Not Logged In
     *
     */
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
    /**
     * API Method For Updating A Drive Request
     * A Drive Request May Only Be Modified By The Creator, Or By An FSR Member
     * Only Provided Fields Will Be Modified
     * ID/Creator/Created Cannot Be Modified
     *
     * @method POST
     *
     * @parameter id {int}
     *  ID of The Request To Modify
     *
     * @parameter [driveClass] {string}
     *  Class That Is Requesting The Drives (ex: CIS 402)
     *
     * @parameter [drives] {int}
     *  Number of Drives Requested, Should Be Greater Than 0
     *
     * @parameter [operatingSystem] {string}
     *  The Operating System To Be Installed On The Drives
     *
     * @parameter [description] {string}
     *  Additional Software / Description of Requirements For The Drives
     *
     * @parameter [status] {string} (Open|Closed)
     *  Status of The Request, Open Means The Request Has Not Yet Been Fulfilled
     *  Closed Means The Drive Request Has Been Fulfilled
     *
     * @return
     *  200:
     *      The Request Was Successfully Modified
     *      json: { id: (the ID of The Modified Request) }
     *  401:
     *      The User Is Not Logged In
     *  403:
     *      The User Is Not Authorized To Modify This Request
     *  404:
     *      The Request Was Not Found, or No id Was Provided
     */
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

        if (!isFsr() && $request->userId !== $_SESSION['userId']) {
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
    /**
     * API Method For Creating A Drive Request
     * A Drive Request May Be Created By an FSR or Faculty Account
     * Drives Are Always Created With Open Status
     *
     * @method POST
     *
     * @parameter driveClass {string}
     *  Class That Is Requesting The Drives (ex: CIS 402)
     *
     * @parameter drives {int}
     *  Number of Drives Requested, Should Be Greater Than 0
     *
     * @parameter operatingSystem {string}
     *  The Operating System To Be Installed On The Drives
     *
     * @parameter description {string}
     *  Additional Software / Description of Requirements For The Drives
     *
     * @return
     *  201:
     *      The Request Was Successfully Created
     *      json: { id: (the ID of The New Request) }
     *  401:
     *      The User Is Not Logged In
     */
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

    /**
     * API Method For Deleting A Drive Request
     * A Drive Request May Only Be Deleted By The Creator, Or By An FSR Member
     *
     * @method POST
     *
     * @parameter id {int}
     *  ID of The Request To Delete
     *
     * @return
     *  200:
     *      The Request Was Successfully Deleted
     *      json: { id: (the ID of The Deleted Request) }
     *  401:
     *      The User Is Not Logged In
     *  403:
     *      The User Is Not Authorized To Delete This Request
     */
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

    /**
     * API Method For Creating A User
     * A User May Only Be Created By An FSR Member
     *
     * @method POST
     *
     * @parameter username {string}
     *  Unique Email of The New User
     *
     * @parameter password {string}
     *  The Password For The New User
     *
     * @parameter role {string} (fsr|faculty)
     *  The Role of The New User
     *
     * @return
     *  201:
     *      The User Was Successfully Created
     *      json: { id: (the ID of The New User) }
     *  400:
     *      Some Field Was Not Provided/Malformed
     *  401:
     *      The User Is Not Logged In
     *  403:
     *      The User Is Not Authorized To Create A User
     *  409:
     *      The username Is Already In Use
     */
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

        if (!isset($_POST['username'])) {
            header(' ', true, 400);
            $resp['message'] = "No Username Provided";
            die();
        }

        if (!isset($_POST['password'])) {
            header(' ', true, 400);
            $resp['message'] = "No password Provided";
            die();
        }

        if (!isset($_POST['role'])) {
            header(' ', true, 400);
            $resp['message'] = "No Role Provided";
            die();
        }

        $existingUser = getUserByUsername($_POST['username']);
        if (!is_null($existingUser)) {
            header(' ', true, 409);
            $resp['message'] = "Email Already Used";
            die();
        }

        $id = createUser($_POST['username'], $_POST['password'], $_POST['role'], 1);
        if ($id == 0) {
            header(' ', true, 400);
            $resp['message'] = 'Failed To Create User';
            die();
        }

        $resp['id'] = $id;
        header(' ', true, 201);
        echo json_encode($resp);
        break;

    /**
     * API Method For Updating A User
     * A User May Only Be Updated By An FSR Member
     *
     * @method POST
     *
     * @parameter [username] {string}
     *  Unique Email of The New User
     *
     * @parameter [password] {string}
     *  The Password For The New User
     *
     * @parameter [role] {string} (fsr|faculty)
     *  The Role of The New User
     *
     * @return
     *  200:
     *      The User Was Successfully Updated
     *      json: { id: (the ID of The Updated User) }
     *  400:
     *      Some Field Was Not Provided/Malformed
     *  404:
     *      The User Was Not Found
     *  401:
     *      The User Is Not Logged In
     *  403:
     *      The User Is Not Authorized To Update A User
     *  409:
     *      The username Is Already In Use
     */
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
        if (is_null($user)) {
            header(' ', true, 404);
            $resp['message'] = "User Not Found";
            echo json_encode($resp);
            die();
        }

        if (isset($_POST['username'])) {
            $existingUser = getUserByUsername($_POST['username']);
            if ($existingUser->id != null && $existingUser->id != $user->id) {
                header(' ', true, 409);
                $resp['message'] = "Email Already Used";
                die();
            }
            $user->username = $_POST['username'];
        }


        if (isset($_POST['password']))
            $user->hashedPass = _generatePassword($_POST['password'], $user->salt);

        if (isset($_POST['role']))
            $user->role = $_POST['role'];

        $response['id'] = persistUser($user);
        echo json_encode($response);
        break;

    /**
     * API Method For Retrieving One User / All Users
     * May Only Be Invoked By FSR Members
     *
     * @method GET
     *
     * @parameter [id] {int}
     *  ID of A User To Retrieve
     *
     * @return
     *  200:
     *      One/All Users Were JSON Encoded And Returned
     *          One: {id, username, is_active, role}
     *          All: json: array({id, username, is_active, role})
     *  401:
     *      User Is Not Logged In
     *  403:
     *      User Is Not An FSR Member
     */
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
            echo json_encode(getReducedUserById($_GET['id']));
            die();
        }

        echo json_encode(getAllUsers());
        die();
        break;

    /**
     * API Method To Change The Password of The Current User
     *
     * @method POST
     *
     * @parameter password {string}
     *  The New Password
     *
     * @return
     *  200:
     *      The User Was Successfully Updated
     *      json: { id: (the ID of The Updated User) }
     *  400:
     *      A Password Was Not Provided
     *  404:
     *      The User Was Not Found
     *  401:
     *      The User Is Not Logged In
     */
    case '/api/user/changePass':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        $currentUser = getUserById($_SESSION['userId']);
        if (is_null($currentUser)) {
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

    /**
     * Server A userForm For A User
     * Only A FSR Member May Access This Page
     *
     * @method GET
     *
     * @parameter id {int}
     *  ID of The User To Populate The Form With
     *
     * @return
     *  200 + View:
     *      The Form Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     *  404:
     *      The User Was Not Found
     *  403:
     *      User Is Not An FSR Member
     */
    case '/user':
        if (!isset($_GET['id'])) {
            header(' ', true, 404);
            die();
        }
        $user = getUserById($_GET['id']);
        if (is_null($user)) {
            header(' ', true, 404);
            die();
        }
        if (!isFsr()) {
            header(' ', true, 403);
            die();
        }
        require_once 'userForm.php';
        break;
    /**
     * Server A Table of All Users
     * Only A FSR Member May Access This Page
     *
     * @return
     *  200 + View:
     *      The User Is Authorised To See This Page
     *  401:
     *      User Is Not Logged In
     *  403:
     *      User Is Not An FSR Member
     */
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
    /**
     * Server A userForm For Creating A User
     * Only A FSR Member May Access This Page
     *
     * @return
     *  200 + View:
     *      The Form Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     *  403:
     *      User Is Not An FSR Member
     */
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
    /**
     * Server A Form For Updating The Current User's Password
     * Any Logged In User May Access This Page
     *
     * @return
     *  200 + View:
     *      The Form Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     */
    case '/changePass':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        require_once 'changeMyPass.php';
        break;
    /**
     * Server A Form For Requesting Drives
     * Any Logged In User May Access This Page
     *
     * @return
     *  200 + View:
     *      The Form Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     */
    case '/requestForm':
        if (!isLoggedIn()) {
            header(' ', true, 401);
            die();
        }
        require_once 'requestForm.php';
        break;
    /**
     * Server A Form For Editing A request
     * Only The Creator Of The Request or A FSR Member May Access This Page
     *
     * @method GET
     *
     * @parameter id {int}
     *  ID of The Request To Populate The Form With
     *
     * @return
     *  200 + View:
     *      The Form Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     *  403:
     *      User Is Not The Creator of The Request Or A FSR Member
     *  404:
     *      The Request Was Not Found
     */
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
    /**
     * Server A View of A request
     * Only The Creator Of The Request or A FSR Member May Access This Page
     *
     * @method GET
     *
     * @parameter id {int}
     *  ID of The Request To Populate The View With
     *
     * @return
     *  200 + View:
     *      The View Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     *  403:
     *      User Is Not The Creator of The Request Or A FSR Member
     *  404:
     *      The Request Was Not Found
     */
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
    /**
     * Shows The Current Lab Schedule
     * Anyone May Access This Page
     */
    case '/schedule':
        require_once 'schedule.php';
        break;
    /**
     * Server A View of Requests
     *  If The User Is faculty, Then only Their Requests Are Shown
     *  If The User Is FSR, Then All Requests Will Be Shown
     *
     *
     * @return
     *  200 + View:
     *      The View Was Successfully Loaded
     *  401:
     *      User Is Not Logged In
     */
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

/**
 * Sets Fields, Generates A Salt, And Persists A User
 *
 * @param $username {string}
 *  Unique Username/Email For The User
 *
 * @param $password {string}
 *  Password For The New User
 *
 * @param $role {string} (fsr|faculty)
 *  Role of The New User
 *
 * @param $isActive {bool}
 *  Flag If The User Is Active Or Not
 *
 * @return int
 *  ID of The New User
 *  0 May Indicate An Error
 */
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

