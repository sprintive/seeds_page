<?php

namespace Drupal\seeds_page;

use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactory;

class SeedsPageManager {

  /**
   * Renderer
   *
   * @var Renderer
   */
  protected $renderer;

  /**
   * Image Factory
   *
   * @var ImageFactory
   */
  protected $imageFactory;

  /**
   * Image Factory
   *
   * @var CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Seeds page configurations.
   *
   * @var ImmutableConfig
   */
  protected $seedsPageConfig;

  public function __construct(Renderer $renderer, ImageFactory $image_factory, CurrentRouteMatch $route_match, ConfigFactory $seeds_page_config) {
    $this->imageFactory = $image_factory;
    $this->renderer = $renderer;
	$this->routeMatch = $route_match;
	$this->seedsPageConfig = $seeds_page_config->get('seeds_page.config');
  }

  public static function isParagraph($entity) {
    return $entity && $entity->getEntityTypeId() == 'paragraph' && $entity->bundle() == 'seeds_paragraph';  
}

  public static function isBlock($entity, $type = NULL) {
    if ($type) {
      return $entity && $entity->getEntityTypeId() == 'block_content' && $entity->bundle() == $type;
    } else {
      return $entity && $entity->getEntityTypeId() == 'block_content' && in_array($entity->bundle(), PARAGRAPH_BLOCKS);
    }
  }

  /**
   * Retuns the node or term from current page.
   *
   * @return EntityInterface
   */
  public function getEntityFromCurrentPage() {
	$route_name = $this->routeMatch->getRouteName();
	$matches = [];
	$entity_landing = preg_match('/entity\.([\w_]+)\.canonical/',$route_name,$matches);
	$current_entity_type = $matches[1];
	$entity = $this->routeMatch->getParameter($current_entity_type);
	$entity_types = $this->seedsPageConfig->get('entity_types');
	if($entity_landing && $entity && $entity_types[$current_entity_type][$entity->bundle()]) {
		return $entity;
	}
	return NULL;
  }

  public function toResponsiveImage($file, $responsive_image_id) {
    if (!$file) {
      return [];
    }

    $responsive_image = '';
    $file_uri = $file->getFileUri();

    $image = $this->imageFactory->get($file->getFileUri());

    if ($image->isValid()) {
      $width = $image->getWidth();
      $height = $image->getHeight();
    } else {
      $width = $height = NULL;
    }
    $image_build = [
      '#theme' => 'responsive_image',
      '#width' => $width,
      '#height' => $height,
      '#responsive_image_style_id' => $responsive_image_id,
      '#uri' => $file_uri,
    ];
    $this->renderer->addCacheableDependency($image_build, $file);
    $responsive_image = $this->renderer->render($image_build);
    return $responsive_image;
  }
}