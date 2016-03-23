<?php

/**
 * @file
 * Contains \Drupal\oauth2_key_clients\Plugin\GithubOauth2KeyClient.
 */

namespace Drupal\github_oauth2_key\Plugin\Oauth2KeyClient;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\key\Entity\Key;
use Drupal\oauth2_key_client\Oauth2KeyClientPluginBase;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use \League\OAuth2\Client\Provider\Github;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * GithubOauth2Key plugin implementation of the oauth2_key_clients.
 *
 * @Oauth2KeyClient(
 *   id = "github",
 *   label = @Translation("Github OAuth2 Client Plugin"),
 *   description = @Translation("plugin implementation of https://packagist.org/packages/league/oauth2-github for fetching access and refresh tokens and storing them as Keys of type Oauth2.")
 * )
 */
class GithubOauth2KeyClient extends Oauth2KeyClientPluginBase {


  public function __construct(array $configuration, $plugn_id, $plugin_definition ){

    parent::__construct($configuration, $plugn_id, $plugin_definition);

    $this->provider = new Github([
      'clientId'          => $configuration['consumer_key'],
      'clientSecret'      => $configuration['consumer_secret'],
      'redirectUri'       => $configuration['redirect_url'],
    ]);
    kint($this->provider);

    // lets load the client from the library


  }
  //  public function __construct(TranslationInterface $string_translation) {
//    // You can skip injecting this service, the trait will fall back to \Drupal::translation()
//    // but it is recommended to do so, for easier testability,
//    $this->stringTranslation = $string_translation;
//  }


public function createKeyEntity(){
  $access_token = $this->fetchAccessToken();
  kint($access_token);
  // assuming we got a token we need to create a new key

    $key = Key::create(array(
      // name the key after the authorization
      'id' => 'testkey',
      'label' => 'testkey',
      'description' => 'testkey',
      'key_type' => 'oauth2',
      'key_type_settings' =>
        array (
        ),
      'key_provider' => 'config',
      'key_provider_settings' =>
        array (
          'access_token' => $access_token,
          //'refresh_token' => $refresh_token,
        ),
      'key_input' => 'oauth2',
      'key_input_settings' =>
        array (
        ),
    ));
    return $key->save();

}

  /**
   * settingsForm.
   *
   * @DCG: Optional.
   */
  public function settingsForm(array &$form_state){
    return __CLASS__ . ' implementation of ' . __FUNCTION__;
  }
  /**
   * {@inheritdoc}
   *
   * @DCG: Optional.
   */
  public function info(array &$form_state) {
    return __CLASS__ . ' implementation of ' . __FUNCTION__;
  }
  /**
   * {@inheritdoc}
   *
   * @DCG: Optional.
   */
  public function fetchAccessToken() {
// lets try client credentials first (because i am not sure right now why code grant fails)


    // in this case we are trying to use the auth code grant

    if (!isset($_GET['code'])) {

      // If we don't have an authorization code then get one
      $authUrl = $this->provider->getAuthorizationUrl();
      $_SESSION['oauth2state'] = $this->provider->getState();

      $response = new RedirectResponse($authUrl);
      kint('redirecting to ' . $authUrl);
      $response->send();
      // no idea why header doesnt work, redirect differenctly
      header('Location: '.$authUrl);

      // Try to get an access token (using the authorization code grant)
      $token = $this->provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
      ]);
      // TODO: figure out whats wrong here .. it fails ... related to redirect url? we should be redirected to the authorization

      exit;

// Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

      unset($_SESSION['oauth2state']);
      exit('Invalid state');

    } else {

      // Try to get an access token (using the authorization code grant)
      $token = $this->provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
      ]);


    }
    return $token;
  }

}
