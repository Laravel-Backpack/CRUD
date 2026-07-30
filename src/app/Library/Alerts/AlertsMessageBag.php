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
        return $this->add(AlertType::Success, $message);
    }

    public function error(string $message): ToastMessage
    {
        return $this->add(AlertType::Error, $message);
    }

    public function warning(string $message): ToastMessage
    {
        return $this->add(AlertType::Warning, $message);
    }

    public function info(string $message): ToastMessage
    {
        return $this->add(AlertType::Info, $message);
    }

    // ── Modal / Dialog messages ──

    public function dialog(?string $title = null, ?string $text = null): ModalMessage
    {
        $modal = new ModalMessage(
            bag: $this,
            type: AlertType::Info,
            text: $text,
        );
        if ($title !== null) {
            $modal->title($title);
        }
        return $this->pushAndReturn($modal);
    }

    public function dialogSuccess(string $text, ?string $title = null): ModalMessage
    {
        return $this->dialogOfType(AlertType::Success, $text, $title);
    }

    public function dialogError(string $text, ?string $title = null): ModalMessage
    {
        return $this->dialogOfType(AlertType::Error, $text, $title);
    }

    public function dialogWarning(string $text, ?string $title = null): ModalMessage
    {
        return $this->dialogOfType(AlertType::Warning, $text, $title);
    }

    public function dialogInfo(string $text, ?string $title = null): ModalMessage
    {
        return $this->dialogOfType(AlertType::Info, $text, $title);
    }

    private function dialogOfType(AlertType $type, string $text, ?string $title = null): ModalMessage
    {
        $modal = new ModalMessage(
            bag: $this,
            type: $type,
            text: $text,
        );
        if ($title !== null) {
            $modal->title($title);
        }
        return $this->pushAndReturn($modal);
    }

    public function add(string|AlertType $type, string $message): ToastMessage
    {
        if (is_string($type)) {
            $type = AlertType::from($type);
        }

        return $this->pushAndReturn(new ToastMessage(
            bag: $this,
            type: $type,
            message: $message,
        ));
    }

    private function pushAndReturn(AlertMessage $message): AlertMessage
    {
        $this->messages[] = $message;
        return $message;
    }

    public function flashSession(): void
    {
        $flat = array_map(fn (AlertMessage $m) => $m->jsonSerialize(), $this->messages);
        $this->session->flash($this->sessionKey, $flat);
    }

    public function flash(): void
    {
        $this->flashSession();
    }

    /** @return array — JSON-serializable toast messages, grouped by type. */
    public function getMessages(): array
    {
        $grouped = [];

        foreach ($this->messages as $message) {
            if (! $message instanceof ToastMessage) {
                continue;
            }

            $key = $message->type()->value;
            $grouped[$key][] = $message->jsonSerialize();
        }

        return $grouped;
    }

    /** @return array — JSON-serializable modal messages. */
    public function getModals(): array
    {
        return array_values(array_map(
            fn (AlertMessage $m) => $m->jsonSerialize(),
            array_filter($this->messages, fn (AlertMessage $m) => $m instanceof ModalMessage),
        ));
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
                type: AlertType::from($data['icon'] ?? $data['type'] ?? 'info'),
                text: $data['text'] ?? '',
            );
            $msg->title($data['title'] ?? '');
            $msg->timer($data['timer'] ?? null);
            $msg->button($data['button'] ?? 'OK');
            $msg->backdrop($data['closeOnClickOutside'] ?? true);
            $msg->escapeKey($data['closeOnEsc'] ?? true);
            $msg->className($data['className'] ?? null);
            $msg->showCloseButton($data['showCloseButton'] ?? false);
            return $msg;
        }

        $msg = new ToastMessage(
            bag: $this,
            type: AlertType::from($data['type']),
            message: $data['message'] ?? '',
        );
        $msg->timeout($data['timeout'] ?? 2500);
        $msg->dismissible($data['dismissible'] ?? true);
        $msg->title($data['title'] ?? '');
        $msg->icon($data['icon'] ?? '');

        return $msg;
    }
}
