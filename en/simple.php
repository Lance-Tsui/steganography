

<html>
    <head>
        <title>Simple</title>
        <meta http-equiv="Content-Language" content="zh-cn">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/> 
	    <style>
		.box{
            overflow: hidden;
            resize: vertical;
        }
        .first{
			width: 50%;
            background-color: #E0FFCC;
            float: left;

        }
        .second{
			width: 50%;
            background-color: #FCCCFF;
            float: left;
        }
        .second, .first{
            margin-bottom: -600px;
            padding-bottom:600px;
        }


		
    </style>
	<style type="text/css">
	<!--
	@import url("button.css");
	-->
    </style>

</head>
<body>
	<div class = "box">
    <div class="first" >
	 <form action="" method="POST" enctype="multipart/form-data">
     <p align = "center"><textarea cols = "30" rows = "5" maxlength = "140" name="textarea" id = "textarea">text before encryption/text after decryption</textarea></p>
	 <p align="center">
			  <input name="clear" type="button" value="Clear" onclick="ClearTextArea()">
	 </p>
	 <p align = "center">&nbsp;</p>
	 
	
	 <p align = "center"><input id="file" class="filepath" onChange="changepic(this)" type="file" name = "file"></p>
	
	
	
	<p align="center">&nbsp;</p>
	 <div class = "first">
	 <p align="center">
			  <input name="encrypt" type="submit" class="button-third" value="Encrypt">
	 </p>
	 <p align="center">
			  <input name="back" type="button" class="button-second" value="Back" onClick="window.location.href='index.php'">
	 </p>
	 
     </div>
     <div class = "first">
	 <p align="center">
			  <input name="decrypt" type="submit" class="button-sixth" value="Decrypt">
	 </p>
	 <p align="center">
			  <input name="help" type="button" class="button-fourth" value="Help" onClick="window.location.href='help.php'">
	 </p>
     </div>
	 </form>
    </div>
    <div class="second">
		<p align = "center"><img src="" id="show" width = "80%" ></p>
	</div>
	</div>
    

</div>


  
</body>
<script>
	function ClearTextArea()
	{
		     document.getElementById("textarea").value="text before encryption/text after decryption";
	} 
    function changepic(obj) {
        //console.log(obj.files[0]);//这里可以获取上传文件的name
        var newsrc=getObjectURL(obj.files[0]);
        document.getElementById('show').src=newsrc;
    }
    //建立一個可存取到該file的url
    function getObjectURL(file) {
        var url = null ;
        // 下面函数执行的效果是一样的，只是需要针对不同的浏览器执行不同的 js 函数而已
        if (window.createObjectURL!=undefined) { // basic
            url = window.createObjectURL(file) ;
        } else if (window.URL!=undefined) { // mozilla(firefox)
            url = window.URL.createObjectURL(file) ;
        } else if (window.webkitURL!=undefined) { // webkit or chrome
            url = window.webkitURL.createObjectURL(file) ;
        }
        return url ;
    }
	
	
	
</script>
<center>

<?php   
		include("transform.php");
	    include("random.php");	
		if(!empty($_POST['encrypt'])) {
			$text = @$_POST['textarea'];
			$text = trim($text);
			$randomkeys = randomkeys();
			if($text == ''){
				echo 'text is empty!';
				return;
			}
			if(empty($_FILES['file']['name'])){
				echo 'picture link is not valid!';
				return;
			}
			$picinfo = $_FILES['file'];
			if(exif_imagetype($_FILES['file']['tmp_name']) != IMAGETYPE_PNG){
				move_uploaded_file($picinfo['tmp_name'],$picinfo['name']);
				transform_image($picinfo['name'], 'png', './' . $picinfo['name']);
			}
			exec("echo " . $text . ">test.txt");
			exec("pngasteg.exe -e " . $picinfo['name'] . " " . $randomkeys  .
                     " " . "test.txt");
			unlink("test.txt");
			unlink($picinfo['name']);
			echo "<script type='text/javascript'>document.getElementById('show').src='" . $randomkeys  . "';</script>";
			echo 'encryption successful! click image on the right side to download!';
		}else{
			if(empty($_FILES['file']['name'])){
				echo 'picture link is not valid!';
				return;
			}
			$picinfo = $_FILES['file'];
			if(exif_imagetype($_FILES['file']['tmp_name']) != IMAGETYPE_PNG){
				echo 'please ensure the picture is .png format!';
				return;
			}
			move_uploaded_file($picinfo['tmp_name'],$picinfo['name']);
            exec("pngasteg.exe -d " . $picinfo['name'] . " " .  'test_out.txt');
			
			
			$file_path = "test_out.txt";
			$str = '';
			if(file_exists($file_path)){
			$fp = fopen($file_path,"r");
			$str = fread($fp,filesize($file_path));//指定读取大小，这里把整个文件内容读取出来
			
			$str = iconv('gbk', 'utf-8', $str);
			$str = str_replace("\r\n","",$str);
			}
			echo "<script type='text/javascript'>document.getElementById('textarea').value= 'text after decryption: " . $str  . "';</script>";
			echo "<script type='text/javascript'>document.getElementById('show').src='';</script>";
			echo "decryption successful!";
			unlink($picinfo['name']);
			unlink("test_out.txt");
        }
?></center>


</html>
