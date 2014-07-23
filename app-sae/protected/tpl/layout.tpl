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
            <h1 class="margintop"><a href="/" title="SAE-appdemo">SAE-appdemo</a></h1>
        </div>
    </div>
    
    <div class="wrapper">
    {$mainContent}
    </div>

    <div class="wrapper">
        <div class="row">
            <hr>
            <p class="aligncenter">
                copyright&copy;vframework<br />
                <a href="http://sae.sina.com.cn" target="_blank"><img src="http://static.sae.sina.com.cn/image/poweredby/117X12px.gif" alt="SAE icon" title="Powered by Sina App Engine"></a>
            </p>
        </div>
    </div>
    
    {$extraScript}
</body>
</html>