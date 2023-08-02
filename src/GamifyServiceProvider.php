<?php

namespace QCod\Gamify;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use QCod\Gamify\Listeners\SyncBadges;
use Illuminate\Support\ServiceProvider;
use QCod\Gamify\Console\MakeBadgeCommand;
use QCod\Gamify\Console\MakePointCommand;
use QCod\Gamify\Events\ReputationChanged;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GamifyServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package->name('gamify')
            ->hasConfigFile()
            ->hasMigrations([
                'add_reputation_on_user_table',
                'create_gamify_tables',
            ])
            ->hasCommands([
                MakePointCommand::class,
                MakeBadgeCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        Event::listen(ReputationChanged::class, SyncBadges::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton('badges', function () {
            return cache()->rememberForever('gamify.badges.all', function () {
                return $this->getBadges()->map(fn($badge) => new $badge);
            });
        });
    }

    /**
     * Get all the badge inside app/Gamify/Badges folder
     *
     * @return Collection<int, class-string>
     */
    protected function getBadges(): Collection
    {
        $badgeRootNamespace = config(
            'gamify.badge_namespace',
            $this->app->getNamespace() . 'Gamify\Badges'
        );

        $badges = [];

        foreach (glob(app_path('/Gamify/Badges/') . '*.php') as $file) {
            if (is_file($file)) {
                $badges[] = app($badgeRootNamespace . '\\' . pathinfo($file, PATHINFO_FILENAME));
            }
        }

        return collect($badges);
    }
}
