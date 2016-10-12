<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Game</title>

  <link rel="stylesheet" href="assets/css/bootstrap.css">
	<link rel="stylesheet" href="assets/css/common.css">


</head>
<body>
<div class="col-md-10 col-md-offset-1 alert alert-success super-success hidden">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <h4> </h4>
</div>

<div class="col-md-10 col-md-offset-1 alert alert-info super-info hidden">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <h4> </h4>
</div>

<div class="col-md-10 col-md-offset-1 alert alert-warning super-warning hidden">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <h4> </h4>
</div>

<div class="col-md-10 col-md-offset-1 alert alert-danger super-danger hidden">
	<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
  <h4> </h4>
</div>

<div id="container">
  <div class="row" style="padding-top:5px;padding-bottom:10px;">
    <div class="col-md-4 col-md-offset-4">
      <!-- <h4>Your Id is <span id="yourId"></span></h4> -->
    </div>
  </div>
	<div class="row">
      <div class="col-md-4 col-md-offset-1">
        <div class="form-group">
          <input type="text" class="form-control" id="gameId" placeholder="Game Id">
        </div>
				<div class="form-group">
          <input type="text" class="form-control" id="j_nick" placeholder="Your nickname">
        </div>
        <div class="form-group">
          <button class="btn btn-primary" onclick="window.common.join()">Join</button>
        </div>
      </div>
      <div class="col-md-1" style="text-align:center;">
        <h4>OR</h4>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          <input type="text" class="form-control" id="nick" placeholder="Your nickname">
        </div>
        <div class="form-group">
          <button class="btn btn-primary" onclick="window.common.create()">New Game</button>
        </div>
      </div>
  </div>
	<div class="row" style="padding-top:20px;border-top:2px dotted #333;">
			<div class="col-md-6">
				<center>
					<table id="matrixMaker">
					</table>
				</center>
			</div>
			<div class="col-md-4" id="controls">
			</div>
	</div>
</div>

</body>
<script src="assets/js/jquery-3.1.1.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/common.js"></script>
</html>
