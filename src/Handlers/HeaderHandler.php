<?php

namespace AndrewSvirin\Ebics\Handlers;

use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\Transaction;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\CryptService;
use DateTime;
use DOMDocument;
use DOMElement;

/**
 * Class HeaderHandler manages header DOM elements.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class HeaderHandler
{
    const ORDER_TYPE_INI = 'INI';
    const ORDER_TYPE_HIA = 'HIA';
    const ORDER_TYPE_HPB = 'HPB';
    const ORDER_TYPE_VMK = 'VMK';
    const ORDER_TYPE_STA = 'STA';
    const ORDER_TYPE_C53 = 'C53';
    const ORDER_TYPE_HAA = 'HAA';
    const ORDER_TYPE_HPD = 'HPD';
    const ORDER_TYPE_HKD = 'HKD';
    const ORDER_TYPE_HTD = 'HTD';
    const ORDER_TYPE_FDL = 'FDL';

    const ORDER_ATTRIBUTE_DZNNN = 'DZNNN';
    const ORDER_ATTRIBUTE_DZHNN = 'DZHNN';

    /**
     * @var User
     */
    private $user;

    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $securityMedium;

    /**
     * @var string
     */
    private $product;

    /**
     * @var KeyRing
     */
    private $keyRing;

    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->bank = $bank;
        $this->user = $user;
        $this->keyRing = $keyRing;
        $this->language = 'de';
        $this->securityMedium = '0000';
        $this->product = 'Ebics client PHP';
    }

    /**
     * Add header for INI Request XML.
     */
    public function handleINI(DOMDocument $xml, DOMElement $xmlRequest) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         null,
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_INI, self::ORDER_ATTRIBUTE_DZNNN),
         $this->handleMutable()
      );
    }

    /**
     * Add header for HIA Request XML.
     */
    public function handleHIA(DOMDocument $xml, DOMElement $xmlRequest) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         null,
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_HIA, self::ORDER_ATTRIBUTE_DZNNN),
         $this->handleMutable()
      );
    }

    /**
     * Add header for HPB Request XML.
     */
    public function handleHPB(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         null,
         $this->handleOrderDetails(self::ORDER_TYPE_HPB, self::ORDER_ATTRIBUTE_DZHNN),
         $this->handleMutable()
      );
    }

    /**
     * Add header for HAA Request XML.
     */
    public function handleHAA(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(self::ORDER_TYPE_HAA, self::ORDER_ATTRIBUTE_DZHNN, $this->handleStandardOrderParams()),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Add header for TransferReceipt Request XML.
     */
    public function handleTransferReceipt(DOMDocument $xml, DOMElement $xmlRequest, Transaction $transaction) : void
    {
        $this->handleTransaction(
         $xml,
         $xmlRequest,
         $transaction,
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_RECEIPT))
      );
    }

    /**
     * Add header for VMK Request XML.
     */
    public function handleVMK(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_VMK,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams($startDateTime, $endDateTime)
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Add header for STA Request XML.
     */
    public function handleSTA(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null) : void
    {
        $this->handle(
            $xml,
            $xmlRequest,
            $this->handleNonce($dateTime),
            $this->handleBank(),
            $this->handleOrderDetails(
                self::ORDER_TYPE_STA,
                self::ORDER_ATTRIBUTE_DZHNN,
                $this->handleStandardOrderParams($startDateTime, $endDateTime)
            ),
            $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
        );
    }

    /**
     * Add header for C53 Request XML.
     */
    public function handleC53(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null) : void
    {
        $this->handle(
            $xml,
            $xmlRequest,
            $this->handleNonce($dateTime),
            $this->handleBank(),
            $this->handleOrderDetails(
                self::ORDER_TYPE_C53,
                self::ORDER_ATTRIBUTE_DZHNN,
                $this->handleStandardOrderParams($startDateTime, $endDateTime)
            ),
            $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
        );
    }

    /**
     * Add header for HPD Request XML.
     */
    public function handleHPD(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_HPD,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams()
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Add header for HTD Request XML.
     */
    public function handleHTD(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_HTD,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams()
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Add header for FDL Request XML.
     */
    public function handleFDL(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime, string $fileInfo, string $countryCode, DateTime $startDateTime = null, DateTime $endDateTime = null) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_FDL,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleFDLOrderParams($fileInfo, $countryCode, $startDateTime, $endDateTime)
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Add header for HKD Request XML.
     */
    public function handleHKD(DOMDocument $xml, DOMElement $xmlRequest, DateTime $dateTime) : void
    {
        $this->handle(
         $xml,
         $xmlRequest,
         $this->handleNonce($dateTime),
         $this->handleBank(),
         $this->handleOrderDetails(
            self::ORDER_TYPE_HKD,
            self::ORDER_ATTRIBUTE_DZHNN,
            $this->handleStandardOrderParams()
         ),
         $this->handleMutable($this->handleTransactionPhase(Transaction::PHASE_INITIALIZATION))
      );
    }

    /**
     * Hook to add mutable information.
     */
    private function handleMutable(callable $transactionPhase = null): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlHeader) use ($transactionPhase) {
            // Add mutable to header.
            $xmlMutable = $xml->createElement('mutable');
            $xmlHeader->appendChild($xmlMutable);

            if (null !== $transactionPhase) {
                // Add TransactionPhase information to mutable.
                $transactionPhase($xml, $xmlMutable);
            }
        };
    }

    /**
     * Hook to add TransactionPhase information.
     */
    private function handleTransactionPhase(string $transactionPhase): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlMutable) use ($transactionPhase) {
            // Add TransactionPhase to mutable.
            $xmlTransactionPhase = $xml->createElement('TransactionPhase');
            $xmlTransactionPhase->nodeValue = $transactionPhase;
            $xmlMutable->appendChild($xmlTransactionPhase);
        };
    }

    /**
     * Hook to add OrderDetails information.
     */
    private function handleOrderDetails(string $orderType, string $orderAttribute, callable $orderParams = null): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlStatic) use ($orderType, $orderAttribute, $orderParams) {
            // Add OrderDetails to static.
            $xmlOrderDetails = $xml->createElement('OrderDetails');
            $xmlStatic->appendChild($xmlOrderDetails);

            // Add OrderType to OrderDetails.
            $xmlOrderType = $xml->createElement('OrderType');
            $xmlOrderType->nodeValue = $orderType;
            $xmlOrderDetails->appendChild($xmlOrderType);

            // Add OrderAttribute to OrderDetails.
            $xmlOrderAttribute = $xml->createElement('OrderAttribute');
            $xmlOrderAttribute->nodeValue = $orderAttribute;
            $xmlOrderDetails->appendChild($xmlOrderAttribute);

            if (null !== $orderParams) {
                // Add OrderParams information to OrderDetails.
                $orderParams($xml, $xmlOrderDetails);
            }
        };
    }

    /**
     * Hook to add StandardOrderParams information.
     */
    private function handleFDLOrderParams(string $fileInfo, string $countryCode = 'FR', DateTime $startDateTime = null, DateTime $endDateTime = null): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlOrderDetails) use ($fileInfo, $countryCode, $startDateTime, $endDateTime) {
            // Add StandardOrderParams to OrderDetails.
            $xmlStandardOrderParams = $xml->createElement('FDLOrderParams');
            $xmlOrderDetails->appendChild($xmlStandardOrderParams);

            // Add FileFormat to FDLOrderParams.
            $xmlFileFormat = $xml->createElement('FileFormat');
            $xmlFileFormat->nodeValue = $fileInfo;
            $xmlFileFormat->setAttribute('CountryCode', $countryCode);
            $xmlStandardOrderParams->appendChild($xmlFileFormat);

            $this->handleDateRangeParams($startDateTime, $endDateTime)($xml, $xmlStandardOrderParams);
        };
    }

    /**
     * Hook to add StandardOrderParams information.
     */
    private function handleStandardOrderParams(DateTime $startDateTime = null, DateTime $endDateTime = null): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlOrderDetails) use ($startDateTime, $endDateTime) {
            // Add StandardOrderParams to OrderDetails.
            $xmlStandardOrderParams = $xml->createElement('StandardOrderParams');
            $xmlOrderDetails->appendChild($xmlStandardOrderParams);
            $this->handleDateRangeParams($startDateTime, $endDateTime)($xml, $xmlStandardOrderParams);
        };
    }

    /**
     * Hook to add DateRange information.
     */
    private function handleDateRangeParams(DateTime $startDateTime = null, DateTime $endDateTime = null): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlOrderDetails) use ($startDateTime, $endDateTime) {
            if (null !== $startDateTime && null !== $endDateTime) {
                // Add DateRange to StandardOrderParams.
                $xmlDateRange = $xml->createElement('DateRange');
                $xmlOrderDetails->appendChild($xmlDateRange);
                // Add Start to StandardOrderParams.
                $xmlStart = $xml->createElement('Start');
                $xmlStart->nodeValue = $startDateTime->format('Y-m-d');
                $xmlDateRange->appendChild($xmlStart);
                // Add End to StandardOrderParams.
                $xmlEnd = $xml->createElement('End');
                $xmlEnd->nodeValue = $endDateTime->format('Y-m-d');
                $xmlDateRange->appendChild($xmlEnd);
            }
        };
    }

    /**
     * Hook to add Nonce and Timestamp information.
     *
     * @param DateTime $dateTime stamped by date time and Nonce
     */
    private function handleNonce(DateTime $dateTime): callable
    {
        return function (DOMDocument $xml, DOMElement $xmlStatic) use ($dateTime) {
            // Add Nonce to static.
            $xmlNonce = $xml->createElement('Nonce');
            $xmlNonce->nodeValue = CryptService::generateNonce();
            $xmlStatic->appendChild($xmlNonce);

            // Add TimeStamp to static.
            $xmlTimeStamp = $xml->createElement('Timestamp');
            $xmlTimeStamp->nodeValue = $dateTime->format('Y-m-d\TH:i:s\Z');
            $xmlStatic->appendChild($xmlTimeStamp);
        };
    }

    /**
     * Hook to add BankPubKeyDigests information.
     */
    private function handleBank(): callable
    {
        $keyRing = $this->keyRing;

        return function (DOMDocument $xml, DOMElement $xmlStatic) use ($keyRing) {
            $algorithm = 'sha256';
            // Add BankPubKeyDigests to static.
            $xmlBankPubKeyDigests = $xml->createElement('BankPubKeyDigests');
            $xmlStatic->appendChild($xmlBankPubKeyDigests);

            if (!($certificateX = $keyRing->getBankCertificateX())) {
                throw new EbicsException('Certificate X is empty.');
            }

            if (!($certificateE = $keyRing->getBankCertificateE())) {
                throw new EbicsException('Certificate E is empty.');
            }

            // Add Authentication to BankPubKeyDigests.
            $xmlAuthentication = $xml->createElement('Authentication');
            $xmlAuthentication->setAttribute('Version', $keyRing->getBankCertificateXVersion());
            $xmlAuthentication->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
            $certificateXDigest = CryptService::calculateDigest($certificateX, $algorithm);
            $xmlAuthentication->nodeValue = base64_encode($certificateXDigest);
            $xmlBankPubKeyDigests->appendChild($xmlAuthentication);

            // Add Encryption to BankPubKeyDigests.
            $xmlEncryption = $xml->createElement('Encryption');
            $xmlEncryption->setAttribute('Version', $keyRing->getBankCertificateEVersion());
            $xmlEncryption->setAttribute('Algorithm', sprintf('http://www.w3.org/2001/04/xmlenc#%s', $algorithm));
            $certificateEDigest = CryptService::calculateDigest($certificateE, $algorithm);
            $xmlEncryption->nodeValue = base64_encode($certificateEDigest);
            $xmlBankPubKeyDigests->appendChild($xmlEncryption);
        };
    }

    /**
     * Add header and children elements to DOM XML.
     */
    private function handle(DOMDocument $xml, DOMElement $xmlRequest, callable $nonce = null, callable $bank = null, callable $orderDetails = null, callable $mutable = null) : void
    {
        // Add header to request.
        $xmlHeader = $xml->createElement('header');
        $xmlHeader->setAttribute('authenticate', 'true');
        $xmlRequest->appendChild($xmlHeader);

        // Add static to header.
        $xmlStatic = $xml->createElement('static');
        $xmlHeader->appendChild($xmlStatic);

        // Add HostID to static.
        $xmlHostId = $xml->createElement('HostID');
        $xmlHostId->nodeValue = $this->bank->getHostId();
        $xmlStatic->appendChild($xmlHostId);

        if (null !== $nonce) {
            // Add Nonce information to static.
            $nonce($xml, $xmlStatic);
        }

        // Add PartnerID to static.
        $xmlPartnerId = $xml->createElement('PartnerID');
        $xmlPartnerId->nodeValue = $this->user->getPartnerId();
        $xmlStatic->appendChild($xmlPartnerId);

        // Add UserID to static.
        $xmlUserId = $xml->createElement('UserID');
        $xmlUserId->nodeValue = $this->user->getUserId();
        $xmlStatic->appendChild($xmlUserId);

        // Add Product to static.
        $xmlProduct = $xml->createElement('Product');
        $xmlProduct->setAttribute('Language', $this->language);
        $xmlProduct->nodeValue = $this->product;
        $xmlStatic->appendChild($xmlProduct);

        if (null !== $orderDetails) {
            // Add OrderDetails information to static.
            $orderDetails($xml, $xmlStatic);
        }

        if (null !== $bank) {
            // Add Bank information to static.
            $bank($xml, $xmlStatic);
        }

        // Add SecurityMedium to static.
        $xmlSecurityMedium = $xml->createElement('SecurityMedium');
        $xmlSecurityMedium->nodeValue = $this->securityMedium;
        $xmlStatic->appendChild($xmlSecurityMedium);

        if (null !== $mutable) {
            // Add Mutable information to header.
            $mutable($xml, $xmlHeader);
        }
    }

    /**
     * Add header and children elements to DOM XML.
     */
    private function handleTransaction(DOMDocument $xml, DOMElement $xmlRequest, Transaction $transaction, callable $mutable = null) : void
    {
        // Add header to request.
        $xmlHeader = $xml->createElement('header');
        $xmlHeader->setAttribute('authenticate', 'true');
        $xmlRequest->appendChild($xmlHeader);

        // Add static to header.
        $xmlStatic = $xml->createElement('static');
        $xmlHeader->appendChild($xmlStatic);

        // Add HostID to static.
        $xmlHostId = $xml->createElement('HostID');
        $xmlHostId->nodeValue = $this->bank->getHostId();
        $xmlStatic->appendChild($xmlHostId);

        // Add TransactionID to static.
        $xmlTransactionID = $xml->createElement('TransactionID');
        $xmlTransactionID->nodeValue = $transaction->getId();
        $xmlStatic->appendChild($xmlTransactionID);

        if (null !== $mutable) {
            // Add Mutable information to header.
            $mutable($xml, $xmlHeader);
        }
    }
}
