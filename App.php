<?php 

require_once('Dispatcher.php');

class UserRegistered
{
    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }
}

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        // Send welcome email to $event->user
    }
}

$dispatcher = new Dispatcher();

$dispatcher->listen('user.registered', SendWelcomeEmail::class.'@handle');
$dispatcher->listen('*', function ($event) {
    // Log all events
});

$user = registerNewUser(); // Some user registration logic
$dispatcher->fire(new UserRegistered($user));