<?php
require(__dir__ . '/git.php');
ini_set('display_errors', 1);

$repo = new Git($_REQUEST['r']);
$master = $repo->getMaster();
$tree = new GitTree($repo->getObject($repo->getMaster()->tree));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $repo->name ?> - UGit</title>
</head>
<body>
<h1><a href="./">UGit</a></h1>
<h2><?php echo $repo->name ?></h2>
<?php echo $repo->traceTree($master->tree); ?>
</body>
</html>
