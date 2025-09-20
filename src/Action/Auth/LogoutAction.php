<?php

declare(strict_types=1);

namespace App\Action\Auth;

use App\Action\AbstractAction;
use App\Session;
use Psr\Http\Message\ResponseInterface;

final class LogoutAction extends AbstractAction
{
    public function handleAction(): ResponseInterface
    {
        Session::destroySession();

        return $this->redirect("auth.login");
    }
}
