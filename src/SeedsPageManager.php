<?php

namespace Drupal\seeds_page;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Routing\CurrentRouteMatch;
use Exception;

/**
 *
 */
class SeedsPageManager {

  /**
   * Accepted Entities to render the banner
   *
   * @var array
   */
  public $accepted_entities = [
      'node',
      'media',
      'taxonomy_term',
      'user'
  ];

  /**
   * Renderer.
   *
   * @var Renderer $renderer
   */
  protected $renderer;

  /**
   * Image Factory.
   *
   * @var ImageFactory $imageFactory
   */
  protected $imageFactory;

  /**
   * Current Route Match.
   *
   * @var CurrentRouteMatch $routeMatch
   */
  protected $routeMatch;

  /**
   * Seeds page configurations.
   *
   * @var ImmutableConfig $seedsPageConfig
   */
  protected $seedsPageConfig;

  /**
   *
   */
  public function __construct(Renderer $renderer, ImageFactory $image_factory, CurrentRouteMatch $route_match, ConfigFactory $seeds_page_config) {
    $this->imageFactory = $image_factory;
    $this->renderer = $renderer;
    $this->routeMatch = $route_match;
    $this->seedsPageConfig = $seeds_page_config->get('seeds_page.config');
  }

  /**
   *
   */
  public static function isParagraph($entity) {
    return $entity && $entity->getEntityTypeId() === 'paragraph' && $entity->bundle() === 'seeds_paragraph';
  }

  /**
   *
   */
  public static function isBlock($entity, $type = NULL) {
    if ($type) {
      return $entity && $entity->getEntityTypeId() === 'block_content' && $entity->bundle() === $type;
    }

    return $entity && $entity->getEntityTypeId() === 'block_content' && in_array($entity->bundle(), PARAGRAPH_BLOCKS, true);
  }

  /**
   *
   */
  public function isAcceptedEntity($entity_type_id){
    return in_array($entity_type_id, $this->accepted_entities, TRUE);
  }

  /**
   * Returns the node or term from current page.
   *
   * @return EntityInterface
   */
  public function getEntityFromCurrentPage() {
    $always_render_banner = $this->seedsPageConfig->get('always_render_banner');
    if(!$always_render_banner){
      $route_name = $this->routeMatch->getRouteName();
      $matches = [];
      $entity_landing = preg_match('/entity\.([\w_]+)\.canonical/', $route_name, $matches);
      $current_entity_type = $matches[1] ?? NULL;
      /** @var EntityInterface $entity */
      $entity = $this->routeMatch->getParameter($current_entity_type);
      if($entity_landing && $entity && $this->isAcceptedEntity($current_entity_type)){
        $entity_types = $this->seedsPageConfig->get('entity_types');
        $allowed_bundle = $entity_types[$current_entity_type][$entity->bundle()] ?? NULL;
        if ($allowed_bundle) {
          return $entity;
        }
      }
    } else {
      $route_params = $this->routeMatch->getParameters();
      foreach ($route_params as $param){
        if($param instanceof FieldableEntityInterface){
          return $param;
        }
      }
    }
    return NULL;
  }

  /**
   *
   * @throws Exception
   */
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
    return $this->renderer->render($image_build);
  }

}
