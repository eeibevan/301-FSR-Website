<nav class="navbar navbar-expand-md navbar-dark mb-4 bg-company-gold">
    <a class="navbar-brand" href="#">Becker Lab</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="./index.php?path=/home">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./index.php?path=/contact">Contact</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="./index.php?path=/schedule">Schedule</a>
            </li>
        </ul>
        <!--Right-->
        <ul class="navbar-nav mt-2 mt-md-0">
            <?php if (isLoggedIn()) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="./index.php?path=/viewDriveRequests">View Drive Requests</a>
                </li>
                <?php if (isFaculty()) { ?>
                <li class="nav-item">
                    <a class="nav-link" href="./index.php?path=/requestForm">Request Drives</a>
                </li>
                <?php } ?>
                <?php if (isFsr()) { ?>
                    <li class="nav-item">
                        <a class="nav-link" href="./index.php?path=/user/all">Manage Users</a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" href="./index.php?path=/changePass">Change Password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./index.php?path=/logout">Log Out</a>
                </li>
            <?php } else { ?>
                <li class="nav-item">
                    <a class="nav-link" href="./index.php?path=/login">Login</a>
                </li>
            <?php }?>
        </ul>
    </div>
</nav>