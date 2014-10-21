<?php

namespace Phpoaipmh;

use PHPUnit_Framework_TestCase;
use GuzzleHttp\Client as GuzzleHttpClient;

/**
 * Endpoint CURL Test
 * @package Phpoaipmh
 */
class EndpointCurlTest extends PHPUnit_Framework_TestCase
{
    // --------------------------------------------------------------

    const ENDPOINT_URL    = 'http://nsdl.org/oai';
    const METADATA_PREFIX = 'oai_dc';
    const RECORD_ID       = 'oai:nsdl.org:2200/20121012134007915T';

    // --------------------------------------------------------------

    protected function setUp()
    {
        //Send a request to the endpoint to compare..
        $guzzle = new GuzzleHttpClient();
        $response = $guzzle->get(self::ENDPOINT_URL . '?verb=Identify');

        //Search for a known string to ensure the endpoint works before testing it
        $searchFor = "<Identify>";
        if (strpos((string) $response->getBody(), $searchFor) === false) {
            $this->markTestSkipped(sprintf('Could not connect to test repository at: %s', self::ENDPOINT_URL));
        }
    }

    // --------------------------------------------------------------

    public function testGetRecordWorksForCorrectRecord()
    {
        //First, make sure the record actually exists using Guzzle
        $guzzle   = new GuzzleHttpClient();
        $uri      = sprintf(self::ENDPOINT_URL . "?verb=GetRecord&identifier=%s&metadataPrefix=%s", self::RECORD_ID, self::METADATA_PREFIX);
        $response = $guzzle->get($uri);

        //Search for a known string to ensure the endpoint works before testing it
        $searchFor = "<GetRecord>";
        if (strpos((string) $response->getBody(), $searchFor) === false) {
            $this->markTestSkipped(sprintf('Could not find test record: %s', self::RECORD_ID));
        }

        //Then, run the test using the Endpoint Library
        $endpoint = $this->getObj();
        $result = $endpoint->getRecord(self::RECORD_ID, self::METADATA_PREFIX);

        $this->assertInstanceOf('\SimpleXMLElement', $result);
        $this->assertEquals((string) $result->GetRecord->record->header->identifier[0], self::RECORD_ID);
    }

    // --------------------------------------------------------------

    /**
     * @depends testGetRecordWorksForCorrectRecord
     * @expectedException \Phpoaipmh\Exception\OaipmhException
     */
    public function testGetRecordThrowsOAIExceptionForInvalidRecord()
    {
        $endpoint = $this->getObj();
         $endpoint->getRecord('thisISTotalFake'. rand(1000, 4000), self::METADATA_PREFIX);
    }

    // --------------------------------------------------------------

    /**
     * Get the Endpoint Object
     *
     * @return Endpoint
     */
    protected function getObj()
    {
        return new Endpoint(new Client(self::ENDPOINT_URL, $this->getHttpAdapterObj()));
    }

    // --------------------------------------------------------------

    /**
     * @return \Phpoaipmh\HttpAdapter\HttpAdapterInterface
     */
    protected function getHttpAdapterObj()
    {
        return new HttpAdapter\CurlAdapter();
    }
}

/* EOF: EndpointRealTest.php */