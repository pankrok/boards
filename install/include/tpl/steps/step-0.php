<?php $check = checkReq(); $c = 0;?>

<div class="col-12 text-justify p-4">
<p>Witaj w instalatorze skryptu BOARDS</p>
<?php 
    echo '<div>Checking minimal requirements:
    <ul>
    <li>
    PHP: '. $check['php'];
    if ($check['php_check'] === true ) {
        echo ' <strong style="color: green">OK</strong></li>';
        $c++;
    } else {
        echo ' <strong style="color: RED">FAIL</strong></li>';
    }
    echo '<li>GD lib:';
    if ($check['gd'] === true ) {
        echo ' <strong style="color: green">OK</strong></li>';
        $c++;
    } else {
        echo ' <strong style="color: RED">FAIL</strong></li>';
    }
    echo '<li>CURL lib:';
    if ($check['gd'] === true ) {
        echo ' <strong style="color: green">OK</strong></li>';
        $c++;
    } else {
        echo ' <strong style="color: RED">FAIL</strong></li>';
    }
    echo '</ul></div>';

 ?>
</div>
<?php 
if ($c == 3) {
    $next = true; 
} else {
    echo '<span class="message red" style="width:100%; display: flex; font-weight: bold;">Serwer nie spałnia wymagań minimalnych!</span>';
}
?>