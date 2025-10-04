<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Action\AbstractAction;
use App\Repository\UserRepository;
use App\Validator\Assert\EmailAssert;
use App\Validator\Assert\NotEmptyAssert;
use App\Validator\Assert\PasswordMatchAssert;
use App\Validator\FormValidator;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class RegisterAction extends AbstractAction
{
    public function __construct(
        private readonly FormValidator $formValidator,
        private readonly UserRepository $userRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        if ($this->request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->render("auth/register/index.html.twig");
        }

        $body = $this->request->getParsedBody();

        $this->formValidator
            ->addField(fieldName: 'username', asserts: [new NotEmptyAssert()])
            ->addField(fieldName: 'email', asserts: [new NotEmptyAssert(), new EmailAssert()])
            ->addField(fieldName: 'password', asserts: [new NotEmptyAssert()])
            ->addField(fieldName: 'repeat_password', asserts: [new NotEmptyAssert(), new PasswordMatchAssert($body['password'])])
        ;

        if (!$this->formValidator->valid($body)) {
            return $this->getInvalidForm();
        }

        $user = $this->formValidator->getFormValues();

        if ($this->userRepository->getUserByEmail($user['email'])) {
            $this->formValidator->setFieldError('email', "This email is already used.");
            return $this->getInvalidForm();
        }

        $hashedPassword = password_hash($user['password'], PASSWORD_BCRYPT);

        $isUserCreated = $this->userRepository->createUser([
            'username' => $user['username'],
            'email' => $user['email'],
            'password' => $hashedPassword
        ]);

        if (!$isUserCreated) {
            return $this->hxRedirect("auth.register");
        }

        return $this->hxRedirect("auth.login");
    }

    private function getInvalidForm(): ResponseInterface
    {
        return $this->render("auth/register/form.html.twig", [
            "form" => $this->formValidator->getForm()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
