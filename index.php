<?php 
require('EDSMclass.php'); 

$wantedMats = ['Tungsten', 'Cadmium', 'Polonium','Nickel','Carbon','Vanadium','Niobium','Germanium','Yttrium','Arsenic'];
?>
<html>
<header>
    <title>EDSM System Analysis</title>
     <link rel="stylesheet" href="./css/style.css">
</header>    
<body>
<?php echo renderMenu(); ?>
<div class="formContainer">
    <form action="results.php">
            <div class="form_group">
                <label>System Name</label>
                <input type="text" name="sysName"> <span><button type="submit">Engage!</button></span>
            </div>
    </form>
</div>