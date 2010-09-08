<?php
/*
 * This file is part of the lyMediaManagerPlugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generator configuration class for the lyMediaManagerPlugin lyMediaFolder module.
 *
 * @package     lyMediaManagerPlugin
 * @subpackage  lyMediaFolder
 * @copyright   Copyright (C) 2010 Massimo Giagnoni.
 * @license     http://www.symfony-project.org/license MIT
 * @version     SVN: $Id: lyMediaFolderGeneratorConfiguration.class.php 29999 2010-06-26 01:33:51Z mgiagnoni $
 */
class lyMediaFolderGeneratorConfiguration extends BaseLyMediaFolderGeneratorConfiguration
{
  public function getFormOptions()
  {
    return array(
      'user' => sfContext::getInstance()->getUser()
    );
  }
}
