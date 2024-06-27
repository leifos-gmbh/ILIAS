<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MetaData\OERExposer\OAIPMH\Responses;

use PHPUnit\Framework\TestCase;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\NullRequest;
use ILIAS\MetaData\Settings\NullSettings;
use ILIAS\MetaData\OERHarvester\ResourceStatus\NullRepository;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\NullTokenHandler;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\FlowControl\TokenHandlerInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RecordInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\NullRecord;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RecordInfosInterface;
use ILIAS\MetaData\OERHarvester\ResourceStatus\NullRecordInfos;

class RequestProcessorTest extends TestCase
{
    protected function getDate(string $string): \DateTimeImmutable
    {
        return new \DateTimeImmutable($string, new \DateTimeZone('UTC'));
    }

    protected function getURI(string $string): URI
    {
        $url = $this->createMock(URI::class);
        $url->method('__toString')->willReturn($string);
        return $url;
    }

    /**
     * Argument names are keys, their values values (all as strings)
     */
    protected function getRequest(
        string $base_url,
        Verb $verb,
        array $arguments_with_values,
        bool $correct_arguments = true
    ): RequestInterface {
        $base_url = $this->getURI($base_url);

        return new class ($base_url, $verb, $arguments_with_values, $correct_arguments) extends NullRequest {
            public function __construct(
                protected URI $base_url,
                protected Verb $verb,
                protected array $arguments_with_values,
                protected bool $correct_values
            ) {
            }

            public function baseURL(): URI
            {
                return $this->base_url;
            }

            public function verb(): Verb
            {
                return $this->verb;
            }

            public function argumentKeys(): \Generator
            {
                foreach ($this->arguments_with_values as $argument_key => $argument_value) {
                    if (!is_null($r = Argument::tryFrom($argument_key))) {
                        yield $r;
                    }
                }
            }

            public function hasArgument(Argument $argument): bool
            {
                return in_array($argument->value, array_keys($this->arguments_with_values));
            }

            public function argumentValue(Argument $argument): string
            {
                return $this->arguments_with_values[$argument->value] ?? '';
            }

            public function hasCorrectArguments(
                array $required,
                array $optional,
                array $exclusive
            ): bool {
                return $this->correct_values;
            }
        };
    }

    protected function getWriter(): WriterInterface
    {
        return new class () extends NullWriter {
            public function writeError(Error $error, string $message): \DOMDocument
            {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('error', $error->value));
                return $doc;
            }

            public function writeIdentifyElements(
                string $repository_name,
                URI $base_url,
                \DateTimeImmutable $earliest_datestamp,
                string $first_admin_email,
                string ...$further_admin_emails
            ): \Generator {
                $els = [
                    $repository_name,
                    (string) $base_url,
                    $earliest_datestamp->format('Y-m-d'),
                    $first_admin_email
                ];
                foreach ($els as $idx => $el) {
                    $doc = new \DOMDocument();
                    $doc->appendChild($doc->createElement('info', $el));
                    yield $doc;
                }
            }

            /**
             * Currently only oai_dc.
             */
            public function writeMetadataFormat(): \DOMDocument
            {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement('md_format', 'some metadata'));
                return $doc;
            }

            public function writeRecordHeader(
                string $identifier,
                \DateTimeImmutable $datestamp
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement(
                    'header',
                    $identifier . ':' . $datestamp->format('Y-m-d')
                ));
                return $doc;
            }

            /**
             * Also includes the header.
             */
            public function writeRecord(
                string $identifier,
                \DateTimeImmutable $datestamp,
                \DOMDocument $metadata
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($root = $doc->createElement('record'));
                $root->appendChild(
                    $doc->createElement(
                        'record_info',
                        $identifier . ':' . $datestamp->format('Y-m-d')
                    )
                );
                $root->appendChild($doc->importNode($metadata->documentElement, true));
                return $doc;
            }

            public function writeResumptionToken(
                string $token,
                int $complete_list_size,
                int $cursor
            ): \DOMDocument {
                $doc = new \DOMDocument();
                $doc->appendChild($doc->createElement(
                    'token',
                    $token . ':' . $complete_list_size . ':' . $cursor
                ));
                return $doc;
            }

            public function writeResponse(
                RequestInterface $request,
                \DOMDocument ...$contents
            ): \DOMDocument {
                return $this->writeResponseOrErrorResponse(
                    'response',
                    $request,
                    ...$contents
                );
            }

            public function writeErrorResponse(
                RequestInterface $request,
                \DOMDocument ...$errors
            ): \DOMDocument {
                return $this->writeResponseOrErrorResponse(
                    'error_response',
                    $request,
                    ...$errors
                );
            }

            protected function writeResponseOrErrorResponse(
                string $root_name,
                RequestInterface $request,
                \DOMDocument ...$contents
            ): \DOMDocument {
                $args = [];
                foreach ($request->argumentKeys() as $key) {
                    $args[] = $key->value . '=' . $request->argumentValue($key);
                }

                $doc = new \DOMDocument();
                $doc->appendChild($root = $doc->createElement($root_name));
                $root->appendChild($doc->createElement(
                    'response_info',
                    $request->baseURL() . ':' . $request->verb()->value . ':' . implode(',', $args)
                ));
                foreach ($contents as $content) {
                    $root->appendChild($doc->importNode($content->documentElement, true));
                }
                return $doc;
            }
        };
    }

    protected function getSettings(
        string $prefix = '',
        string $repo_name = '',
        string $contact_mail = ''
    ): SettingsInterface {
        return new class ($prefix, $repo_name, $contact_mail) extends NullSettings {
            public function __construct(
                protected string $prefix,
                protected string $repo_name,
                protected string $contact_mail
            ) {
            }

            public function getOAIIdentifierPrefix(): string
            {
                return $this->prefix;
            }

            public function getOAIContactMail(): string
            {
                return $this->contact_mail;
            }

            public function getOAIRepositoryName(): string
            {
                return $this->repo_name;
            }
        };
    }

    /**
     * Append datestamps to identifiers with +YYYY-MM-DD
     */
    protected function getRepository(
        string $earliest_datestamp = null,
        string ...$identifiers
    ): RepositoryInterface {
        $earliest_datestamp = $this->getDate($earliest_datestamp ?? '@0');
        return new class ($earliest_datestamp, $identifiers) extends NullRepository {
            public function __construct(
                protected \DateTimeImmutable $earliest_datestamp,
                protected array $identifiers,
            ) {
            }

            public function getEarliestExposedDatestamp(): \DateTimeImmutable
            {
                return $this->earliest_datestamp;
            }

            public function doesExposedRecordWithIdentifierExist(string $identifier): bool
            {
                return in_array($identifier, $this->identifiers);
            }

            public function getExposedRecordByIdentifier(string $identifier): ?RecordInterface
            {
                if (!$this->doesExposedRecordWithIdentifierExist($identifier)) {
                    return null;
                }

                return new class ($identifier) extends NullRecord {
                    public function __construct(protected string $identifier)
                    {
                    }

                    public function infos(): RecordInfosInterface
                    {
                        return new class ($this->identifier) extends NullRecordInfos {
                            public function __construct(protected string $identifier)
                            {
                            }

                            public function datestamp(): \DateTimeImmutable
                            {
                                return new \DateTimeImmutable(
                                    explode('+', $this->identifier)[1],
                                    new \DateTimeZone('UTC')
                                );
                            }

                            public function identfifier(): string
                            {
                                return $this->identifier;
                            }
                        };
                    }

                    public function metadata(): \DOMDocument
                    {
                        $doc = new \DOMDocument();
                        $doc->appendChild($doc->createElement('md', 'md for ' . $this->identifier));
                        return $doc;
                    }
                };
            }
        };
    }

    protected function getTokenHandler(): TokenHandlerInterface
    {
        return new NullTokenHandler();
    }

    public function testGetResponseToRequestBadVerbError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:NoVerb:</response_info>
              <error>badVerb</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest('base url', Verb::NULL, []));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    /*
     * GetRecord
     */

    public function testGetResponseToRequestGetRecord(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <record>
                <record_info>prefix_id+2022-11-27:2022-11-27</record_info>
                <md>md for id+2022-11-27</md>
              </record>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordBadArgumentsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordWrongMDFormatError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=invalid</response_info>
              <error>cannotDisseminateFormat</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'invalid']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordInvalidIdentifierError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'id+2022-11-27'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=invalid_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'invalid_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordNotFoundError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=oai_dc</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'oai_dc']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestGetRecordMultipleErrors(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:GetRecord:identifier=prefix_id+2022-11-27,metadataPrefix=invalid,from=date</response_info>
              <error>badArgument</error>
              <error>cannotDisseminateFormat</error>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::GET_RECORD,
            [Argument::IDENTIFIER->value => 'prefix_id+2022-11-27', Argument::MD_PREFIX->value => 'invalid', Argument::FROM_DATE->value => 'date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    /*
     * Identify
     */

    public function testGetResponseToRequestIdentify(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('', 'name of repo', 'mail of contact'),
            $this->getRepository('2021-10-20'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:Identify:</response_info>
              <info>name of repo</info>
              <info>base url</info>
              <info>2021-10-20</info>
              <info>mail of contact</info>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::IDENTIFY,
            []
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestIdentifyBadArgumentsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('', 'name of repo', 'mail of contact'),
            $this->getRepository('2021-10-20'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:Identify:from=some date</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::IDENTIFY,
            [Argument::FROM_DATE->value => 'some date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    /*
     * ListIdentifiers
     */

    public function testGetResponseToRequestListIdentifiers(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWithFromDate(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWithUntilDate(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWithBothDates(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWithResumptionToken(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWithResumptionTokenWithFromDate(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersBadArgumentsError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersBadResumptionTokenError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersInvalidFromDateError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersInvalidUntilDateError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersWrongMDFormatError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersNoRecordsFoundError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersNoSetsError(): void
    {
    }

    public function testGetResponseToRequestListIdentifiersMultipleErrors(): void
    {
    }

    /*
     * ListMetadataFormats
     */

    public function testGetResponseToRequestListMetadataFormats(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListMetadataFormats:</response_info>
              <md_format>some metadata</md_format>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            []
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsWithIdentifier(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'id'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id</response_info>
              <md_format>some metadata</md_format>
            </response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsBadArgumentsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:until=some date</response_info>
              <error>badArgument</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::UNTIL_DATE->value => 'some date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsInvalidIdentifierError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(null, 'no prefix'),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=no prefix</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'no prefix']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsRecordNotFoundError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id</response_info>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id']
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListMetadataFormatsMultipleErrors(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings('prefix_'),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListMetadataFormats:identifier=prefix_id,until=some date</response_info>
              <error>badArgument</error>
              <error>idDoesNotExist</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_MD_FORMATS,
            [Argument::IDENTIFIER->value => 'prefix_id', Argument::UNTIL_DATE->value => 'some date'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    /*
     * ListRecords
     */

    public function testGetResponseToRequestListRecords(): void
    {
    }

    public function testGetResponseToRequestListRecordsWithFromDate(): void
    {
    }

    public function testGetResponseToRequestListRecordsWithUntilDate(): void
    {
    }

    public function testGetResponseToRequestListRecordsWithBothDates(): void
    {
    }

    public function testGetResponseToRequestListRecordsWithResumptionToken(): void
    {
    }

    public function testGetResponseToRequestListRecordsWithResumptionTokenWithFromDate(): void
    {
    }

    public function testGetResponseToRequestListRecordsBadArgumentsError(): void
    {
    }

    public function testGetResponseToRequestListRecordsBadResumptionTokenError(): void
    {
    }

    public function testGetResponseToRequestListRecordsInvalidFromDateError(): void
    {
    }

    public function testGetResponseToRequestListRecordsInvalidUntilDateError(): void
    {
    }

    public function testGetResponseToRequestListRecordsWrongMDFormatError(): void
    {
    }

    public function testGetResponseToRequestListRecordsNoRecordsFoundError(): void
    {
    }

    public function testGetResponseToRequestListRecordsNoSetsError(): void
    {
    }

    public function testGetResponseToRequestListRecordsMultipleErrors(): void
    {
    }

    /*
     * ListSets
     */

    public function testGetResponseToRequestListSetsNoSetsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListSets:</response_info>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest('base url', Verb::LIST_SETS, []));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }

    public function testGetResponseToRequestListSetsNotSetsAndBadArgumentsError(): void
    {
        $processor = new RequestProcessor(
            $this->getWriter(),
            $this->getSettings(),
            $this->getRepository(),
            $this->getTokenHandler()
        );

        $expected_response = <<<XML
            <error_response>
              <response_info>base url:ListSets:identifier=some id</response_info>
              <error>badArgument</error>
              <error>noSetHierarchy</error>
            </error_response>
            XML;

        $response = $processor->getResponseToRequest($this->getRequest(
            'base url',
            Verb::LIST_SETS,
            [Argument::IDENTIFIER->value => 'some id'],
            false
        ));

        $this->assertXmlStringEqualsXmlString($expected_response, $response->saveXML());
    }
}
