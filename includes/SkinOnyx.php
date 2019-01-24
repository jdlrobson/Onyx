<?php
/**
 * SkinTemplate class for the Onyx skin.
 * 
 * @file
 * @ingroup Skins
 */

 class SkinOnyx extends SkinTemplate {

  var $skinname = 'onyx';
  var $stylename = 'Onyx';
  var $template = 'OnyxTemplate';
  var $useHeadElement = true;
  
  /**
   * This function adds JavaScript to the skin, via ResourceLoader.
   * 
   * @param OutputPage $out
   */
  public function initPage(OutputPage $out) {
    parent::initPage($out);
    $out->addModules('resources/main.js');
  }

  /**
   * Add CSS to the skin, via ResourceLoader.
   * 
   * @param OutputPage $out
   */
  function setupSkinUserCss(OutputPage $out) {
    parent::setupSkinUserCss($out);
    $out->addModuleStyles(array('mediawiki.skinning.interface', 'skins.onyx'));
  }
 }