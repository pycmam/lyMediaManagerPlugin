<?php
/*
 * This file is part of the lyMediaManagerPlugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * lyMediaTestFunctional.
 *
 * Shortcuts for common actions in functional tests.
 *
 * @package     lyMediaManagerPlugin
 * @subpackage  test
 * @copyright   Copyright (C) 2010 Massimo Giagnoni.
 * @license     http://www.symfony-project.org/license MIT
 * @version     SVN: $Id: lyMediaTestFunctional.class.php 30005 2010-06-28 00:37:26Z mgiagnoni $
 */
class lyMediaTestFunctional extends sfTestFunctional
{
  /**
   * Checks file existence in filesystem.
   *
   * @see checkFile()
   */
  public function isFile($folder, $file, $check_thumbs = true)
  {
    return $this->checkFile($folder, $file, $check_thumbs);
  }

  /**
   * Checks file non existence in filesystem.
   *
   * @see checkFile()
   */
  public function isntFile($folder, $file, $check_thumbs = true)
  {
    return $this->checkFile($folder, $file, $check_thumbs, false);
  }

  /**
   * Saves a form that creates a new record.
   *
   * @see saveForm()
   */
  public function saveNew($class, $data)
  {
    return $this->saveForm($class, $data, true);
  }

  /**
   * Saves a form that edits a record.
   *
   * @see saveForm()
   */
  public function saveEdit($class, $data)
  {
    return $this->saveForm($class, $data, false);
  }

  /**
   * Checks if asset file and thumbnails exist in filesystem.
   *
   * @param string $folder asset folder (relative path)
   * @param string $file asset filename
   * @param boolean $check_thumbs check existence/non-existence of thumbnail files
   * @param boolean $exist true=must exist, false=must not exist
   * 
   * @return lyMediaTestFunctional current lyMediaTestFunctional instance
   */
  protected function checkFile($folder, $file, $check_thumbs = true, $exist = true)
  {
    $file_path = lyMediaTools::getBasePath() . $folder . $file;

    $this->test()->is(is_file($file_path), $exist, 'File ' . $folder . $file . ($exist ? ' has ' : ' has not ') . 'been found');

    if($check_thumbs)
    {

      foreach(lyMediaTools::getThumbnailSettings() as $key => $params)
      {
        $file_path = lyMediaTools::getThumbnailPath($folder, $file, $key, false);
        $this->test()->is(is_file($file_path), $exist, 'Thumbnail ' .  $key . '_' . $file . ($exist ? ' has ' : ' has not ') . 'been found');
      }
    }
    return $this;
  }

  /**
   * Creates / edits a record by submitting an admin generator form
   *
   * @param string $class model/module (assumed same name)
   * @param array $data data sent to the form
   * @param boolean $new true=new, false=edit
   *
   * @return lyMediaTestFunctional current lyMediaTestFunctional instance
   */
  protected function saveForm($class, $data, $new = false)
  {
    return $this->click('li.sf_admin_action_save input', $data)->

    with('request')->begin()->
      isParameter('module', $class)->
      isParameter('action', $new ? 'create' : 'update')->
    end()->

    with('form')->
      hasErrors(false)->

    with('response')->
      isRedirected()->

    followRedirect()->
    with('request')->begin()->
      isParameter('module', $class)->
      isParameter('action', 'edit')->
    end()->

    with('response')->begin()->
      isStatusCode(200)->
      checkForm($class . 'Form')->
    end();
  }
}
