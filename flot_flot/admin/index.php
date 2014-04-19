<?php
	# manage site

	require_once('../core/flot.php');


	$flot = new Flot;

	if(!$flot->b_is_user_admin()){
		# forward them to login page
		$flot->_page_change("/flot_flot/admin/login.php");
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<?php
			echo $flot->s_admin_header();
		?>
	</head>
	<body>




		<nav class="navbar navbar-default" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">flot</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#">help</a></li>
        <li><a href="logout.php">logout</a></li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>


      <!-- tabs left -->
      <div class="tabbable tabs-left">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#a" data-toggle="tab">Webpages</a></li>
          <li><a href="#b" data-toggle="tab">Pictures</a></li>
          <li><a href="#b" data-toggle="tab">Pictures</a></li>
          <li><a href="#c" data-toggle="tab">Menus</a></li>
          <li><a href="#d" data-toggle="tab">Settings</a></li>
        </ul>
        <div class="tab-content">
         <div class="tab-pane active" id="a">
         	Webpages
         </div>
         <div class="tab-pane" id="b">
         	Pictures
         </div>
         <div class="tab-pane" id="c">
         	Menus
         </div>
         <div class="tab-pane" id="d">
         	Settings
         </div>
        </div>
      </div>
      <!-- /tabs -->

	</body>
</html>