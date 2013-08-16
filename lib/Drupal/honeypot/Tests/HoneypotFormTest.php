<?php

/**
 * @file
 * Definition of Drupal\honeypot\Tests\HoneypotFormTest.
 */

namespace Drupal\honeypot\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Language\Language;

/**
 * Test the functionality of the Honeypot module for an admin user.
 */
class HoneypotFormTest extends WebTestBase {
  protected $admin_user;
  protected $web_user;
  protected $node;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('honeypot', 'node', 'comment');

  public static function getInfo() {
    return array(
      'name' => 'Honeypot form protections',
      'description' => 'Ensure that Honeypot protects site forms properly.',
      'group' => 'Honeypot',
    );
  }

  public function setUp() {
    // Enable modules required for this test.
    parent::setUp();

    // Set up required Honeypot configuration.
    $honeypot_config = config('honeypot.settings');
    $honeypot_config->set('element_name', 'url');
    $honeypot_config->set('time_limit', 0); // Disable time_limit protection.
    $honeypot_config->set('protect_all_forms', TRUE); // Test protecting all forms.
    $honeypot_config->set('log', FALSE);
    $honeypot_config->save();

    // Set up other required configuration.
    $user_config = config('user.settings');
    $user_config->set('verify_mail', TRUE);
    $user_config->set('register', USER_REGISTER_VISITORS);
    $user_config->save();

    // Create an Article node type.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(array('type' => 'article', 'name' => 'Article'));
    }

    // Set up admin user.
    $this->admin_user = $this->drupalCreateUser(array(
      'administer honeypot',
      'bypass honeypot protection',
      'administer content types',
      'administer users',
      'access comments',
      'post comments',
      'skip comment approval',
      'administer comments',
    ));

    // Set up web user.
    $this->web_user = $this->drupalCreateUser(array(
      'access comments',
      'post comments',
      'create article content',
    ));

    // Set up example node.
    $this->node = $this->drupalCreateNode(array(
      'type' => 'article',
    ));
  }

  /**
   * Test user registration (anonymous users).
   */
  public function testProtectRegisterUserNormal() {
    // Set up form and submit it.
    $edit['name'] = $this->randomName();
    $edit['mail'] = $edit['name'] . '@example.com';
    $this->drupalPost('user/register', $edit, t('Create new account'));

    // Form should have been submitted successfully.
    $this->assertText(t('A welcome message with further instructions has been sent to your e-mail address.'), 'User registered successfully.');
  }

  public function testProtectUserRegisterHoneypotFilled() {
    // Set up form and submit it.
    $edit['name'] = $this->randomName();
    $edit['mail'] = $edit['name'] . '@example.com';
    $edit['url'] = 'http://www.example.com/';
    $this->drupalPost('user/register', $edit, t('Create new account'));

    // Form should have error message.
    $this->assertText(t('There was a problem with your form submission. Please refresh the page and try again.'), 'Registration form protected by honeypot.');
  }

  public function testProtectRegisterUserTooFast() {
    // Enable time limit for honeypot.
    $honeypot_config = config('honeypot.settings')->set('time_limit', 5)->save();

    // Set up form and submit it.
    $edit['name'] = $this->randomName();
    $edit['mail'] = $edit['name'] . '@example.com';
    $this->drupalPost('user/register', $edit, t('Create new account'));

    // Form should have error message.
    $this->assertText(t('There was a problem with your form submission. Please wait'), 'Registration form protected by time limit.');
  }

  public function testProtectCommentFormNormal() {
    $comment = 'Test comment.';

    // Disable time limit for honeypot.
    $honeypot_config = config('honeypot.settings')->set('time_limit', 0)->save();

    // Log in the web user.
    $this->drupalLogin($this->web_user);

    // Set up form and submit it.
    $langcode = Language::LANGCODE_NOT_SPECIFIED;
    $edit["comment_body[$langcode][0][value]"] = $comment;
    $this->drupalPost('comment/reply/' . $this->node->nid, $edit, t('Save'));
    $this->assertText(t('Your comment has been queued for review'), 'Comment posted successfully.');
  }

  public function testProtectCommentFormHoneypotFilled() {
    $comment = 'Test comment.';

    // Log in the web user.
    $this->drupalLogin($this->web_user);

    // Set up form and submit it.
    $langcode = Language::LANGCODE_NOT_SPECIFIED;
    $edit["comment_body[$langcode][0][value]"] = $comment;
    $edit['url'] = 'http://www.example.com/';
    $this->drupalPost('comment/reply/' . $this->node->nid, $edit, t('Save'));
    $this->assertText(t('There was a problem with your form submission. Please refresh the page and try again.'), 'Comment posted successfully.');
  }

  public function testProtectCommentFormHoneypotBypass() {
    // Log in the admin user.
    $this->drupalLogin($this->admin_user);

    // Get the comment reply form and ensure there's no 'url' field.
    $this->drupalGet('comment/reply/' . $this->node->nid);
    $this->assertNoText('id="edit-url" name="url"', 'Honeypot home page field not shown.');
  }
}
