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

                    var email = $("#inputEmail").val();
                    var pass = $("#inputPassword").val();

                    $.ajax({
                        type: "POST",
                        url: './index.php?path=/api/login',
                        dataType: 'json',
                        data: {
                            username: email,
                            password: pass
                        }
                    }).done(function (data, status, xhr) {
                        window.location.replace('./index.php?path=/home');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        var data = jqXHR.responseJSON;
                        if (data !== undefined)
                            $("#signInError").show().html(data.message);
                    })

                })
            })
        })();
    </script>
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container col-lg-4">
    <form id="signInForm" class="form-signin" novalidate>
        <h2 class="form-signin-heading">Please sign in</h2>
        <div id="signInError" class="alert alert-danger" role="alert" style="display: none"></div>
        <input type="text" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" required>
        <button id="btnLogin" class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>
</main>
</body>
</html>