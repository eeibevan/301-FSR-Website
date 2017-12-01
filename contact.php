<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Contact</title>

    <?php require_once 'scriptAndCss.php' ?>

</head>
<body>

<?php require_once 'nav.php' ?>

<main role="main" class="container align-content-center align-items-center">
    <h3>Lab Employees</h3>
    <table class="table table-striped table-sm table-responsive-sm">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
        </tr>
        </thead>
        <tr>
            <td>Csorba, Thomas</td>
            <td>T.L.Csorba@eagle.clarion.edu</td>
        <tr>
            <td>Day, Alexander</td>
            <td>A.D.Day@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Holben, Tyler</td>
            <td>T.S.Holben@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Maitland, Keith</td>
            <td>K.H.Maitland@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Stanton, Liz</td>
            <td>E.C.Stanton@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Vigus, Trey</td>
            <td>T.M.Vigus@eagle.clarion.edu</td>
        </tr>
    </table>

    <h3 style="margin-top: 75px">FSR Employees</h3>
    <table class="table table-striped table-sm table-responsive-sm">
        <thead>
        <tr>
            <th scope="col">Name</th>
            <th scope="col">Email</th>
        </tr>
        </thead>
        <tr onclick="window.location.href = './index.php?path=/chicken'">
            <td>Black, Evan</td>
            <td>E.P.Black@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Frye, Chris</td>
            <td>C.T.Frye@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>Georgvich, John</td>
            <td>J.L.Georgvich@eagle.clarion.edu</td>
        </tr>
        <tr>
            <td>420, WeebLord</td>
            <td>E.J.Dyer@eagle.clarion.edu</td>
        </tr>
    </table>
</main>
</body>
</html>