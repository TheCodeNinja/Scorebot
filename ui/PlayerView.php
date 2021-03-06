<?php
require_once 'ui/Player.php';

session_start();
		if(isset($_POST['logout'])){
	
		AuthenticationManager::logOut($_SESSION['username']);
		session_destroy();
		header( 'Location: ../index.php' ) ;
	}
		//probably should improve this
	if(! AuthenticationManager::checkSession()){
		die("You've been a naughty boy");
	}

	

	?>
	<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>CodeNinja's Scorebot</title>
<link href="../scripts/style.css" rel="stylesheet" type="text/css">
<link rel="icon" 
      type="image/png" 
      href="../images/tux-matrix.png">

<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />

  <script type="text/javascript" src="../scripts/jquery-2.1.0.js"></script>
  <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script type="text/javascript" src="../scripts/tabs.js"></script>
  <style>
  #feedback { font-size: 1.4em; }
  #selectable .ui-selecting { background: #FECA40; }
  #selectable .ui-selected { background: #F39814; color: white; }
  #selectable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
  #selectable li { margin: 3px; padding: 0.4em; font-size: 1.4em; min-height: 18px; }
  </style>
   <script>
  $(function() {
    $( "#selectable" ).selectable({
    selected: function(event,ui){document.getElementById('selflag').value=ui.selected.value;

    ui.selected.id='selecteditem';
  },
    unselected: function(event, ui){
        ui.unselected.id='unselecteditem'
    }
});
  });
window.onload=function() {

  // get tab container
  var container = document.getElementById("tabContainer");
    // set current tab
    var navitem = container.querySelector(".tabs ul li");
    //store which tab we are on
    var ident = navitem.id.split("_")[1];
    navitem.parentNode.setAttribute("data-current",ident);
    //set current tab with class of activetabheader
    navitem.setAttribute("class","tabActiveHeader");

    //hide two tab contents we don't need
    var pages = container.querySelectorAll(".tabpage");
    for (var i = 1; i < pages.length; i++) {
      pages[i].style.display="none";
    }

    //this adds click event to tabs
    var tabs = container.querySelectorAll(".tabs ul li");
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].onclick=displayPage;
    }
    <?php 
    	if(isset($_POST['getscores'])){
    		echo "document.getElementById('tabHeader_2').click();";
    	}
		if(isset($_POST['servicepassword'])){
			echo "document.getElementById('tabHeader_5').click();";
		}
    ?>
};
  
  </script>
</head>
<body>
	  <script type="text/javascript" src="../scripts/Highcharts-3.0.5/js/highcharts.js"></script>
  <script type= "text/javascript" src="../scripts/Highcharts-3.0.5/js/modules/exporting.js"></script>

<div id="pic" style="text-align:center">
<img align="middle" src="../images/nix.png"/>
</div>
<div id="wrapper">
  <div id="tabContainer">
    <div class="tabs">
      <ul>
        <li id="tabHeader_1">Flags</li>
        <li id="tabHeader_2">Score</li>
        <li id="tabHeader_5">Assets</li>
        <li id="tabHeader_4">Files</li>
        <li id="tabHeader_3">Sign Out</li>
      </ul>
    </div>
    <div class="tabscontent">
      <div class="tabpage" id="tabpage_1">
        <h2>Flags</h2>
        <p>

        
         <div style="width: 100%; height: 500px;overflow-y:scroll;">
          <ol style="width:100%;" id="selectable">
    
           <?php
          	if(isset($_POST['submitflag'])){
		if(Player::submitFlag($_POST['id'], $_POST['submission']))
		{
			echo "<script>alert('Correct!');</script>";
		}
		else
		{
			echo "<script>alert('Wrong!');</script>";
		}
		
	}
            $flags = Player::getUnsolvedFlags();
            
            while ($flag = mysql_fetch_assoc($flags)){
              echo("<li class='ui-widget-content' value = '".$flag['id']."' onclick='changeFlag(this.value)'>
                <b>Flag ".$flag['id'].": ".$flag['name']."</b><br/>".$flag['description']."<br/>(".$flag['points']." Points)</li>");
              
            }
            ?>
      
         </ol>
         </div>
         <form action="PlayerView.php" method="POST">
         	<input type="hidden" id="selflag" name="id" class="hiddenlabel" style="height:0; width:0;"/>
        	<input id="submission" name="submission"></input>
          	<input type="submit" name="submitflag" value="Submit Flag" />
         	
         </form>

   </p>
      </div>
      <div class="tabpage" id="tabpage_2">
        <h2>Scores</h2>
        <p>
        	<form action="PlayerView.php" method="POST">
        		<input name="getscores" value="Get Scores" type="submit"/>
        
        	</form>
        	 
        
        	</p>
       
        <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto">
        			
       
	<?php
        			if(isset($_POST['getscores'])){
        				$scoresJSON = Player::getFlagScores();
        				echo "<script>getScores('$scoresJSON'); </script>";
        				
        			}
        		?>





        </div>
      </div>
      
      
           <div class="tabpage" id="tabpage_5"style="clear:left;">
        <h2>Assets</h2>
        <p>
        	 
        <?php
        if(isset($_POST['servicepassword'])){
        	Player::setServicePassword($_POST['serviceid'],$_POST['password']);
        }
        $result = Player::getAssets();
		$currentAsset ="";
		while($row = mysql_fetch_assoc($result)){
			$check = Player::getLatestServiceCheck($row['id']);
			$time = date('g:i:s A',$check['timestamp']);
			if(!isset($check['timestamp'])){
			$available = "Unavailable";
			$intact ="Compromised";	
			}
			else {
			$available = $check['available']? "Available":"Unavailable";
			$intact = $check['intact']  ?"Intact":"Compromised";		
			}

			if(strcmp($row['asset_name'],$currentAsset)!=0){
				$tr = (strcmp($currentAsset,"") ==0? "": "</table>" )."
				<table class='tftable' border='1'>
				<caption><h2>{$row['asset_name']}@{$row['ip']}</h2></caption>
				<tr>
					<th>Name</th>
					<th>Port</th>
					<th>Username</th>
					<th>Password</th>
					<th>Availability</th>
					<th>Integrity</th>
					<th>Last Check</th>
				</tr>
				<tr>
						<td>{$row['name']}</td>
						<td> {$row['port']}</td>
						<td>{$row['username']} </td>
						<td> <form action='PlayerView.php' method='POST'> <input type='hidden' name='serviceid' value='{$row['id']}'/><input type='text' name='password' value ='{$row['password']}'/><input type='submit' name='servicepassword' value='Change'/></form></td>
						<td>$available</td>
						<td>$intact </td>
						<td>$time</td>
					</tr>
				";
				echo $tr;
			}
			else{
					$tr = "
					<tr>
						<td>{$row['name']}</td>
						<td> {$row['port']}</td>
						<td>{$row['username']} </td>
						<td> <form action='PlayerView.php' method='POST'> <input type='hidden' name='serviceid' value='{$row['id']}'/><input type='text' name='password' value ='{$row['password']}'/><input type='submit' name='servicepassword' value='Change'/></form></td>
						<td>$available</td>
						<td>$intact </td>
						<td>$time</td>
					</tr>
				";
				echo $tr;
			}
		}
        
        
        ?>
   </table>


        
        </p>
      </div>
      
      
      
      <div class="tabpage" id="tabpage_3">
        <h2>Sign Out</h2>
        <p>
        
       <form action="PlayerView.php" method="POST">
       	<input type="submit" name= "logout" value="Sign Out" />
    
       </form>
        
        </p>
      </div>

       <div class="tabpage" id="tabpage_4">
        <h2>Files</h2>
        <p>
        
      <div style="width: 100%; height: 500px;overflow-y:scroll;">
          <ol style="width:100%;" id="selectable">
          <?php
          
            $files = Player::getFiles();
            
            while( $file = mysql_fetch_assoc($files)){
              echo("<li class='ui-widget-content ui-selectee' style='list-style-type: none;' >
                <b>File ".$file['id'].": ".$file['name']."</b><br/>".$file['description']."<a style='text-decoration:underline;padding-top:1px; color:#000000; text-align:left;' href='../".$file['link']."'>Here</a></li>");
              
            }
            ?>
       </ol>
          </div>
        </p>
      </div>
    </div>
  </div>

</body>
</html>
