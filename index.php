<?php ini_set('display_errors', 1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>U-Git</title>
</head>
<body>
<ul>
<?php
array_walk(glob(__dir__ . '/*.git'), function($dir) {
    $name = substr($dir, strlen(__dir__) + 1);
    $name = substr($name, 0, strlen($name) - 4);
?>
    <li><a href="repo.php?r=<?php echo $name ?>"><?php echo $name ?></a></li>
<?php
});
?>
</ul>
</body>
</html>
