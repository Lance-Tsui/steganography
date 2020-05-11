<?php
function transform_image($image_path, $to_ext = 'png', $save_path = null)
{
  if (!in_array($to_ext, ['png', 'gif', 'jpeg', 'wbmp', 'webp', 'xbm'])) {
    throw new \Exception('unsupport transform image to ' . $to_ext);
  }
  switch (exif_imagetype($image_path)) {
    case IMAGETYPE_GIF :
      $img = imagecreatefromgif($image_path);
      break;
    case IMAGETYPE_JPEG :
    case IMAGETYPE_JPEG2000:
      $img = imagecreatefromjpeg($image_path);
      break;
    case IMAGETYPE_PNG:
      $img = imagecreatefrompng($image_path);
      break;
    case IMAGETYPE_BMP:
    case IMAGETYPE_WBMP:
      $img = imagecreatefromwbmp($image_path);
      break;
    case IMAGETYPE_XBM:
      $img = imagecreatefromxbm($image_path);
      break;
    case IMAGETYPE_WEBP: //(从 PHP 7.1.0 开始支持)
      $img = imagecreatefromwebp($image_path);
      break;
    default :
      throw new \Exception('Invalid image type');
  }
  $function = 'image'.$to_ext;
  if ($save_path) {
    return $function($img, $save_path);
  } else {
    $tmp = __DIR__.'/'.uniqid().'.'.$to_ext;
    if ($function($img, $tmp)) {
      $content = file_get_contents($tmp);
      unlink($tmp);
      return $content;
    } else {
      unlink($tmp);
      throw new \Exception('the file '.$tmp.' can not write');
    }
  }
}
?>