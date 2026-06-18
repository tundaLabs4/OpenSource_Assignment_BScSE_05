<?php

function getConnection() {
    static $conn;

    if ($conn === null) {
        $conn = new mysqli("localhost", "root", "2010", "open_source_project");
    }

    return $conn;
}


function tableHead()
{
    return '<table class="data-table">';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP Project</title>
    <link rel="stylesheet" href="style.css">
    <script src="js/validation.js" defer></script>
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">