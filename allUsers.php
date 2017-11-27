<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Requests</title>

    <?php require_once 'scriptAndCss.php' ?>

    <script>
        (function() {
            window.addEventListener('load', function() {
                var table = $("#userTable");
                $.ajax({
                    url:'index.php?path=/api/users',
                    dataType: 'json',
                    data: {
                        status: 'Open'
                    },
                    statusCode: {
                        401: function () {
                            window.location.href = "./index.php?path=/login";
                        }
                    }
                }).done(function (data, status, xhr) {
                    var accumulator = table.html();

                    for (var i = 0; i < data.length; i++) {
                        var user = data[i];
                        accumulator += "<tr>" +
                            "<td><a href='./index.php?path=/user&id=" + user.id + "'>" + user.username + "</a></td>" +
                            "<td>" + user.role + "</td>" +
                            "</tr>";
                    }
                    table.html(accumulator);
                })
            }, false);
        })();
    </script>
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container">
    <table id="userTable" class="table">
        <thead>
        <tr>
            <th>Username</th>
            <th>Role</th>
        </tr>
        </thead>
    </table>
    <div class="form-group">
        <a class="btn btn-lg btn-success" href="index.php?path=/userForm">Add User</a>
    </div>
</main>
</body>
</html>