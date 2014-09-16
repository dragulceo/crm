<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailTemplateRepository;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class TransportSettingsEmailTemplateListener implements EventSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Fill template choices based on Existing EmailCampaign{MarketingList} entity class.
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $entityName = $event->getForm()->getParent()->getData()->getEntityName();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * Fill template choices based on new EmailCampaign{MarketingList} entity class
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data['parentData']['marketingList'])) {
            return;
        }

        $marketingList = $this->getMarketingListById((int)$data['parentData']['marketingList']);
        if (is_null($marketingList)) {
            return;
        }

        $entityName = $marketingList->getEntity();
        $this->fillEmailTemplateChoices($event->getForm(), $entityName);
    }

    /**
     * @param int $id
     * @return MarketingList
     */
    protected function getMarketingListById($id)
    {
        return $this->registry
            ->getRepository('OroCRMMarketingListBundle:MarketingList')
            ->find($id);
    }

    /**
     * @param FormInterface $form
     * @param string $entityName
     */
    protected function fillEmailTemplateChoices(FormInterface $form, $entityName)
    {
        FormUtils::replaceField(
            $form,
            'template',
            [
                'selectedEntity' => $entityName,
                'query_builder' => function (EmailTemplateRepository $templateRepository) use ($entityName) {
                    return $templateRepository->getEntityTemplatesQueryBuilder($entityName);
                },
            ],
            ['choice_list', 'choices']
        );
    }
}
