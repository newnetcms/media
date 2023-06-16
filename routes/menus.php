<?php

use Newnet\Media\MediaAdminMenuKey;
use Newnet\Setting\SettingAdminMenuKey;

AdminMenu::addItem(__('media::module.module_name'), [
    'id' => MediaAdminMenuKey::MEDIA,
    'parent' => SettingAdminMenuKey::SYSTEM,
    'route' => 'media.admin.media.index',
    'icon' => 'fas fa-photo-video',
    'order' => 5,
]);
