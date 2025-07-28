<?php

namespace Laravel\NFSe\Helpers;

use DOMDocument;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class XmlSigner
{
  /**
   * Assina uma tag do XML com base no ID e retorna o XML assinado.
   */
  public static function sign(
    DOMDocument $xml,
    string $tag,
    string $attributeId,
    string $certPath,
    string $certPassword
  ): string {
    // Localiza a tag a ser assinada
    $elements = $xml->getElementsByTagName($tag);
    if ($elements->length === 0) {
      throw new \Exception("Tag <$tag> não encontrada no XML.");
    }

    $elementToSign = $elements->item(0);

    // Força o atributo ID para que a lib consiga encontrá-lo
    $elementToSign->setAttributeNS(
      'http://www.w3.org/2000/xmlns/',
      'xmlns:ds',
      'http://www.w3.org/2000/09/xmldsig#'
    );
    $elementToSign->setAttribute('Id', $elementToSign->getAttribute('Id'));
    $elementToSign->setIdAttribute($attributeId, true);

    // Inicia a assinatura
    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);

    $objDSig->addReference(
      $elementToSign,
      XMLSecurityDSig::SHA1,
      ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
      ['force_uri' => true]
    );

    // Carrega chave privada do certificado
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $certContent = file_get_contents($certPath);
    $objKey->loadKey($certContent, false);

    // Adiciona a chave e certificado
    $objDSig->sign($objKey);
    $objDSig->add509Cert($certContent, true, false, ['subjectName' => true]);

    // Anexa a assinatura na tag
    $objDSig->appendSignature($elementToSign);

    return $xml->saveXML();
  }
}
