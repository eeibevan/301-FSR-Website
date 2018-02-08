<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>User</title>

    <?php require_once 'scriptAndCss.php' ?>

    <?php if (isset($user)) { ?>
    <script>
        (function() {
            $(document).ready(function() {
                var form = $('#userForm');

                form.on('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    form.addClass('was-validated');

                    var data = {
                        id: <?php echo $user->id ?>
                    };
                    data.username = $("#username").val();

                    var password = $("#password").val();
                    if (password.length)
                        data.password = password;

                    data.role = $("#role").val();

                    $.ajax({
                        type:'POST',
                        url:'./index.php?path=/api/user/update',
                        dataType:'json',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: data
                    }).done(function (data, textStatus, jqXHR) {
                        window.location.replace('./index.php?path=/user/all');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('error');
                    })
                });
            });
        })();
    </script>
    <?php } else { ?>
    <script>
        (function() {
            $(document).ready(function() {
                var form = $('#userForm');

                form.on('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    form.addClass('was-validated');

                    $.ajax({
                        type:'POST',
                        url:'./index.php?path=/api/user/add',
                        dataType:'json',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            username: $("#username").val(),
                            password: $("#password").val(),
                            role: $("#role").val()
                        }
                    }).done(function (data, textStatus, jqXHR) {
                        window.location.replace('./index.php?path=/user/all');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('error');
                    })
                });
            });
        })();
    </script>
    <?php } ?>
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container">
    <div class="container">
        <?php if (isset($user)) {?>
            <h1>Edit User</h1>
        <?php } else { ?>
            <h1>Create User</h1>
        <?php } ?>
        <form class="form" id="userForm" novalidate>

            <div class="form-group col-lg-5">
                <label for="username">Username</label>
                <input id="username" type="text" class="form-control" placeholder="fsradmin" value="<?php if (isset($user)) { echo htmlspecialchars($user->username); }?>" required>
                <div class="invalid-feedback">Username is required</div>
            </div>
            <div class="form-group col-lg-5">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" placeholder="password" <?php if (!isset($user)) echo 'required'; ?>/>
                <div class="invalid-feedback">Password is required</div>
            </div>
            <div class="form-group col-lg-3">
                <label for="role">Role</label>
                <select class="form-control" id="role" required>
                    <option value="fsr" <?php if (isset($user) && $user->role === 'fsr') echo 'selected';?> >FSR</option>
                    <option value="faculty" <?php if (isset($user) && $user->role === 'faculty') echo 'selected';?>>Faculty</option>
                </select>
                <div class="invalid-feedback">Role is required</div>
            </div>


            <?php if (isset($user)) {?>
                <button class="btn btn-lg btn-primary offset-lg-4" type="submit">Edit User</button>
            <?php } else { ?>
                <button class="btn btn-lg btn-primary offset-lg-4" type="submit">Create User</button>
            <?php } ?>

        </form>
    </div>

</main>
</body>
</html>