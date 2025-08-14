<?php

namespace Laravel\NFSe\Helpers;

class XmlSigner
{
  // Canonicalização clássica (alinhado ao modelo da prefeitura):
  public const C14N_CLASSIC = 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315';
  // Se sua prefeitura exigir EXCLUSIVE, troque para: 'http://www.w3.org/2001/10/xml-exc-c14n#'

  /**
   * Assina um elemento por ID. Ajuste os algoritmos de digest/sign se necessário.
   * $referenceId deve existir como atributo Id="...".
   */
  public static function sign(\DOMDocument $doc, string $referenceId, string $pfxOrPemPath, string $password): \DOMDocument
  {
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = false;

    // --- Carrega a chave/certificado (exemplo PEM PKCS#12 já extraído) ---
    $cert = file_get_contents($pfxOrPemPath);
    if ($cert === false) {
      throw new \RuntimeException("Certificado não encontrado: {$pfxOrPemPath}");
    }

    // Use sua lógica atual de extração de chave/chain:
    // $privateKey = ... (openssl_pkey_get_private etc.)
    // $x509       = ... (certificado em base64 sem headers)
    // Aqui vou supor que você já tenha métodos prontos:
    [$privateKey, $x509] = self::loadKeys($pfxOrPemPath, $password);

    // --- Cria elementos da assinatura ---
    $ds = 'http://www.w3.org/2000/09/xmldsig#';
    $xpath = new \DOMXPath($doc);
    $xpath->registerNamespace('ds', $ds);

    // Encontra o nó de referência por Id
    $refNode = self::findNodeById($doc, $referenceId);
    if (!$refNode) {
      throw new \RuntimeException("Elemento com Id='{$referenceId}' não encontrado para assinatura.");
    }

    $sig = $doc->createElementNS($ds, 'ds:Signature');
    $signedInfo = $doc->createElement('ds:SignedInfo');
    $canonMethod = $doc->createElement('ds:CanonicalizationMethod');
    $canonMethod->setAttribute('Algorithm', self::C14N_CLASSIC); // <<<<<< AQUI
    $signedInfo->appendChild($canonMethod);

    // Algoritmo de assinatura (ajuste se usa RSA-SHA256 na sua prefeitura)
    $sigMethod = $doc->createElement('ds:SignatureMethod');
    $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    $signedInfo->appendChild($sigMethod);

    // Reference
    $ref = $doc->createElement('ds:Reference');
    $ref->setAttribute('URI', '#' . $referenceId);

    // Transform (canonicalização CLÁSSICA aqui também)
    $transforms = $doc->createElement('ds:Transforms');
    $transform  = $doc->createElement('ds:Transform');
    $transform->setAttribute('Algorithm', self::C14N_CLASSIC); // <<<<<< AQUI
    $transforms->appendChild($transform);
    $ref->appendChild($transforms);

    // DigestMethod + DigestValue
    $digestMethod = $doc->createElement('ds:DigestMethod');
    $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
    $ref->appendChild($digestMethod);

    $digestValue = $doc->createElement('ds:DigestValue', self::calcDigest($refNode));
    $ref->appendChild($digestValue);

    $signedInfo->appendChild($ref);
    $sig->appendChild($signedInfo);

    // SignatureValue
    $signatureValue = $doc->createElement('ds:SignatureValue', self::calcSignature($signedInfo, $privateKey));
    $sig->appendChild($signatureValue);

    // KeyInfo (certificado em base64 sem headers e quebras)
    $x509Data = $doc->createElement('ds:X509Data');
    $x509Cert = $doc->createElement('ds:X509Certificate', $x509);
    $x509Data->appendChild($x509Cert);
    $keyInfo = $doc->createElement('ds:KeyInfo');
    $keyInfo->appendChild($x509Data);
    $sig->appendChild($keyInfo);

    // Anexa assinatura como irmã/filha conforme seu padrão (geralmente dentro do elemento assinado)
    $refNode->parentNode->insertBefore($sig, $refNode->nextSibling);

    return $doc;
  }

  // ==== Helpers de exemplo (adapte aos seus atuais) ====
  protected static function findNodeById(\DOMDocument $doc, string $id): ?\DOMElement
  {
    $xpath = new \DOMXPath($doc);
    $query = "//*[@Id='{$id}']";
    $nlist = $xpath->query($query);
    return ($nlist && $nlist->length) ? $nlist->item(0) : null;
  }

  protected static function calcDigest(\DOMElement $node): string
  {
    $c14n = $node->C14N(true, false); // inclusive namespaces
    $hash = base64_encode(sha1($c14n, true));
    return $hash;
  }

  protected static function calcSignature(\DOMElement $signedInfo, $privateKey): string
  {
    $c14n = $signedInfo->C14N(true, false);
    openssl_sign($c14n, $signature, $privateKey, OPENSSL_ALGO_SHA1);
    return base64_encode($signature);
  }

  protected static function loadKeys(string $pemOrPfxPath, string $password): array
  {
    // implemente com sua rotina atual (mantive como stub)
    // deve retornar [$privateKeyResource, $x509CertificateBase64]
    throw new \RuntimeException('Implementar loadKeys() conforme sua rotina atual.');
  }
}
