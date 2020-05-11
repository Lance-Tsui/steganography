<html>
    <head>
        <title>Encryption</title>
        <meta http-equiv="Content-Language" content="zh-cn">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
        <style type="text/css">
<!--
@import url("button.css");
-->
        </style>
        
        <script>
        function refresh(){
            window.location.href = 'encryption.php';
        }
        function back(){
            window.location.href = 'index.php';
        }
        </script>
</head>
    <body>
    
		    
		<form action="" method="POST" enctype="multipart/form-data" name="form">
		  <p align="center">&nbsp;</p>
			<p align="center">the picture you want to show(.png or .jpg)&nbsp;&nbsp;&nbsp; 
			  <input type="file" name="up_picture" size="20"><p align="center">&nbsp;&nbsp;&nbsp;
			<p align="center">files you want to encrypt&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
		      <input type="file" name="up_file" size="20"></p>
			<p align="center">&nbsp;</p>
			<p align="center">set valid time for key	
			  <select name="duration" size="1">
			    <option value="5" selected>5</option>
			    <option value="10">10</option>
			    <option value="30">30</option>
			    <option value="60">60</option>
		      </select>
			</p>
			<p>&nbsp;</p>
            <p align="center">verify code
              <input type="text" name="code" maxlength="6" onKeyDown="if(event.keyCode==13){return false;}">
            <img  src="check.php" id = "refresh" title="refreshing" align="absmiddle" onClick="document.getElementById('refresh').src='check.php' "></p>
            <p>&nbsp;</p>
			<p align="center">
			<input name="encryptfile" type="submit" class="button-third" value="encryptfile">
			</p>

		</form>
        

        <center><?php include("transform.php");
            
            
            session_start();
            if(@$_POST['code'] != @$_SESSION['img_number']){
                echo 'verify code wrong!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if(empty($_FILES['up_picture']['name']) || empty($_FILES['up_file']['name'])){
                if(empty($_FILES['up_picture']['name'])){
                    echo 'picture link not valid!';
                    echo '<br>';
                }else if(empty($_FILES['up_file']['name'])){
                    echo 'file link not valid!';
                    echo '<br>';
                }
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if($_FILES['up_picture']['name'] === $_FILES['up_file']['name']){
                echo 'file name cannot be same!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            

            $servername = "127.0.0.1";      //change the ip address to the server's
            $databasename = "nova_eval";
            $username = "root";
            $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
            $key_valid=false;
            $keypass='';
            $password = '';
            $duration=$_POST['duration'];
            while($key_valid===false){
            try {
            $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);    
            //$sql ="SELECT * FROM `eval_pass` WHERE `value_key` = '" . $keypass . "'";
            $keypass='';
            
            for ($keynumber = 0; $keynumber<16; $keynumber++) {   
                $keypass .= $pattern{mt_rand(0,60)};
            }
            //echo $keypass;
            $sql = $conn->prepare('SELECT * FROM `eval_pass` WHERE `value_key` = :pass');
            $sql->execute(array(':pass' => $keypass));

            $numcount=$sql->rowCount();
            if($numcount==0){
                $key_valid=true;
            }else{
                $key_valid=false;
            }


            }
            catch(PDOException $e)
            {

                echo $sql . "<br>" . $e->getMessage();
            }

            $conn = null;
            }

            $pictureinfo=$_FILES['up_picture'];
            $fileinfo=$_FILES['up_file'];

            $newpicname= $keypass . ".png";
            
            //$rooter=substr($_SERVER['HTTP_REFERER'],0,strlen($_SERVER["HTTP_REFERER"])-14);
            
            
            if(exif_imagetype($_FILES['up_picture']['tmp_name']) == IMAGETYPE_JPEG
               || exif_imagetype($_FILES['up_picture']['tmp_name']) == IMAGETYPE_GIF){
                move_uploaded_file($pictureinfo['tmp_name'],$pictureinfo['name']);
                move_uploaded_file($fileinfo['tmp_name'],$fileinfo['name']);
                transform_image($pictureinfo['name'], 'png', './' . $pictureinfo['name']);
                $imagearr=getimagesize($pictureinfo['name']);
                $imagesize=$imagearr[0]*$imagearr[1];
                $encryptsize = $fileinfo['size'] + 16 * 8;
                $realquotient=floor($imagesize/$encryptsize);
                if($fileinfo['size'] > 0 && $realquotient > 8){
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);

                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO eval_pass (value_key,create_time,duration)
                    VALUES ('" . $keypass . "', '" .  date('Y-m-d H:i:s') . "', '" . $duration . "')";

                    $conn->exec($sql);

                }
                catch(PDOException $e){

                    echo $sql . "<br>" . $e->getMessage();
                }

                $conn = null;

                exec("pngasteg.exe -e " . $pictureinfo['name'] . " " .  $newpicname .
                     " " . $fileinfo['name'] . " 1 " . $keypass);

                unlink($pictureinfo['name']);
                unlink($fileinfo['name']);
                //Header("HTTP/1.1 303 See Other");
                //Header("Location:" . $newpicname);

                //exit; 
                header("Cache-control: private, must-revalidate");
                header("Pragma: hack");
                header("Content-type: application/zip");
                header("Content-transfer-encoding: binary/n");
                header("Content-disposition: attachment; filename=" . $newpicname);
                header("Content-Length: ".filesize($newpicname));
                ob_clean();
                ob_start();
                readfile($newpicname);
                unlink($newpicname);
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;  
                }
                else{
                    echo 'your width of picture: ' . $imagearr[0] . ' length of picture: ' . $imagearr[1];
                    echo '<br>';
                    echo 'your size of file:  ' . $fileinfo['size'];
                    echo '<br>';
                    echo 'file is too large compare to picture!';
                    echo '<br>';
                    unlink($pictureinfo['name']);
                    unlink($fileinfo['name']);
                    echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                    echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                    value="back"></p>';
                    return;  
                }
            }
            
            elseif(exif_imagetype($_FILES['up_picture']['tmp_name']) == IMAGETYPE_PNG){
                move_uploaded_file($pictureinfo['tmp_name'],$pictureinfo['name']);
                move_uploaded_file($fileinfo['tmp_name'],$fileinfo['name']);
                $imagearr=getimagesize($pictureinfo['name']);
                $imagesize=$imagearr[0]*$imagearr[1];
                $encryptsize = $fileinfo['size'] + 16 * 8;
                $realquotient=floor($imagesize/$encryptsize);
                if($fileinfo['size'] > 0 && $realquotient > 8){
                try {
                    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username, $password);

                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $sql = "INSERT INTO eval_pass (value_key,create_time,duration)
                    VALUES ('" . $keypass . "', '" .  date('Y-m-d H:i:s') . "', '" . $duration . "')";

                    $conn->exec($sql);

                }
                catch(PDOException $e){

                    echo $sql . "<br>" . $e->getMessage();
                }

                $conn = null;

                exec("pngasteg.exe -e " . $pictureinfo['name'] . " " .  $newpicname .
                     " " . $fileinfo['name'] . " 1 " . $keypass);

                unlink($pictureinfo['name']);
                unlink($fileinfo['name']);
                //Header("HTTP/1.1 303 See Other");
                //Header("Location:" . $newpicname);

                //exit; 
                header("Cache-control: private, must-revalidate");
                header("Pragma: hack");
                header("Content-type: application/zip");
                header("Content-transfer-encoding: binary/n");
                header("Content-disposition: attachment; filename=" . $newpicname);
                header("Content-Length: ".filesize($newpicname));
                ob_clean();
                ob_start();
                readfile($newpicname);
                unlink($newpicname);
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;  
                }
                else{
                    echo 'your width of picture: ' . $imagearr[0] . ' length of picture: ' . $imagearr[1];
                    echo '<br>';
                    echo 'your file size is:  ' . $fileinfo['size'];
                    echo '<br>';
                    echo 'file is too large compare to picture!';
                    echo '<br>';
                    unlink($pictureinfo['name']);
                    unlink($fileinfo['name']);
                    echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                    echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                    value="back"></p>';
                    return;  
                }
            }
            else{
                echo 'please ensure it is .png format!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;  
            }
            
            
            ?></center>

        
    </body>
</html>
