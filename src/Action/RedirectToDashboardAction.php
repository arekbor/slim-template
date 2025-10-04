<?php

declare(strict_types=1);

namespace App\Action;

use Psr\Http\Message\ResponseInterface;

final class RedirectToDashboardAction extends AbstractAction
{
    public function handleAction(): ResponseInterface
    {
        return $this->redirect('dashboard');
    }
}
