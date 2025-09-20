<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Action\AbstractAction;
use App\Repository\UserRepository;
use App\Validator\Assert\EmailAssert;
use App\Validator\Assert\NotEmptyAssert;
use App\Validator\Assert\PasswordMatchAssert;
use App\Validator\Validator;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class RegisterAction extends AbstractAction
{
    public function __construct(
        private readonly Validator $validator,
        private readonly UserRepository $userRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        if ($this->request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->render("auth/register/index.html.twig");
        }

        $body = $this->request->getParsedBody();

        $this->validator
            ->assertField('username', [new NotEmptyAssert()])
            ->assertField('email', [new NotEmptyAssert(), new EmailAssert()])
            ->assertField('password', [new NotEmptyAssert()])
            ->assertField('repeat_password', [new NotEmptyAssert(), new PasswordMatchAssert($body['password'])])
        ;

        if (!$this->validator->valid($body)) {
            return $this->getForm();
        }

        if ($this->userRepository->getUserByEmail($body['email'])) {
            $this->validator->addError('email', 'This email is already used.');
            return $this->getForm();
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

    private function getForm(): ResponseInterface
    {
        return $this->render("auth/register/form.html.twig", [
            "data" => $this->validator->getData()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
