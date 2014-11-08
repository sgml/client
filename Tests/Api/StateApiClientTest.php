<?php

/*
 * This file is part of the xAPI package.
 *
 * (c) Christian Flothmann <christian.flothmann@xabbuh.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xabbuh\XApi\Client\Tests\Api;

use Xabbuh\XApi\Client\Api\StateApiClient;
use Xabbuh\XApi\Model\Activity;
use Xabbuh\XApi\Model\Agent;
use Xabbuh\XApi\Model\State;
use Xabbuh\XApi\Model\StateDocument;
use Xabbuh\XApi\Serializer\ActorSerializer;
use Xabbuh\XApi\Serializer\DocumentSerializer;

/**
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
class StateApiClientTest extends ApiClientTest
{
    /**
     * @var StateApiClient
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = new StateApiClient(
            $this->requestHandler,
            '1.0.1',
            new DocumentSerializer($this->serializer),
            new ActorSerializer($this->serializer)
        );
    }

    public function testCreateOrUpdateDocument()
    {
        $document = $this->createStateDocument();

        $this->validateStoreApiCall(
            'post',
            'activities/state',
            array(
                'activityId' => 'activity-id',
                'agent' => 'agent-as-json',
                'stateId' => 'state-id',
            ),
            204,
            '',
            $document,
            array(array('data' => $document->getState()->getActor(), 'result' => 'agent-as-json'))
        );

        $this->client->createOrUpdateDocument($document);
    }

    public function testCreateOrReplaceDocument()
    {
        $document = $this->createStateDocument();

        $this->validateStoreApiCall(
            'put',
            'activities/state',
            array(
                'activityId' => 'activity-id',
                'agent' => 'agent-as-json',
                'stateId' => 'state-id',
            ),
            204,
            '',
            $document,
            array(array('data' => $document->getState()->getActor(), 'result' => 'agent-as-json'))
        );

        $this->client->createOrReplaceDocument($document);
    }

    public function testDeleteDocument()
    {
        $state = $this->createState();

        $this->validateDeleteDocumentCall(
            'activities/state',
            array(
                'activityId' => 'activity-id',
                'agent' => 'agent-as-json',
                'stateId' => 'state-id',
            ),
            array(array('data' => $state->getActor(), 'result' => 'agent-as-json'))
        );

        $this->client->deleteDocument($state);
    }

    public function testGetStateDocument()
    {
        $state = $this->createState();
        $document = new StateDocument();
        $document['x'] = 'foo';

        $this->validateRetrieveApiCall(
            'get',
            'activities/state',
            array(
                'activityId' => 'activity-id',
                'agent' => 'agent-as-json',
                'stateId' => 'state-id',
            ),
            200,
            'StateDocument',
            $document,
            array(array('data' => $state->getActor(), 'result' => 'agent-as-json'))
        );

        $document = $this->client->getDocument($state);

        $this->assertInstanceOf('Xabbuh\XApi\Model\StateDocument', $document);
        $this->assertEquals($state, $document->getState());
    }

    private function createState()
    {
        $agent = new Agent('mailto:alice@example.com');
        $activity = new Activity('activity-id');
        $state = new State($activity, $agent, 'state-id');

        return $state;
    }

    private function createStateDocument()
    {
        $state = $this->createState();
        $document = new StateDocument();
        $document['x'] = 'foo';
        $document['y'] = 'bar';
        $document->setState($state);

        return $document;
    }
}
