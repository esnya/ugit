<?php
require_once(__dir__ . '/conf.php');

if ($config['debug']) {
    ini_set('display_errors', 1);
}

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

    public function getObject($id) {
        $this->validateObjectID($id);
        $path = $this->getPath() . '/objects/' . substr($id, 0, 2) . '/' . substr($id, 2);
        return new GitObject(gzuncompress(file_get_contents($path)));
    }

    public function getMaster() {
        $data = $this->getObject(trim(file_get_contents($this->getPath() . '/refs/heads/master')))->pop()->data;
        $master = array();
        foreach(preg_split('/[\r\n]+/', $data) as $line) {
            if (preg_match('/^([^ \t]+)[ \t]+(.+)$/', $line, $matches)) {
                $master[$matches[1]] = $matches[2];
            } else {
                $master[] = $line;
            }
        }
        return (object)$master;
    }

    public function traceTree($root, $path = '') {
        $tree = new GitTree($this->getObject($root));

        ob_start();
        echo '<ul>';
        while ($item = $tree->pop()) {
            $type = $this->getObject($item->hash)->pop()->type;
            echo '<li>';
            if ($type == 'blob') {
                $href = "blob.php?r={$this->name}&o={$item->hash}&p={$path}/{$item->name}";
                echo "<a href=\"$href\">";
                echo $item->name;
                echo '</a>';
            } else if ($type == 'tree') {
                echo $item->name; echo '/';
                echo $this->traceTree($item->hash, $path . '/' . $item->name);
            } else {
                echo 'Unknown object type: ' . $type;
            }
            echo '</li>';
        }
        echo '</ul>';
        return ob_get_clean();
    }

    static public function getAllRepositories() {
        global $config;
        return $config['list'];
    }
}

class GitObjectReader {
    public function pop() {
        $pos = strpos($this->data, "\0");

        if ($pos == false) return FALSE;

        return $this->_pop($pos);
    }

    public function toArray() {
        $array = array();

        while ($data = $this->pop()) {
            $array[] = $data;
        }

        return $array;
    }

    protected function _popData($n) {
        $this->data = substr($this->data, $n);
    }

    protected function _pop() {
        return FALSE;
    }
}

class GitObject extends GitObjectReader {
    public function __construct($data) {
        $this->data = $data;
    }

    protected function _pop($pos) {
        $header = explode(' ', substr($this->data, 0, $pos));
        $type = $header[0];
        $size = (int)$header[1];

        $data = substr($this->data, $pos + 1, $size);

        $this->_popData($pos + 1 + $size);

        return (object)array(
            'type' => $type,
            'size' => $size,
            'data' => $data,
        );
    }
}

class GitTree extends GitObjectReader {
    public function __construct(GitObject $object) {
        $this->data = $object->pop()->data;
    }

    protected function _pop($pos) {
        $header = explode(' ', substr($this->data, 0, $pos));

        $mode = $header[0];
        $name = $header[1];
        $hash = bin2hex(substr($this->data, $pos + 1, 20));

        $this->_popData($pos + 1 + 20);

        return (object)array(
            'mode' => $mode,
            'name' => $name,
            'hash' => $hash,
        );
    }
}
