<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Action\AbstractAction;
use App\Repository\UserRepository;
use App\Session;
use App\Validator\Assert\EmailAssert;
use App\Validator\Assert\NotEmptyAssert;
use App\Validator\Validator;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class LoginAction extends AbstractAction
{
    public function __construct(
        private readonly Validator $validator,
        private readonly UserRepository $userRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        if ($this->request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->render("auth/login/index.html.twig");
        }

        $this->validator
            ->assertField('email', [new NotEmptyAssert(), new EmailAssert()])
            ->assertField('password', [new NotEmptyAssert()])
        ;

        $body = $this->request->getParsedBody();

        if (!$this->validator->valid($body)) {
            return $this->getForm();
        }

        $user = $this->userRepository->getUserByEmail($body['email']);
        if (!$user || !password_verify($body['password'], $user['password'])) {
            $this->validator->addError('password', "Invalid email or password.");
            return $this->getForm();
        }

        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('logged_in_at', time());

        return $this->hxRedirect("home");
    }

    private function getForm(): ResponseInterface
    {
        return $this->render("auth/login/form.html.twig", [
            "data" => $this->validator->getData()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
