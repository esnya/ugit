<?php
require(__dir__ . '/git.php');

$repo = new Git($_REQUEST['r']);

$oid = $_REQUEST['o'];
$repo->validateObjectID($oid);

$path = htmlspecialchars($_REQUEST['p'], ENT_QUOTES, 'UTF-8');

$data = str_replace("\t", '    ', str_replace('<', '&lt;', str_replace('>', '&gt;', $repo->catFile($oid))));
if (preg_match('/\.d$/', $path)) {
    $syntax = array(
        'type' => array('auto', 'body', 'bool', 'byte', 'cdouble', 'cent', 'cfloat', 'char', 'creal', 'dchar', 'double', 'float', 'idouble', 'ifloat', 'int', 'ireal', 'long', 'real', 'short', 'ubyte', 'ucent', 'uint', 'ulong', 'ushort', 'void', 'wchar'),
        'keyword' => array('abstract', 'alias', 'align', 'asm', 'assert', 'auto', 'body', 'bool', 'break', 'byte', 'case', 'cast', 'catch', 'cdouble', 'cent', 'cfloat', 'char', 'class', 'const', 'continue', 'creal', 'dchar', 'debug', 'default', 'delegate', 'delete', 'deprecated', 'do', 'double', 'else', 'enum', 'export', 'extern', 'false', 'final', 'finally', 'float', 'for', 'foreach', 'foreach_reverse', 'function', 'goto', 'idouble', 'if', 'ifloat', 'immutable', 'import', 'in', 'inout', 'int', 'interface', 'invariant', 'ireal', 'is', 'lazy', 'long', 'macro (unused)', 'mixin', 'module', 'new', 'nothrow', 'null', 'out', 'override', 'package', 'pragma', 'private', 'protected', 'public', 'pure', 'real', 'ref', 'return', 'scope', 'shared', 'short', 'static', 'struct', 'super', 'switch', 'synchronized', 'template', 'this', 'throw', 'true', 'try', 'typedef', 'typeid', 'typeof', 'ubyte', 'ucent', 'uint', 'ulong', 'union', 'unittest', 'ushort', 'version', 'void', 'volatile', 'wchar', 'while', 'with', '__FILE__', '__MODULE__', '__LINE__', '__FUNCTION__', '__PRETTY_FUNCTION__', '__gshared', '__traits', '__vector', '__parameters'),
        'token' => array('/', '/=', '.', '..', '...', '&', '&=', '&&', '|', '|=', '||', '-', '-=', '--', '+', '+=', '++', '&lt;', '&lt;=', '&lt;&lt;', '&lt;&lt;=', '&lt;&gt;', '&lt;&gt;=', '&gt;', '&gt;=', '&gt;&gt;=', '&gt;&gt;&gt;=', '&gt;&gt;', '&gt;&gt;&gt;', '!', '!=', '!&lt;&gt;', '!&lt;&gt;=', '!&lt;', '!&lt;=', '!&gt;', '!&gt;=', '(', ')', '[', ']', '{', '}', '?', ',', ';', ':', '$', '=', '==', '*', '*=', '%', '%=', '^', '^=', '^^', '^^=', '~', '~=', '@', '=&gt;', '#',),
    );

    foreach ($syntax as $key => $list) {
        usort($syntax[$key], function ($a, $b) {
            return strlen($a) < strlen($b);
        });
    }

    $src = $data;
    $dst = '';

    $context = null;

    function syntax($class) {
        global $context, $dst;

        if ($context != $class) {
            if ($context) {
                $dst .= '</code>';
            }

            $context = $class;
            $dst .= "<code class=\"syntax-$class\">";
        }
    }

    $patterns = array(
        'type' => '/^(' . implode($syntax['type'], '|') . ')[^a-zA-Z_]/',
        'keyword' => '/^(' . implode($syntax['keyword'], '|') . ')[^a-zA-Z_]/',
        'token' => '{^(' . implode(array_map(function ($ptn) {
            return preg_quote($ptn);
        }, $syntax['token']), '|') . ')}',
    );
    while (strlen($src) > 0) {
        if (preg_match('/^[ \t\r\n]+/', $src, $matches)) {
            syntax('white-space');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match('|^//.*|', $src, $matches)) {
            syntax('comment comment-line');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match('/^(".*"|`.*`)[dw]?/', $src, $matches)) {
            syntax('string');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match('/^\'.?\'[dw]?/', $src, $matches)) {
            syntax('char');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match('/^([+-]|0x)?[0-9][9-9_]*(\.[0-9_]+)?(e[+-][0-9_]+)?[Lu]?/', $src, $matches)) {
            syntax('number');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match($patterns['type'], $src, $matches)) {
            syntax('type');

            $n = strlen($matches[0]) - 1;
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match($patterns['keyword'], $src, $matches)) {
            syntax('keyword');

            $n = strlen($matches[0]) - 1;
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match($patterns['token'], $src, $matches)) {
            syntax('token');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else if (preg_match('/^[a-zA-Z0-9_]+/', $src, $matches)) {
            syntax('identifier');

            $n = strlen($matches[0]);
            $dst .= substr($src, 0, $n);
            $src = substr($src, $n);
        } else {
            syntax('error');
            $dst .= substr($src, 0, 1);
            $src = substr($src, 1);
        }
    }

    $data = $dst;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $path ?> - UGit</title>
    <style>
.syntax-char { color: #55aa00; }
.syntax-comment { color: gray; }
.syntax-error { color: white; background: red; }
.syntax-keyword { color: blue; }
.syntax-number { color: red; }
.syntax-string { color: #55aa00; }
.syntax-token { color: #555555; }
.syntax-type { color: #ff8800; }
    </style>
</head>
<body>
<h1><a href="./">UGit</a></h1>
<h2><a href="tree.php?r=<?php echo $repo->name ?>"><?php echo $repo->name ?></a><?php echo $path ?></h2>
<pre>
<code><?php echo $data ?></code>
</pre>
</body>
</html>
