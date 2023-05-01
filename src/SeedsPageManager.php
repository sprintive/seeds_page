<?php

namespace Drupal\seeds_page;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 *
 */
class SeedsPageManager {

  /**
   * Block types provided by Seeds.
   */
  protected const PARAGRAPH_BLOCKS = [
    'seeds_html',
    'seeds_paragraph',
    'seeds_slider',
    'seeds_modal',
    'seeds_accordion',
    'seeds_carousel',
    'seeds_tabs',
    'seeds_grid',
  ];

  /**
   * Accepted entity types supported by Seeds to render the banner.
   */
  protected const ACCEPTED_ENTITY_TYPES = [
    'node',
    'media',
    'taxonomy_term',
    'user',
  ];

  /**
   * Renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Image Factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * Current Route Match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * Config Factory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Construct new SeedsPageManager object.
   */
  public function __construct(
        Renderer $renderer,
        ImageFactory $image_factory,
        CurrentRouteMatch $route_match,
        ConfigFactoryInterface $config_factory
    ) {
    $this->imageFactory = $image_factory;
    $this->renderer = $renderer;
    $this->routeMatch = $route_match;
    $this->configFactory = $config_factory;
  }

  /**
   * Checks if the passed entity is a Paragraph and is provided by Seeds.
   *
   * @param $entity
   *   The entity to be checked.
   *
   * @return bool
   */
  public static function isSeedsParagraph($entity) {
    return $entity && $entity->getEntityTypeId() === 'paragraph' && $entity->bundle() === 'seeds_paragraph';
  }

  /**
   * Checks if the passed entity is a Block and is of certain type.
   *
   * @param $entity
   *   The entity to be checked.
   * @param $type
   *   [Optional] The type of the block.
   *
   * @return bool
   */
  public static function isSeedsBlock($entity, $type = NULL) {
    if ($type) {
      return $entity && $entity->getEntityTypeId() === 'block_content' && $entity->bundle() === $type;
    }

    return $entity && $entity->getEntityTypeId() === 'block_content' && in_array($entity->bundle(), self::PARAGRAPH_BLOCKS, TRUE);
  }

  /**
   * Checks if the passed entity type id is supported by Seeds.
   *
   * @param $entity_type_id
   *   The id of the entity type to be checked.
   *
   * @return bool
   */
  public function isAcceptedEntity($entity_type_id) {
    return in_array($entity_type_id, self::ACCEPTED_ENTITY_TYPES, TRUE);
  }

  /**
   * Returns the node or term from current page.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntityFromCurrentPage() {
    $always_render_banner = $this->configFactory->get('seeds_page.config')->get('always_render_banner');
    if (!$always_render_banner) {
      $route_name = $this->routeMatch->getRouteName();
      $matches = [];
      $entity_landing = preg_match('/entity\.([\w_]+)\.canonical/', $route_name, $matches);
      $current_entity_type = $matches[1] ?? NULL;
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $entity_landing ? $this->routeMatch->getParameter($current_entity_type) : NULL;
      if ($entity_landing && $entity && $this->isAcceptedEntity($current_entity_type)) {
        $entity_types = $this->configFactory->get('seeds_page.config')->get('entity_types');
        $allowed_bundle = $entity_types[$current_entity_type][$entity->bundle()] ?? NULL;
        if ($allowed_bundle) {
          return $entity;
        }
      }
    }
    else {
      $route_params = $this->routeMatch->getParameters();
      foreach ($route_params as $param) {
        if ($param instanceof FieldableEntityInterface) {
          return $param;
        }
      }
    }
    return NULL;
  }

  /**
   * Gets the rendered array of an image with a responsive image style applied.
   *
   * @param $file
   *   The image file.
   * @param $responsive_image_id
   *   The responsive image style to be applied.
   *
   * @return array|\Drupal\Component\Render\MarkupInterface|mixed|string|void
   *
   * @throws \Exception
   */
  public function toResponsiveImage($file, $responsive_image_id) {
    if (!$file || !$responsive_image_id) {
      return [];
    }

    $file_uri = $file->getFileUri();

    $image = $this->imageFactory->get($file->getFileUri());

    if ($image->isValid()) {
      $width = $image->getWidth();
      $height = $image->getHeight();
    }
    else {
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

  /**
   * Build allowed view modes option list to be used when creating a paragraph.
   *
   * @param $paragraph_bundle
   *   The bundle of the paragraph that is being created.
   *
   * @return array
   */
  public function getAllowedViewModesOptions($paragraph_bundle) {
    $paragraph_view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle('paragraph', $paragraph_bundle);
    $allowed_view_modes = $this->configFactory->get('seeds_page.paragraph_view_modes')->get('allowed_view_modes');
    $options = [];

    foreach ($allowed_view_modes as $key => $value) {
      if ($value !== 0 && array_key_exists($key, $paragraph_view_modes)) {
        if ($key === 'default') {
          $options["_none"] = $paragraph_view_modes[$key];
        }
        else {
          $options["paragraph.$key"] = $paragraph_view_modes[$key];
        }
      }
    }

    return $options;
  }

}
