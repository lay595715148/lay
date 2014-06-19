<html>
<head><title>1</title>
</head>
<body>
<pre>
<?php
if(version_compare(phpversion(), '5.4.0') > 0) {
    echo json_encode($vars, JSON_PRETTY_PRINT);
} else {
    echo json_encode($vars);
}
//var_dump(extract($vars));
?>
</pre>
</body>
</html>
