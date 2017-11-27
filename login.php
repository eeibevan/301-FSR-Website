<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Log In</title>

    <?php require_once 'scriptAndCss.php' ?>
    <style>
        #signInForm input {
            margin-top: 15px;
            margin-bottom: 10px;
        }
    </style>
    <script>
        (function () {
            $(document).ready(function () {
                var form = $("#signInForm");
                form.submit(function () {
                    event.preventDefault();
                    var htmlForm = form[0];

                    var email = $("#inputEmail").val();
                    var pass = $("#inputPassword").val();

                    if (htmlForm.checkValidity()) {
                        $.ajax({
                            type: "POST",
                            url: './index.php?path=/api/login',
                            dataType: 'json',
                            data: {
                                username: email,
                                password: pass
                            }
                        }).done(function () {
                            window.location.replace('./index.php?path=/home');
                        }).fail(function (jqXHR, textStatus, errorThrown) {
                            var data = jqXHR.responseJSON;
                            $("#signInError").show().html(data.message);
                        })
                    }
                })
            })
        })();
    </script>
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container">
    <form id="signInForm" class="form-signin offset-4 col-4" >
        <h2 class="form-signin-heading">Please sign in</h2>
        <div id="signInError" class="alert alert-danger" role="alert" style="display: none"></div>
        <input type="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <button id="btnLogin" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>
</main>
</body>
</html>