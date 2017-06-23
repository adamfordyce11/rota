<?php
include_once 'inc/db_connect.php';
include_once 'inc/functions.php';

sec_session_start();

if (login_check($mysqli) == true) {
    $logged = 'in';
} else {
    $logged = 'out';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Adam Fordyce">
    <link rel="icon" href="img/favicon.ico">

    <title>Monthly - Marine Support Rota</title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/sticky-footer-navbar.css" rel="stylesheet" media='print'>
    <link rel="stylesheet" href="css/monthly.css" />

  </head>

  <body>

    <!-- Fixed navbar -->
    <nav class="navbar navbar-toggleable-md navbar-inverse navbar-fixed-top bg-inverse container-float">
      <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <a class="navbar-brand" href="#">Planner</a>
      <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php">Month Overview </a>
          </li>
          <li class="nav-item active">
            <a class="nav-link" href="monthly.php">Sign off sheet <span class="sr-only">(current)</span></a>
          </li>
        </ul>
        <form class="form-inline mt-2 mt-md-0">
          <input class="form-control mr-sm-2" type="text" placeholder="Search">
          <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
        </form>
        <ul class="navbar-nav pull-right">
        <?php if($logged == "in"){ ?>
          <li class="nav-item pull-right">
            <a href="inc/logout.php" class="nav-link">
              <span class="glyphicon glyphicon-log-in"></span> Log Out </a>
            </a>
          </li>
        <?php } else { ?>
          <li class="nav-item pull-right">
            <a href="login.php" class="nav-link">
              <span class="glyphicon glyphicon-log-out"></span> Log In </a>
            </a>
          </li>
        <?php } ?>
        </ul>
      </div>
    </nav>

    <!-- Begin page content -->
    <div class="container-float">
      <div class="row" style="margin-left:0px; margin-right:0px;">
        <div class="col-md-12">
          <div class="row">
            <div class="col-10">
              <center><h1>Marine Support Rota - Sign off sheet</h1></center>
            </div>
          </div>
    <?php if ($logged == "in") { ?>
          <div class="row">
      <?php if (isset($_GET['error'])) { echo '<p class="error">Error Logging In!</p>'; } ?>
            <div class="col-1"></div>
            <div class="col-8" id="month1" edit="<?php echo htmlentities($_SESSION['username']); ?>" uid="<?php echo htmlentities($_SESSION['user_id']);?>"></div>
            <div class="col-2">
              <div class="row">
                <div id="month1sum"></div>
              </div>
              <div class="row">
                <div id="month1sumKey"></div>
              </div>
            </div>
          </div>
      <?php } else { ?>
          <div class="row">
    <?php if (isset($_GET['error'])) { echo '<p class="error">Error Logging In!</p>'; } ?>
            <div class="col-1"></div>
            <div class="col-8" id="monthnoedit"></div>
            <div class="col-2">
              <div class="row">
                <div id="monthnoeditsum"></div>
              </div>
              <div class="row">
                <div id="monthnoeditsumkey"></div>
              </div>
            </div>
          </div>
    <?php } ?>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="container">
        <div class="row">
        <?php if ($logged == "in") { ?>
          <div class="col-1"></div>
          <div class="col-10 text-muted"><center><?php echo "Logged in as ". htmlentities($_SESSION['username'])." Editable view";?></center></div>
          <div class="col-1"></div>
        <?php } else { ?>
          <div class="col-1"></div>
          <div class="col-10 text-muted"><center>Read only view<center></div>
          <div class="col-1"></div>
        <?php } ?>
        </div>
      </div>
    </footer>


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery-3.2.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="js/ie10-viewport-bug-workaround.js"></script>
    <script type="text/JavaScript" src="js/monthly.js"></script>
  </body>
</html>
