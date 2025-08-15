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
  public static function sign(\DOMDocument|string $docOrXml, string $referenceId, string $pfxOrPemPath, string $password): \DOMDocument
  {
    if ($docOrXml instanceof \DOMDocument) {
      $doc = $docOrXml;
    } else { // string
      $doc = new \DOMDocument('1.0', 'UTF-8');
      $doc->preserveWhiteSpace = false;
      $doc->formatOutput = false;
      if (!$doc->loadXML($docOrXml, LIBXML_NOBLANKS)) {
        throw new \InvalidArgumentException('XML inválido passado como string para XmlSigner::sign().');
      }
    }

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

  // dentro da classe Laravel\NFSe\Helpers\XmlSigner

  /**
   * Carrega a chave privada e o certificado X.509 a partir de um arquivo .pem (contendo KEY e CERT)
   * ou .pfx/.p12. Retorna [$privateKeyResource, $x509CertificateBase64].
   */
  protected static function loadKeys(string $pemOrPfxPath, string $password): array
  {
    if (!is_file($pemOrPfxPath)) {
      throw new \RuntimeException("Certificado não encontrado em: {$pemOrPfxPath}");
    }

    $blob = file_get_contents($pemOrPfxPath);
    if ($blob === false || $blob === '') {
      throw new \RuntimeException("Falha ao ler o arquivo de certificado: {$pemOrPfxPath}");
    }

    // Heurística simples para decidir entre PKCS#12 e PEM
    $ext = strtolower(pathinfo($pemOrPfxPath, PATHINFO_EXTENSION));
    $looksPkcs12 = $ext === 'p12' || $ext === 'pfx' || str_contains($blob, 'BEGIN PKCS12');

    if ($looksPkcs12) {
      // ---- PKCS#12 (.pfx/.p12) ----
      $certs = [];
      if (!@openssl_pkcs12_read($blob, $certs, $password)) {
        throw new \RuntimeException('Não foi possível ler o PKCS#12 (pfx/p12). Senha inválida ou arquivo corrompido.');
      }

      // $certs['pkey'] e $certs['cert']
      $priv = @openssl_pkey_get_private($certs['pkey'], $password);
      if ($priv === false) {
        // alguns PKCS#12 já vêm sem passphrase na pkey interna
        $priv = @openssl_pkey_get_private($certs['pkey']);
      }
      if ($priv === false) {
        throw new \RuntimeException('Falha ao extrair a chave privada do PKCS#12.');
      }

      $x509 = @openssl_x509_read($certs['cert']);
      if ($x509 === false) {
        throw new \RuntimeException('Falha ao ler o certificado X.509 do PKCS#12.');
      }

      if (!@openssl_x509_export($x509, $certPem)) {
        throw new \RuntimeException('Falha ao exportar o certificado X.509 do PKCS#12.');
      }

      $certBase64 = self::pemToDerBase64($certPem);
      return [$priv, $certBase64];
    }

    // ---- PEM (arquivo .pem com PRIVATE KEY e CERTIFICATE) ----

    // Tenta extrair o bloco da chave privada (RSA/ECDSA)
    if (preg_match('~-----BEGIN (?:ENCRYPTED )?(?:RSA |EC )?PRIVATE KEY-----.*?-----END (?:RSA |EC )?PRIVATE KEY-----~s', $blob, $mKey)) {
      $keyPem = $mKey[0];
    } else {
      throw new \RuntimeException('Bloco "PRIVATE KEY" não encontrado no PEM.');
    }

    // Tenta extrair o (primeiro) CERTIFICATE
    if (preg_match('~-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----~s', $blob, $mCert)) {
      $certPem = $mCert[0];
    } else {
      throw new \RuntimeException('Bloco "CERTIFICATE" não encontrado no PEM.');
    }

    // Abre a chave privada (com/sem senha)
    $priv = @openssl_pkey_get_private($keyPem, $password);
    if ($priv === false) {
      // tenta sem senha caso a key não esteja protegida
      $priv = @openssl_pkey_get_private($keyPem);
    }
    if ($priv === false) {
      throw new \RuntimeException('Falha ao abrir a chave privada do PEM (senha incorreta?).');
    }

    // Lê/exporta o X.509
    $x509 = @openssl_x509_read($certPem);
    if ($x509 === false) {
      throw new \RuntimeException('Falha ao ler o certificado X.509 do PEM.');
    }
    if (!@openssl_x509_export($x509, $exportedPem)) {
      throw new \RuntimeException('Falha ao exportar o certificado X.509 do PEM.');
    }

    $certBase64 = self::pemToDerBase64($exportedPem);
    return [$priv, $certBase64];
  }

  /**
   * Converte um PEM ("-----BEGIN CERTIFICATE----- ...") para o conteúdo DER em base64
   * sem quebras/headers/footers.
   */
  private static function pemToDerBase64(string $pem): string
  {
    $clean = preg_replace('~-----BEGIN [^-]+-----|-----END [^-]+-----|\s+~', '', $pem);
    return trim($clean ?? '');
  }
}
