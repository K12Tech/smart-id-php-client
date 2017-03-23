<?php
namespace Sk\SmartId\Tests\Api;

use PHPUnit\Framework\TestCase;
use Sk\SmartId\Api\AuthenticationResponseValidator;
use Sk\SmartId\Api\Data\CertificateLevelCode;
use Sk\SmartId\Api\Data\SessionEndResultCode;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResponse;
use Sk\SmartId\Api\Data\SmartIdAuthenticationResultError;

class AuthenticationResponseValidatorTest extends TestCase
{
  const VALID_SIGNATURE_IN_BASE64 = 'Whg9fyCr7/Pp/QWSoOauHLOj+hH3Q144kpjg889zWzpo1eAxXp4fTE3MZ+LRKNn7KiBtkXd/BcKmok66yk+NMNc95hhMbTm87tWUhqYVbqJz5popYz+vkMbzYNtMsvUBVLlOYCJcJqyFwzcYAkcyfgk4iDt10nkG7a1ngIGgSLkblQMySPduI++H0j4IFNGEhAXM51dSEx9NtpNRs5zlklOd+ccdVR0uXdK8OmAOrhzG03b8/FfVrDP62l7FtJEGej1GUkKJn6gcjaOi/lnQiPw2qZWFrsPA4OABkAgQUtwZ2CKTnrlAP3A4qMvbb5GF015PWtboz8T854pw/xixpzkL6sg79xSOAminwQQFovrKKzWn0A2bSRjUz5hRiv9yqZ1DL77pyZIQ91YZIBiS/rVE6+/jmbxtVIJnrecK8OnnfZo+HQu3LnNHkU0yq7im/VGCrSxNw9aExRFA5RuTNyCzZuX9iJfRibPLq2WUAYUb/JMiTc6Bg57DH5OUB9jOeRnj+vsciCXuYMy1B+fHG9WgR5M2EbpKije7rp6goDcpmS4xa+y2lVujXTFBbnOgxSo0ZF+xgko6PLWliRacgXPVVM3NgyfGHgrzZf8ANBlEOCBa47LQyeBSRiBsmzFrmkIvHf+57oHZhN1Ac8AOcX9O5LXJ0hpZyHQvHQXwbJk=';
  const INVALID_SIGNATURE_IN_BASE64 = 'Xhg9fyCr7/Pp/QWSoOauHLOj+hH3Q144kpjg889zWzpo1eAxXp4fTE3MZ+LRKNn7KiBtkXd/BcKmok66yk+NMNc95hhMbTm87tWUhqYVbqJz5popYz+vkMbzYNtMsvUBVLlOYCJcJqyFwzcYAkcyfgk4iDt10nkG7a1ngIGgSLkblQMySPduI++H0j4IFNGEhAXM51dSEx9NtpNRs5zlklOd+ccdVR0uXdK8OmAOrhzG03b8/FfVrDP62l7FtJEGej1GUkKJn6gcjaOi/lnQiPw2qZWFrsPA4OABkAgQUtwZ2CKTnrlAP3A4qMvbb5GF015PWtboz8T854pw/xixpzkL6sg79xSOAminwQQFovrKKzWn0A2bSRjUz5hRiv9yqZ1DL77pyZIQ91YZIBiS/rVE6+/jmbxtVIJnrecK8OnnfZo+HQu3LnNHkU0yq7im/VGCrSxNw9aExRFA5RuTNyCzZuX9iJfRibPLq2WUAYUb/JMiTc6Bg57DH5OUB9jOeRnj+vsciCXuYMy1B+fHG9WgR5M2EbpKije7rp6goDcpmS4xa+y2lVujXTFBbnOgxSo0ZF+xgko6PLWliRacgXPVVM3NgyfGHgrzZf8ANBlEOCBa47LQyeBSRiBsmzFrmkIvHf+57oHZhN1Ac8AOcX9O5LXJ0hpZyHQvHQXwbJk=';

  /**
   * @var AuthenticationResponseValidator
   */
  private $validator;

  protected function setUp()
  {
    $this->validator = new AuthenticationResponseValidator();
  }

  /**
   * @test
   */
  public function validationReturnsValidAuthenticationResult()
  {
    $response = $this->createValidValidationResponse();
    $authenticationResult = $this->validator->validate( $response );

    $this->assertTrue( $authenticationResult->isValid() );
    $this->assertTrue( empty( $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   */
  public function validationReturnsValidAuthenticationResult_whenEndResultLowerCase()
  {
    $response = $this->createValidValidationResponse();
    $response->setEndResult( strtolower( SessionEndResultCode::OK ) );
    $authenticationResult = $this->validator->validate( $response );

    $this->assertTrue( $authenticationResult->isValid() );
    $this->assertTrue( empty( $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   */
  public function validationReturnsInvalidAuthenticationResult_whenEndResultNotOk()
  {
    $response = $this->createValidationResponseWithInvalidEndResult();
    $authenticationResult = $this->validator->validate( $response );

    $this->assertFalse( $authenticationResult->isValid() );
    $this->assertTrue( in_array( SmartIdAuthenticationResultError::INVALID_END_RESULT,
        $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   */
  public function validationReturnsInvalidAuthenticationResult_whenSignatureVerificationFails()
  {
    $response = $this->createValidationResponseWithInvalidSignature();
    $authenticationResult = $this->validator->validate( $response );

    $this->assertFalse( $authenticationResult->isValid() );
    $this->assertTrue( in_array( SmartIdAuthenticationResultError::SIGNATURE_VERIFICATION_FAILURE,
        $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   */
  public function validationReturnsValidAuthenticationResult_whenCertificateLevelHigherThanRequested()
  {
    $response = $this->createValidationResponseWithHigherCertificateLevelThanRequested();
    $authenticationResult = $this->validator->validate( $response );

    $this->assertTrue( $authenticationResult->isValid() );
    $this->assertTrue( empty( $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   */
  public function validationReturnsInvalidAuthenticationResult_whenCertificateLevelLowerThanRequested()
  {
    $response = $this->createValidationResponseWithLowerCertificateLevelThanRequested();
    $authenticationResult = $this->validator->validate( $response );

    $this->assertFalse( $authenticationResult->isValid() );
    $this->assertTrue( in_array( SmartIdAuthenticationResultError::CERTIFICATE_LEVEL_MISMATCH,
        $authenticationResult->getErrors() ) );
  }

  /**
   * @test
   * @expectedException \Sk\SmartId\Exception\TechnicalErrorException
   */
  public function whenCertificateIsNull_ThenThrowException()
  {
    $response = $this->createValidValidationResponse();
    $response->setCertificate( null );
    $this->validator->validate( $response );
  }

  /**
   * @test
   * @expectedException \Sk\SmartId\Exception\TechnicalErrorException
   */
  public function whenSignatureIsEmpty_ThenThrowException()
  {
    $response = $this->createValidValidationResponse();
    $response->setValueInBase64( '' );
    $this->validator->validate( $response );
  }

  /**
   * @test
   * @expectedException \Sk\SmartId\Exception\TechnicalErrorException
   */
  public function whenRequestedCertificateLevelIsNullEmpty_ThenThrowException()
  {
    $response = $this->createValidValidationResponse();
    $response->setRequestedCertificateLevel( '' );
    $this->validator->validate( $response );
  }

  /**
   * @return SmartIdAuthenticationResponse
   */
  private function createValidValidationResponse()
  {
    return $this->createValidationResponse( SessionEndResultCode::OK, self::VALID_SIGNATURE_IN_BASE64,
        CertificateLevelCode::QUALIFIED, CertificateLevelCode::QUALIFIED );
  }

  /**
   * @return SmartIdAuthenticationResponse
   */
  private function createValidationResponseWithInvalidEndResult()
  {
    return $this->createValidationResponse( 'NOT OK', self::VALID_SIGNATURE_IN_BASE64, CertificateLevelCode::QUALIFIED,
        CertificateLevelCode::QUALIFIED );
  }

  /**
   * @return SmartIdAuthenticationResponse
   */
  private function createValidationResponseWithInvalidSignature()
  {
    return $this->createValidationResponse( SessionEndResultCode::OK, self::INVALID_SIGNATURE_IN_BASE64,
        CertificateLevelCode::QUALIFIED, CertificateLevelCode::QUALIFIED );
  }

  private function createValidationResponseWithLowerCertificateLevelThanRequested()
  {
    return $this->createValidationResponse( SessionEndResultCode::OK, self::VALID_SIGNATURE_IN_BASE64,
        CertificateLevelCode::ADVANCED, CertificateLevelCode::QUALIFIED );
  }

  /**
   * @return SmartIdAuthenticationResponse
   */
  private function createValidationResponseWithHigherCertificateLevelThanRequested()
  {
    return $this->createValidationResponse( SessionEndResultCode::OK, self::VALID_SIGNATURE_IN_BASE64,
        CertificateLevelCode::QUALIFIED, CertificateLevelCode::ADVANCED );
  }

  /**
   * @param string $endResult
   * @param string $signatureInBase64
   * @param string $certificateLevel
   * @param string $requestedCertificateLevel
   * @return SmartIdAuthenticationResponse
   */
  private function createValidationResponse( $endResult, $signatureInBase64, $certificateLevel, $requestedCertificateLevel )
  {
    $authenticationResponse = new SmartIdAuthenticationResponse();
    $authenticationResponse->setEndResult( $endResult )
        ->setValueInBase64( $signatureInBase64 )
        ->setCertificate( DummyData::CERTIFICATE )
        ->setSignedData( 'Hello World!' )
        ->setCertificateLevel( $certificateLevel )
        ->setRequestedCertificateLevel( $requestedCertificateLevel );
    return $authenticationResponse;
  }
}