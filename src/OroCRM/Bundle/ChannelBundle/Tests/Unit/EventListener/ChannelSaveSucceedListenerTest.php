<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\EventListener\ChannelSaveSucceedListener;
use OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelSaveSucceedListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface */
    protected $registry;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SettingsProvider */
    protected $settingProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject|ChannelSaveEvent */
    protected $event;

    /** @var Channel */
    protected $entity;

    /** @var Integration */
    protected $integration;

    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager */
    protected $em;

    public function setUp()
    {
        $this->registry        = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $this->settingProvider = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()->getMock();
        $this->event           = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Event\ChannelSaveEvent')
            ->disableOriginalConstructor()->getMock();
        $this->em          = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $this->entity          = new Channel();
        $this->integration     = new Integration();

        $this->entity->setDataSource($this->integration);
    }

    protected function tearDown()
    {
        unset($this->entity, $this->integration);
    }

    public function testOnChannelSucceedSave()
    {
        $this->entity->setEntities(
            [
                'OroCRM\Bundle\AcmeBundle\Entity\TestEntity1',
                'OroCRM\Bundle\AcmeBundle\Entity\TestEntity2',
            ]
        );

        $this->event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));

        $this->settingProvider
            ->expects($this->at(0))
            ->method('getIntegrationConnectorName')
            ->with('OroCRM\Bundle\AcmeBundle\Entity\TestEntity1')
            ->will($this->returnValue('TestConnector1'));

        $this->settingProvider
            ->expects($this->at(1))
            ->method('getIntegrationConnectorName')
            ->with('OroCRM\Bundle\AcmeBundle\Entity\TestEntity2')
            ->will($this->returnValue('TestConnector2'));

        $this->registry->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
        $this->em->expects($this->once())->method('persist')->with($this->integration);
        $this->em->expects($this->once())->method('flush');

        $channelSaveSucceedListener = new ChannelSaveSucceedListener($this->settingProvider, $this->registry);
        $channelSaveSucceedListener->onChannelSucceedSave($this->event);

        $this->assertEquals($this->integration->getConnectors(), ['TestConnector1', 'TestConnector2']);
    }
}
