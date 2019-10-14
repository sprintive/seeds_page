<?php

namespace Drupal\seeds_page\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\AccessAwareRouter;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeBundleInfo;

/**
 * Class SeedsPageForm.
 */
class SeedsPageForm extends ConfigFormBase {

  /**
   * Entity type manager.
   *
   * @var EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Router.
   *
   * @var AccessAwareRouter
   */
  protected $router;

  /**
   * Bundle info.
   *
   * @var EntityTypeBundleInfo
   */
  protected $bundleInfo;

  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, AccessAwareRouter $router, EntityTypeBundleInfo $bundle_info) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->router = $router;
    $this->bundleInfo = $bundle_info;
  }

  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('router'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'seeds_page_form';
  }

  public function getEditableConfigNames() {
    return 'seeds_page.config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('seeds_page.config');
    $media = $this->entityTypeManager->getStorage('media')->load($config->get('media_id'));
    $form['media_wrapper'] = [
      '#type' => 'container',
    ];
    $form['media_wrapper']['media'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#title' => $this->t('Default Media'),
      '#description' => $this->t('Select a media image to provide as a default banner.'),
      '#default_value' => $media,
      '#selection_settings' => array(
        'target_bundles' => array('image'),
      ),
      '#weight' => '0',
    ];

    $form['media_wrapper']['html'] = [
      '#markup' => 'Use the following varaibles in preprocess page:
      <ul><li>seeds_banner.image</li><li>seeds_banner.page_title</li></ul>
      ',
    ];

    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t("Field to Render"),
      '#options' => $this->getMediaFields(),
      '#default_value' => $config->get('field'),
    ];

    $form['entity_types_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Bundles'),
      '#tree' => TRUE,
    ];

    $entity_types = $this->loadAllEntityTypes();
    foreach($entity_types as $entity_type_id => $entity_type) {
      $form['entity_types_wrapper'][$entity_type_id] = [
        '#type' => 'checkboxes',
        '#multiple' => TRUE,
        '#title' => $entity_type['label'],
        '#options' => $entity_type['bundles'],
        '#default_value' => $config->get('entity_types')[$entity_type_id],
      ];
    }
    

    $form['render_banner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render The Banner'),
      '#description' => $this->t('If you don\'t want to use twig, use this option to let the module render the banner.'),
      '#default_value' => $config->get('render_banner'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  private function loadAllEntityTypes() {
    $entity_types = [];
    $entity_definitions = \Drupal::entityTypeManager()->getDefinitions();
    foreach ($entity_definitions as $entity_type_id => $entity_definition) {
      if ($entity_definition instanceof ContentEntityType) {
        // Check if the entity has a landing page by checking its route.
        $route_name = "entity.$entity_type_id.canonical";
        $has_landing_route = $this->router->getRouteCollection()->get($route_name) ? TRUE : FALSE;
        if ($has_landing_route) {
          $bundles = $this->bundleInfo->getBundleInfo($entity_type_id);
          foreach($bundles as $id => $bundle) {
            $entity_types[$entity_type_id]['bundles'][$id] = $bundle['label'];
          }
          $entity_types[$entity_type_id]['label'] = $entity_definition->getLabel();
        }
      }
    }
    return $entity_types;
  }

  private function getMediaFields() {
    $filtered_media_fields = [];
    $media_fields = $this->entityTypeManager->getStorage('field_storage_config')->loadByProperties(
      array(
        'settings' => array(
          'target_type' => 'media',
        ),
        'type' => 'entity_reference',
        'deleted' => FALSE,
        'status' => 1,
      )
    );
    foreach($media_fields as $key => $field) {
      $field_name = explode('.',$key)[1];
      $filtered_media_fields[$field_name] = $field_name;
    }
    return $filtered_media_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('seeds_page.config');
    $config->set('media_id', $form_state->getValue('media'));
    $config->set('render_banner', $form_state->getValue('render_banner'));
    $config->set('entity_types', $form_state->getValue('entity_types_wrapper'));
    $config->set('field', $form_state->getValue('field'));

    $config->save();
    return parent::submitForm($form, $form_state);
  }

}
