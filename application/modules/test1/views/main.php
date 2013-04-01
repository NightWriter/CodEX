<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Test site</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="./css/bootstrap.css" rel="stylesheet">
    <style>
      body {
        padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
      }
    </style>
    <link href="./css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

  </head>

  <body>

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">Test site</a>
          <div class="nav-collapse">
            
          <ul class="nav">
          <li class="active"><a>Menu item 1</a></li>
          </ul>
            
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>
    
<div class="container-fluid">
  <div class="row-fluid">
    <div class="span2" style="width:20%;">
<div class="accordion" id="accordion2" style="margin-right:20px">
            <div class="accordion-group">
              <div class="accordion-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
                  Left menu 1
                </a>
              </div>
              <div id="collapseOne" class="accordion-body collapse in">
                <div class="accordion-inner" style="background-color:whiteSmoke">
                 <ul class="nav nav-list">
             
              <li class="active"><a href="#">Item 1</a></li>
            </ul>
                </div>
              </div>
            </div>
          </div>    
      <!--Sidebar content-->
    </div>
    <div class="span10" style="width:80%;margin:0">
    <!-- Header ends -->
<!--    Here we go with the content -->
<?=$content?>
<!-- Footer starts -->
  </div>
 
</div>   
 <hr />
  <footer>
  © SiteName 2012
  </footer>
<script src="./js/jquery.js"></script>
<script src="./js/bootstrap-collapse.js"></script>
<script src="./js/bootstrap-modal.js"></script>

</body>
</html>