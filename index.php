<?php require(__dir__ . '/git.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UGit</title>
</head>
<body>
<ul>
<?php foreach (Git::getAllRepositories() as $name => $path): ?>
    <li><a href="tree.php?r=<?php echo $name ?>"><?php echo $name ?></a></li>
<?php endforeach; ?>
</ul>
</body>
</html>
