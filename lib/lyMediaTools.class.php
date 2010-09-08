<?php
/*
 * This file is part of the lyMediaManagerPlugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * lyMediaTools class.
 *
 * Many functions taken from sfAssetsLibraryTools class of sfAssetsLibraryPlugin
 *
 * @package     lyMediaManagerPlugin
 * @subpackage  tools
 * @copyright   Copyright (C) 2010 Massimo Giagnoni.
 * @license     http://www.symfony-project.org/license MIT
 * @version     SVN: $Id: lyMediaTools.class.php 30741 2010-08-24 11:48:40Z mgiagnoni $
 */
class lyMediaTools
{
  public static function formatAssetCaption($asset)
  {
    return(nl2br(wordwrap($asset->getFilename(),sfConfig::get('app_lyMediaManager_caption_row_max_chars',20), "\n", true)));
  }

  public static function generateThumbnails($folder, $filename)
  {
    $source = self::getBasePath() . $folder . $filename;
    $thumbnailSettings = self::getThumbnailSettings();

    foreach ($thumbnailSettings as $key => $params)
    {
      $width  = $params['width'];
      $height = $params['height'];
      $shave  = isset($params['shave']) ? $params['shave'] : false;
      self::generateThumbnail($source, self::getThumbnailPath($folder, $filename, $key), $width, $height, $shave);
    }
  }

  public static function generateThumbnail($source, $dest, $width, $height, $shave_all = false)
  {
    if (class_exists('sfThumbnail') && file_exists($source))
    {
      if (sfConfig::get('app_lyMediaManager_use_ImageMagick', false))
      {
        $adapter = 'sfImageMagickAdapter';
        $mime = 'image/jpg';
      }
      else
      {
        $adapter = 'sfGDAdapter';
        $mime = 'image/jpeg';
      }
      if ($shave_all)
      {
        $thumbnail  = new sfThumbnail($width, $height, false, true, 85, $adapter, array('method' => 'shave_all'));
        $thumbnail->loadFile($source);
        $thumbnail->save($dest, $mime);
        return true;
      }
      else
      {
        list($w, $h, $type, $attr) = getimagesize($source);
        $newHeight = $width ? ceil(($width * $h) / $w) : $height;
        $thumbnail = new sfThumbnail($width, $newHeight, true, true, 85, $adapter);
        $thumbnail->loadFile($source);
        $thumbnail->save($dest, $mime);
        return true;
      }
    }
    return false;
  }

  public static function getAllowedExtensions()
  {
    return sfConfig::get('app_lyMediaManager_allowed_extensions',
      array('jpg','png','gif'));
  }

  public static function getAllowedMimeTypes()
  {
    return sfConfig::get('app_lyMediaManager_mime_types',
      array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'image/x-png',
        'image/gif'
      ));
  }

  public static function getAssetURI($asset)
  {
    return '/' . $asset->getPath();
  }

  public static function getBasePath()
  {
    return sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR;
  }

  public static function getThumbnailFile($asset, $type = 'small')
  {
    $thumbnail = 'unknown.png';

    if($asset->supportsThumbnails())
    {
      $thumbnail = $type . '_' . $asset->getFilename();
    }
    else
    {
      list($mtype, $mstype) = explode('/', $asset->getType());

      switch($mtype)
      {
        case 'image':
          $thumbnail = 'image-x-generic.png';
          break;
        case 'application':
          switch($mstype)
          {
            case 'pdf':
            case 'x-pdf':
              $thumbnail = 'application-pdf.png';
              break;
          }
          break;
        case 'text':
          $thumbnail = 'text-x-generic.png';
          break;
        }
      }
      
    return $thumbnail;
  }

  public static function getThumbnailFolder()
  {
    return trim(sfConfig::get('app_lyMediaManager_thumbnail_folder', 'thumbs'), "\/");
  }

  public static function getThumbnailPath($path, $filename, $thumbnailType, $create = true)
  {
    $fs = new lyMediaFileSystem();
    $folder = $fs->makePathAbsolute($path) . self::getThumbnailFolder();
    
    if($create && !file_exists($folder))
    {
      $fs->mkdir($folder);
    }
    return $folder . DIRECTORY_SEPARATOR . $thumbnailType . '_' . $filename;
  }

  public static function getThumbnailSettings()
  {
    return sfConfig::get('app_lyMediaManager_thumbnails', array(
      'small' => array('width' => 84, 'height' => 84, 'shave' => true),
      'medium' => array('width' => 194, 'height' => 152)
    ));
  }

  public static function getThumbnailURI($asset, $folder_path, $type = 'small')
  {
    if($asset->supportsThumbnails())
    {
      $img = '/' . (isset($folder_path) ? $folder_path : $asset->getFolderPath()) . self::getThumbnailFolder() . '/' . self::getThumbnailFile($asset, $type);
    }
    else
    {
      $img = '/lyMediaManagerPlugin/images/' . self::getThumbnailFile($asset, $type);
    }

    return $img;
  }

  public static function log($message, $color = '')
  {
    switch ($color)
    {
      case 'green':
        $message = "\033[32m".$message."\033[0m\n";
        break;
      case 'red':
        $message = "\033[31m".$message."\033[0m\n";
        break;
      case 'yellow':
        $message = "\033[33m".$message."\033[0m\n";
        break;
      default:
        $message = $message . "\n";
    }
    fwrite(STDOUT, $message);
  }
  public static function splitPath($path, $separator = DIRECTORY_SEPARATOR)
  {
    $path = rtrim($path, $separator);
    $dirs = preg_split('/' . preg_quote($separator, '/') . '+/', $path);
    $name = array_pop($dirs);
    $relativePath =  implode($separator, $dirs);

    return array($relativePath, $name);
  }
}