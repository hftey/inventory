
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Venz Inventory Management System</title>



<link href="/template/default/style.css" media="screen" rel="stylesheet" type="text/css" /><link rel="stylesheet" type="text/css" href="/css/pager.css" />

<!-- jQuery 1.10.2 -->
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>


<link rel="stylesheet" type="text/css" href="/js/jquery/impromptu/jquery-impromptu.css" />
<script type="text/javascript" src="/js/jquery/impromptu/jquery-impromptu.js"></script>	
<script language='Javascript'>


$(document).ready(function() {

})
</script>
</head>
<div id="divJQ"></div>
<!-- Load Bootstrap 2.3.2 stylesheets -->
<link rel="stylesheet" type="text/css" href="/wayout/css/bootstrap.min.css" media="screen" />
<link rel="stylesheet" type="text/css" href="/wayout/css/bootstrap-responsive.min.css" media="screen" />
<!-- Load Fonts -->
<link rel="stylesheet" type="text/css" href="/wayout/css/fonts.css" />
<!-- WAYOUT MENU -->
<link rel="stylesheet" type="text/css" href="/wayout/css/wayoutmenu.css" />
<link rel="stylesheet" type="text/css" href="/wayout/css/wayoutmenueffects.css" />
<link rel="stylesheet" type="text/css" href="/wayout/css/wayoutmenuresponsive.css" />
  
<!-- Lightbox stylesheet -->
<link rel="stylesheet" type="text/css" href="/wayout/css/jquery.lightbox.min.css" />
<!-- Input slider -->
<link href="/wayout/css/slider.css" rel="stylesheet" type="text/css" />
<!-- Color Picker --> 
<link rel="stylesheet" type="text/css" href="/wayout/css/spectrum.css" />


<!-- Bootstrap -->
<script src="/wayout/js/bootstrap.min.js"></script>
<!-- Lightbox -->
<script src="/wayout/js/jquery.lightbox.min.js"></script>  
<!-- Wayout Menu -->
<script src="/wayout/js/wayoutmenu.js"></script>  

<!-- Wayout Menu DEMO (Theme customization) -->
<script src="/wayout/js/demo.js"></script> 
<!-- Color Picker for DEMO -->
<script src="/wayout/js/spectrum.js"></script>  
<!-- Input Slider for DEMO -->
<script src="/wayout/js/slider.min.js"></script>

<script>

$(document).ready(function() {
	});
</script>
<!--[if lt IE 10]>
<script type="text/javascript">
$(document).ready(function() {



  $('.hidden-menu').css('display','none');
});
</script>
<![endif]-->
<body>


  <!-- MAIN CONTAINER -->
  <div id="main-container" class="container-fluid relative overflow-hidden full-height">
    <!-- Side Menu (.st-effect-1) -->
    <nav class="hidden-menu st-effect-1" id="menu-1">
      <h2>Links</h2>
	      </nav>
    <!-- /Side Menu (.st-effect-1) -->
    
    <!-- WRAPPER -->
    <div class="wrapper relative full-height">
      
      <!-- ST-CONTENT -->
      <div class="st-content relative full-height"><!-- this is the wrapper for the content -->
        <!-- ST-CONTENT-INNER -->
        <div class="st-content-inner relative">
          <!-- MAIN -->
          <div id="mainn" class="main" style="position:relative">
            <!-- MENU-BG -->
            <div class="container-fluid menu-bg" style="width: 100%;">
              <!-- BM-CONTAINER -->
			<div class="row-fluid site-width bm-container">
                <div class="site-title"><table border=0><TR><TD><a href='/'><img src='/images/icons/logo-small.png'></a></TD><TD><h1>Inventory System</TD></TR></Table></div>
                <!-- Wayout Menu - TOP NAV -->
                <div class="top-nav hidden-phone">
				
				
				
				<ul class="navigation">
    <li>
        <a id="menu-home-link" href="/">Home</a>
    </li>
    <li>
        <a href="/#">Administration</a>
        <ul>
            <li>
                <a href="/brand">Brand</a>
            </li>
            <li>
                <a href="/admin/acl/users">User Management</a>
            </li>
        </ul>
    </li>
</ul>				
				
				
                </div>
                <!-- /Wayout Menu - TOP NAV -->

                
            </div>
            <!-- /MENU-BG -->
          </div>
          <!-- MAIN -->
          

          <!-- !!!!!!!!!!! YOUR CONTENT GOES HERE !!!!!!!!!!! -->
     


		<div id="content">
		<div id="content_top"></div>
        <div id="content_main">
				          
<div class="alert alert-error">System Error<BR><BR>Please inform administrator on the error details below<HR>&nbsp;

<?=$msgError?> <BR><BR>
<?echo "<div style='word-wrap: break-word; width:800px;'>".base64_encode(print_r($_SERVER, true))."</div>"; ?>

</div>


		</div>
		
        <div id="content_bottom"></div>
            
		</div>
          <!-- !!!!!!!!!!! /YOUR CONTENT GOES HERE !!!!!!!!!!! -->
          
        </div>
        <!-- /ST-CONTENT-INNER -->
      </div>
      <!-- /ST-CONTENT -->
    </div>
    <!-- /WRAPPER -->

  </div>
  <!-- /MAIN CONTAINER -->
<script>
  // Initiate Lightbox
  $(function() {
    $('.image-item > a').lightbox(); 
  });
</script>


</body>
</html>


