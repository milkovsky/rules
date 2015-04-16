<?php

/**
 * @file
 * Contains \Drupal\rules\Form\RulesSettingsForm.
 */

namespace Drupal\rules\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rules\Engine\RulesLog;

/**
 * Provides rules settings form.
 */
class RulesSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rules_settings_Form';
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('rules.settings');

    $form['rules_log_errors'] = array(
      '#type' => 'radios',
      '#title' => t('Logging of Rules evaluation errors'),
      '#options' => array(
        RulesLog::WARN => t('Log all warnings and errors'),
        RulesLog::ERROR => t('Log errors only'),
      ),
      '#default_value' => $config->get('log_errors') ? $config->get('log_errors') : RulesLog::WARN,
      '#description' => t('Evaluations errors are logged to the system log.'),
    );

    $form['debug']['rules_debug_log'] = array(
      '#type' => 'checkbox',
      '#title' => t('Log debug information to the system log'),
      '#default_value' => $config->get('debug_log'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();

    $account = $form_state->getValue('account');
    // Mail one time login URL and instructions using current language.
    $mail = _user_mail_notify('password_reset', $account, $langcode);
    if (!empty($mail)) {
      $this->logger('user')->notice('Password reset instructions mailed to %name at %email.', array('%name' => $account->getUsername(), '%email' => $account->getEmail()));
      drupal_set_message($this->t('Further instructions have been sent to your email address.'));
    }

    $form_state->setRedirect('user.page');
  }

}
