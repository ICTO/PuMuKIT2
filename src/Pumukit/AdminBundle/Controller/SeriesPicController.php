<?php

namespace Pumukit\AdminBundle\Controller;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;

class SeriesPicController extends ElementController
{
  /**
   * Render create.html
   */
  public function createAction(Request $request)
  {
    $config = $this->getConfiguration();

    if (null != $request->attributes->get('id')) {
      $id = $request->attributes->get('id');
      $pic_service = $this->get('pumukitschema.pic');
      $series = $pic_service->getResource('Series', $id);
    }else{
      $series = null;
    }
	 
    if (!isset($series)) {
      //raise error or show message
    }

    // TODO search in picservice according to page (in criteria)
    if ($request->get('page', null)) {
      $this->get('session')->set('admin/seriespic/page', $request->get('page', 1));
    }
    $page = $this->get('session')->get('admin/seriespic/page', 1);

    list($pics, $total) = $pic_service->getPics('Series', $id, $page);
	
    $view = $this
      ->view()
      ->setTemplate($config->getTemplate('create.html'))
      ->setData(array(
		      'resource' => $series,
		      'resource_name' => 'series',
		      'pics' => $pics,
		      'page' => $page,
		      'total' => $total
		      ));

    return $this->handleView($view);
  }

  /**
   * Assign a picture from an url 
   * or from an existing one
   * to the series
   */
  public function updateAction(Request $request)
  {
    $config = $this->getConfiguration();

    if ($request->get('url', null)){
      $series_id = $request->attributes->get('id');
      $pic_service = $this->get('pumukitschema.pic');
      $series = $pic_service->setPicUrl('Series', $series_id, $request->get('url'));
    }
    
    $view = $this
      ->view()
      ->setTemplate($config->getTemplate('list.html'))
      ->setData(array('series' => $series));

    return $this->handleView($view);
  }
  
}