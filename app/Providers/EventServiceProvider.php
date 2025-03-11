<?php

namespace App\Providers;

use App\Models\ClientParty;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(BuildingMenu::class, function (BuildingMenu $event) {

            // todo: if auth has can approve my office
            $count = ClientParty::where('Status', 'PendingManagerApproval')
                                ->orwhere('Status', 'Submitted')->get()->Count();

            //todo: if auth has can approve for all
            // $count = ClientParty::where('Status', 'PendingFinanceApproval')->get()->Count();


            $event->menu->addIn('client_party', [
                'text' => 'Approve CP',
                'url'  => 'client-party/approve',
                'icon' => 'fas fa-fw fa-list',
                'classes' => '',
                'active' => ['client-party/approve'],
                'can'  => 'approve-entity-request',
                'label'       => $count,
                'label_color' => ($count >= 1) ? 'warning' : 'success',
            ]);
        });
    }
}
