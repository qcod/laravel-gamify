<?php

return [
    // Model which will be having points, generally it will be User
    'payee_model' => '\App\User',

    // Reputation model
    'reputation_model' => '\QCod\Gamify\Reputation',

    // Allow duplicate reputation points
    'allow_reputation_duplicate' => true,

    // Broadcast on private channel
    'broadcast_on_private_channel' => true,

    // Channel name prefix, user id will be suffixed
    'channel_name' => 'user.reputation.',

    // Badge model
    'badge_model' => '\QCod\Gamify\Badge',

    // Where all badges icon stored
    'badge_icon_folder' => 'images/badges/',

    // Extention of badge icons
    'badge_icon_extension' => '.svg',

    // All the levels for badge
    'badge_levels' => [
        'beginner' => 1,
        'intermediate' => 2,
        'advanced' => 3,
    ],

    // Default level
    'badge_default_level' => 1
];
