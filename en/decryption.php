<html>
    <head>
        <title>Decryption</title>
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
            window.location.href = 'decryption.php';
        }
        function back(){
            window.location.href = 'index.php';
        }
        </script>
</head>
    <body>
    
		    
		<form action="" method="POST" enctype="multipart/form-data" name="form">
			<p align="center">需要解密的图片的密钥 
		      <input type="text" name="keyname" size="20" onKeyDown="if(event.keyCode==13){return false;}" maxlength="16"></p>
			<p align="center">&nbsp;</p>
			<p align="center">解密后的文件名(包含后缀名)&nbsp;&nbsp;&nbsp;
		      <input type="text" name="name_file" size="20" onKeyDown="if(event.keyCode==13){return false;}"></p>
			<p align="center">&nbsp;</p>
			<p align="center">需要解密的图片(png格式)&nbsp;&nbsp;&nbsp; 
		      <input type="file" name="up_picture" size="20"><p align="center">&nbsp;&nbsp;&nbsp;
			<p align="center">请确保图片是被加密过的</p>
            <p>&nbsp;</p>
            <p align="center">验证码
              <input type="text" name="code" maxlength="6" onKeyDown="if(event.keyCode==13){return false;}">
            <img  src="check.php" id = "refresh" title="refreshing" align="absmiddle" onClick="document.getElementById('refresh').src='check.php' "></p>
            <p>&nbsp;</p>
            
			<p align="center">
			<input name="decryptfile" type="submit" class="button-first" value="decryptfile">
			</p>
		</form>
        

        <center><?php
            
            
            session_start();
            if(@$_POST['code'] != @$_SESSION['img_number']){
                echo '验证码错误!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if(empty($_FILES['up_picture']['name']) || $_POST['name_file'] == ""){
                if(empty($_FILES['up_picture']['name'])){
                    echo '图片路径为空!';
                    echo '<br>';
                }else if($_POST['name_file'] == ""){
                    echo '文件路径为空!';
                    echo '<br>';
                }
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            if($_FILES['up_picture']['name'] === $_POST['name_file']){
                echo '文件名不能相同!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
                
            $keypass=$_POST["keyname"];
            $key_valid=false;
            $servername = "127.0.0.1";      //change the ip address to the server's
            $databasename = "nova_eval";
            $username = "root";

            $keypass=trim($keypass);
            if(strlen($keypass)!=16){
                echo '密钥长度不准确!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
            try {
            $conn = new PDO("mysql:host=$servername;dbname=$databasename", $username );

            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            //$sql ="SELECT * FROM `eval_pass` WHERE `value_key` = '" . $keypass . "'";
            $sql = $conn->prepare('SELECT * FROM `eval_pass` WHERE `value_key` = :pass');
            $sql->execute(array(':pass' => $keypass));
            $numcount=$sql->rowCount();

            if($numcount==0){
                $key_valid=false;
            }else{
                $sql->setFetchMode(PDO::FETCH_ASSOC);
                $row=$sql->fetch();
                $created_time=strtotime($row['create_time']);
                $temp_time=strtotime(date('Y-m-d H:i:s'));
                $diff_time=ceil(($temp_time-$created_time)/60);
                $duration=$row['duration'];
                if($diff_time<=$duration){
                    $key_valid=true;
                }else{
                    $key_valid=false;
                }
            }
            }
            catch(PDOException $e)
            {

                echo $sql . "<br>" . $e->getMessage();
            }

            $conn = null;
            
            
            if($key_valid === false){
                echo '密钥不对或失效!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }


            $pictureinfo=$_FILES['up_picture'];
            
            //$rooter=substr($_SERVER['HTTP_REFERER'],0,strlen($_SERVER["HTTP_REFERER"])-14);
            

            if(exif_imagetype($_FILES['up_picture']['tmp_name']) == IMAGETYPE_PNG){



                move_uploaded_file($pictureinfo['tmp_name'],$pictureinfo['name']);
                exec("pngasteg.exe -d " . $pictureinfo['name'] . " " .  $_POST['name_file']  . " 1 " . $keypass);
                unlink($pictureinfo['name']);
                //Header("HTTP/1.1 303 See Other");
                //Header("Location:" . $_POST['name_file']);

                //exit; 

                header("Cache-control: private, must-revalidate");
                header("Pragma: hack");
                header("Content-type: application/zip");
                header("Content-transfer-encoding: binary/n");
                header("Content-disposition: attachment; filename=" . $_POST['name_file']);
                header("Content-Length: ".filesize($_POST['name_file']));
                ob_clean();
                ob_start();
                readfile($_POST['name_file']);
                unlink($_POST['name_file']);




                //other sentences
                //do execution to the picture and file


            }
            else{
                echo '请确保图片为png格式!';
                echo '<br>';
                echo '<p align="center"><input name="refresh" type="button" class="button-fourth" onClick="refresh();" value="refresh"></p>';
                echo '<p align="center"><input name="back" type="button" class="button-fifth" onClick="back();"
                value="back"></p>';
                return;
            }
                
            
            
            ?></center>


<form method="POST" action="">
<p align="center"><input name="refresh" type="button" class="button-third" onClick="window.location.href='decryption.php'" value="refresh
">
</p>
</form>
<p align="center">&nbsp;</p>
<form method="POST" action="--WEBBOT-SELF--">
		<!--webbot bot="SaveResults" U-File="fpweb:///_private/form_results.csv" S-Format="TEXT/CSV" S-Label-Fields="TRUE" -->
		<p align="center"><input name="backmain" type="button" class="button-sixth" onClick="window.location.href='index.php'" value="back to main page
		">
		</p>
</form>



    </body>
</html>