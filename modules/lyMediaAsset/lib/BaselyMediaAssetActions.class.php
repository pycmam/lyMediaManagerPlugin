<?php
/*
 * This file is part of the lyMediaManagerPlugin package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base actions for the lyMediaManagerPlugin lyMediaAsset module.
 * 
 * @package     lyMediaManagerPlugin
 * @subpackage  lyMediaAsset
 * @copyright   Copyright (C) 2010 Massimo Giagnoni.
 * @license     http://www.symfony-project.org/license MIT
 * @version     SVN: $Id: BaselyMediaAssetActions.class.php 30501 2010-08-02 10:30:54Z mgiagnoni $
 */
abstract class BaselyMediaAssetActions extends autoLyMediaAssetActions
{
  /**
   * Shows assets as list.
   *
   * @param sfWebRequest $request
   */
  public function executeIndex(sfWebRequest $request)
  {
    $this->getUser()->setAttribute('view', 'list');
    parent::executeIndex($request);
  }
  /**
   * Shows assets as icons.
   *
   * @param sfWebRequest $request
   */
  public function executeIcons(sfWebRequest $request)
  {
    if($request->hasParameter('page'))
    {
      $this->getUser()->setAttribute('page', $request->getParameter('page'));
    }
    if($folder_id = $request->getParameter('folder_id'))
    {
      $this->getUser()->setAttribute('folder_id', $folder_id);
      $this->getUser()->setAttribute('page', 1);
    }
    $folder_id = $this->getUser()->getAttribute('folder_id', 0);
    $this->folder = Doctrine::getTable('lyMediaFolder')
      ->retrieveCurrent($folder_id);

    $this->forward404Unless($this->folder);
    $this->folders = $this->folder->getNode()->getChildren();

    if($request->hasParameter('sort'))
    {
      $this->getUser()->setAttribute('sort_field', $request->getParameter('sort'));
    }
    $this->sort_field = $this->getUser()->getAttribute('sort_field', 'name');

    if($request->hasParameter('dir'))
    {
      $this->getUser()->setAttribute('sort_dir', $request->getParameter('dir'));
    }
    $this->sort_dir = $this->getUser()->getAttribute('sort_dir');

    $this->pager = new sfDoctrinePager('lyMediaAsset', sfConfig::get('app_lyMediaManager_assets_per_page', 20));
    $this->pager->setQuery($this->folder->retrieveAssetsQuery(array(
      'sort_field' => $this->sort_field, 
      'sort_dir' => $this->sort_dir
    )));
    $this->pager->setPage($this->getUser()->getAttribute('page', 1));
    $this->pager->init();

    $this->popup = $request->getParameter('popup', 0);
    $this->getUser()->setAttribute('popup', $this->popup ? 1:0);

    if($this->popup)
    {
      $this->setLayout($this->getContext()->getConfiguration()->getTemplateDir('lyMediaAsset', 'popupLayout.php') . DIRECTORY_SEPARATOR . 'popupLayout');
      $this->getResponse()->addJavascript('tiny_mce/tiny_mce_popup');
      $this->getResponse()->addJavascript('/lyMediaManagerPlugin/js/lymedia_tiny_popup.js', 'last');
      $this->getResponse()->addStyleSheet('/lyMediaManagerPlugin/css/lymedia_popup.css');
    }
    $this->getUser()->setAttribute('view', 'icons');

    $this->folder_form = new lyMediaCreateFolderForm();
    $this->upload_form = new lyMediaUploadForm(null, array('folder' => $this->folder));
    $this->nbfolders = $this->folders ? count($this->folders) : 0;
    $this->total_size = $this->folder->sumFileSizes();
  }

  /**
   * Deletes an asset.
   * 
   * @param sfWebRequest $request
   */
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));

    if ($this->getRoute()->getObject()->delete())
    {
      $this->getUser()->setFlash('notice', 'The item was deleted successfully.');
    }

    if($this->getUser()->getAttribute('view') == 'icons')
    {
      $this->redirect('@ly_media_asset_icons?folder_id=' . $this->getUser()->getAttribute('folder_id', 0) . ($this->getUser()->getAttribute('popup', 0) ? '&popup=1' : ''));
    }
    else
    {
      $this->redirect('@ly_media_asset');
    }
  }
  
  /**
   * Uploads an asset
   * 
   * @param sfWebRequest $request 
   */
  public function executeUpload(sfWebRequest $request)
  {
    $folder = Doctrine::getTable('lyMediaFolder')
      ->retrieveCurrent($this->getUser()->getAttribute('folder_id', 0));

    $form = new lyMediaUploadForm(null, array(
      'folder' => $folder)
    );

    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));

    if($form->isValid())
    {
      $form->save();
      $this->getUser()->setFlash('notice', 'File successfully uploaded.');

    }
    else
    {
      if($form['filename']->hasError())
      {
        $msg = 'Error on file name: ';
        $msg .= $form['filename']->getError()->getMessage();
      }
      elseif($form->hasGlobalErrors())
      {
        $errors = $form->getGlobalErrors();
        $msg = $errors[0]->getMessage();
      }
      $this->getUser()->setFlash('error', $msg);
    }
    $this->redirect('@ly_media_asset_icons?folder_id=' . $this->getUser()->getAttribute('folder_id', 0) . ($this->getUser()->getAttribute('popup', 0) ? '&popup=1' : ''));
  }
}
