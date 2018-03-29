<?php

use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;

/**
 * Emit a new stack publish version signal after content is published in legacy.
 * This signal can be detected by a Signal Listener (see ../../../../../EventListener/PublishVersionListener.php).
 */
class PublishSignalType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_STRING = "publishsignal";

    public function __construct()
    {
        $this->eZWorkflowEventType(PublishSignalType::WORKFLOW_TYPE_STRING, "Publish content signal");
        $this->setTriggerTypes([
            'content' => [
                'publish' => [
                    'after'
                ]
            ]
        ]);
    }

    public function execute($process, $event)
    {
        $parameters = $process->attribute('parameter_list');
        $container = ezpKernel::instance()->getServiceContainer();
        $logger = $container->get('logger');
        $signalDispatcher = $container->get('ezpublish.signalslot.signal_dispatcher');

        $logger->debug('Emitting PublishVersionSignal for content ID: ' . $parameters['object_id'] . ", version " . $parameters['version']);
        $signalDispatcher->emit(
            new PublishVersionSignal(
                array(
                    'contentId' => $parameters['object_id'],
                    'versionNo' => $parameters['version'],
                )
            )
        );

        return PublishSignalType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType(PublishSignalType::WORKFLOW_TYPE_STRING, "PublishSignalType");
