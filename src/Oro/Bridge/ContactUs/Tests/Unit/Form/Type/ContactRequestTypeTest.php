<?php

namespace Oro\Bridge\ContactUs\Tests\Unit\Form\Extension;

use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bridge\ContactUs\Tests\Unit\Stub\ContactRequestStub;
use Oro\Bundle\ContactUsBundle\Form\Type\ContactRequestType as BaseContactRequestType;
use Oro\Bridge\ContactUs\Form\Type\ContactRequestType;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Oro\Component\Testing\Unit\EntityTrait;

class ContactRequestTypeTest extends TypeTestCase
{
    use EntityTrait;

    /**
     * @var ContactRequestType
     */
    protected $type;

    /**
     * @var SecurityFacade|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $securityFacade;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->securityFacade = $this->createMock(SecurityFacade::class);
        $this->type = new ContactRequestType($this->securityFacade);

        parent::setUp();
    }

    public function testSubmit()
    {
        $this->securityFacade->expects($this->once())
            ->method('getLoggedUser')
            ->willReturn(null);
        $contactRequest = $this->getEntity(ContactRequestStub::class);
        $form = $this->factory->create(
            ContactRequestType::class,
            $contactRequest
        );

        $form->submit(
            [
                'organizationName' => 'OroCRM',
                'firstName' => 'Amanda',
                'lastName' => 'Cole',
                'emailAddress' => 'AmandaRCole@example.org',
                'preferredContactMethod' => 'oro.contactus.contactrequest.method.phone',
                'contactReason' => 'test_contact_reason',
            ]
        );

        $expected = new ContactRequestStub();
        $expected->setFirstName('Amanda');
        $expected->setLastName('Cole');
        $expected->setEmailAddress('AmandaRCole@example.org');
        $expected->setOrganizationName('OroCRM');
        $expected->setPreferredContactMethod('oro.contactus.contactrequest.method.phone');
        $expected->setContactReason($this->mockContactReason());

        $this->assertEquals($expected, $contactRequest);
    }

    public function testPreSetDataListener()
    {
        $organization = new Organization();
        $organization->setName('OroCRM');
        /** @var CustomerUser $customerUser */
        $customerUser = $this->getEntity(
            CustomerUser::class,
            [
                'firstName' => 'Amanda',
                'lastName' => 'Cole',
                'email' => 'AmandaRCole@example.org',
                'organization' => $organization,
            ]
        );
        $this->securityFacade->expects($this->once())
            ->method('getLoggedUser')
            ->willReturn($customerUser);
        $contactRequest = $this->getEntity(ContactRequestStub::class);
        $form = $this->factory->create(
            ContactRequestType::class,
            $contactRequest
        );
        $view = $form->createView();

        $expected = new ContactRequestStub();
        $expected->setFirstName('Amanda');
        $expected->setLastName('Cole');
        $expected->setEmailAddress('AmandaRCole@example.org');
        $expected->setOrganizationName('OroCRM');
        $expected->setCustomerUser($customerUser);

        $this->assertEquals($expected, $contactRequest);

        $this->assertEquals('Amanda', $view['firstName']->vars['value']);
        $this->assertEquals('Cole', $view['lastName']->vars['value']);
        $this->assertEquals('AmandaRCole@example.org', $view['emailAddress']->vars['value']);
        $this->assertEquals('OroCRM', $view['organizationName']->vars['value']);
    }

    public function testPreSetDataListenerWithWrongLoggedUser()
    {
        $organization = new Organization();
        $organization->setName('OroCRM');
        $customerUser = new \stdClass;
        $this->securityFacade->expects($this->once())
            ->method('getLoggedUser')
            ->willReturn($customerUser);
        $contactRequest = $this->getEntity(ContactRequestStub::class);
        $form = $this->factory->create(
            ContactRequestType::class,
            $contactRequest
        );
        $view = $form->createView();

        $expected = new ContactRequestStub();

        $this->assertEquals($expected, $contactRequest);

        $this->assertEmpty($view['firstName']->vars['value']);
        $this->assertEmpty($view['lastName']->vars['value']);
        $this->assertEmpty($view['emailAddress']->vars['value']);
        $this->assertEmpty($view['organizationName']->vars['value']);
    }

    public function testGetParent()
    {
        $this->assertEquals(BaseContactRequestType::class, $this->type->getParent());
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtensions()
    {
        $entityType = new EntityType(['test_contact_reason' => $this->mockContactReason()]);

        return [
            new PreloadedExtension(
                [
                    $this->type,
                    $entityType->getName() => $entityType,
                ],
                []
            ),
        ];
    }

    /**
     * @return ContactReason|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockContactReason()
    {
        return $this->createMock(ContactReason::class);
    }
}
