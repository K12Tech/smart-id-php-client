<?php
namespace Sk\SmartId\Api\Data;

class SessionStatusRequest
{
  /**
   * @var string
   */
  private $sessionId;

  /**
   * In milliseconds
   * @var int
   */
  private $sessionStatusResponseSocketTimeoutMs;

  /**
   * @var string
   */
  private $networkInterface;

  /**
   * @var string
   */
  private $proxy;

  /**
   * @param string $sessionId
   */
  public function __construct( $sessionId )
  {
    $this->sessionId = $sessionId;
  }

  /**
   * @return string
   */
  public function getSessionId()
  {
    return $this->sessionId;
  }

  /**
   * @return int
   */
  public function getSessionStatusResponseSocketTimeoutMs()
  {
    return $this->sessionStatusResponseSocketTimeoutMs;
  }

  /**
   * @param int $sessionStatusResponseSocketTimeoutMs
   * @return $this
   */
  public function setSessionStatusResponseSocketTimeoutMs( $sessionStatusResponseSocketTimeoutMs )
  {
    $this->sessionStatusResponseSocketTimeoutMs = $sessionStatusResponseSocketTimeoutMs;
    return $this;
  }

  /**
   * @return bool
   */
  public function isSessionStatusResponseSocketTimeoutSet()
  {
    return isset( $this->sessionStatusResponseSocketTimeoutMs ) && $this->sessionStatusResponseSocketTimeoutMs > 0;
  }

  /**
   * @param string $networkInterface
   * @return $this
   */
  public function setNetworkInterface( $networkInterface )
  {
    $this->networkInterface = $networkInterface;
    return $this;
  }

  /**
   * @param string $proxy
   * @return $this
   */
  public function setProxy( $proxy )
  {
    $this->proxy = $proxy;
    return $this;
  }

  /**
   * @return array
   */
  public function toArray()
  {
    $requiredArray = array();

    if ( $this->isSessionStatusResponseSocketTimeoutSet() )
    {
      $requiredArray[ 'timeoutMs' ] = $this->sessionStatusResponseSocketTimeoutMs;
    }

    if ( isset( $this->networkInterface ) )
    {
      $requiredArray[ 'networkInterface' ] = $this->networkInterface;
    }

    if ( isset( $this->proxy ) )
    {
      $requiredArray[ 'proxy' ] = $this->proxy;
    }

    return $requiredArray;
  }
}
