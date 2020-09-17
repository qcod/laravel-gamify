<?php

namespace QCod\Gamify;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class BadgeType
{
    /**
     * @var Model
     */
    protected $model;

    
    /**
     * @var Array
     */
    public $_extraVariablesForQualify;

    /**
     * BadgeType constructor.
     */
    public function __construct(...$extraVariablesForQualify)
    {
        $this->_extraVariablesForQualify = $extraVariablesForQualify;
        $this->model = $this->storeBadge();
    }

    /**
     * Check if user qualifies for this badge
     *
     * @param $user
     * @return bool
     */
    abstract public function islevelArchived($user);

    /**
     * Get name of badge
     *
     * @return string
     */
    public function getName()
    {
        return property_exists($this, 'name')
            ? $this->name
            : $this->getDefaultBadgeName();
    }

    /**
     * Get description of badge
     *
     * @return string
     */
    public function getDescription()
    {
        return isset($this->description)
            ? $this->description
            : '';
    }

    /**
     * Get the icon for badge
     *
     * @return string
     */
    public function getIcon()
    {
        return property_exists($this, 'icon')
            ? $this->icon
            : $this->getDefaultIcon();
    }


    /**
     * Get the model for badge
     *
     * @return Model
     */
    public function getModel($level = null)
    {
        if ($level) {
            return $this->model[$level];
        }
        return $this->model;
    }

    /**
     * Get the level for badge
     *
     * @return int
     */
    public function getLevel()
    {
        $level = property_exists($this, 'level')
            ? $this->level
            : config('gamify.badge_default_level', 1);

        if (is_numeric($level)) {
            return $level;
        }

        return Arr::get(
            config('gamify.badge_levels', []),
            $level,
            config('gamify.badge_default_level', 1)
        );
    }

    /**
     * Get badge id
     *
     * @return mixed
     */
    public function getBadgeId($level = false)
    {
        if (!$level) {
            $level = $this->getLevel();
        }
        return $this->model[$level]->getKey();
    }

    /**
     * Get the default name if not provided
     *
     * @return string
     */
    protected function getDefaultBadgeName()
    {
        return ucwords(Str::snake(class_basename($this), ' '));
    }

    /**
     * Get the default icon if not provided
     *
     * @return string
     */
    protected function getDefaultIcon()
    {
        return sprintf(
            '%s/%s%s',
            rtrim(config('gamify.badge_icon_folder', 'images/badges'), '/'),
            Str::kebab(class_basename($this)),
            config('gamify.badge_icon_extension', '.svg')
        );
    }

    /**
     * Store or update badge
     *
     * @return mixed
     */
    protected function storeBadge()
    {
        $badgeName = get_class($this);
        
        return cache()->tags('laravel-gamify')->rememberForever('gamify.badges.' . $badgeName, function () {
            $levels = (property_exists($this, 'levels')) ? $this->levels : [$this->getLevel() => $this->getDescription()];
            $return = [];
            foreach ($levels as $levelKey => $levelDescription) {
                $badge = app(config('gamify.badge_model'))
            ->firstOrNew(['name' => $this->getName(), 'level' => $levelKey])
            ->forceFill([
                'level' => $levelKey,
                'description' => $levelDescription,
                'icon' => $this->getIcon()
            ]);

                $badge->save();
                $return[$levelKey] = $badge;
            }
            return $return;
        });
    }
}
