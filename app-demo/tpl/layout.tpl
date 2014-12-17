<!doctype html>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{$title|default:"Untitled"}</title>
    <!-- CSS Hórus Framework -->
    <link rel="stylesheet" type="text/css" href="{$staticUrl}/css/horus.css">
    <!-- Your Style -->
    <link rel="stylesheet" type="text/css" href="{$staticUrl}/css/style.css">
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
    {$mainContent}
    </div>

    <div class="wrapper">
        <div class="row">
            <hr>
            <p class="aligncenter">
                copyright&copy;null
            </p>
        </div>
    </div>
    
    {if defined('V_DEBUG') && V_DEBUG}
    <div class="wrapper">
        <div class="row">
            {Timer::getInstance()->output()}
        </div>
    </div>
    {/if}

    {$extraScript}
</body>
</html>