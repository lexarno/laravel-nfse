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
    string $xmlContent,
    string $tag,
    string $attributeId,
    string $certPath,
    string $certPassword
  ): string {
    $dom = new \DOMDocument('1.0', 'utf-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;
    $dom->loadXML($xmlContent);

    $elements = $dom->getElementsByTagName($tag);
    if ($elements->length === 0) {
      throw new \Exception("Tag <{$tag}> não encontrada no XML.");
    }

    $elementToSign = $elements->item(0);

    // Cria a assinatura digital
    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
    $objDSig->addReference(
      $elementToSign,
      XMLSecurityDSig::SHA1,
      ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
      ['id_name' => $attributeId, 'overwrite' => false]
    );

    // Adiciona a chave privada
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $objKey->loadKey($certPath, true);
    $objDSig->sign($objKey);

    // Adiciona a cadeia de certificados
    $objDSig->add509Cert(file_get_contents($certPath), true, false, ['subjectName' => true]);

    // Insere a assinatura como filho do elemento assinado
    $objDSig->appendSignature($elementToSign);

    return $dom->saveXML();
  }
}
