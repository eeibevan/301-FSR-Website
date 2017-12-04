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
                var table = $("#requestsTable");
                $.ajax({
                    url:'index.php?path=/api/requests',
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
                        var request = data[i];
                        accumulator += "<tr>" +
                            "<td><a href='./index.php?path=/request&id=" + request.id + "'>" + request.class + "</a></td>" +
                            "<td>" + request.username + "</td>" +
                            "<td>" + request.status + "</td>" +
                            "<td>" + request.drives + "</td>" +
                            "<td>" + request.operatingSystem + "</td>" +
                            "<td><pre>" + request.other + "</pre></td>" +
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
    <table id="requestsTable" class="table table-striped table-responsive-sm">
        <thead>
        <tr>
            <th>Class</th>
            <th>Author</th>
            <th>Status</th>
            <th># of Drives</th>
            <th>Operating System</th>
            <th>Description</th>
        </tr>
        </thead>
    </table>
</main>
</body>
</html>