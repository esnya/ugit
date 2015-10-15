<?php
$repo = $_REQUEST['r'];
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $repo)) {
    die('Invalid Repository Name');
}

$object = $_REQUEST['o'];
$path = $_REQUEST['p'];

$base = __dir__ . '/' . $repo . '.git';

function catFile($id) {
    global $base;
    return `git --git-dir={$base} cat-file -p {$id}`;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $path ?></title>
</head>
<body>
<h1><a href="./">Git</a></h1>
<h2><a href="repo.php?r=<?php echo $repo ?>"><?php echo $repo ?></a><?php echo $path ?></h2>
<pre>
<?php echo catFile($object) ?>
</pre>
</body>
</html>
