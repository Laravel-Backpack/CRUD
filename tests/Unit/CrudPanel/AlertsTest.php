<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\app\Library\Alerts\Alert;
use Backpack\CRUD\app\Library\Alerts\AlertType;
use Backpack\CRUD\app\Library\Alerts\AlertsMessageBag;
use Backpack\CRUD\app\Library\Alerts\ModalMessage;
use Backpack\CRUD\app\Library\Alerts\ToastMessage;
use Backpack\CRUD\Tests\BaseTestClass;

class AlertsTest extends BaseTestClass
{
    protected function setUp(): void
    {
        parent::setUp();
        Alert::flush();
    }

    public function test_alert_type_enum_has_four_cases()
    {
        $cases = AlertType::cases();

        $this->assertCount(4, $cases);
        $this->assertEquals('success', AlertType::Success->value);
        $this->assertEquals('error', AlertType::Error->value);
        $this->assertEquals('warning', AlertType::Warning->value);
        $this->assertEquals('info', AlertType::Info->value);
    }

    public function test_alert_type_from_string_works()
    {
        $this->assertEquals(AlertType::Success, AlertType::from('success'));
        $this->assertEquals(AlertType::Error, AlertType::from('error'));
    }

    public function test_alert_type_from_invalid_string_throws()
    {
        $this->expectException(\ValueError::class);
        AlertType::from('invalid');
    }

    public function test_toast_message_serializes_correctly()
    {
        $bag = app('alerts');

        $msg = new ToastMessage($bag, AlertType::Success, 'Item saved.');

        $expected = [
            'mode' => 'toast',
            'type' => 'success',
            'message' => 'Item saved.',
            'title' => '',
            'icon' => null,
            'timeout' => 2500,
            'dismissible' => true,
            'className' => null,
            'position' => null,
        ];

        $this->assertEquals($expected, $msg->jsonSerialize());
    }

    public function test_toast_message_modifiers_update_properties()
    {
        $bag = app('alerts');

        $msg = new ToastMessage($bag, AlertType::Error, 'Failed.');
        $msg->title('Oops')->icon('la la-times-circle')->timeout(0)->dismissible(false);

        $data = $msg->jsonSerialize();

        $this->assertEquals('Oops', $data['title']);
        $this->assertEquals('la la-times-circle', $data['icon']);
        $this->assertEquals(0, $data['timeout']);
        $this->assertFalse($data['dismissible']);
    }

    public function test_alert_success_creates_toast_and_returns_it()
    {
        $msg = Alert::success('Saved.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Success, $msg->type());
    }

    public function test_alert_error_creates_toast()
    {
        $msg = Alert::error('Failed.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Error, $msg->type());
    }

    public function test_alert_warning_creates_toast()
    {
        $msg = Alert::warning('Careful.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Warning, $msg->type());
    }

    public function test_alert_info_creates_toast()
    {
        $msg = Alert::info('Note.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Info, $msg->type());
    }

    public function test_alert_add_with_string_type_works()
    {
        $msg = Alert::add('success', 'Saved.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Success, $msg->type());
    }

    public function test_alert_add_with_enum_type_works()
    {
        $msg = Alert::add(AlertType::Error, 'Failed.');

        $this->assertInstanceOf(ToastMessage::class, $msg);
        $this->assertEquals(AlertType::Error, $msg->type());
    }

    public function test_alert_add_with_invalid_type_throws()
    {
        $this->expectException(\ValueError::class);
        Alert::add('invalid', 'msg');
    }

    public function test_get_messages_returns_grouped_format()
    {
        Alert::success('A')->flash();
        Alert::error('B')->flash();
        Alert::info('C')->flash();

        $messages = Alert::getMessages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('success', $messages);
        $this->assertArrayHasKey('error', $messages);
        $this->assertArrayHasKey('info', $messages);
        $this->assertCount(1, $messages['success']);
        $this->assertCount(1, $messages['error']);
        $this->assertCount(1, $messages['info']);
    }

    public function test_get_messages_returns_objects_not_strings()
    {
        Alert::success('Saved.')->flash();

        $messages = Alert::getMessages();

        $this->assertIsArray($messages['success'][0]);
        $this->assertEquals('Saved.', $messages['success'][0]['message']);
    }

    public function test_messages_are_grouped_by_type()
    {
        Alert::success('A')->flash();
        Alert::success('B')->flash();
        Alert::error('C')->flash();

        $messages = Alert::getMessages();

        $this->assertCount(2, $messages['success']);
        $this->assertCount(1, $messages['error']);
    }

    public function test_chaining_multiple_messages_works()
    {
        Alert::success('A')->flash()->error('B')->flash();

        $messages = Alert::getMessages();

        $this->assertCount(1, $messages['success']);
        $this->assertCount(1, $messages['error']);
    }

    public function test_modifiers_work_before_flash()
    {
        Alert::success('Saved.')->title('Success')->icon('la la-check')->timeout(3000)->flash();

        $msg = Alert::getMessages()['success'][0];

        $this->assertEquals('Success', $msg['title']);
        $this->assertEquals('la la-check', $msg['icon']);
        $this->assertEquals(3000, $msg['timeout']);
    }

    public function test_flush_clears_all_messages()
    {
        Alert::success('A')->flash();
        Alert::error('B')->flash();
        $this->assertEquals(2, Alert::count());

        Alert::flush();

        $this->assertEquals(0, Alert::count());
        $this->assertFalse(Alert::has());
    }

    public function test_has_returns_true_when_messages_exist()
    {
        $this->assertFalse(Alert::has());

        Alert::success('A')->flash();

        $this->assertTrue(Alert::has());
    }

    public function test_count_returns_correct_number()
    {
        $this->assertEquals(0, Alert::count());

        Alert::success('A')->flash();
        Alert::error('B')->flash();
        Alert::warning('C')->flash();

        $this->assertEquals(3, Alert::count());
    }

    public function test_macro_registers_custom_alert_type()
    {
        Alert::macro('notice', function (string $message) {
            return $this->add(AlertType::from('info'), $message)->icon('la la-info-circle');
        });

        $msg = Alert::notice('Reminder')->flash();

        $this->assertInstanceOf(AlertsMessageBag::class, $msg);

        $data = Alert::getMessages()['info'][0];
        $this->assertEquals('Reminder', $data['message']);
        $this->assertEquals('la la-info-circle', $data['icon']);
    }

    public function test_session_round_trip_preserves_data()
    {
        Alert::success('Saved.')->title('Success')->timeout(3000)->flash();
        Alert::error('Failed.')->icon('la la-times')->flash();

        $session = app('session.store');
        $bag = new AlertsMessageBag($session, 'alert_messages');

        $messages = $bag->getMessages();

        $this->assertArrayHasKey('success', $messages);
        $this->assertArrayHasKey('error', $messages);
        $this->assertEquals('Saved.', $messages['success'][0]['message']);
        $this->assertEquals('Success', $messages['success'][0]['title']);
        $this->assertEquals(3000, $messages['success'][0]['timeout']);
        $this->assertEquals('Failed.', $messages['error'][0]['message']);
        $this->assertEquals('la la-times', $messages['error'][0]['icon']);
    }

    public function test_messages_without_flash_are_still_in_bag()
    {
        Alert::warning('This works without flash().');

        $this->assertEquals(1, Alert::count());
        $this->assertTrue(Alert::has());
    }

    public function test_class_name_modifier_is_included_in_serialization()
    {
        $bag = app('alerts');
        $msg = new ToastMessage($bag, AlertType::Success, 'Saved.');
        $msg->className('text-bg-purple');

        $data = $msg->jsonSerialize();

        $this->assertEquals('text-bg-purple', $data['className']);
    }

    public function test_macro_with_custom_class_name_includes_it_in_payload()
    {
        Alert::macro('audit', function (string $message) {
            return $this->add(AlertType::Info, $message)
                ->icon('la la-shield-check')
                ->className('text-bg-purple');
        });

        Alert::audit('Audit log updated.')->flash();

        $data = Alert::getMessages()['info'][0];

        $this->assertEquals('Audit log updated.', $data['message']);
        $this->assertEquals('la la-shield-check', $data['icon']);
        $this->assertEquals('text-bg-purple', $data['className']);
    }

    public function test_normal_toast_has_null_class_name()
    {
        Alert::success('Plain toast.')->flash();

        $data = Alert::getMessages()['success'][0];

        $this->assertArrayHasKey('className', $data);
        $this->assertNull($data['className']);
    }

    public function test_position_modifier_is_included_in_serialization()
    {
        $bag = app('alerts');
        $msg = new ToastMessage($bag, AlertType::Success, 'Saved.');
        $msg->position('bottomLeft');

        $data = $msg->jsonSerialize();

        $this->assertEquals('bottomLeft', $data['position']);
    }

    public function test_normal_toast_has_null_position()
    {
        Alert::success('Plain toast.')->flash();

        $data = Alert::getMessages()['success'][0];

        $this->assertArrayHasKey('position', $data);
        $this->assertNull($data['position']);
    }

    public function test_flash_returns_bag_for_chaining()
    {
        $result = Alert::success('A')->flash();

        $this->assertInstanceOf(AlertsMessageBag::class, $result);
    }

    public function test_modal_message_serializes_correctly()
    {
        $bag = app('alerts');

        $msg = new ModalMessage($bag, AlertType::Success, 'Profile updated.');

        $expected = [
            'mode' => 'modal',
            'title' => '',
            'text' => 'Profile updated.',
            'icon' => 'success',
            'timer' => null,
            'button' => 'OK',
            'closeOnClickOutside' => true,
            'closeOnEsc' => true,
            'className' => null,
            'showCloseButton' => false,
        ];

        $this->assertEquals($expected, $msg->jsonSerialize());
    }

    public function test_modal_message_defaults_have_confirm_button()
    {
        $bag = app('alerts');
        $msg = new ModalMessage($bag, AlertType::Info, 'FYI.');

        $data = $msg->jsonSerialize();

        $this->assertEquals('OK', $data['button']);
    }

    public function test_modal_message_button_can_be_disabled()
    {
        $bag = app('alerts');
        $msg = new ModalMessage($bag, AlertType::Warning, 'Auto-closing...');
        $msg->button(false);

        $data = $msg->jsonSerialize();

        $this->assertNull($data['button']);
    }

    public function test_modal_message_button_can_be_customized()
    {
        $bag = app('alerts');
        $msg = new ModalMessage($bag, AlertType::Success, 'Done.');
        $msg->button('Got it!');

        $data = $msg->jsonSerialize();

        $this->assertEquals('Got it!', $data['button']);
    }

    public function test_modal_message_modifiers_update_properties()
    {
        $bag = app('alerts');

        $msg = new ModalMessage($bag, AlertType::Error, 'Failed.');
        $msg->title('Oops')
            ->timer(5000)
            ->backdrop(false)
            ->escapeKey(false)
            ->className('my-custom-class')
            ->showCloseButton(true);

        $data = $msg->jsonSerialize();

        $this->assertEquals('Oops', $data['title']);
        $this->assertEquals(5000, $data['timer']);
        $this->assertFalse($data['closeOnClickOutside']);
        $this->assertFalse($data['closeOnEsc']);
        $this->assertEquals('my-custom-class', $data['className']);
        $this->assertTrue($data['showCloseButton']);
    }

    public function test_modal_message_fluent_chaining_works()
    {
        $bag = app('alerts');

        $msg = new ModalMessage($bag, AlertType::Warning, 'Careful.');
        $returned = $msg->title('Warning')->timer(3000)->backdrop(false)->showCloseButton(true);

        $this->assertSame($msg, $returned);
    }

    public function test_dialog_success_returns_modal_message()
    {
        $msg = Alert::dialogSuccess('Profile updated!');

        $this->assertInstanceOf(ModalMessage::class, $msg);

        $data = $msg->jsonSerialize();
        $this->assertEquals('success', $data['icon']);
        $this->assertEquals('Profile updated!', $data['text']);
    }

    public function test_dialog_error_returns_modal_message()
    {
        $msg = Alert::dialogError('Something failed.', 'Error');

        $this->assertInstanceOf(ModalMessage::class, $msg);

        $data = $msg->jsonSerialize();
        $this->assertEquals('error', $data['icon']);
        $this->assertEquals('Something failed.', $data['text']);
        $this->assertEquals('Error', $data['title']);
    }

    public function test_dialog_warning_returns_modal_message()
    {
        $msg = Alert::dialogWarning('Be careful.');

        $this->assertInstanceOf(ModalMessage::class, $msg);

        $data = $msg->jsonSerialize();
        $this->assertEquals('warning', $data['icon']);
    }

    public function test_dialog_info_returns_modal_message()
    {
        $msg = Alert::dialogInfo('Heads up.');

        $this->assertInstanceOf(ModalMessage::class, $msg);

        $data = $msg->jsonSerialize();
        $this->assertEquals('info', $data['icon']);
    }

    public function test_dialog_builder_sets_title_and_text()
    {
        $msg = Alert::dialog('My Title', 'My message text.');

        $data = $msg->jsonSerialize();
        $this->assertEquals('My Title', $data['title']);
        $this->assertEquals('My message text.', $data['text']);
    }

    public function test_dialog_builder_can_chain_modifiers()
    {
        $msg = Alert::dialog('Title', 'Text')
            ->timer(3000)
            ->button('Understood')
            ->backdrop(false)
            ->showCloseButton(true);

        $data = $msg->jsonSerialize();

        $this->assertEquals(3000, $data['timer']);
        $this->assertEquals('Understood', $data['button']);
        $this->assertFalse($data['closeOnClickOutside']);
        $this->assertTrue($data['showCloseButton']);
    }

    public function test_get_modals_returns_only_modal_messages()
    {
        Alert::success('Toast message.')->flash();
        Alert::dialogSuccess('Modal message.')->flash();

        $toasts = Alert::getMessages();
        $modals = Alert::getModals();

        $this->assertCount(1, $toasts['success']);
        $this->assertCount(1, $modals);
        $this->assertEquals('modal', $modals[0]['mode']);
        $this->assertEquals('Modal message.', $modals[0]['text']);
    }

    public function test_get_modals_returns_empty_array_when_no_modals()
    {
        Alert::success('Only a toast.')->flash();

        $modals = Alert::getModals();

        $this->assertIsArray($modals);
        $this->assertEmpty($modals);
    }

    public function test_modal_message_session_round_trip_preserves_data()
    {
        Alert::dialogSuccess('Profile updated!')
            ->title('Success')
            ->timer(5000)
            ->backdrop(false)
            ->showCloseButton(true)
            ->flash();

        $session = app('session.store');
        $bag = new AlertsMessageBag($session, 'alert_messages');

        $modals = $bag->getModals();

        $this->assertCount(1, $modals);
        $this->assertEquals('modal', $modals[0]['mode']);
        $this->assertEquals('Profile updated!', $modals[0]['text']);
        $this->assertEquals('Success', $modals[0]['title']);
        $this->assertEquals(5000, $modals[0]['timer']);
        $this->assertFalse($modals[0]['closeOnClickOutside']);
        $this->assertTrue($modals[0]['showCloseButton']);
    }

    public function test_toasts_and_modals_can_coexist_in_bag()
    {
        Alert::success('Toast!')->flash();
        Alert::dialogError('Modal!')->flash();

        $this->assertEquals(2, Alert::count());
        $this->assertCount(1, Alert::getModals());
        $this->assertCount(1, Alert::getMessages()['success']);
    }
}