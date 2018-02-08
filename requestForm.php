<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Request Drives</title>

    <?php require_once 'scriptAndCss.php' ?>

    <?php if (isset($request)) { ?>
    <script>
        (function() {
            window.addEventListener('load', function() {
                var form = document.getElementById('driveForm');

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    form.classList.add('was-validated');

                    var driveClass = $("#forClass").val();
                    var numberOfDrives = $("#numberOfDrives").val();
                    var os = $("#operatingSystem").val();
                    var otherReq = $("#softwareAndOtherRequirements").val();
                    $.ajax({
                        type:'POST',
                        url:'./index.php?path=/api/requests/update',
                        dataType:'json',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            id: <?php echo $request->id ?>,
                            driveClass: driveClass,
                            drives: numberOfDrives,
                            operatingSystem: os,
                            description: otherReq
                        }
                    }).done(function (data, textStatus, jqXHR) {
                        window.location.replace('./index.php?path=/viewDriveRequests');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('error');
                    })

                }, false);
            }, false);
        })();
    </script>
    <?php } else { ?>
    <script>
        (function() {

            window.addEventListener('load', function() {
                var form = document.getElementById('driveForm');
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    form.classList.add('was-validated');

                    var driveClass = $("#forClass").val();
                    var numberOfDrives = $("#numberOfDrives").val();
                    var os = $("#operatingSystem").val();
                    var otherReq = $("#softwareAndOtherRequirements").val();
                    $.ajax({
                        type:'POST',
                        url:'./index.php?path=/api/requests/create',
                        dataType:'json',
                        statusCode: {
                            401: function () {
                                window.location.href = "./index.php?path=/login";
                            }
                        },
                        data: {
                            driveClass: driveClass,
                            drives: numberOfDrives,
                            operatingSystem: os,
                            description: otherReq
                        }
                    }).done(function (data, textStatus, jqXHR) {
                        window.location.replace('./index.php?path=/viewDriveRequests');
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        alert('error');
                    })

                }, false);
            }, false);
        })();
    </script>
    <?php } ?>

</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container">
    <?php if (isset($request)) {?>
    <h1>Edit Request</h1>
    <?php } else { ?>
    <h1>Request Drives</h1>
    <?php } ?>
    <form id="driveForm" novalidate>
        <div class="row">
            <div class="form-group col-4">
                <label for="forClass">Class</label>
                <input id="forClass" type="text" class="form-control" placeholder="CIS 312" value="<?php if (isset($request)) {echo htmlspecialchars($request->class); }?>" required>
                <div class="invalid-feedback">Class is required</div>
            </div>
            <div class="form-group col-4">
                <label for="numberOfDrives">Number of Drives</label>
                <input type="number" class="form-control" id="numberOfDrives" placeholder="28" value="<?php if (isset($request)) { echo htmlspecialchars($request->drives); }?>" required/>
                <div class="invalid-feedback">Number of Drives is required</div>
            </div>
            <div class="form-group col-4">
                <label for="operatingSystem">Operating System</label>
                <input type="text" class="form-control" id="operatingSystem" placeholder="Windows 10" value="<?php if (isset($request)) { echo htmlspecialchars($request->operatingSystem); }?>" required/>
                <div class="invalid-feedback">Operating System is required</div>
            </div>
        </div>

        <div class="form-group">
            <label for="softwareAndOtherRequirements">Software / Other Requirements</label>
            <textarea class="form-control" id="softwareAndOtherRequirements" rows="10"><?php if (isset($request)) { echo htmlspecialchars($request->other); }?></textarea>
        </div>
        <?php if (isset($request)) {?>
        <button class="btn btn-lg btn-primary float-right" type="submit">Edit request</button>
        <?php } else { ?>
        <button class="btn btn-lg btn-primary float-right" type="submit">Submit request</button>
        <?php } ?>

    </form>
</main>
</body>
</html>