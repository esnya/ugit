<?php
$repo = $_REQUEST['r'];
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $repo)) {
    die('Invalid Repository Name');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $repo ?> - U-Git</title>
</head>
<body>
<h1><a href="./">Git</a></h1>
<?php
$base = __dir__ . '/' . $repo . '.git';

function catFile($id)  {
    global $base;
    return `git --git-dir={$base} cat-file -p {$id}`;
}

$master_id = file_get_contents($base . '/refs/heads/master');
$master = catFile($master_id);//`git --git-dir={$base} cat-file -p {$master_id}`;

if (!preg_match('/^tree[ \t]+([0-9a-f]+)/', $master, $matches)) {
    die();
}
trace($matches[1]);

function trace($id, $path = '') {
    global $repo, $base;

    $tree = catFile($id);
    $items = array_filter(array_map(function($line) {
        return preg_split('/[ \t]+/', $line);
    }, explode("\n", $tree)), function($item) {
        return count($item) == 4;
    });

    echo '<ul>';
    array_walk($items, function($item) use($repo, $path) {
        echo '<li>';
        if ($item[1] == 'blob') {
            $href = "blob.php?r={$repo}&o={$item[2]}&p={$path}/{$item[3]}";
            echo "<a href=\"$href\">";
            echo $item[3];
            echo '</a>';
        } else if ($item[1] == 'tree') {
            echo $item[3]; echo '/';
            trace($item[2], $path . '/' . $item[3]);
        } else {
            echo $item[3];
        }
        echo '</li>';
    });
    echo '</ul>';
}
?>
</body>
</html>
