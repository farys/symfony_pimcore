<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\TodoTask;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportTodosListCommand extends AbstractCommand
{

    protected $endpointUrl = "https://jsonplaceholder.typicode.com/todos/";
    protected $todosFolder = "/TODO List";

    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setName('app:import-todos-list');
    }

    /**
     * {@inheritdoc}
     * @throws TransportExceptionInterface
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->httpClient->request('GET', $this->endpointUrl);
        $statusCode = $response->getStatusCode();
        $contentType = null;

        try {
            if (!empty($response->getHeaders()['content-type'])) {
                $contentType = $response->getHeaders()['content-type'][0];
            }
        } catch (ClientExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $output->writeln("Couldn't retrieve content type from endpoint's response headers");
            return Command::FAILURE;
        }

        if ($statusCode != 200 || !str_starts_with($contentType, "application/json")) {
            $output->writeln("Received incorrect content type from endpoint's response headers");
            return Command::FAILURE;
        }

        try {
            $todos = $response->toArray();
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $output->writeln("Couldn't convert json to array from endpoint's response");
            return Command::FAILURE;
        }

        $parentFolder = \Pimcore\Model\DataObject\Folder::getByPath($this->todosFolder);

        if(!$parentFolder){
            $output->writeln("Todos folder not found.");
            return Command::FAILURE;
        }

        foreach ($todos as $todoRecord) {

            $id = intval($todoRecord['id']);

            $todoTask = TodoTask::getByPath($parentFolder . "/" . $id);

            if (!($todoTask instanceof TodoTask)) {
                $todoTask = TodoTask::create();
                $todoTask->setKey($id);
                $todoTask->setParent($parentFolder);
            }

            if ($todoTask->getUserId() != $todoRecord['userId']) {
                $todoTask->setUserId($todoRecord['userId']);
            }

            $completed = boolval($todoRecord['completed']);

            if($todoTask->getCompleted() != $completed){
                $todoTask->setCompleted($completed);
            }

            if($todoTask->getTitle() != $todoRecord['title']){
                $todoTask->setTitle($todoRecord['title']);
            }

            if ($todoTask->hasDirtyFields()) {
                $todoTask->save();
            }
        }

        $output->writeln('Done. Updated/Created tasks: ' . count($todos));
        return Command::SUCCESS;
    }
}
