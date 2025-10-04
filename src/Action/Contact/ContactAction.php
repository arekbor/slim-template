<?php

declare(strict_types=1);

namespace App\Action\Contact;

use App\Action\AbstractAction;
use App\Repository\ContactRepository;
use App\Validator\Assert\EmailAssert;
use App\Validator\Assert\NotEmptyAssert;
use App\Validator\FormValidator;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class ContactAction extends AbstractAction
{
    public function __construct(
        protected readonly FormValidator $formValidator,
        protected readonly ContactRepository $contactRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        $contactId = $this->getArg('id');
        $contact = $contactId ? $this->contactRepository->getContactById(intval($contactId)) : null;

        $this->formValidator
            ->addField(
                fieldName: 'id',
                value: $contact['id'] ?? null
            )
            ->addField(
                fieldName: 'firstname',
                value: $contact['firstname'] ?? null,
                asserts: [new NotEmptyAssert()]
            )
            ->addField(
                fieldName: 'lastname',
                value: $contact['lastname'] ?? null,
                asserts: [new NotEmptyAssert()]
            )
            ->addField(
                fieldName: 'email',
                value: $contact['email'] ?? null,
                asserts: [new NotEmptyAssert(), new EmailAssert()]
            );

        if ($this->request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->render("contact/index.html.twig", [
                'contactId' => $contactId,
                'form' => $this->formValidator->getForm()
            ]);
        }

        $body = $this->request->getParsedBody();
        if (!$this->formValidator->valid($body)) {
            return $this->getInvalidForm();
        }

        $userId = $_SESSION['user']['id'];
        if (!$userId) {
            $this->formValidator->addFormError("User ID not found when saving contact.");
            return $this->getInvalidForm();
        }

        $contact = $this->formValidator->getFormValues();

        if ($contact['id']) {
            //TODO: jest taka sytuacja, ze jezeli nie wykryje zmian to zwraca false
            //TODO: zrób sprawdzanie czy userId === $contact['id']
            $this->contactRepository->updateContact([
                'id' => $contact['id'],
                'firstname' => $contact['firstname'],
                'lastname' => $contact['lastname'],
                'email' => $contact['email']
            ]);

            return $this->hxRedirect('dashboard');
        }

        $isContactCreated = $this->contactRepository->createContact([
            'firstname' => $contact['firstname'],
            'lastname' => $contact['lastname'],
            'email' => $contact['email'],
            'userId' => $userId
        ]);

        if (!$isContactCreated) {
            $this->formValidator->addFormError("Error while creating contact.");
            return $this->getInvalidForm();
        }

        return $this->hxRedirect('dashboard');
    }

    private function getInvalidForm(): ResponseInterface
    {
        return $this->render("contact/form.html.twig", [
            "form" => $this->formValidator->getForm()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
