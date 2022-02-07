<?php

namespace Drupal\seeds_page\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ParagraphViewModesForm.
 */
class ParagraphViewModesForm extends ConfigFormBase {
    /**
     * Entity Display Repository definition.
     *
     * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
     */
    protected $entityDisplayRepository;

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
        $instance = parent::create($container);
        $instance->entityDisplayRepository = $container->get('entity_display.repository');
        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'seeds_page.paragraph_view_modes',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'paragraph_view_modes_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('seeds_page.paragraph_view_modes');
        $form['allowed_view_modes'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Allowed View Modes'),
            '#description' => $this->t('Choose which view modes will be used in the Paragraph creation form.'),
            '#default_value' => $config->get('allowed_view_modes'),
            '#options' => $this->getParagraphViewModes(),
            '#required' => TRUE
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        parent::submitForm($form, $form_state);

        $this->config('seeds_page.paragraph_view_modes')
            ->set('allowed_view_modes', $form_state->getValue('allowed_view_modes'))
            ->save();
    }


    private function getParagraphViewModes() {
        $view_modes = $this->entityDisplayRepository->getViewModeOptions('paragraph');
        $options = [];

        foreach ($view_modes as $key => $value) {
            $options[$key] = $value;
        }

        return $options;
    }

}
