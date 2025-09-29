<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Action\AbstractAction;
use App\Repository\UserRepository;
use App\Validator\Assert\EmailAssert;
use App\Validator\Assert\NotEmptyAssert;
use App\Validator\FormValidator;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class LoginAction extends AbstractAction
{
    public function __construct(
        private readonly FormValidator $formValidator,
        private readonly UserRepository $userRepository
    ) {}

    public function handleAction(): ResponseInterface
    {
        if ($this->request->getMethod() === RequestMethodInterface::METHOD_GET) {
            return $this->render("auth/login/index.html.twig");
        }

        $this->formValidator
            ->addField('email', [new NotEmptyAssert(), new EmailAssert()])
            ->addField('password', [new NotEmptyAssert()]);

        $body = $this->request->getParsedBody();

        if (!$this->formValidator->valid($body)) {
            return $this->getInvalidForm();
        }

        $user = $this->userRepository->getUserByEmail($body['email']);
        if (!$user || !password_verify($body['password'], $user['password'])) {
            $this->formValidator->addFormError("Invalid email or password.");
            return $this->getInvalidForm();
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'logged_at' => time()
        ];

        return $this->hxRedirect("home");
    }

    private function getInvalidForm(): ResponseInterface
    {
        return $this->render("auth/login/form.html.twig", [
            "form" => $this->formValidator->getForm()
        ], StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
    }
}
