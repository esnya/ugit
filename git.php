<?php
require_once(__dir__ . '/conf.php');

class Git {
    public function __construct($name) {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name) || !array_key_exists($name, Git::getAllRepositories())) {
            die('Invalid Repository Name');
        }

        $this->name = $name;
    }

    public function getPath() {
        $repositories = Git::getAllRepositories();
        return $repositories[$this->name];
    }

    public function validateObjectID($id) {
        if (!preg_match('/^[0-9a-f]+$/', $id)) {
            die('Invalid Object ID');
        }
    }

    public function catFile($id) {
        $gitDir = escapeshellarg($this->getPath());
        $_id = escapeshellarg($id); 
        return shell_exec("git --git-dir={$gitDir} cat-file -p {$_id}");
    }

    public function getMaster() {
        $raw = $this->catFile(trim(file_get_contents($this->getPath() . '/refs/heads/master')));
        $master = array();
        foreach(preg_split('/[\r\n]+/', $raw) as $line) {
            if (preg_match('/^([^ \t]+)[ \t]+(.+)$/', $line, $matches)) {
                $master[$matches[1]] = $matches[2];
            } else {
                $master[] = $line;
            }
        }
        return $master;
    }

    public function traceTree($id, $path = '') {
        $tree = $this->catFile($id);

        $items = array_filter(array_map(function($line) {
            return preg_split('/[ \t]+/', $line);
        }, explode("\n", $tree)), function($item) {
            return count($item) == 4;
        });

        ob_start();
        echo '<ul>';
        foreach ($items as $item) {
            echo '<li>';
            if ($item[1] == 'blob') {
                $href = "blob.php?r={$this->name}&o={$item[2]}&p={$path}/{$item[3]}";
                echo "<a href=\"$href\">";
                echo $item[3];
                echo '</a>';
            } else if ($item[1] == 'tree') {
                echo $item[3]; echo '/';
                echo $this->traceTree($item[2], $path . '/' . $item[3]);
            } else {
                echo $item[3];
            }
            echo '</li>';
        }
        echo '</ul>';
        return ob_get_clean();
    }

    static public function getAllRepositories() {
        global $list;
        return $list;
    }
}
