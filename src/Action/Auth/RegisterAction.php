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
            ->addField('username', [new NotEmptyAssert()])
            ->addField('email', [new NotEmptyAssert(), new EmailAssert()])
            ->addField('password', [new NotEmptyAssert()])
            ->addField('repeat_password', [new NotEmptyAssert(), new PasswordMatchAssert($body['password'])])
        ;

        if (!$this->formValidator->valid($body)) {
            return $this->getInvalidForm();
        }

        if ($this->userRepository->getUserByEmail($body['email'])) {
            //TODO: add flash message "This email is already used."
            return $this->getInvalidForm();
        }

        $hashedPassword = password_hash($body['password'], PASSWORD_BCRYPT);

        $isUserCreated = $this->userRepository->createUser([
            'username' => $body['username'],
            'email' => $body['email'],
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
            "fields" => $this->formValidator->getFields()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
