<?php
namespace Avalara;
use GuzzleHttp\Client;

/*****************************************************************************
 *                  Transaction Builder Convenience Object                   *
 *                                                                           *
 *    This file is not automatically generated by the AvaTax SDK process.    *
 *                        You may edit this file.                            *
 *                                                                           *
 *****************************************************************************/
/**
 * TransactionBuilder helps you construct a new transaction using a literate interface
 */
class TransactionBuilder
{
    /**
     * The in-progress model
     */
    private $_model;

    /**
     * Keeps track of the line number when adding multiple lines
     */
    private $_line_number;

    /**
     * The client that will be used to create the transaction
     */
    private $_client;

    /**
     * TransactionBuilder helps you construct a new transaction using a literate interface
     *
     * @param AvaTaxClient  $client        The AvaTaxClient object to use to create this transaction
     * @param string        $companyCode   The code of the company for this transaction
     * @param DocumentType  $type          The type of transaction to create (See DocumentType::* for a list of allowable values)
     * @param string        $customerCode  The customer code for this transaction
     */
    public function __construct($client, $companyCode, $type, $customerCode)
    {
        $this->_client = $client;
        $this->_line_number = 1;
        $this->_model = [
            'companyCode' => $companyCode,
            'customerCode' => $customerCode,
            'date' => date('Y-m-d H:i:s'),
            'type' => $type,
            'lines' => [],
        ];
    }

    /**
     * Set the commit flag of the transaction.
     *
     * @return
     */
    public function withCommit()
    {
        $this->_model['commit'] = true;
        return $this;
    }

    /**
     * Enable diagnostic information
     *
     * @return  TransactionBuilder
     */
    public function withDiagnostics()
    {
        $this->_model['debugLevel'] = Constants::TAXDEBUGLEVEL_DIAGNOSTIC;
        return $this;
    }

    /**
     * Set a specific discount amount
     *
     * @param   float               $discount
     * @return  TransactionBuilder
     */
    public function withDiscountAmount($discount)
    {
        $this->_model['discount'] = $discount;
        return $this;
    }

    /**
     * Set if discount is applicable for the current line
     *
     * @param   boolean             discounted
     * @return  TransactionBuilder
     */
    public function withItemDiscount($discounted)
    {
        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['discounted'] = $discounted;
        return $this;
    }

    /**
     * Set a specific transaction code
     *
     * @param   string              code
     * @return  TransactionBuilder
     */
    public function withTransactionCode($code)
    {
        $this->_model['code'] = $code;
        return $this;
    }

    /**
     * Set the document type
     *
     * @param   string              type    (See DocumentType::* for a list of allowable values)
     * @return  TransactionBuilder
     */
    public function withType($type)
    {
        $this->_model['type'] = $type;
        return $this;
    }

    /**
     * Set VAT business identification number for customer
     *
     * @param   string              no
     * @return TransactionBuilder
     */
    public function withBusinessIdentificationNo($no)
    {
        $this->_model['businessIdentificationNo'] = $no;
        return $this;
    }

    /**
     * Set client application customer or usage type
     *
     * @param   string              code    (See API endpoint `/api/v2/definitions/entityusecodes` for a list of allowable values)
     * @return TransactionBuilder
     */
    public function withEntityUseCode($code)
    {
        $this->_model['entityUseCode'] = $code;
        return $this;
    }

    /**
     * Set purchase order number
     *
     * @param   string              no
     * @return TransactionBuilder
     */
    public function withPurchaseOrderNo($no)
    {
        $this->_model['purchaseOrderNo'] = $no;
        return $this;
    }

    /**
     * Set customer-provided reference code
     *
     * @param   string              code
     * @return TransactionBuilder
     */
    public function withReferenceCode($code)
    {
        $this->_model['referenceCode'] = $code;
        return $this;
    }

    /**
     * Set the currency code
     *
     * @param   string              code    (three-character ISO-4217 currency code)
     * @return TransactionBuilder
     */
    public function withCurrencyCode($code)
    {
        $this->_model['currencyCode'] = $code;
        return $this;
    }

    /**
     * Set the sale location code for reporting this document to the tax authority
     *
     * @param   string              code
     * @return TransactionBuilder
     */
    public function withReportingLocationCode($code)
    {
        $this->_model['reportingLocationCode'] = $code;
        return $this;
    }

    /**
     * Set flag for seller as importer of record
     *
     * @return TransactionBuilder
     */
    public function withSellerIsImporterOfRecord()
    {
        $this->_model['isSellerImporterOfRecord'] = true;
        return $this;
    }

    /**
     * Set exchange rate information
     *
     * @param   float               rate
     * @param   date                effectiveDate
     * @return TransactionBuilder
     */
    public function withExchangeRate($rate, $effectiveDate = null)
    {
        $this->_model['exchangeRate'] = $rate;
        if ($effectiveDate) {
            $this->_model['exchangeRateEffectiveDate'] = $effectiveDate;
        }
        return $this;
    }

    /**
     * Add a parameter at the document level
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withParameter($name, $value)
    {
        if (empty($this->_model['parameters'])) $this->_model['parameters'] = [];
        $this->_model['parameters'][$name] = $value;
        return $this;
    }

    /**
     * Add a parameter to the current line
     *
     * @param   string              name
     * @param   string              value
     * @return  TransactionBuilder
     */
    public function withLineParameter($name, $value)
    {
        $li = $this->getMostRecentLineIndex();
        if (empty($this->_model['lines'][$li]['parameters'])) {
            $this->_model['lines'][$li]['parameters'] = [];
        }
        $this->_model['lines'][$li]['parameters'][$name] = $value;
        return $this;
    }

    /**
     * Add an address to this transaction
     *
     * @param   string              type          Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1         The street address, attention line, or business name of the location.
     * @param   string              line2         The street address, business name, or apartment/unit number of the location.
     * @param   string              line3         The street address or apartment/unit number of the location.
     * @param   string              city          City of the location.
     * @param   string              region        State or Region of the location.
     * @param   string              postalCode    Postal/zip code of the location.
     * @param   string              country       The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        if (empty($this->_model['addresses'])) $this->_model['addresses'] = [];
        $ai = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        $this->_model['addresses'][$type] = $ai;
        return $this;
    }

    /**
     * Add a lat/long coordinate to this transaction
     *
     * @param   string              $type       Address Type (See AddressType::* for a list of allowable values)
     * @param   float               $latitude   The latitude of the geolocation for this transaction
     * @param   float               $longitude  The longitude of the geolocation for this transaction
     * @return  TransactionBuilder
     */
     public function withLatLong($type, $latitude, $longitude)
    {
        $this->_model['addresses'][$type] = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];
        return $this;
    }

    /**
     * Add an address to this line
     *
     * @param   string              type        Address Type (See AddressType::* for a list of allowable values)
     * @param   string              line1       The street address, attention line, or business name of the location.
     * @param   string              line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              line3       The street address or apartment/unit number of the location.
     * @param   string              city        City of the location.
     * @param   string              region      State or Region of the location.
     * @param   string              postalCode  Postal/zip code of the location.
     * @param   string              country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withLineAddress($type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['addresses'][$type] = [
            'line1' => $line1,
            'line2' => $line2,
            'line3' => $line3,
            'city' => $city,
            'region' => $region,
            'postalCode' => $postalCode,
            'country' => $country
        ];
        return $this;
    }

    /**
     * Add a document-level Tax Override to the transaction.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type       Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason     Reason of the Tax Override.
     * @param   float               $taxAmount  Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate    Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        $this->_model['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Add a line-level Tax Override to the current line.
     *  - A TaxDate override requires a valid DateTime object to be passed.
     * TODO: Verify Tax Override constraints and add exceptions.
     *
     * @param   string              $type        Type of the Tax Override (See TaxOverrideType::* for a list of allowable values)
     * @param   string              $reason      Reason of the Tax Override.
     * @param   float               $taxAmount   Amount of tax to apply. Required for a TaxAmount Override.
     * @param   date                $taxDate     Date of a Tax Override. Required for a TaxDate Override.
     * @return  TransactionBuilder
     */
    public function withLineTaxOverride($type, $reason, $taxAmount, $taxDate)
    {
        // Address the DateOverride constraint.
        if (($type == Constants::TAXOVERRIDETYPE_TAXDATE) && (empty($taxDate))) {
            throw new Exception("A valid date is required for a Tax Date Tax Override.");
        }

        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['taxOverride'] = [
            'type' => $type,
            'reason' => $reason,
            'taxAmount' => $taxAmount,
            'taxDate' => $taxDate
        ];

        // Continue building
        return $this;
    }

    /**
     * Set description for current line
     *
     * @param   string              description
     * @return TransactionBuilder
     * @throws \Exception
     */
    public function withLineDescription($description)
    {
        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['description'] = $description;

        return $this;
    }

    /**
     * Set flag on current line to indicate tax is included
     *
     * @return TransactionBuilder
     * @throws \Exception
     */
    public function withLineTaxIncluded()
    {
        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['taxIncluded'] = true;

        return $this;
    }

    /**
     * Set customer defined fields for current line
     *
     * @param   string              ref1
     * @param   string              ref2
     * @return TransactionBuilder
     * @throws \Exception
     */
    public function withLineCustomFields($ref1, $ref2 = null)
    {
        $li = $this->getMostRecentLineIndex();
        $this->_model['lines'][$li]['ref1'] = $ref1;
        if ($ref2) {
            $this->_model['lines'][$li]['ref2'] = $ref2;
        }

        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the item.
     * @param   float               $quantity    Quantity of the item.
     * @param   string              $taxCode     Tax Code of the item. If left blank, the default item (P0000000) is assumed.
     * @return  TransactionBuilder
     */
    public function withLine($amount, $quantity, $itemCode, $taxCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => $quantity,
            'amount' => $amount,
            'taxCode' => $taxCode,
            'itemCode' => $itemCode
        ];
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line to this transaction
     *
     * @param   float               $amount      Value of the line
     * @param   string              $type        Address Type  (See AddressType::* for a list of allowable values)
     * @param   string              $line1       The street address, attention line, or business name of the location.
     * @param   string              $line2       The street address, business name, or apartment/unit number of the location.
     * @param   string              $line3       The street address or apartment/unit number of the location.
     * @param   string              $city        City of the location.
     * @param   string              $region      State or Region of the location.
     * @param   string              $postalCode  Postal/zip code of the location.
     * @param   string              $country     The two-letter country code of the location.
     * @return  TransactionBuilder
     */
    public function withSeparateAddressLine($amount, $type, $line1, $line2, $line3, $city, $region, $postalCode, $country)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'addresses' => [
                $type => [
                    'line1' => $line1,
                    'line2' => $line2,
                    'line3' => $line3,
                    'city' => $city,
                    'region' => $region,
                    'postalCode' => $postalCode,
                    'country' => $country
                ]
            ]
        ];

        // Put this line in the model
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Add a line with an exemption to this transaction
     *
     * @param   float               $amount         The amount of this line item
     * @param   string              $exemptionCode  The exemption code for this line item
     * @return  TransactionBuilder
     */
    public function withExemptLine($amount, $itemCode, $exemptionCode)
    {
        $l = [
            'number' => $this->_line_number,
            'quantity' => 1,
            'amount' => $amount,
            'exemptionCode' => $exemptionCode,
            'itemCode'      => $itemCode
        ];
        array_push($this->_model['lines'], $l);
        $this->_line_number++;

        // Continue building
        return $this;
    }

    /**
     * Checks to see if the current model has a line.
     *
     * @return  int
     */
    private function getMostRecentLineIndex()
    {
        $c = count($this->_model['lines']);
        if ($c <= 0) {
            throw new \Exception("No lines have been added. The $memberName method applies to the most recent line.  To use this function, first add a line.");
        }

        return $c-1;
    }

    /**
     * Get the line number of the most recently added line
     *
     * @return int|null
     */
    public function getCurrentLineNumber()
    {
        try {
            return $this->getMostRecentLineIndex();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create this transaction
     *
     * @param string $include Specifies objects to include in the response after transaction is created
     * @return  TransactionModel
     */
    public function create($include = null)
    {
        return $this->_client->createTransaction($include, $this->_model);
    }

    /**
     * Create a transaction adjustment request that can be used with the AdjustTransaction() API call
     *
     * @return  AdjustTransactionModel
     */
    public function createAdjustmentRequest($desc, $reason)
    {
        return [
            'newTransaction' => $this->_model,
            'adjustmentDescription' => $desc,
            'adjustmentReason' => $reason
        ];
    }
}
?>
