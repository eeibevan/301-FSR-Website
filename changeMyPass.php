<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Change Password</title>

    <?php require_once 'scriptAndCss.php' ?>

    <script>
        (function() {
            $(document).ready(function() {
                var form = $('#passwordForm');

                form.on('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (form[0].checkValidity())
                        form.addClass('was-validated');
                    else {
                        return;
                    }


                    var password = $("#password").val();
                    var confirmPassword = $("#confirmPassword").val();
                    if (password !== confirmPassword) {
                        $("#noMatchError").html("Passwords Do Not Match").show();
                        return;
                    } else {
                        $("#noMatchError").hide();
                    }

                    $.ajax({
                        type:'POST',
                        url:'./index.php?path=/api/user/changePass',
                        dataType:'json',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            password: password
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
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container">
    <h1>Change Password</h1>
    <form class="col-6" id="passwordForm" novalidate>
        <div id="noMatchError" class="alert alert-danger" role="alert" style="display: none"></div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" placeholder="password" required/>
            <div class="invalid-feedback">Password is required</div>
        </div>
        <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" class="form-control" id="confirmPassword" placeholder="password" required/>
            <div class="invalid-feedback">Password is required</div>
        </div>
        <button class="btn btn-lg btn-primary float-right">Change Password</button>
    </form>
</main>
</body>
</html>