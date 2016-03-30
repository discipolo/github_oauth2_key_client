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

  /**
   * @param array $configuration
   * @param string $plugn_id
   * @param mixed $plugin_definition
   */
  public function __construct(array $configuration, $plugn_id, $plugin_definition ){

    parent::__construct($configuration, $plugn_id, $plugin_definition);
    $this->key = new Key(
      array(
      'id' => 'testkey2',
      'label' => 'testkey',
      'description' => 'testkey',
      'key_type' => 'oauth2',
      'key_input' => 'oauth2',
      ),
      'key'
    );

    $this->provider = new Github([
      'clientId'          => $configuration['consumer_key'],
      'clientSecret'      => $configuration['consumer_secret'],
      'redirectUri'       => $configuration['redirect_url'],
    ]);



  }
  //  public function __construct(TranslationInterface $string_translation) {
//    // You can skip injecting this service, the trait will fall back to \Drupal::translation()
//    // but it is recommended to do so, for easier testability,
//    $this->stringTranslation = $string_translation;
//  }


public function createKeyEntity(){
  $token = $this->fetchAccessToken();
  $access_token = $token->getToken();
  $refresh_token = $token->getRefreshToken();
  kint($token);
  kint($refresh_token);
  kint($access_token);
  // assuming we got a token we need to create a new key

//    $this->key = Key::create(array(
//      // name the key after the authorization
//      'id' => 'testkey2',
//      'label' => 'testkey',
//      'description' => 'testkey',
//      'key_type' => 'oauth2',
//      'key_type_settings' =>
//        array (
//        ),
//      'key_provider' => 'config',
//      'keyValue' =>
//        array (
//          'access_token' => $token->getToken(),
//          'refresh_token' => $token->getRefreshToken(),
//        ),
//      'key_input' => 'oauth2',
//      'key_input_settings' =>
//        array (
//        ),
//    ));
  $this->key->setKeyValue($token->getToken()
  // TODO: the default key providers only take a string for key_value. discuss this
  //array (
  //    'access_token' => $token->getToken(),
  //    'refresh_token' => $token->getRefreshToken(),
  //)
);
    return $this->key;

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

    // in this case we are trying to use the auth code grant

    if (!isset($_GET['code'])) {

      // If we don't have an authorization code then get one
      $authUrl = $this->provider->getAuthorizationUrl();
      $_SESSION['oauth2state'] = $this->provider->getState();

      $response = new RedirectResponse($authUrl);
      kint('redirecting to ' . $authUrl);
      $response->send();
      // no idea why header doesnt work, redirect differenctly
      //header('Location: '.$authUrl);

      // Try to get an access token (using the authorization code grant)

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
