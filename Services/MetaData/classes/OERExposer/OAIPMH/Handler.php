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

namespace ILIAS\MetaData\OERExposer\OAIPMH;

use ILIAS\MetaData\OAIPMH\Requests\Argument;
use ILIAS\MetaData\OERExposer\OAIPMH\HTTP\RequestParserInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Responses\WriterInterface;
use ILIAS\MetaData\OAIPMH\Responses\Error;
use ILIAS\MetaData\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\RequestInterface;
use ILIAS\MetaData\OERExposer\OAIPMH\Requests\Request;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as ResourceStatusRepositoryInterface;

class Handler
{
    protected RequestParserInterface $request_parser;
    protected WriterInterface $writer;
    protected \ilMDSettings $settings;
    protected ResourceStatusRepositoryInterface $resource_status_repository;

    protected readonly string $valid_md_prefix;
    protected readonly int $max_list_size;
    protected readonly URI $base_url;

    public function __construct(
        RequestParserInterface $request_parser,
        WriterInterface $writer,
        \ilMDSettings $settings,
        ResourceStatusRepositoryInterface $resource_status_repository
    ) {
        $this->request_parser = $request_parser;
        $this->writer = $writer;
        $this->settings = $settings;
        $this->resource_status_repository = $resource_status_repository;

        $this->valid_md_prefix = 'oai_dc';
        $this->max_list_size = 100;
        $this->base_url = new URI('Insert base URL here');
    }

    /**
     * TODO have this send the request
     */
    public function respondToRequest(): \DomDocument
    {
        $request = $this->parseRequest();
        if ($request->verb() === Verb::NULL) {
            return $this->writer->writeErrorResponse(
                $request,
                $this->writer->writeError(
                    Error::BAD_VERB,
                    'No valid OAI-PMH verb in request.'
                )
            );
        }

        return match ($request->verb()) {
            Verb::GET_RECORD => $this->getRecord($request),
            Verb::IDENTIFY => $this->identify($request),
            Verb::LIST_IDENTIFIERS, Verb::LIST_RECORDS => $this->listRecordsOrIdentifiers($request),
            Verb::LIST_MD_FORMATS => $this->listMetadataFormats($request),
            Verb::LIST_SETS => $this->listSets($request),
            default => $this->writer->writeErrorResponse(
                $request,
                $this->writer->writeError(
                    Error::BAD_VERB,
                    'No valid OAI-PMH verb in request.'
                )
            )
        };
    }

    protected function getRecord(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if ($request->hasCorrectArguments([Argument::IDENTIFIER, Argument::MD_PREFIX], [], [])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::GET_RECORD,
                ...$request->argumentKeys()
            );
        }

        if (
            $request->hasArgument(Argument::MD_PREFIX) &&
            $request->argumentValue(Argument::MD_PREFIX) !== $this->valid_md_prefix
        ) {
            $errors[] = $this->writer->writeError(
                Error::CANNOT_DISSEMINATE_FORMAT,
                'This repository only supports oai_dc as metadata format.'
            );
        }

        $record = null;
        if ($request->hasArgument(Argument::IDENTIFIER)) {
            $identifier = $request->argumentValue(Argument::IDENTIFIER);
            $record = $this->resource_status_repository->getExposedRecordByIdentifier($identifier);
            if (is_null($record)) {
                $errors[] = $this->writer->writeError(
                    Error::ID_DOES_NOT_EXIST,
                    'This repository does not have a record with identifier "' . $identifier . '".'
                );
            }
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            $this->writer->writeRecord(
                $record->infos()->identfifier(),
                $record->infos()->datestamp(),
                $record->metadata()
            )
        );
    }

    protected function identify(RequestInterface $request): \DomDocument
    {
        if (!$request->hasCorrectArguments([], [], [])) {
            return $this->writer->writeErrorResponse(
                $request,
                $this->writeBadArgumentError(
                    Verb::IDENTIFY,
                    ...$request->argumentKeys()
                )
            );
        }

        return $this->writer->writeResponse(
            $request,
            ...$this->writer->writeIdentifyElements(
                $this->settings->getOAIRepositoryName(),
                $request->baseURL(),
                $this->resource_status_repository->getEarliestExposedDatestamp(),
                $this->settings->getOAIContactMail()
            )
        );
    }

    protected function listMetadataFormats(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if ($request->hasCorrectArguments([], [Argument::IDENTIFIER], [])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_MD_FORMATS,
                ...$request->argumentKeys()
            );
        }

        if (
            $request->hasArgument(Argument::IDENTIFIER) &&
            !$this->resource_status_repository->doesExposedRecordWithIdentifierExist(
                $identifier = $request->argumentValue(Argument::IDENTIFIER)
            )
        ) {
            $errors[] = $this->writer->writeError(
                Error::ID_DOES_NOT_EXIST,
                'This repository does not have a record with identifier "' . $identifier . '".'
            );
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            $this->writer->writeMetadataFormat()
        );
    }

    protected function listSets(RequestInterface $request): \DomDocument
    {
        $errors = [];
        if ($request->hasCorrectArguments([], [], [Argument::RESUMPTION_TOKEN])) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_SETS,
                ...$request->argumentKeys()
            );
        }
        $errors[] = $this->writer->writeError(
            Error::NO_SET_HIERARCHY,
            'This repository does not support sets.'
        );

        return $this->writer->writeErrorResponse(
            $request,
            ...$errors
        );
    }

    /**
     * TODO Implement resumption token
     */
    protected function listRecordsOrIdentifiers(
        RequestInterface $request
    ): \DomDocument {
        $errors = [];
        if ($request->hasCorrectArguments(
            [Argument::MD_PREFIX],
            [Argument::FROM_DATE, Argument::UNTIL_DATE, Argument::SET],
            [Argument::RESUMPTION_TOKEN]
        )) {
            $errors[] = $this->writeBadArgumentError(
                Verb::LIST_IDENTIFIERS,
                ...$request->argumentKeys()
            );
        }

        if ($request->hasArgument(Argument::SET)) {
            $errors[] = $this->writer->writeError(
                Error::NO_SET_HIERARCHY,
                'This repository does not support sets.'
            );
        }

        if (
            $request->hasArgument(Argument::MD_PREFIX) &&
            $request->argumentValue(Argument::MD_PREFIX) !== $this->valid_md_prefix
        ) {
            $errors[] = $this->writer->writeError(
                Error::CANNOT_DISSEMINATE_FORMAT,
                'This repository only supports oai_dc as metadata format.'
            );
        }

        $from_date = null;
        if ($request->hasArgument(Argument::FROM_DATE)) {
            $from_date = $request->argumentValue(Argument::FROM_DATE);
        }
        $until_date = null;
        if ($request->hasArgument(Argument::UNTIL_DATE)) {
            $until_date = $request->argumentValue(Argument::UNTIL_DATE);
        }

        $content_xmls = [];
        if ($request->verb() === Verb::LIST_IDENTIFIERS) {
            $record_infos = $this->resource_status_repository->getExposedRecordInfos($from_date, $until_date);
            foreach ($record_infos as $info) {
                $content_xmls[] = $this->writer->writeRecordHeader(
                    $info->identfifier(),
                    $info->datestamp()
                );
            }
        } elseif ($request->verb() === Verb::LIST_RECORDS) {
            $records = $this->resource_status_repository->getExposedRecords($from_date, $until_date);
            foreach ($records as $record) {
                $content_xmls[] = $this->writer->writeRecord(
                    $record->infos()->identfifier(),
                    $record->infos()->datestamp(),
                    $record->metadata()
                );
            }
        } else {
            throw new \ilMDOERExposerException('Invalid verb handling.');
        }

        if (empty($content_xmls)) {
            $errors[] = $this->writer->writeError(
                Error::NO_RECORDS_MATCH,
                'No matching records found.'
            );
        }

        if (!empty($errors)) {
            return $this->writer->writeErrorResponse($request, ...$errors);
        }
        return $this->writer->writeResponse(
            $request,
            ...$content_xmls
        );
    }

    protected function writeBadArgumentError(Verb $verb, Argument ...$arguments): \DomDocument
    {
        if (empty($arguments)) {
            $message = $verb->value . ' must come with additional arguments.';
        } else {
            $message = implode(', ', $arguments) .
            ' is not a valid set of arguments for ' . $verb->value . '.';
        }
        return $this->writer->writeError(
            Error::BAD_ARGUMENT,
            $message
        );
    }

    protected function parseRequest(): RequestInterface
    {
        $verb = Verb::NULL;
        if ($this->request_parser->hasArgument(Argument::VERB)) {
            $verb = $this->request_parser->retrieveArgument(Argument::VERB);
        }

        $request = $this->getEmptyRequest($verb);

        foreach (Argument::cases() as $argument) {
            if (
                $argument === Argument::VERB ||
                !$this->request_parser->hasArgument($argument)
            ) {
                continue;
            }
            $request->withArgument(
                $argument,
                $this->request_parser->retrieveArgument($argument)
            );
        }

        return $request;
    }

    protected function getEmptyRequest(Verb $verb): RequestInterface
    {
        return new Request(
            $this->base_url,
            $verb
        );
    }
}
