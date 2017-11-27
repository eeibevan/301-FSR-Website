<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Drive Request</title>

    <?php require_once 'scriptAndCss.php' ?>

    <style>
        #controlButtons {
            margin-top: 50px;
        }
        #controlButtons button,a {
            margin-right: 15px;
        }
    </style>
    <script>
        $(document).ready(function () {
            var btnClose = $("#btnClose");
            if (btnClose.length) {
                btnClose.click(function () {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url:'./index.php?path=/api/requests/update',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            id: <?php echo $request->id ?>,
                            status: 'Closed'
                        }
                    }).done(function () {
                        window.location.replace('./index.php?path=/viewDriveRequests')
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('fail');
                    })
                });
            }

            var btnDelete = $("#btnDelete");
            if (btnDelete.length) {
                btnDelete.click(function () {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url:'./index.php?path=/api/requests/delete',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            id: <?php echo $request->id ?>
                        }
                    }).done(function () {
                        window.location.replace('./index.php?path=/viewDriveRequests')
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('fail');
                    })
                });
            }
        })
    </script>
</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" style="font-size: 1.35em" class="container">
    <h1>Drive Request</h1>
    <div class="form-group">
        <div>Class: <?php echo htmlspecialchars($request->class)?></div>
    </div>
    <div class="form-group">
        <div>Status: <?php echo htmlspecialchars($request->status)?></div>
    </div>
    <div class="form-group">
        <div>Number of Drives: <?php echo htmlspecialchars($request->drives)?></div>
    </div>
    <div class="form-group">
        <div>Operating System: <?php echo htmlspecialchars($request->operatingSystem)?></div>
    </div>

    <div class="form-group">
        <div>Software / Other Requirements:</div>
        <pre><?php echo htmlspecialchars($request->other)?></pre>
    </div>
    <?php if (isFsr() || $request->userId === $_SESSION['userId']) { ?>
    <div id="controlButtons" class="form-group">
        <a href="./index.php?path=/request/edit&id=<?php echo $request->id ?>" class="btn btn-lg btn-primary">Edit</a>
        <button id="btnClose" class="btn btn-lg btn-success">Close</button>
        <button id="btnDelete" class="btn btn-lg btn-danger">Delete</button>
    </div>
    <?php } ?>
</body>
</html>