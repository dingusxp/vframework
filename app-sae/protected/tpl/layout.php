<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo $title?$title:'Untitled';?></title>
        <!-- CSS Hórus Framework -->
        <link rel="stylesheet" type="text/css" href="<?php echo $staticUrl;?>/css/horus.css">
        <!-- Your Style -->
        <link rel="stylesheet" type="text/css" href="<?php echo $staticUrl;?>/css/style.css">
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
            <script src="http://css3-mediaqueries-js.googlecode.com/files/css3-mediaqueries.js"></script>
        <![endif]-->
	</head>
<body>    
    <div class="wrapper">
        <div class="row">
            <h1 class="margintop">vframework app-demo</h1>
		</div>
    </div>
    
    <div class="wrapper">
    <?php echo $mainContent;?>
    </div>

    <div class="wrapper">
        <div class="row">
            <hr>
            <p class="aligncenter">
                copyright&copy;null
            </p>
        </div>
    </div>
    
    <?php if (defined('V_DEBUG') && V_DEBUG) { ?>
    <div class="wrapper">
        <div class="row">
            <?php V::debug('showtimeline');?>
        </div>
    </div>
    <?php } ?>
    
    <?php echo $extraScript;?>
</body>
</html>