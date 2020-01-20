<?php

namespace Coatesap\PaymentSense\Message;

use DOMDocument;
use SimpleXMLElement;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Cross Reference Transaction Request
 */

class CrossReferenceTransactionRequest extends AbstractRequest {
		
	protected $endpoint = 'https://gw1.paymentsensegateway.com:4430/';
    protected $namespace = 'https://www.thepaymentgateway.net/';

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
	}
	
	public function getCrossReference()
    {
        return $this->getParameter('crossReference');
    }

    public function setCrossReference($value)
    {
        return $this->setParameter('crossReference', $value);
    }

    public function getDescription()
    {
        return $this->getParameter('description');
    }

    public function setDescription($value)
    {
        return $this->setParameter('description', $value);
    }



    public function getData()
    {
	
		$this->validate('amount');

        $data = new SimpleXMLElement('<CrossReferenceTransaction/>');
        $data->addAttribute('xmlns', $this->namespace);

        $data->PaymentMessage->MerchantAuthentication['MerchantID'] = $this->getMerchantId();
        $data->PaymentMessage->MerchantAuthentication['Password'] = $this->getPassword();
        $data->PaymentMessage->TransactionDetails['Amount'] = $this->getAmountInteger();
        $data->PaymentMessage->TransactionDetails['CurrencyCode'] = $this->getCurrencyNumeric();
        $data->PaymentMessage->TransactionDetails->OrderID = $this->getTransactionId();
        $data->PaymentMessage->TransactionDetails->OrderDescription = $this->getDescription();
        $data->PaymentMessage->TransactionDetails->MessageDetails['TransactionType'] = 'REFUND';

        $data->PaymentMessage->TransactionDetails->MessageDetails['CrossReference'] = $this->getCrossReference();
        $data->PaymentMessage->TransactionDetails->MessageDetails['NewTransaction'] = false;
		
		// $data->PaymentMessage->CustomerDetails->CustomerIPAddress = $this->getClientIp();

        return $data;
    }

    public function sendData($data)
    {
        // the PHP SOAP library sucks, and SimpleXML can't append element trees
        // TODO: find PSR-0 SOAP library
        $document = new DOMDocument('1.0', 'utf-8');
        $envelope = $document->appendChild(
            $document->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soap:Envelope')
        );
        $envelope->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $envelope->setAttribute('xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');
        $body = $envelope->appendChild($document->createElement('soap:Body'));
        $body->appendChild($document->importNode(dom_import_simplexml($data), true));

        // post to Cardsave
        $headers = array(
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => $this->namespace.$data->getName());

        $httpResponse = $this->httpClient->request('post',$this->endpoint, $headers, $document->saveXML());
        
        return $this->response = new Response($this, $httpResponse->getBody());
    }	    

}