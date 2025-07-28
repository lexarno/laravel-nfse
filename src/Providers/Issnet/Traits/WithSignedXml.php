<?php

namespace Laravel\NFSe\Providers\Issnet\Traits;

trait WithSignedXml
{
  protected function assinarXml(string $xml, string $tagAssinatura, string $certificadoPath, string $certPassword): string
  {
    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = false;
    $dom->loadXML($xml);

    $tag = $dom->getElementsByTagName($tagAssinatura)->item(0);
    if (!$tag) {
      throw new \Exception("Tag de assinatura '{$tagAssinatura}' não encontrada.");
    }

    $id = $tag->getAttribute('Id');
    if (empty($id)) {
      throw new \Exception("Tag '{$tagAssinatura}' deve conter atributo 'Id' para assinatura.");
    }

    // Inicializa a assinatura
    $objXmlDSig = new \RobRichards\XMLSecLibs\XMLSecurityDSig();
    $objXmlDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);
    $objXmlDSig->addReference(
      $tag,
      \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1,
      ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
      ['uri' => '#' . $id]
    );

    // Adiciona a chave privada
    $objKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(\RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $objKey->loadKey($certificadoPath, true);

    $objXmlDSig->sign($objKey);
    $objXmlDSig->add509Cert(file_get_contents($certificadoPath));
    $objXmlDSig->insertSignature($tag, $tag->firstChild);

    return $dom->saveXML();
  }
}
