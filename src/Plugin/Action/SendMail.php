<?php

/**
 * @file
 * Contains \Drupal\rules\Plugin\Action\SendMail.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\rules\Engine\RulesActionBase;

/**
 * Provides a 'Send mail' action.
 *
 * @Action(
 *   id = "rules_send_mail",
 *   label = @Translation("Send mail"),
 *   context = {
 *     "to" = @ContextDefinition("email",
 *       label = @Translation("To"),
 *       description = @Translation("The e-mail address or addresses where the message will be sent to. The formatting of this string must comply with RFC 2822."),
 *       multiple = TRUE
 *     ),
 *     "subject" = @ContextDefinition("string",
 *       label = @Translation("Subject"),
 *       description = @Translation("The mail's subject.")
 *     ),
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message"),
 *       description = @Translation("The mail's message body.")
 *     ),
 *     "reply" = @ContextDefinition("email",
 *       label = @Translation("reply"),
 *       description = @Translation("The mail's reply address. Leave it empty to use the site-wide configured address."),
 *       required = FALSE
 *     ),
 *     "language" = @ContextDefinition("language",
 *       label = @Translation("Language"),
 *       description = @Translation("If specified, the language used for getting the mail message and subject."),
 *       required = FALSE
 *     )
 *   }
 * )
 *
 * @todo: Add access callback information from Drupal 7.
 * @todo: Add group information from Drupal 7.
 * @todo: Language context has "options list", "default value" and "default
 *   mode" settings in Drupal 7.
 * @todo Get RulesPlugin $element variable in execute function to generate
 *   unique key for the mail. Now $this->getPluginId() and $this->getBaseId() is
 *   used, but it's needs to be fixed.
 */
class SendMail extends RulesActionBase {

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Send mail');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $to = implode(',', $this->getContextValue('to'));
    $reply = $this->getContextValue('reply');
    $language = $this->getContextValue('language');
    $langcode = isset($language) ? $language->getId() : LanguageInterface::LANGCODE_NOT_SPECIFIED;
    $params = array(
      'subject' => $this->getContextValue('subject'),
      'message' => $this->getContextValue('message'),
      'langcode' => $langcode,
    );
    // Set a unique key for this mail.
    $name = $this->getBaseId() ? $this->getBaseId() : 'unnamed';
    $key = 'rules_action_mail_' . $name . '_' . $this->getPluginId();

    $message = drupal_mail('rules', $key, $to, $langcode, $params, $reply);
    if ($message['result']) {
      \Drupal::logger('rules')->notice('Successfully sent email to %recipient.', array('%recipient' => $to));
    }
  }
}
