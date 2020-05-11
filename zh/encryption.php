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
			<p align="center">显示在首页的图片(png或jpg格式)&nbsp;&nbsp;&nbsp; 
			  <input type="file" name="up_picture" size="20"><p align="center">&nbsp;&nbsp;&nbsp;
			<p align="center">要加密的文件&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
		      <input type="file" name="up_file" size="20"></p>
			<p align="center">&nbsp;</p>
			<p align="center">密钥有效时间(分钟)	
			  <select name="duration" size="1">
			    <option value="5" selected>5</option>
			    <option value="10">10</option>
			    <option value="30">30</option>
			    <option value="60">60</option>
		      </select>
			</p>
			<p>&nbsp;</p>
            <p align="center">验证码
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
                echo '验证码错误!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if(empty($_FILES['up_picture']['name']) || empty($_FILES['up_file']['name'])){
                if(empty($_FILES['up_picture']['name'])){
                    echo '图片路径为空!';
                    echo '<br>';
                }else if(empty($_FILES['up_file']['name'])){
                    echo '文件路径为空!';
                    echo '<br>';
                }
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if($_FILES['up_picture']['name'] === $_FILES['up_file']['name']){
                echo '文件名不能相同!';
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
            $duration=$_POST['duration'];
            while($key_valid===false){
            try {
            $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username);
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
                    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username );

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
                    echo '您的图片宽度为: ' . $imagearr[0] . ' 图片长度为: ' . $imagearr[1];
                    echo '<br>';
                    echo '您的文件大小为:  ' . $fileinfo['size'];
                    echo '<br>';
                    echo '文件相比于图片过大!';
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
                    $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username );

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
                    echo '您的图片宽度为: ' . $imagearr[0] . ' 图片长度为: ' . $imagearr[1];
                    echo '<br>';
                    echo '您的文件大小为:  ' . $fileinfo['size'];
                    echo '<br>';
                    echo '文件相比于图片过大!';
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
                echo '请确保上传的图片是.png格式!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;  
            }
            
            
            ?></center>

        
    </body>
</html>
