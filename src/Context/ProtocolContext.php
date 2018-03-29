<?php
namespace DennisDigital\Behat\Protocol\Context;

use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\Mink\Mink;
use Behat\Mink\Exception\ExpectationException;

/**
 * ProtocolContext
 */
class ProtocolContext implements MinkAwareContext {
  /**
   * @var Mink
   */
  private $mink;

  /**
   * @var Mink parameters
   */
  private $minkParameters = array();

  /**
   * @var array Hosts to check
   */
  private $hosts = array();

  /**
   * ProtocolContext constructor.
   *
   * @param array $parameters
   */
  public function __construct($parameters = array()) {
    if (isset($parameters['hosts'])) {
      $this->hosts = $parameters['hosts'];
    }
  }

  /**
   * Get array of http base URLs to check.
   *
   * @return array
   */
  protected function getHttpBaseURls() {
    $urls = [$this->getBaseHttpUrl()];
    foreach ($this->hosts as $host) {
      $urls[] = 'http://' . $host;
    }
    return $urls;
  }

  /**
   * @Given the response should not contain internal http urls
   */
  public function assertResponseNotContainsHttpUrls() {
    foreach ($this->getHttpBaseURls() as $base_url) {
      try {
        $this->mink->assertSession()->responseNotContains($base_url);
      }
      catch (ExpectationException $e) {
        throw new Exception($this->mink->getSession()->getCurrentUrl() . ' contains http:// URL.' . PHP_EOL . $e->getMessage());
      }
    }
  }

  /**
   * @Given I should not see any internal http urls in JavaScript
   */
  public function notSeeHttpJSReferences() {
    if ($urls = $this->getInternalScriptUrls()) {
      $this->assertNotSeeHttpJsReferences($urls);
    }
  }

  /**
   * Assert that scripts don't contain http:// urls.
   *
   * @param $urls
   * @throws Exception
   */
  protected function assertNotSeeHttpJsReferences($urls) {
    $session = $this->mink->getSession();
    $current_url = $session->getCurrentUrl();

    foreach ($urls as $url) {
      $session->visit($url);
      $this->assertResponseNotContainsHttpUrls();
    }

    // Go back to original page.
    $session->visit($current_url);
  }

  /**
   * Get internal script urls.
   *
   * @return array
   */
  protected function getInternalScriptUrls() {
    $script_urls = $this->getScriptUrls();
    return (array) $script_urls['internal'];
  }

  /**
   * Get all script urls.
   */
  protected function getScriptUrls() {
    $current_url = $this->mink->getSession()->getCurrentUrl();
    $host = parse_url($current_url, PHP_URL_HOST);
    $protocol = parse_url($current_url, PHP_URL_SCHEME);

    $js_urls = array(
      'internal' => array(),
      'external' => array(),
    );

    foreach ($this->getScriptTags() as $script) {
      if ($script->hasAttribute('src')) {
        $js_src = $script->getAttribute('src');
        $js_protocol = parse_url($js_src, PHP_URL_SCHEME);

        if (parse_url($js_src, PHP_URL_HOST) === $host) {
          // Request internal scripts with current protocol as local and testing environments might not support https.
          $js_urls['internal'][] = ($js_protocol ? '' : $protocol . ':') . $js_src;
        }
        else {
          // Always fetch 3rd party scripts over https:// if src is protocol-relative.
          $js_urls['external'][] = ($js_protocol ? '' : 'https:') . $js_src;
        }
      }
      else {
        // Extract require paths if available.
        if (preg_match('~requirejs.config\({"paths":(.*?})~', $script->getHtml(), $matches)){
          $paths = json_decode($matches[1]);
          foreach ($paths as $path) {
            // Swap https with requested protocol (http), until https is enabled on VM/CI.
            $js_urls['internal'][] = str_replace('https://', $protocol . '://', $path) . '.js';
          }
        }
      }
    }

    return $js_urls;
  }

  /**
   * Get base url with http:// protocol.
   *
   * @return string
   */
  protected function getBaseHttpUrl() {
    return str_replace('https://', 'http://', $this->minkParameters['base_url']);
  }

  /**
   * Get script tags from current page.
   */
  protected function getScriptTags() {
    return $this->mink->getSession()->getPage()->findAll('css', 'script');
  }

  /**
   * @inheritdoc
   */
  public function setMink(Mink $mink) {
    $this->mink = $mink;
  }

  /**
   * @inheritdoc
   */
  public function setMinkParameters(array $parameters) {
    $this->minkParameters = $parameters;
  }
}