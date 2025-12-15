<?php 
require('EDSMclass.php'); 
?>
<html>
<header>
    <title>EDSM System Analysis</title>
     <link rel="stylesheet" href="./css/style.css">
      <!-- font awesome -->
     <link rel="stylesheet" type="text/css" href="//use.fontawesome.com/releases/v5.7.2/css/all.css">
     
     <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
         <script src="./js/index.js"></script>
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