<?php

namespace Backpack\CRUD\app\Library\Alerts;

use Illuminate\Session\Store;
use Illuminate\Support\Traits\Macroable;

class AlertsMessageBag
{
    use Macroable;

    /** @var AlertMessage[] */
    private array $messages = [];

    public function __construct(
        private readonly Store $session,
        private readonly string $sessionKey = 'alert_messages',
    ) {
        $this->loadFromSession();
    }

    public function success(string $message): ToastMessage
    {
        return $this->pushAndReturn($this->add(AlertType::Success, $message));
    }

    public function error(string $message): ToastMessage
    {
        return $this->pushAndReturn($this->add(AlertType::Error, $message));
    }

    public function warning(string $message): ToastMessage
    {
        return $this->pushAndReturn($this->add(AlertType::Warning, $message));
    }

    public function info(string $message): ToastMessage
    {
        return $this->pushAndReturn($this->add(AlertType::Info, $message));
    }

    public function add(string|AlertType $type, string $message): ToastMessage
    {
        if (is_string($type)) {
            $type = AlertType::from($type);
        }

        return new ToastMessage(
            bag: $this,
            type: $type,
            message: $message,
        );
    }

    public function confirm(string $title): ModalMessage
    {
        return $this->pushAndReturn(new ModalMessage(
            bag: $this,
            action: ModalAction::Confirm,
            title: $title,
        ));
    }

    private function pushAndReturn(AlertMessage $message): AlertMessage
    {
        $this->messages[] = $message;
        return $message;
    }

    public function push(AlertMessage $message): void
    {
        $this->messages[] = $message;
    }

    public function flashSession(): void
    {
        $this->session->flash($this->sessionKey, $this->getMessages());
    }

    public function flash(): void
    {
        $this->flashSession();
    }

    /** @return array[] — JSON-serializable payload for frontend */
    public function getMessages(): array
    {
        return array_map(fn (AlertMessage $m) => $m->jsonSerialize(), $this->messages);
    }

    public function flush(bool $withSession = true): void
    {
        $this->messages = [];
        if ($withSession) {
            $this->session->forget($this->sessionKey);
        }
    }

    public function has(): bool
    {
        return count($this->messages) > 0;
    }

    public function count(): int
    {
        return count($this->messages);
    }

    private function loadFromSession(): void
    {
        $stored = $this->session->get($this->sessionKey, []);

        if (! empty($stored)) {
            $this->messages = array_map(
                fn (array $data) => $this->hydrateMessage($data),
                $stored,
            );
        }
    }

    private function hydrateMessage(array $data): AlertMessage
    {
        $mode = $data['mode'] ?? 'toast';

        if ($mode === 'modal') {
            $msg = new ModalMessage(
                bag: $this,
                action: ModalAction::from($data['action']),
                title: $data['title'] ?? '',
            );
            $msg->text($data['text'] ?? '');
            $msg->confirmText($data['confirmText'] ?? '');
            $msg->cancelText($data['cancelText'] ?? '');
            $msg->variant($data['variant'] ?? 'primary');
            $msg->icon($data['icon'] ?? '');
        } else {
            $msg = new ToastMessage(
                bag: $this,
                type: AlertType::from($data['type']),
                message: $data['message'] ?? '',
            );
            $msg->timeout($data['timeout'] ?? 2500);
            $msg->dismissible($data['dismissible'] ?? true);
        }

        $msg->title($data['title'] ?? '');
        $msg->icon($data['icon'] ?? '');

        return $msg;
    }
}
